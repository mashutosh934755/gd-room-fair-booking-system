<?php
/**
 * Plugin Name: BU GD Final Duplicate Booking Blocker
 * Description: Cross-room server-side duplicate blocker for all GD rooms. Blocks same room/date/slot, same email/date, same enrollment/date, same phone/date, and member email/enrollment/date across all GD rooms.
 * Version: 2.0.1
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('bu_gd_xroom_rooms')) {
    function bu_gd_xroom_rooms() {
        return array(
            'gd1m'   => array('label' => 'GD1M',    'db' => 'gd2m',        'prefix' => 'wprx_'),
            'gd2m'   => array('label' => 'GD2M',    'db' => 'gd2mlibrary', 'prefix' => 'wprx_'),
            'gd3m'   => array('label' => 'GD3M',    'db' => 'gd3m',        'prefix' => 'wprx_'),
            'gd4m'   => array('label' => 'GD4M',    'db' => 'gd4m',        'prefix' => 'wprx_'),
            'gd1law' => array('label' => 'GD1 Law', 'db' => 'gd1law',      'prefix' => 'wprx_'),
            'gd2law' => array('label' => 'GD2 Law', 'db' => 'gd2law',      'prefix' => 'wprx_'),
        );
    }
}

if (!function_exists('bu_gd_xroom_current_slug')) {
    function bu_gd_xroom_current_slug() {
        $path = isset($_SERVER['REQUEST_URI']) ? strtolower((string) $_SERVER['REQUEST_URI']) : '';
        foreach (array_keys(bu_gd_xroom_rooms()) as $slug) {
            if (strpos($path, '/' . strtolower($slug) . '/') !== false || strpos($path, '/' . strtolower($slug)) === 0) {
                return $slug;
            }
        }

        $home = function_exists('home_url') ? strtolower(home_url('/')) : '';
        foreach (array_keys(bu_gd_xroom_rooms()) as $slug) {
            if (strpos($home, '/' . strtolower($slug) . '/') !== false) {
                return $slug;
            }
        }

        return '';
    }
}

if (!function_exists('bu_gd_xroom_flatten')) {
    function bu_gd_xroom_flatten($value) {
        if (is_array($value)) {
            $out = '';
            foreach ($value as $v) {
                $out .= ' ' . bu_gd_xroom_flatten($v);
            }
            return $out;
        }
        return (string) $value;
    }
}

if (!function_exists('bu_gd_xroom_request_text')) {
    function bu_gd_xroom_request_text() {
        $text = '';
        foreach ($_POST as $k => $v) {
            $text .= ' ' . $k . ' ' . bu_gd_xroom_flatten($v);
        }
        return $text;
    }
}

if (!function_exists('bu_gd_xroom_is_submit')) {
    function bu_gd_xroom_is_submit() {
        $method = $_SERVER['REQUEST_METHOD'] ?? '';
        if (strtoupper($method) !== 'POST') {
            return false;
        }

        $text = strtolower(bu_gd_xroom_request_text());

        $needles = array(
            'booking',
            'rangetime',
            'date_booking',
            'wpbc',
            'email',
            'enrollment',
            'phone'
        );

        foreach ($needles as $n) {
            if (strpos($text, $n) !== false) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('bu_gd_xroom_emails')) {
    function bu_gd_xroom_emails($text) {
        preg_match_all('/[a-z0-9._%+\-]+@[a-z0-9.\-]+\.[a-z]{2,}/i', $text, $m);
        $emails = array_map('strtolower', $m[0] ?? array());
        return array_values(array_unique(array_filter($emails)));
    }
}

if (!function_exists('bu_gd_xroom_enrollments')) {
    function bu_gd_xroom_enrollments($text) {
        preg_match_all('/\b[A-Z]?\d{2}[A-Z]{2,}[A-Z0-9]*\d{3,}\b/i', $text, $m);
        $items = array_map('strtoupper', $m[0] ?? array());
        return array_values(array_unique(array_filter($items)));
    }
}

if (!function_exists('bu_gd_xroom_phones')) {
    function bu_gd_xroom_phones($text) {

        preg_match_all(
            '/(?<!\\d)(?:\\+?91[\\s().-]*)?(?:0[\\s().-]*)?[6-9](?:[\\s().-]*\\d){9}(?!\\d)/',
            (string)$text,
            $m
        );

        $phones = array();

        foreach (($m[0] ?? array()) as $raw) {

            $digits = preg_replace('/\\D+/', '', (string)$raw);

            /*
             * Canonical Indian mobile identity:
             * 9876543210
             * 09876543210
             * 919876543210
             * +91 98765 43210
             *
             * Normalize to final 10 digits.
             */
            if (strlen($digits) > 10) {
                $digits = substr($digits, -10);
            }

            if (preg_match('/^[6-9]\\d{9}$/', $digits)) {
                $phones[] = $digits;
            }
        }

        return array_values(
            array_unique(
                array_filter($phones)
            )
        );
    }
}

