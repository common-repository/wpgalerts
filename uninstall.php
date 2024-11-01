<?php

    global $wpdb;
    if ( ! current_user_can( 'activate_plugins' ) )
        return;
    check_admin_referer( 'bulk-plugins' );

    // Important: Check if the file is the one
    // that was registered during the uninstall hook.

    delete_option("WPGAlerts_Strip_Tags");
	delete_option("WPGAlerts_Max_Alerts");
	delete_option("WPGAlerts_Title_Pre");
	delete_option("WPGAlerts_Title_Post");
	delete_option("WPGAlerts_Author_Pre");
	delete_option("WPGAlerts_Author_Post");
	delete_option("WPGAlerts_Content_Pre");
	delete_option("WPGAlerts_Content_Post");
	delete_option("WPGAlerts_db_version");
	$table_name = $wpdb->prefix . "WPGAFeeds";
	$sql = "
	DROP TABLE $table_name
	";
	$table_name = $wpdb->prefix . "WPGAlerts";
	$wpdb->query($sql);
	$sql = "
	DROP TABLE $table_name
	";
	$wpdb->query($sql);
?>