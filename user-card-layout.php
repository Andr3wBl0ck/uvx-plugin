<?php
/**
 * Plugin Name: UserViewExpress
 * Description: An extension on the Wordpress User management interface. Search, locate and edit users easily.
 * Version: 2.0
 * Author: andrewb@gymgrader.com
 */

// Hook for adding admin menus
add_action('admin_menu', 'user_card_layout_menu');

function user_card_layout_menu() {
    add_menu_page('UserViewExpress', 'UserViewExpress', 'manage_options', 'user_card_layout', 'user_card_layout_page', 'dashicons-groups');
}

// user-card-layout.php
function user_card_layout_page() {
    // Capturing the search query
    $search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $users = get_users(); // Your method for getting all users
    
    if ($search_query) {
        $users = array_filter($users, function($user) use ($search_query) {
            return strpos(strtolower($user->display_name), strtolower($search_query)) !== false;
        });
    }

    include(plugin_dir_path(__FILE__) . 'user_card_layout_template.php');
}

// Importing CSS Styles from assets folder
function enqueue_user_card_layout_styles() {
    // Only enqueue the styles on your specific admin page
    $screen = get_current_screen();
    if ( 'toplevel_page_user_card_layout' === $screen->id ) {
        wp_enqueue_style( 'user-card-layout-admin-css', plugin_dir_url( __FILE__ ) . 'assets/admin-style.css' );
    }
}
add_action( 'admin_enqueue_scripts', 'enqueue_user_card_layout_styles' );

function set_last_login_time($user_login, $user) {
    // Updating the last login time
    update_user_meta($user->ID, 'last_login_time', current_time('mysql'));

    // Fetch the existing login events from user meta
    $login_events = get_user_meta($user->ID, 'login_events', true);

    // Initialize the array if it's empty
    if (empty($login_events)) {
        $login_events = [];
    }

    // Append the new login event
    $login_events[] = [
        'time' => current_time('mysql'),
        'ip' => $_SERVER['REMOTE_ADDR']
    ];

    // Update the login events in user meta
    update_user_meta($user->ID, 'login_events', $login_events);
}
add_action('wp_login', 'set_last_login_time', 10, 2);




