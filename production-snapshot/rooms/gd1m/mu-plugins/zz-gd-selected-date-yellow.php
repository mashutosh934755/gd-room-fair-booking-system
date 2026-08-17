<?php
/*
Plugin Name: ZZ GD Selected Date Yellow
Description: Highlight selected booking date in yellow for GD room booking calendars.
Version: 1.0
*/
if (!defined('ABSPATH')) exit;

add_action('wp_head', function () {
?>
<style id="zz-gd-selected-date-yellow-css">
/* Selected/current day highlight */
.datepick-inline td.gd-user-selected-date,
.datepick-inline td.gd-user-selected-date a,
.datepick-inline td.gd-user-selected-date span,
.datepick-inline td.datepick-current-day,
.datepick-inline td.datepick-current-day a,
.datepick-inline td.datepick-current-day span,
.datepick-inline td.datepick-days-cell-over,
.datepick-inline td.datepick-days-cell-over a,
.datepick-inline td.datepick-days-cell-over span {
    background: #fff3b5 !important;
    color: #001b3f !important;
    font-weight: 800 !important;
}

/* Available green should become yellow only when user selected it */
.datepick-inline td.gd-user-selected-date.date_available,
.datepick-inline td.gd-user-selected-date.date_available a,
.datepick-inline td.gd-user-selected-date.calendar-links,
.datepick-inline td.gd-user-selected-date.calendar-links a,
.datepick-inline td.gd-user-selected-date.wpbc_available_day,
.datepick-inline td.gd-user-selected-date.wpbc_available_day a {
    background: #fff3b5 !important;
    color: #001b3f !important;
    font-weight: 800 !important;
    outline: 2px solid #0b4f9c !important;
    outline-offset: -2px !important;
}
</style>
<?php
}, 100000);

add_action('wp_footer', function () {
?>
<script id="zz-gd-selected-date-yellow-js">
(function () {
    function bindSelectedDateHighlight() {
        document.querySelectorAll('.datepick-inline td').forEach(function (td) {
            if (td.dataset.gdSelectedBound === '1') return;
            td.dataset.gdSelectedBound = '1';

            td.addEventListener('click', function () {
                var text = (td.textContent || '').trim();
                var day = parseInt(text, 10);

                if (!day || isNaN(day)) return;

                document.querySelectorAll('.datepick-inline td').forEach(function (x) {
                    x.classList.remove('gd-user-selected-date');
                });

                td.classList.add('gd-user-selected-date');
            }, true);
        });
    }

    function detectAlreadySelected() {
        var selected = document.querySelector(
            '.datepick-inline td.datepick-current-day, .datepick-inline td.datepick-days-cell-over'
        );

        if (selected && !selected.classList.contains('gd-user-selected-date')) {
            selected.classList.add('gd-user-selected-date');
        }
    }

    function run() {
        bindSelectedDateHighlight();
        detectAlreadySelected();
    }

    document.addEventListener('DOMContentLoaded', run);
    window.addEventListener('load', run);
    document.addEventListener('click', function () {
        setTimeout(run, 100);
        setTimeout(run, 500);
    });
    setInterval(run, 800);
})();
</script>
<?php
}, 100000);
