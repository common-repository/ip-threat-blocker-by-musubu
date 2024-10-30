<?php

require_once(dirname(__FILE__) . '/libraries/class-wp-list-table.php');
use Musubu_Ip_Blocked_Table_List_Table\Inc\Libraries;



//if (!class_exists('WP_List_Table')) {
//    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
//}

/**
 * Musubu_Ip_Blocked_Table_List_Table class that will display our custom table
 * records in nice table
 */
class Musubu_Ip_Blocked_Table_List_Table extends Libraries\WP_List_Table
{
  /** ************************************************************************
   * REQUIRED. Set up a constructor that references the parent constructor. We
   * use the parent reference to set some default configs.
   ***************************************************************************/
  function __construct()
  {
    global $status, $page;

    //Set parent defaults
    parent::__construct(array(
      'singular'  => 'IP Blocked',     //singular name of the listed records
      'plural'    => 'IPs Blocked',    //plural name of the listed records
      'ajax'      => false        //does this table support ajax?
    ));
  }

  /**
   * [REQUIRED] this is a default column renderer
   *
   * @param $item - row (key, value array)
   * @param $column_name - string (key)
   * @return HTML
   */
  //    function column_default($item, $column_name)
  //    {
  //        return $item[$column_name];
  //    }
  /** ************************************************************************
   * Recommended. This method is called when the parent class can't find a method
   * specifically build for a given column. Generally, it's recommended to include
   * one method for each column you want to render, keeping your package class
   * neat and organized. For example, if the class needs to process a column
   * named 'title', it would first see if a method named $this->column_title()
   * exists - if it does, that method will be used. If it doesn't, this one will
   * be used. Generally, you should try to use custom column methods as much as
   * possible.
   *
   * Since we have defined a column_title() method later on, this method doesn't
   * need to concern itself with any column with a name of 'title'. Instead, it
   * needs to handle everything else.
   *
   * For more detailed insight into how columns are handled, take a look at
   * WP_List_Table::single_row_columns()
   *
   * @param array $item A singular item (one full row's worth of data)
   * @param array $column_name The name/slug of the column to be processed
   * @return string Text or HTML to be placed inside the column <td>
   **************************************************************************/
  function column_default($item, $column_name){
    switch($column_name){
    case 'ip':
    case 'unblocked':
    case 'date_added':
    case 'threat_score':
    case 'blacklist_class':
    case 'country_code':
      #case true:
      return $item[$column_name];
      //default:
      //    return print_r($item,true); //Show the whole array for troubleshooting purposes
    }
  }


  /**
   * [OPTIONAL] this is example, how to render specific column
   *
   * method name must be like this: "column_[column_name]"
   *
   * @param $item - row (key, value array)
   * @return HTML
   */
  function column_country_code($item)
  {
    $countryFullNames = '';
    $countrySelectArray = iptbbm_getCountryCodesWithCountryNames();
    if(stripos($item['country_code'] , ',') !== false )
    {
      $dbCountryCodesArray = explode (',', $item['country_code']);
      foreach ($dbCountryCodesArray as $countryCode) {
        $countryFullName = $countrySelectArray[$countryCode];
        $countryFullNames .= "$countryFullName,";
      }
      $countryFullNames = substr($countryFullNames, 0, -1);
    }
    else
    {
      $countryFullNames = $countrySelectArray[$item['country_code']];
    }
    return '<em>' . $countryFullNames . '</em>';
  }


  /**
   * [OPTIONAL] this is example, how to render column with actions,
   * when you hover row "Edit | Delete" links showed
   *
   * @param $item - row (key, value array)
   * @return HTML
   */

