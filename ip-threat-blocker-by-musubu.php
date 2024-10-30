<?php
/*
   Plugin Name: IP Threat Blocker by Musubu
   Plugin URI: https://wpthreat.co/
   Description: Musubu&rsquo;s IP Threat Blocker for WordPress leverages the full power of the Musubu API to dynamically screen incoming IP addresses to your website, allowing you to automatically (&ldquo;set it and forget it&rdquo;) or manually block IPs by threat score, threat class, or country of origin.
   Version: 1.1.1
   Author: Musubu
   Author URI: https://wpthreat.co/
   License: GPLv2 or later
   Text Domain: ip-threat-blocker-by-musubu
 */

if (defined('WP_INSTALLING') && WP_INSTALLING) {
  return;
}
if (!defined('ABSPATH')) {
  exit;
}

###SET PLUGIN VERSION FOR MANAGING UPGRADES/UPDATES TO PLUGIN########
###UPGRADE THIS VERSION NUMBER TO UPDATE THE PLUGIN##################
define('IPTBBM_IP_BLOCKER_VERSION', '1.1.1');

$apiKeyDefaultIptbbm = false;

$dbMusubuApiKeyIptbbm = get_option("iptbbm_api_key", $apiKeyDefaultIptbbm);
define('IPTBBM_API_KEY', $dbMusubuApiKeyIptbbm);

###CONTAINS COMMON FUNCTIONS REQUIRED IN BOTH THE MAIN PLUGIN ENTRY FILE AND iptable.php###
require_once(dirname(__FILE__) . '/inc/functions.php');
require_once(dirname(__FILE__) . '/inc/libraries/admin-notices/Notices.php');
require_once(dirname(__FILE__) . '/inc/libraries/admin-notices/Notice.php');
require_once(dirname(__FILE__) . '/inc/libraries/admin-notices/Dismiss.php');

/**
 * Check for API key and thus if valid API key query the API and process requests
 * @global type $wpdb
 */
