<?php
/*
Plugin Name: ZZ GD Force 12 Hour Time Slot Display
Description: Force Time Slots dropdown/text to show 12-hour AM/PM format on frontend.
Version: 1.0
*/
if (!defined('ABSPATH')) exit;

add_action('wp_footer', function () {
?>
<script id="zz-gd-force-12hour-timeslot-js">
(function () {
    const map = {
        "09:00 - 11:00": "09:00 AM - 11:00 AM",
        "11:00 - 13:00": "11:00 AM - 01:00 PM",
        "13:00 - 15:00": "01:00 PM - 03:00 PM",
        "15:00 - 17:00": "03:00 PM - 05:00 PM",
        "17:00 - 19:00": "05:00 PM - 07:00 PM",
        "19:00 - 21:00": "07:00 PM - 09:00 PM",
        "21:00 - 23:00": "09:00 PM - 11:00 PM",
        "23:00 - 02:00": "11:00 PM - 02:00 AM",

        "09:00-11:00": "09:00 AM - 11:00 AM",
        "11:00-13:00": "11:00 AM - 01:00 PM",
        "13:00-15:00": "01:00 PM - 03:00 PM",
        "15:00-17:00": "03:00 PM - 05:00 PM",
        "17:00-19:00": "05:00 PM - 07:00 PM",
        "19:00-21:00": "07:00 PM - 09:00 PM",
        "21:00-23:00": "09:00 PM - 11:00 PM",
        "23:00-02:00": "11:00 PM - 02:00 AM",

        "11:00 AM - 13:00 PM": "11:00 AM - 01:00 PM",
        "13:00 PM - 15:00 PM": "01:00 PM - 03:00 PM",
        "15:00 PM - 17:00 PM": "03:00 PM - 05:00 PM",
        "17:00 PM - 19:00 PM": "05:00 PM - 07:00 PM",
        "19:00 PM - 21:00 PM": "07:00 PM - 09:00 PM",
        "21:00 PM - 23:00 PM": "09:00 PM - 11:00 PM",
        "23:00 PM - 02:00 AM": "11:00 PM - 02:00 AM"
    };

    function cleanText(txt) {
        let t = String(txt || '').trim();
        return map[t] || t;
    }

    function fixSelectOptions() {
        document.querySelectorAll('select[name*="rangetime"], select').forEach(function (select) {
            Array.from(select.options || []).forEach(function (opt) {
                const oldText = (opt.textContent || '').trim();
                const newText = cleanText(oldText);

                if (newText !== oldText) {
                    opt.textContent = newText;
                    opt.label = newText;
                }
            });
        });
    }

    function fixVisibleTextNodes() {
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, null);
        const nodes = [];

        while (walker.nextNode()) {
            nodes.push(walker.currentNode);
        }

        nodes.forEach(function (node) {
            const oldText = (node.nodeValue || '').trim();
            const newText = cleanText(oldText);

            if (newText !== oldText) {
                node.nodeValue = node.nodeValue.replace(oldText, newText);
            }
        });
    }

    function fixAll() {
        fixSelectOptions();
        fixVisibleTextNodes();
    }

    document.addEventListener('DOMContentLoaded', fixAll);
    window.addEventListener('load', fixAll);
    document.addEventListener('click', function () {
        setTimeout(fixAll, 100);
        setTimeout(fixAll, 500);
    });

    setInterval(fixAll, 700);
})();
</script>
<?php
}, 100001);