  /** ************************************************************************
   * Recommended. This is a custom column method and is responsible for what
   * is rendered in any column with a name/slug of 'title'. Every time the class
   * needs to render a column, it first looks for a method named
   * column_{$column_title} - if it exists, that method is run. If it doesn't
   * exist, column_default() is called instead.
   *
   * This example also illustrates how to implement rollover actions. Actions
   * should be an associative array formatted as 'slug'=>'link html' - and you
   * will need to generate the URLs yourself. You could even ensure the links
   *
   *
   * @see WP_List_Table::::single_row_columns()
   * @param array $item A singular item (one full row's worth of data)
   * @return string Text to be placed inside the column <td> (movie title only)
   **************************************************************************/
  function column_ip($item)
  {
    // links going to /admin.php?page=[your_plugin_page][&other_params]
    // notice how we used $_REQUEST['page'], so action will be done on current page
    $actions = array(
      #'edit' => sprintf('<a href="?page=activities_form&id=%s">%s</a>', $item['activity_id'], __('Edit')),
      'See More Threat Info ' => sprintf('<a target="_blank"  href="https://musubuapp.co/detail/%s">%s</a>', $item['ip'], __('See More Threat Info')),
      'delete' => sprintf('<a href="?page=%s&action=delete&id=%s">%s</a>', esc_attr($_REQUEST['page']), $item['iptable_id'], __('Delete')),
    );

    //Return the title(column) contents
    return sprintf('%1$s %2$s',
      /*$1%s*/  $item['ip'],
      /*$2%s*/  $this->row_actions($actions)
    );

  }

  function column_unblocked($item)
  {
    #button action
    $actions = array(
      'unblock' => sprintf('<a href="?page=%s&action=unblock&id=%s">%s</a>', esc_attr($_REQUEST['page']), $item['iptable_id'], __('Unblock')),
    );

    //Return the title(column) contents
    return sprintf('%1$s %2$s',
      /*$1%s*/  $item['unblocked'],
      /*$2%s*/  $this->row_actions($actions)
    );
  }

  /**
   * [REQUIRED] this is how checkbox column renders
   *
   * @param $item - row (key, value array)
   * @return HTML
   */
  function column_cb($item)
  {
    return sprintf(
      '<input type="checkbox" name="id[]" value="%s" />',
      $item['iptable_id']
    );
  }

  /**
   * [REQUIRED] This method return columns to display in table
   * you can skip columns that you do not want to show
   * like content, or description
   *
   * @return array
   */
  function get_columns()
  {
    $columns = array(
      'cb' => '<input type="checkbox" />', //Render a checkbox instead of text
      'ip' => __('IP Address'),
      'threat_score' => __('Threat Score'),
      'blacklist_class' => __('Threat Class'),
      'country_code' => __('Country of Origin'),
      'date_added' => __('Date Blocked'),
      'unblocked' => __('Status'),
      #'last_modified' => __('Date Modified')
    );
    return $columns;
  }

  /**
   * [OPTIONAL] This method return columns that may be used to sort table
   * all strings in array - is column names
   * notice that true on name column means that its default sort
   *
   * @return array
   */
  function get_sortable_columns()
  {
    $sortable_columns = array(
      'iptable_id' => array('iptable_id', false),
      'ip' => array('ip', false),
      'threat_score' => array('threat_score', true),
      'blacklist_class' => array('blacklist_class', false),
      'country_code' => array('country_code', false),
      'date_added' => array('date_added', false),
      'unblocked' => array('unblocked', false),
    );
    return $sortable_columns;
  }

  /**
   * [OPTIONAL] Return array of bult actions if has any
   *
   * @return array
   */
  function get_bulk_actions()
  {
    $actions = array(
      'delete' => 'Delete',
      'unblock' => 'Unblock'
    );
    return $actions;
  }

  ###USED TO MAINTAIN FUNCTION WIDE SCOPE TO DISPLAY UPDATE/DELETE/ERROR STATUS###
  public $message = '';
  public $notice = '';

  /**
   * [OPTIONAL] This method processes bulk actions
   * it can be outside of class
   * it can not use wp_redirect coz there is output already
   * in this example we are processing delete action
   * message about successful deletion will be shown on page in next part
   */
  function process_bulk_action()
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'iptable'; // do not forget about tables prefix

    if ('delete' === $this->current_action()) {
      $ids = isset($_REQUEST['id']) ? array_map(intval, $_REQUEST['id']) : array();

      if (is_array($ids)) $ids = implode(',', $ids);
      if (!empty($ids)) {
        #$wpdb->query("UPDATE $table_name set is_deleted=1 WHERE activity_id IN($ids)");
        $result = $wpdb->query("DELETE FROM $table_name WHERE iptable_id IN($ids)");

        if ($result) {
          $this->message = $result == 1 ? __('IP Address was successfully deleted') : __($result . ' IP Addresses were successfully deleted');
        } else {
          $this->notice = __('There was an error while deleting IP address');
        }
      }
    }

