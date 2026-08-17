<?php
/*
Plugin Name: GD Clean Date Limit 3 Days
Description: Allows GD booking only from today up to next 3 days. Does not block admin listing pages.
Version: 1.0
*/

if (!defined('ABSPATH')) {
    exit;
}

function gd_clean_date_limit_message() {
    return 'Booking is allowed only from today up to the next 3 days.';
}

function gd_clean_extract_booking_dates_from_request() {
    $dates = [];

    $keys = [
        'date_booking',
        'booking_date',
        'selected_dates',
        'dates',
        'rangetime',
        'calendar_dates',
    ];

    foreach ($keys as $key) {
        if (isset($_REQUEST[$key])) {
            $val = wp_unslash($_REQUEST[$key]);
            if (is_array($val)) {
                $val = implode(',', $val);
            }
            if (is_string($val)) {
                preg_match_all('/\d{4}-\d{2}-\d{2}|\d{2}[.\/-]\d{2}[.\/-]\d{4}/', $val, $m);
                foreach ($m[0] as $d) {
                    $dates[] = $d;
                }
            }
        }
    }

    return array_unique($dates);
}

function gd_clean_normalize_date($date) {
    $date = trim((string) $date);

    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return $date;
    }

    if (preg_match('/^(\d{2})[.\/-](\d{2})[.\/-](\d{4})$/', $date, $m)) {
        return $m[3] . '-' . $m[2] . '-' . $m[1];
    }

    return '';
}

function gd_clean_is_booking_submit_request() {
    if (($_SERVER["REQUEST_METHOD"] ?? "") !== 'POST') {
        return false;
    }

    $uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';

    // Do not block normal admin listing/filter pages.
    if (is_admin() && strpos($uri, 'admin.php?page=wpbc') !== false && empty($_POST['booking_form_type'])) {
        return false;
    }

    $haystack = strtolower($uri . ' ' . wp_json_encode($_REQUEST));

    return (
        strpos($haystack, 'booking') !== false ||
        strpos($haystack, 'wpbc') !== false
    );
}

add_action('init', function () {
    if (!gd_clean_is_booking_submit_request()) {
        return;
    }

    $dates = gd_clean_extract_booking_dates_from_request();

    if (empty($dates)) {
        return;
    }

    $today_ts = strtotime(current_time('Y-m-d'));
    $max_ts   = strtotime('+3 days', $today_ts);

    foreach ($dates as $raw_date) {
        $date = gd_clean_normalize_date($raw_date);

        if ($date === '') {
            continue;
        }

        $ts = strtotime($date);

        if ($ts < $today_ts || $ts > $max_ts) {
            wp_die(
                '<div style="max-width:680px;margin:40px auto;font-family:Arial,sans-serif;border:1px solid #eee;padding:24px;border-radius:10px;">
                    <h2 style="color:#c80d2e;margin-top:0;">Booking Not Allowed</h2>
                    <p style="font-size:18px;font-weight:700;">' . esc_html(gd_clean_date_limit_message()) . '</p>
                    <p>Please go back and select a valid booking date.</p>
                    <button onclick="history.back()" style="padding:10px 18px;border:0;background:#111;color:#fff;border-radius:6px;cursor:pointer;">Go Back</button>
                </div>',
                'Booking Not Allowed',
                ['response' => 400]
            );
        }
    }
});
