<?php
/**
 * Plugin Name: BU Hide Only WPBC Setup Notices
 */
if (!defined('ABSPATH')) exit;

add_action('admin_head', function () {
    ?>
    <style>
      .wpbc_ui_el__flex_container:has(a[href*="wpbc-setup"]),
      .wpbc_ui_el__flex_container:has(a[href*="wpbc-welcome"]),
      .wpbc_ui_el__flex_container:has(a[href*="wpbc-about"]),
      div:has(> a[href*="wpbc-setup"]),
      div:has(> a[href*="wpbc-welcome"]),
      div:has(> a[href*="wpbc-about"]) {
          display: none !important;
      }
    </style>
    <?php
}, 9999);