    if ('unblock' === $this->current_action()) {
      $ids = isset($_REQUEST['id']) ? array_map(intval, $_REQUEST['id']) : array();

      if (is_array($ids)) $ids = implode(',', $ids);
      if (!empty($ids)) {
        $result = $wpdb->query("UPDATE $table_name set unblocked=1 WHERE iptable_id IN($ids)");

        if ($result) {
          $this->message = __('IP address was successfully unblocked');
        } else {
          $this->notice = __('There was an error while unblocking IP address');
        }
      }
    }
  }

  /**
   * [REQUIRED] This is the most important method
   *
   * It will get rows from database and prepare them to be showed in table
   */
  function prepare_items()
  {
    global $wpdb;
    $table_name = $wpdb->prefix . 'iptable'; // do not forget about tables prefix

    /**
     * First, lets decide how many records per page to show
     */
    $per_page = 100;


    /**
     * REQUIRED. Now we need to define our column headers. This includes a complete
     * array of columns to be displayed (slugs & titles), a list of columns
     * to keep hidden, and a list of columns that are sortable. Each of these
     * can be defined in another method (as we've done here) before being
     * used to build the value for our _column_headers property.
     */
    $columns = $this->get_columns();
    $hidden = array();
    $sortable = $this->get_sortable_columns();


    /**
     * REQUIRED. Finally, we build an array to be used by the class for column
     * headers. The $this->_column_headers property takes an array which contains
     * 3 other arrays. One for all columns, one for hidden columns, and one
     * for sortable columns.
     */
    $this->_column_headers = array($columns, $hidden, $sortable);


    /**
     * Optional. You can handle your bulk actions however you see fit. In this
     * case, we'll handle them within our package just to keep things clean.
     */
    $this->process_bulk_action();

    // will be used in pagination settings
    $total_items = $wpdb->get_var("SELECT COUNT(iptable_id) FROM $table_name WHERE unblocked=0");

    // prepare query params, as usual current page, order by and order direction
    $paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged']) - 1) : 0;
    $offset = $paged * $per_page;
    // if entered page is more then total pages available reset it to 1st page.
    if($paged >= ceil($total_items / $per_page) )
      $offset = 0;
    $orderby = (isset($_REQUEST['orderby']) && in_array($_REQUEST['orderby'], array_keys($this->get_sortable_columns()))) ? $_REQUEST['orderby'] : 'threat_score';###DEFAULT SORT COLUMN NAME
    $order = (isset($_REQUEST['order']) && in_array($_REQUEST['order'], array('asc', 'desc'))) ? $_REQUEST['order'] : 'asc';

    $sqlQuery = "SELECT iptable_id, ip, threat_score, blacklist_class, country_code, IF(unblocked = 0, 'Blocked', 'Unblocked') unblocked, DATE_FORMAT(date_added, '%%d-%%b-%%Y  %%T') date_added
      FROM $table_name WHERE unblocked=0
      ORDER BY $orderby $order
      LIMIT %d OFFSET %d";

    // [REQUIRED] define $items array
    // notice that last argument is ARRAY_A, so we will retrieve array
    $this->items = $wpdb->get_results($wpdb->prepare($sqlQuery, $per_page, $offset), ARRAY_A);


    /**
     * REQUIRED. We also have to register our pagination options & calculations.
     */
    $this->set_pagination_args(array(
      'total_items' => $total_items, // total items defined above
      'per_page' => $per_page, // per page constant defined at top of method
      'total_pages' => ceil($total_items / $per_page) // calculate pages count
    ));
  }
}

/**
 * PART 3. Admin page
 * ============================================================================
 *
 * In this part you are going to add admin page for custom table
 *
 * http://codex.wordpress.org/Administration_Menus
 */


###add_action('admin_menu', 'activity_table_admin_menu');

/**
 * List page handler
 *
 * This function renders our custom table
 * Notice how we display message about successfull deletion
 * Actualy this is very easy, and you can add as many features
 * as you want.
 *
 * Look into /wp-admin/includes/class-wp-*-list-table.php for examples
 */