function iptbbm_init()
{
  ##GET THE WPDB CONNECTION##
  global $wpdb;
  $table_name = $wpdb->prefix . 'iptable';

  ####READ FROM DB TABLE FOR musubu_ip_blocks AND BLOCK ANY ENTRY THAT HAS BEEN BLOCKED#################STARTS######################

  /**
   * Default automatic : if threat score > 90, manual depends on threat score, blackist class & country. If these criteria matches than we insert and block that IP.
   * Only RUN IP CHECK IF API KEY IS SAVED IN PLUGIN OPTIONS
   */

  if (IPTBBM_API_KEY == false) {
    $iptbbm_options_page  = get_permalink() . '/wp-admin/options-general.php?page=iptbbm-options';
    $iptbbm_registration_page = 'https://musubuapp.co/my-account/3';
    $my_theme_notices = new \WPTRT\AdminNotices\Notices();
    $my_theme_notices->add("iptbbm_freeregistration", "Free Registration", '<strong>One more step to secure your website:</strong>
      <p>Please visit <a href="' . $iptbbm_registration_page . '" target="_blank">Musubuapp.co</a> to copy your free license and paste it into your IP Threat Installation ' .
      '<a href="' . $iptbbm_options_page . '">IP Threat Settings</a> </p>');

    $my_theme_notices->boot();
  }

  if (IPTBBM_API_KEY !== false) {
    #add_option($option, $value); ### USE IT TO ADD PLUGIN MANUAL/AUTOMATIC PREFERENCE
    $currentIp = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);

    if ($currentIp === false || strpos($currentIp, "::") !== false) {
      $currentIp = '198.0.0.1';
    }

    $currentIpDbCount = $wpdb->get_var("SELECT count(*) FROM $table_name WHERE  unblocked = 0 AND ip = '$currentIp'");

    if (get_option('iptbbm_splunk_token', '') != '') {
      $row_data = $wpdb->get_row("SELECT ip, threat_score, blacklist_class, country_code, date_added, unblocked FROM $table_name WHERE ip = '$currentIp'");
      if ($row_data != null) {
        $post_return = wp_remote_post(get_option('iptbbm_splunk_url', 'localhost'), array(
          'body' => iptbbm_splunk_json($row_data),
          'headers' => array(
            'Authorization' => 'Splunk ' . get_option('iptbbm_splunk_token',''),
          )
        ));
      }
    }

    if ($currentIpDbCount > 0) {
      #IP BANNED SO BLOCK ACCESS
      iptbbm_blockAccess();
    } else { ###CALL MUSUBU API AS THIS DATA IS NOT IN OUR DATABASE
      $currentIpUnblockedDbCount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE unblocked = 1 AND ip = '$currentIp'");
      if ($currentIpUnblockedDbCount == 0) {
        $response = wp_remote_get("https://api.musubu.co/MusubuAPI/Musubu?IP=$currentIp&key=" . IPTBBM_API_KEY . "&format=JSON&level=verbose");##https://developer.wordpress.org/plugins/http-api/
        $httpCode = wp_remote_retrieve_response_code($response);
        $apiBodyJson = wp_remote_retrieve_body($response);

        $apiResponse = json_decode(utf8_encode($apiBodyJson), true);###API RETURNS JSON DATA SO WE DECODE IT##############

        if ($httpCode == 200 && empty($apiResponse['error'])) {
          ###AUTOMATIC BLOCK AT threat score > 80###
          $scanMod = get_option('iptbbm_scan_mod', 'auto');#auto/manual
          if (($scanMod == 'auto' || $scanMod == 'evaluation') && $apiResponse['threat_potential_score_pct'] > 80) {
            iptbbm_insertIpData($apiResponse);
            iptbbm_blockAccess();
          } else { ###MANUAL BLOCK BASED ON THREAT SCORE, BLACKLIST_CLASS AND COUNTRY CODE###
            $dbThreatScore = get_option('iptbbm_threat_score', 0);
            $dbBlacklistClass = get_option('iptbbm_blacklist_class', array());
            $dbCountryCode = get_option('iptbbm_country_code', array());

            ###IF 'all' FOUND FOR BOTH BLACKLIST CLASS AND COUNTRY CODE MULTI SELECT OPTIONS THAN ASSIGN ALL VALUES AND COMPARE###
            if (in_array('all', $dbBlacklistClass)) {
              $dbBlacklistClass = iptbbm_getBlackListClassValues();
            }
            if (in_array('all', $dbCountryCode)) {
              $dbCountryCode = array_keys(iptbbm_getCountryCodesWithCountryNames());
            }

            if ($apiResponse['threat_potential_score_pct'] >= $dbThreatScore &&
              (in_array($apiResponse['blacklist_class'], $dbBlacklistClass) &&
              (in_array($apiResponse['country'], $dbCountryCode)))) {
              iptbbm_insertIpData($apiResponse);
              iptbbm_blockAccess();
            }
          }
        }
      }
    }
  }
}

function iptbbm_splunk_json($sqlRow)
{
  return json_encode( array( "event" => array( 
    'ipaddress' => $sqlRow->ip,
    'threat_potential_score_pct' => $sqlRow->threat_score,
    'blacklist_class' => $sqlRow->blacklist_class,
    'country' => $sqlRow->country_code,
    'date_added' => $sqlRow->date_added,
    'unblocked' => $sqlRow->unblocked == 1,
  )));
}

function iptbbm_insertIpData($apiResponse)
{
  global $wpdb;
  $table_name = $wpdb->prefix . 'iptable';

  $insertColumnData = array(
    'ip' => $apiResponse['ipaddress'],
    'threat_score' => $apiResponse['threat_potential_score_pct'],
    'blacklist_class' => $apiResponse['blacklist_class'],
    'country_code' => $apiResponse['country'],
  );

  //THIS NEEDS TO HAVE ALL THE COLUMN NAMES WHICH NEEDS TO BE INSERTED
  $defaultInsertColumnValues = array(
    'ip' => 0,
    'threat_score' => '',
    'blacklist_class' => '',
    'country_code' => '',
    'unblocked' => 0,
    'date_added' => current_time('mysql', 1),
  );

  $item = shortcode_atts($defaultInsertColumnValues, $insertColumnData);

  $wpdb->insert($table_name, $item);
}

function iptbbm_blockAccess()
{
  if (get_option('iptbbm_evaluation', '') != 'evaluation') {
    ##########DO NOT CACHE HEADERS#############STARTS#########################
    header("Pragma: no-cache");
    header("Cache-Control: no-cache, must-revalidate, private");
    header("Expires: Sat, 27 Mar 1998 05:00:00 GMT"); //In the past
    ##########DO NOT CACHE HEADERS#############ENDS###########################

    header('HTTP/1.1 503 Service Temporarily Unavailable');
    header('Status: 503 Service Temporarily Unavailable');
    echo "<p><b>Your IP has been blocked due to high threat level.</b></p>";
    exit();
  }
}
####READ FROM DB TABLE FOR musubu_ip_blocks AND BLOCK ANY ENTRY THAT HAS BEEN BLOCKED#################ENDS########################



