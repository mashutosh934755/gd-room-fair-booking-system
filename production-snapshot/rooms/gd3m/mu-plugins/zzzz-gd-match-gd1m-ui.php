<?php
/*
Plugin Name: ZZZZ GD Match GD1M UI
Description: Force GD3M/GD4M booking form layout like GD1M.
Version: 1.0
*/
if (!defined('ABSPATH')) exit;

add_action('wp_head', function () {
    if (is_admin()) return;
?>
<style>
/* Main content width */
.entry-content,
.site-main,
#primary,
#content {
    max-width: 920px !important;
}

/* Wrapper banne ke baad final layout */
.gd1m-like-wrap{
    display:flex !important;
    align-items:flex-start !important;
    gap:26px !important;
    max-width:780px !important;
    width:100% !important;
    margin-top:10px !important;
}
.gd1m-like-left{
    flex:0 0 180px !important;
    width:180px !important;
    max-width:180px !important;
}
.gd1m-like-right{
    flex:0 0 360px !important;
    width:360px !important;
    max-width:360px !important;
}

/* Calendar left side */
.gd1m-like-left .wpbc_calendar_wrap,
.gd1m-like-left .booking_calendar,
.gd1m-like-left .wpdev_hint_with_text_container,
.gd1m-like-left .datepick-inline,
.gd1m-like-left .wpbc_calendar {
    width:100% !important;
    max-width:100% !important;
}

/* Right form column */
.gd1m-like-right form,
.gd1m-like-right .booking_form_div,
.gd1m-like-right .wpbc_booking_form,
.gd1m-like-right .wpbc_booking_form_structure {
    width:100% !important;
    max-width:100% !important;
    float:none !important;
    clear:both !important;
}

/* Sare fields vertical */
.gd1m-like-right p,
.gd1m-like-right .form-group,
.gd1m-like-right .wpbc_structure_form > div,
.gd1m-like-right .wpbc_booking_form_structure > div {
    display:block !important;
    width:100% !important;
    max-width:100% !important;
    float:none !important;
    clear:both !important;
    margin:0 0 14px 0 !important;
}

/* Labels */
.gd1m-like-right label,
.gd1m-like-right strong {
    display:block !important;
    width:100% !important;
    margin-bottom:5px !important;
}

/* Inputs full width */
.gd1m-like-right input[type="text"],
.gd1m-like-right input[type="email"],
.gd1m-like-right input[type="tel"],
.gd1m-like-right input[type="number"],
.gd1m-like-right textarea,
.gd1m-like-right select {
    display:block !important;
    width:100% !important;
    max-width:100% !important;
    min-width:100% !important;
    box-sizing:border-box !important;
    float:none !important;
    clear:both !important;
}

.gd1m-like-right textarea{
    min-height:72px !important;
    height:72px !important;
}

/* Group members / purpose box full width */
.gd1m-like-right .bu-gd-purpose-box,
.gd1m-like-right .bu-gd-members-box,
.gd1m-like-right .gd-member-card,
.gd1m-like-right .gd-members-wrapper {
    width:100% !important;
    max-width:100% !important;
    float:none !important;
    clear:both !important;
}

/* Add member button */
.gd1m-like-right .bu-gd-add-member,
.gd1m-like-right .gd-add-member-btn,
.gd1m-like-right button {
    width:auto !important;
    min-width:110px !important;
}

/* Captcha + send row bhi vertical */
.gd1m-like-right .captcha_size,
.gd1m-like-right .wpbc_captcha,
.gd1m-like-right .wpbc_submit_button {
    display:block !important;
    width:auto !important;
    clear:both !important;
    float:none !important;
    margin-top:10px !important;
}

/* Agar koi stray textarea / field form ke bahar aa raha ho to hide */
.gd-hide-stray {
    display:none !important;
}

/* Mobile */
@media (max-width: 900px){
    .gd1m-like-wrap{
        flex-direction:column !important;
        gap:18px !important;
    }
    .gd1m-like-left,
    .gd1m-like-right{
        width:100% !important;
        max-width:100% !important;
        flex:0 0 100% !important;
    }
}
</style>
<?php
}, 100500);

add_action('wp_footer', function () {
    if (is_admin()) return;
?>
<script>
(function(){
    function qs(sel, root){ return (root || document).querySelector(sel); }
    function qsa(sel, root){ return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }

    function isVisible(el){
        return !!(el && (el.offsetWidth || el.offsetHeight || el.getClientRects().length));
    }

    function findCalendar(){
        var selectors = [
            '.booking_calendar',
            '.wpbc_calendar_wrap',
            '.wpbc_calendar',
            '.datepick-inline',
            '.wpdev_hint_with_text_container'
        ];
        for (var i=0;i<selectors.length;i++){
            var el = qs(selectors[i]);
            if (el && isVisible(el)) return el;
        }
        return null;
    }

    function findBookingForm(){
        var forms = qsa('form');
        for (var i=0;i<forms.length;i++){
            var f = forms[i];
            if (
                f.querySelector('select[name*="rangetime"], select[id*="rangetime"], select') &&
                (
                    f.innerText.indexOf('First Name') !== -1 ||
                    f.innerText.indexOf('Email') !== -1 ||
                    f.innerText.indexOf('Time Slots') !== -1
                )
            ){
                return f;
            }
        }
        return null;
    }

    function moveStrayElementsIntoForm(form, calendar){
        var parent = form.parentNode;
        if (!parent) return;

        var kids = Array.prototype.slice.call(parent.children);
        kids.forEach(function(el){
            if (el === form) return;
            if (el === calendar) return;
            if (el.closest && el.closest('.gd1m-like-wrap')) return;

            var txt = (el.innerText || '').trim();

            /* useless / broken stray visible items */
            if (
                el.tagName === 'TEXTAREA' ||
                txt === 'Selected Date:' ||
                txt.indexOf('Selected Date:') === 0
            ){
                el.classList.add('gd-hide-stray');
            }
        });
    }

    function apply(){
        if (document.querySelector('.gd1m-like-wrap')) return;

        var cal = findCalendar();
        var form = findBookingForm();

        if (!cal || !form) return;

        var wrap = document.createElement('div');
        wrap.className = 'gd1m-like-wrap';

        var left = document.createElement('div');
        left.className = 'gd1m-like-left';

        var right = document.createElement('div');
        right.className = 'gd1m-like-right';

        var anchor = cal.parentNode;
        anchor.insertBefore(wrap, cal);

        wrap.appendChild(left);
        wrap.appendChild(right);

        left.appendChild(cal);
        right.appendChild(form);

        moveStrayElementsIntoForm(form, cal);
    }

    document.addEventListener('DOMContentLoaded', apply);
    window.addEventListener('load', apply);
    setTimeout(apply, 500);
    setTimeout(apply, 1200);
})();
</script>
<?php
}, 100500);