function iptbbm_iptable_table_page_handler()
{
  global $wpdb;

  $table = new Musubu_IP_Blocked_Table_List_Table();
  $table->prepare_items();

  #########SAVE LOGIC FOR PLUGIN OPTIONS#############################STARTS##################################################
  $table_name = $wpdb->prefix . 'iptable'; // do not forget about tables prefix

  // here we are verifying does this request is post back and have correct nonce
  if (!empty($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'], basename(__FILE__))) {

    //echo "<Pre>";
    //print_r($_POST);
    //echo "</pre>";#die;
    // validate data, and if all ok save item to database
    // Check all $_POST fields for required fields and values
    $item_valid = iptbbm_iptable_table_validate();
    if ($item_valid === true) {

      update_option('iptbbm_scan_mod', $_POST['iptbbm_scan_mod']);
      if( isset($_POST['iptbbm_evaluation']) ) { 
        update_option('iptbbm_evaluation', $_POST['iptbbm_evaluation']);
      } else {
        update_option('iptbbm_evaluation', '');
      }
      update_option('iptbbm_splunk_url', esc_url_raw($_POST['iptbbm_splunk_url']));
      update_option('iptbbm_splunk_token', sanitize_key($_POST['iptbbm_splunk_token']));
      if($_POST['iptbbm_scan_mod'] == 'manual')
      {
        update_option('iptbbm_threat_score', $_POST['iptbbm_threat_score']);
        update_option('iptbbm_blacklist_class', $_POST['iptbbm_blacklist_class']);
        update_option('iptbbm_country_code', $_POST['iptbbm_country_code']);
      }


      $table->message = __('Options updated successfully');

    } else {
      // if $item_valid not true it contains error message(s)
      $table->notice = $item_valid;
    }
  }
  #########SAVE LOGIC FOR PLUGIN OPTIONS#############################ENDS####################################################
?>

    <div class="wrap">

        <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
        <h1>
            <?php _e('IP Threat Blocker by Musubu')?>
            <!--<a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=iptbbm-options');?>"><?php _e('Add new')?></a>-->
        </h1>

        <!--NOTICE AND UPDATE MESSAGE DISPLAY-->
        <?php if (!empty($table->notice)): ?>
        <div id="notice" class="error"><p><?php echo $table->notice; $table->notice = ''; ?></p></div>
        <?php endif;?>
        <?php if (!empty($table->message)): ?>
        <div id="message" class="updated"><p><?php echo $table->message; $table->message = ''; ?></p></div>
        <?php endif;?>

        <p>Musubu&rsquo;s IP Threat Blocker for WordPress leverages the full power of the Musubu API to dynamically screen incoming IP addresses to your website, allowing you to automatically ("set it and forget it") or manually block IPs by threat score, threat class, or country of origin.</p>

        <!--OPTIONS FOR PLUGIN FOR MANUAL/AUTO MODE-->

        <form id="formMusubuOptions" method="post" action="options-general.php?page=iptbbm-options">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce(basename(__FILE__))?>"/>

            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>"/>

  <script>
  jQuery(document).ready(function($) {
    $('#blacklistClass, #countryCode').select2();
  });
  </script>

            <style>
                .iptable th{
                    padding-left : 20px;
                }
                .iptable h2{
                    margin : 0;
                }

                form#formMusubuOptions table.iptable select, form#formMusubuOptions table.iptable input[type=text] {
                    width: 20%;
                }
                form#formMusubuOptions table.iptable select{ height: 6em; }
                table.iptable #apiKeyStatusContainer { width: 80%;}

                #apiKeyStatusContainer {
                    margin-top: 1em;
                    margin-bottom: 0px;
                }

                @media screen and (max-width: 1124px){
                    form#formMusubuOptions table.iptable select, form#formMusubuOptions table.iptable input[type=text] {
                        width: 80%;
                    }
                }
                #post-body-content p{
                  font-size: 13px;
                  margin-bottom: 13px;
                  margin-top: 13px;
                }

            </style>
            <div class="metabox-holder" id="musubuFormContent">
                <div id="post-body">
                    <div id="post-body-content">
                        <table cellspacing="2" cellpadding="5" style="width: 100%;" class="form-table iptable">
                            <tbody>

                                <tr class="form-field">
                                    <td colspan="2">
                                        <h2>Key </h2>
                                        <p>
                                            If you haven't already purchased an API key, you will need to do so now. You can purchase an API key <a href="https://www.wpthreat.co" target="_blank">here</a>.<br/>
                                            Enter the API Key provided by email and then click the Save button to retrieve the key and activate the license.
                                        </p>
                                    </td>
                                </tr>


                                <tr class="form-field">
                                    <th valign="top" scope="row">
                                        <?php _e('Set Musubu API Key')?>
                                    </th>
                                    <td>
                                        <input id="apiKeyForm" name="apiKeyForm" type="text"
                                            size="50" class="code" placeholder="<?php _e('XXXXXXX-XXXXXXX-XXXXXXX-XXXXXXX')?>" >

                                        <span class="submit">
                                            <button type="button" id="saveApiKey" class="button button-primary" ><?php _e('Save'); ?></button>
                                            <img id="loadingImageSubmit" src="<?= plugin_dir_url( dirname( __FILE__ ) ) . '/images/'; ?>loadingMusubu.gif" alt='loading...'
                                                style="display: none; width: 32px;padding-left: 0.5em;vertical-align: middle;margin-top: -2px;" />
                                        </span>

                                        <div id="apiKeyStatusContainer" style="display: none;"><p id="apiKeyStatus"></p></div>

                                    </td>
                                </tr>

                                <tr class="form-field" id="apiKeyContainer" <?= empty(IPTBBM_API_KEY) ? 'style="display: none;"' : ''; ?> >
                                    <th valign="top" scope="row">
                                        <?php _e('Current Musubu API Key'); ?>
                                    </th>
                                    <td>
                                        <p id="apiKey"><?php echo esc_attr(IPTBBM_API_KEY); ?></p>
                                    </td>
                                </tr>

                                <tr class="form-field">
                                    <td colspan="2">
                                        <h2> Settings</h2>
                                        <h4> Block Mode</h4>
                                        <p>'Automatic' Block Mode will automatically set the threat score, threat class, and country thresholds to block any matching IP addresses from viewing your website. <br/>
                                        Switching to 'Manual' mode allows you to manually set thresholds to block IPs by threat score, threat class, or country of origin. <br/>
                                        Threat Score is a number from 0 to 100. The Threat Score setting will match all IP addresses with a threat score equal to or greater than the number set. <br/>
                                        Threat Class is the type of threat that the IP address has been identified as. <br/>
                                        Country of Origin is the country where the IP address is located. </p>
                                    </td>
                                </tr>


                                <tr class="form-field">
                                    <th valign="top" scope="row">
                                        <?php _e('Blocking method')?>
                                    </th>
                                    <td>
                                        <p>
                                            <input type="radio" id="automatic" name="iptbbm_scan_mod"
                                                <?= ( (get_option("iptbbm_scan_mod") !== 'manual') ? 'checked="checked"' : ''); ?> value="auto" > <label for="automatic">Automatic</label>
                                        </p>
                                        <p>
                                            <input type="radio" id="manual" name="iptbbm_scan_mod"
                                                <?= ( (get_option("iptbbm_scan_mod") == 'manual') ? 'checked="checked"' : ''); ?> value="manual" > <label for="manual">Manual</label>
                                        </p>
                                    </td>
                                </tr>

                                <?php #( (get_option("iptbbm_scan_mod") == 'manual') ? '<style>.manualFormOptions{display: block;}</style>' : '<style>.manualFormOptions{display: none;}</style>'); ?>

                                <tr class="form-field manualFormOptions" >
                                    <th valign="top" scope="row">
                                        <?php _e('Threat Score'); ?>
                                    </th>
                                    <td>
                                        <!--<input type="text" style="width: 95%" value="" size="50" class="code" placeholder="<?php _e('Threat Score'); ?>" required>-->
                                        <input id="iptbbm_threat_score" name="iptbbm_threat_score" type="number" step="1" min="1" max="100"
                                            value="<?php echo esc_attr(get_option("iptbbm_threat_score", 80) )?>" style="width: 20%" required>

                                        <p>Equal to or greater than the number set.</p>
                                    </td>
                                </tr>

                                <tr class="form-field manualFormOptions" >
                                    <th valign="top" scope="row">
                                        <?php _e('Threat Class')?>
                                    </th>
                                    <td>
                                      <?php
                                        $blacklistClassOptions = iptbbm_getBlackListClassValues();
                                        $selectBlacklistClass = '<select id="blacklistClass" name="iptbbm_blacklist_class[]"  multiple >';
                                        $dbBlacklistClass = get_option("iptbbm_blacklist_class", array() );
                                        foreach ($blacklistClassOptions as $keyBlacklistclass => $valueBlacklistclass) {
                                          $selected = in_array($valueBlacklistclass, $dbBlacklistClass) ? 'selected' : '';
                                          $selectBlacklistClass .= "<option $selected value='$valueBlacklistclass'>" . ucwords($valueBlacklistclass) . "</option>";
                                        }
                                        echo $selectBlacklistClass .= '</select>';
                                      ?>

                                        <p>Select one or multiple.</p>
                                    </td>
                                </tr>

                                <tr class="form-field manualFormOptions" >
                                    <th valign="top" scope="row">
                                        <?php _e('Country of Origin')?>
                                    </th>
                                    <td>
                                      <?php
                                        $dbCountryCode = get_option("iptbbm_country_code", array() );
                                        iptbbm_displayCountryDropdown($dbCountryCode);
                                      ?>

                                        <p>Select one or multiple.</p>
                                    </td>
                                </tr>
                                <tr class="form-field">
                                  <td colspan="2">
                                    <h4> Evaluation Mode</h4>
                                    <p> This setting will populate the table with IP addresses that would have been blocked without performing any blocking. 
                                        This mode is useful for evaluating the plugin without providing protection. </br>
                                        With evaluation mode enabled the items in the table may still be labeled as 'Blocked' but the blocking will not be enforced.</p>
                                  </td>
                                </tr>
                                <tr class="form-field">
                                    <th valign="top" scope="row">
                                      <?php _e('Evaluation enabled')?>
                                    </th>
                                    <td>
                                      <p>
                                        <input type="checkbox" id="evaluation" name="iptbbm_evaluation"
                                            <?= ( (get_option("iptbbm_evaluation") == 'evaluation') ? 'checked="checked"' : ''); ?> value="evaluation" > <label for="evaluation"></label>

                                      </p>
                                    </td>
                                </tr>

                                <tr class="form-field">
                                  <td colspan="2">
                                    <h4> Splunk Log Forwarding</h4>
                                    <p> Foward all access logs to your Splunk Enterprise instance. 
                                       Configure The URL and Token below based on your HTTP Event Collector (HEC) Splunk configurations. 
                                    </p>
                                  </td>
                                </tr>
                                <tr class="form-field">
                                  <th valign="top" scope="row">
                                   <?php _e('Splunk HEC URL')?>
                                  </th>
                                  <td>
                                    <p>
                                      <input type="text" id="SplunkURL" name="iptbbm_splunk_url" style="width: 400px;" onkeypress="this.style.width = ((this.value.length + 1) * 8) + 'px';"
                                          <?= (get_option("iptbbm_splunk_url", '') != ''  ? 'value="' . get_option("iptbbm_splunk_url") . '"' : '') ; ?> 
                                          placeholder="https://mysplunkinstance.com:8088/services/collector/event" >
                                      </input>
                                    </p>
                                  </td>
                                </tr>
                                <tr class="form-field">
                                  <th valign="top" scope="row">
                                   <?php _e('Splunk HEC Token')?>
                                  </th>
                                  <td>
                                    <p>
                                      <input type="text" id="SplunkToken" name="iptbbm_splunk_token" size="60" onkeypress="this.style.width = ((this.value.length + 1) * 8) + 'px';"
                                          <?= (get_option("iptbbm_splunk_token", '') != ''  ? 'value="' . get_option("iptbbm_splunk_token") . '"' : '') ; ?> 
                                          placeholder="1cc40864-4c7e-40c0-9c15-7b502c39fb58" >
                                      </input>
                                    </p>
                                  </td>
                                </tr>
                            </tbody>
                        </table>

                        <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Save Changes'); ?>"></p>

                    </div>
                </div>
            </div>

  <script>
  jQuery(document).ready(function($) {
    $('#formMusubuOptions').on('change', 'input[type="radio"][name="iptbbm_scan_mod"]', function() {
      var $scanModValue = jQuery('input[type="radio"][name="iptbbm_scan_mod"]:checked').val();
      iptbbm_toggleOptions($scanModValue);
    });

    //Initial form options display based on checked radio button(Fixes browser soft refresh bug)
    var $scanModValue = jQuery('input[type="radio"][name="iptbbm_scan_mod"]:checked').val();
    iptbbm_toggleOptions($scanModValue);

    function iptbbm_toggleOptions($scanModValue)
    {
      if ($scanModValue == 'auto') {
        $('.manualFormOptions').hide('slow');
      }
      else if ($scanModValue== 'manual') {
        $('.manualFormOptions').show('slow');
      }
    }
  });
  </script>

        </form>


        <h2> Blocked IP Addresses </h2>
        <p> Below is a list of all IP Addresses that have been blocked from viewing this website. Click on any IP address shown to view more details about that IP address's location, network type, network owner, street address and more for free in Musubu's industry-leading IP & Network Threat Intelligence web portal, <a href="https://musubuapp.co" target="_blank">MusubuApp</a>. Any blocked IP addresses in the list can be individually unblocked. </p>


        <form id="activity-table" method="get">
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>"/>
            <?php $table->display() ?>
        </form>

    </div>
