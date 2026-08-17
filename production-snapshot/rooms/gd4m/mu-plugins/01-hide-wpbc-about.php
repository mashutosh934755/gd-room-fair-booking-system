<?php
/**
 * Plugin Name: BU Hide WPBC About Page
 * Description: Hides Booking Calendar about page from staff/admin menu.
 */

if (!defined('ABSPATH')) exit;

add_action('admin_menu', function () {
    remove_submenu_page('wpbc', 'wpbc-about');
    remove_submenu_page('wpbc-new', 'wpbc-about');
    remove_submenu_page('index.php', 'wpbc-about');
}, 9999);
