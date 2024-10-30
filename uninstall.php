<?php
// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}


if ( !is_multisite() )
{
    delete_option('iptbbm_db_version');
    delete_option('iptbbm_api_key');
    delete_option('iptbbm_api_key_email');
    delete_option('iptbbm_scan_mod');
    delete_option('iptbbm_threat_score');
    delete_option('iptbbm_blacklist_class');
    delete_option('iptbbm_country_code');
    delete_option('iptbbm_evaluation');
    delete_option('wptrt_notice_dismissed_iptbbm_freeregistration');

    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . 'iptable');
}
else
{
    // for site options in Multisite
    //delete_site_option('iptbbm_db_version');
    //delete_site_option('iptbbm_api_key');
    //delete_site_option('iptbbm_scan_mod');
    //delete_site_option('iptbbm_threat_score');
    //delete_site_option('iptbbm_blacklist_class');
    //delete_site_option('iptbbm_country_code');

    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
    $original_blog_id = get_current_blog_id();

    foreach ( $blog_ids as $blog_id )
    {
        switch_to_blog( $blog_id );

        delete_option('iptbbm_db_version');
        delete_option('iptbbm_api_key');
        delete_option('iptbbm_api_key_email');
        delete_option('iptbbm_scan_mod');
        delete_option('iptbbm_threat_score');
        delete_option('iptbbm_blacklist_class');
        delete_option('iptbbm_country_code');
        delete_option('iptbbm_evaluation');
        delete_option('iptbbm_splunk_url');
        delete_option('iptbbm_splunk_token');
        delete_option('wptrt_notice_dismissed_iptbbm_freeregistration');

        // drop a custom database table
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS " . $wpdb->prefix . 'iptable');

    }
    switch_to_blog( $original_blog_id );
}