if (!function_exists('bu_gd_xroom_date')) {
    function bu_gd_xroom_date($text) {
        if (preg_match('/\b(20\d{2})[-\/](\d{1,2})[-\/](\d{1,2})\b/', $text, $m)) {
            return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
        }

        if (preg_match('/\b(\d{1,2})[-\/](\d{1,2})[-\/](20\d{2})\b/', $text, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        return function_exists('wp_date') ? wp_date('Y-m-d') : date('Y-m-d');
    }
}

if (!function_exists('bu_gd_xroom_slot')) {
    function bu_gd_xroom_slot($text) {
        if (preg_match('/\b([01]?\d|2[0-3]):([0-5]\d)\s*-\s*([01]?\d|2[0-3]):([0-5]\d)\b/', $text, $m)) {
            return array(sprintf('%02d:%02d:00', $m[1], $m[2]), sprintf('%02d:%02d:00', $m[3], $m[4]));
        }

        if (preg_match('/\b(\d{1,2}):([0-5]\d)\s*(am|pm)\s*-\s*(\d{1,2}):([0-5]\d)\s*(am|pm)\b/i', $text, $m)) {
            $s = date('H:i:s', strtotime($m[1] . ':' . $m[2] . ' ' . $m[3]));
            $e = date('H:i:s', strtotime($m[4] . ':' . $m[5] . ' ' . $m[6]));
            return array($s, $e);
        }

        return array('', '');
    }
}

if (!function_exists('bu_gd_xroom_block')) {
    function bu_gd_xroom_block($msg) {
        wp_die(
            '<div style="max-width:640px;margin:40px auto;padding:24px;border:1px solid #ddd;border-radius:12px;font-family:Arial,sans-serif;background:#fff;">
                <h2 style="color:#c9002b;margin-top:0;">Booking Not Allowed</h2>
                <p style="font-size:16px;line-height:1.6;">' . esc_html($msg) . '</p>
                <p style="font-size:14px;color:#555;">Please go back and select another available slot or contact the library help desk.</p>
                <p><a href="javascript:history.back()" style="display:inline-block;background:#1f5f9f;color:#fff;padding:10px 18px;border-radius:8px;text-decoration:none;">Go Back</a></p>
            </div>',
            'Duplicate Booking Blocked',
            array('response' => 409)
        );
    }
}

if (!function_exists('bu_gd_xroom_db')) {
    function bu_gd_xroom_db($db_name) {
        if (!class_exists('wpdb')) {
            return null;
        }

        $dbh = new wpdb(DB_USER, DB_PASSWORD, $db_name, DB_HOST);
        if (!empty($dbh->error)) {
            return null;
        }

        return $dbh;
    }
}

if (!function_exists('bu_gd_xroom_check')) {
    function bu_gd_xroom_check($date, $emails, $enrollments, $phones, $start_time) {
        $rooms = bu_gd_xroom_rooms();
        $current_slug = bu_gd_xroom_current_slug();

        foreach ($rooms as $slug => $cfg) {
            $db = bu_gd_xroom_db($cfg['db']);
            if (!$db) {
                continue;
            }

            $booking_table = $cfg['prefix'] . 'booking';
            $dates_table   = $cfg['prefix'] . 'bookingdates';

            $booking_exists = $db->get_var($db->prepare("SHOW TABLES LIKE %s", $booking_table));
            $dates_exists   = $db->get_var($db->prepare("SHOW TABLES LIKE %s", $dates_table));

            if (!$booking_exists || !$dates_exists) {
                continue;
            }

            $rows = $db->get_results(
                $db->prepare(
                    "SELECT b.booking_id, b.form, d.booking_date
                     FROM {$booking_table} b
                     INNER JOIN {$dates_table} d ON b.booking_id = d.booking_id
                     WHERE DATE(d.booking_date) = %s
                       AND (b.trash IS NULL OR b.trash = 0)
                     ORDER BY d.booking_date ASC",
                    $date
                )
            );

            if (!$rows) {
                continue;
            }

            foreach ($rows as $r) {
                $form = strtolower((string) $r->form);
                $form_digits = preg_replace('/\D+/', '', $form);
                $booking_time = date('H:i:s', strtotime($r->booking_date));

                if ($slug === $current_slug && $start_time && $booking_time === $start_time) {
                    return 'This room and time slot is already booked for the selected date.';
                }

                foreach ($emails as $email) {
                    if ($email && strpos($form, strtolower($email)) !== false) {
                        return 'This email ID already has a booking for the selected date in ' . $cfg['label'] . '. Only one GD room booking per day is allowed.';
                    }
                }

                foreach ($enrollments as $enr) {
                    if ($enr && stripos($form, $enr) !== false) {
                        return 'This enrollment number already has a booking for the selected date in ' . $cfg['label'] . '. Only one GD room booking per day is allowed.';
                    }
                }

                foreach ($phones as $phone) {
                    if ($phone && strpos($form_digits, $phone) !== false) {
                        return 'This mobile number already has a booking for the selected date in ' . $cfg['label'] . '. Only one GD room booking per day is allowed.';
                    }
                }
            }
        }

        return null;
    }
}

add_action('init', function () {
    if (!bu_gd_xroom_is_submit()) {
        return;
    }

    $text = bu_gd_xroom_request_text();

    $date = bu_gd_xroom_date($text);
    $emails = bu_gd_xroom_emails($text);
    $enrollments = bu_gd_xroom_enrollments($text);
    $phones = bu_gd_xroom_phones($text);
    list($start_time, $end_time) = bu_gd_xroom_slot($text);

    if (empty($emails) && empty($enrollments) && empty($phones) && !$start_time) {
        return;
    }

    $msg = bu_gd_xroom_check($date, $emails, $enrollments, $phones, $start_time);

    if ($msg) {
        bu_gd_xroom_block($msg);
    }
}, 1);
