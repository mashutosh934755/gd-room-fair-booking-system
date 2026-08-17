<?php
/*
Plugin Name: ZZ GD Admin Booking Only Access
Description: Restrict username admin to Booking Calendar pages only.
Version: 2.0
*/
if (!defined('ABSPATH')) exit;

function gd_is_booking_only_user() {
    $u = wp_get_current_user();
    return ($u && isset($u->user_login) && strtolower($u->user_login) === 'admin');
}

function gd_booking_home_url() {
    return admin_url('admin.php?page=wpbc');
}

/* Hide everything except Booking Calendar */
add_action('admin_menu', function () {
    if (!gd_is_booking_only_user()) return;

    global $menu, $submenu;

    foreach ((array)$menu as $item) {
        $title = isset($item[0]) ? strtolower(wp_strip_all_tags($item[0])) : '';
        $slug  = isset($item[2]) ? strtolower($item[2]) : '';

        $keep = false;

        if (strpos($title, 'booking') !== false || strpos($slug, 'wpbc') !== false || strpos($slug, 'booking') !== false) {
            $keep = true;
        }

        if ($slug === 'profile.php') {
            $keep = true;
        }

        if (!$keep && !empty($item[2])) {
            remove_menu_page($item[2]);
        }
    }

    foreach ((array)$submenu as $parent => $items) {
        foreach ((array)$items as $i => $item) {
            $title = isset($item[0]) ? strtolower(wp_strip_all_tags($item[0])) : '';
            $slug  = isset($item[2]) ? strtolower($item[2]) : '';

            $keep = false;

            if (strpos($title, 'booking') !== false || strpos($slug, 'wpbc') !== false || strpos($slug, 'booking') !== false) {
                $keep = true;
            }

            if ($slug === 'profile.php') {
                $keep = true;
            }

            if (!$keep) {
                unset($submenu[$parent][$i]);
            }
        }
    }
}, 9999);

/* Redirect non-booking admin pages */
add_action('admin_init', function () {
    if (!gd_is_booking_only_user()) return;

    $script = basename($_SERVER['PHP_SELF'] ?? '');
    $page   = isset($_GET['page']) ? strtolower(sanitize_text_field($_GET['page'])) : '';

    if ($script === 'admin-ajax.php' || $script === 'async-upload.php') {
        return;
    }

    if ($script === 'profile.php') {
        return;
    }

    if ($script === 'admin.php' && (strpos($page, 'wpbc') !== false || strpos($page, 'booking') !== false)) {
        return;
    }

    if ($script === 'index.php') {
        wp_safe_redirect(gd_booking_home_url());
        exit;
    }

    wp_safe_redirect(gd_booking_home_url());
    exit;
}, 100);

add_filter('login_redirect', function ($redirect_to, $request, $user) {
    if ($user instanceof WP_User && strtolower($user->user_login) === 'admin') {
        return gd_booking_home_url();
    }
    return $redirect_to;
}, 20, 3);

/* Hide toolbar items and notices */
add_action('admin_head', function () {
    if (!gd_is_booking_only_user()) return;
    ?>
    <style>
      #wpadminbar #wp-admin-bar-new-content,
      #wpadminbar #wp-admin-bar-comments,
      #wpadminbar #wp-admin-bar-customize,
      #wpadminbar #wp-admin-bar-updates,
      #wpadminbar #wp-admin-bar-wp-logo,
      .update-nag,
      .notice,
      #dashboard-widgets,
      .wrap > h1 + .notice {
        display:none!important;
      }
    </style>
    <?php
}, 9999);
