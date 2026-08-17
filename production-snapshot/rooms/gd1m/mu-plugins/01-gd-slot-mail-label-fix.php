<?php
/**
 * Plugin Name: BU GD Slot Mail Label Fix
 * Description: Converts unique 24-hour slot values into readable 12-hour labels in emails/output.
 * Version: 1.0
 */

if (!defined('ABSPATH')) exit;

function bu_gd_slot_label_map($text) {
    $map = [
        '08:00 - 10:00' => '08:00 AM - 10:00 AM',
        '10:00 - 12:00' => '10:00 AM - 12:00 PM',
        '12:00 - 14:00' => '12:00 PM - 02:00 PM',
        '14:00 - 16:00' => '02:00 PM - 04:00 PM',
        '16:00 - 18:00' => '04:00 PM - 06:00 PM',
        '18:00 - 20:00' => '06:00 PM - 08:00 PM',
        '20:00 - 22:00' => '08:00 PM - 10:00 PM',
        '22:00 - 00:00' => '10:00 PM - 12:00 AM',
        '22:00 - 23:59' => '10:00 PM - 12:00 AM',
        '00:00 - 02:00' => '12:00 AM - 02:00 AM',
        '02:00 - 04:00' => '02:00 AM - 04:00 AM',
        '04:00 - 06:00' => '04:00 AM - 06:00 AM',
    ];

    return strtr($text, $map);
}

add_filter('wp_mail', function($args){
    if (is_array($args)) {
        if (isset($args['subject'])) $args['subject'] = bu_gd_slot_label_map($args['subject']);
        if (isset($args['message'])) $args['message'] = bu_gd_slot_label_map($args['message']);
    }
    return $args;
}, 999999);