############HANDLE PLUGIN INSTALL AND INITIAL DB TABLE CREATE AND INSERT#############################STARTS##############################

function iptbbm_install()
{
  global $wpdb;

  $table_name = $wpdb->prefix . 'iptable';

  $charset_collate = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE $table_name (
    iptable_id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
    ip VARCHAR(15) NOT NULL,
    threat_score INT(3) DEFAULT '0' NOT NULL,
    blacklist_class VARCHAR(25) NOT NULL,
    country_code VARCHAR(2) NOT NULL,
    remark TEXT,
    unblocked TINYINT(1) DEFAULT '0' NOT NULL COMMENT 'if 0 then blocked',
    date_added DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
    PRIMARY KEY  (iptable_id)
  ) $charset_collate";



  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);

  add_option('iptbbm_db_version', IPTBBM_IP_BLOCKER_VERSION);
}

register_activation_hook(__FILE__, 'iptbbm_install');

############HANDLE PLUGIN INSTALL AND INITIAL DB TABLE CREATE AND INSERT#############################ENDS################################
############HANDLE UPGRADE OF PLUGIN DB STRUCTURE ON PLUGIN UPGRADE#############################STARTS##############################
global $wpdb;

$installed_ver_iptbbm = get_option("iptbbm_db_version");

if ($installed_ver_iptbbm != IPTBBM_IP_BLOCKER_VERSION) {
  $table_name_iptbbm = $wpdb->prefix . 'iptable';

  $sql = '';

  ###UPGRADE SQL STATEMENT###
  //    $sql = "ALTER TABLE $table_name_iptbbm
  //                CHANGE `blacklist_class` `blacklist_class` TEXT NOT NULL,
  //                CHANGE `country_code` `country_code` TEXT NOT NULL;
  //        ";

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);

  update_option("iptbbm_db_version", IPTBBM_IP_BLOCKER_VERSION);
}

/**
 * Since 3.1 the activation function registered with register_activation_hook() is not called when a plugin is updated.
 * So to run the above code after the plugin is upgraded, you need to check the plugin db version on another hook, and
 * call the function manually if the the database version is old.
 * Below function does that job
 * @global string IPTBBM_IP_BLOCKER_VERSION
 */
function iptbbm_update_db_check()
{
  if (get_site_option('iptbbm_db_version') != IPTBBM_IP_BLOCKER_VERSION) {
    iptbbm_install();
  }
}

add_action('plugins_loaded', 'iptbbm_update_db_check');

############HANDLE UPGRADE OF PLUGIN DB STRUCTURE ON PLUGIN UPGRADE#############################ENDS################################


add_action('admin_enqueue_scripts', 'iptbbm_enqueue');
function iptbbm_enqueue($hook)
{
  if ('settings_page_iptbbm-options' != $hook) {
    return;
  }
  wp_enqueue_script(
    'iptbbm_select2-js',
    plugin_dir_url(__FILE__) . '/inc/select2/select2.min.js',
    array( 'jquery' )
  );

  wp_enqueue_style('iptbbm_select2', plugin_dir_url(__FILE__) . '/inc/select2/select2.min.css');
}


#INITIALIZE THE PLUGIN FUNCTIONALITY
iptbbm_init();

require_once(dirname(__FILE__) . '/inc/iptable.php');



#############REGISTER PLUGIN MENU IN ADMIN##########################STARTS###############################################################
/** Step 2 (from text above). */
add_action('admin_menu', 'iptbbm_plugin_menu');

/** Step 1. */
function iptbbm_plugin_menu()
{
  add_options_page('IP Threat Blocker', 'IP Threat Blocker', 'manage_options', 'iptbbm-options', 'iptbbm_plugin_options');
}

/** Step 3. */
function iptbbm_plugin_options()
{
  if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }
  //    echo '<div class="wrap">';
  //    echo '<p>Here is where the form would go if I actually had options.</p>';
  //    echo '</div>';

  iptbbm_iptable_table_page_handler();
}

#############REGISTER PLUGIN MENU IN ADMIN##########################ENDS##################################################################