<?php
}

/**
 * PART 4. Form for adding and or editing row
 * ============================================================================
 *
 * In this part you are going to add admin page for adding andor editing items
 * You cant put all form into this function, but in this example form will
 * be placed into meta box, and if you want you can split your form into
 * as many meta boxes as you want
 *
 * http://codex.wordpress.org/Data_Validation
 * http://codex.wordpress.org/Function_Reference/selected
 */





/**
 * Simple function that validates data and retrieve bool on success
 * and error message(s) on error
 *
 * @param $item
 * @return bool|string
 */
function iptbbm_iptable_table_validate()
{
  $messages = array();


  //if (empty(trim($_POST['iptbbm_api_key']) ) )
  //    $messages[] = __('Musubu API Key is required');

  if (empty(trim($_POST['iptbbm_scan_mod']) ) )
    $messages[] = __('Block Mode is required');

  if (!empty(trim($_POST['iptbbm_scan_mod']) ) && trim($_POST['iptbbm_scan_mod']) == 'manual' )
  {
    if (empty(trim($_POST['iptbbm_threat_score']) ) )
      $messages[] = __('Threat Score is required');
    else if((int)trim($_POST['iptbbm_threat_score']) > 100 || (int)trim($_POST['iptbbm_threat_score']) < 0)
    {
      $messages[] = __('Threat Score can only be assigned a value between 0 and 100.');
    }

    if (empty($_POST['iptbbm_blacklist_class'] ) )
      $messages[] = __('Threat Class is required');

    if (empty($_POST['iptbbm_country_code'] ) )
      $messages[] = __('Country is required');
  }

  //   ECHO "<PRE>activityCount:$activityCount";
  //   print_r($item);
  //   ECHO "</PRE>";DIE;
  //    if (!empty($item['email']) && !is_email($item['email'])) $messages[] = __('E-Mail is in wrong format');
  //    if (!ctype_digit($item['age'])) $messages[] = __('Age in wrong format');
  //if(!empty($item['age']) && !absint(intval($item['age'])))  $messages[] = __('Age can not be less than zero');
  //if(!empty($item['age']) && !preg_match('/[0-9]+/', $item['age'])) $messages[] = __('Age must be number');
  //...

  if (empty($messages))
    return true;

  return implode('<br />', $messages);
}

/**
 * Do not forget about translating your plugin, use __('english string', 'your_uniq_plugin_name') to retrieve translated string
 * and _e('english string', 'your_uniq_plugin_name') to echo it
 * in this example plugin your_uniq_plugin_name == custom_table_example
 *
 * to create translation file, use poedit FileNew catalog...
 * Fill name of project, add "." to path (ENSURE that it was added - must be in list)
 * and on last tab add "__" and "_e"
 *
 * Name your file like this: [my_plugin]-[ru_RU].po
 *
 * http://codex.wordpress.org/Writing_a_Plugin#Internationalizing_Your_Plugin
 * http://codex.wordpress.org/I18n_for_WordPress_Developers
 */
function iptbbm_iptable_table_languages()
{
  load_plugin_textdomain('musubu_iptable', false, dirname(plugin_basename(__FILE__)));
}

add_action('init', 'iptbbm_iptable_table_languages');
