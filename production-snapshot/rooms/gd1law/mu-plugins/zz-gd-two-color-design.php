<?php
/*
Plugin Name: ZZ GD Two Color Design
Description: Same normal GD booking design with only two date colors.
Version: 1.0
*/
if (!defined('ABSPATH')) exit;

add_action('wp_head', function () {
?>
<style id="zz-gd-two-color-design-css">
body,.site,.site-content,.entry-content{background:#fff!important}
.ast-container,.site-content .ast-container{max-width:1200px!important}
.entry-title,h1.entry-title,.entry-content h1,.entry-content h2{color:#001b3f!important;font-weight:700!important}

.wpbc_booking_form_structure.wpbc_form_right{
    display:grid!important;
    grid-template-columns:310px 1fr!important;
    gap:280px!important;
    align-items:start!important;
    max-width:1000px!important;
}
.wpbc_structure_calendar{width:300px!important}
.wpbc_structure_form{width:360px!important}
.wpbc_structure_form p{color:#001b3f!important;font-weight:700!important;margin-bottom:18px!important}

.wpbc_structure_form input[type="text"],
.wpbc_structure_form input[type="email"],
.wpbc_structure_form textarea,
.wpbc_structure_form select{
    width:100%!important;
    max-width:350px!important;
    border:1px solid #7e7e7e!important;
    background:#fff!important;
    color:#001b3f!important;
    box-shadow:none!important;
    border-radius:0!important;
    padding:6px 8px!important;
    font-size:13px!important;
}
.wpbc_structure_form textarea{min-height:95px!important}

.wpbc_structure_form input[type="submit"],
.wpbc_structure_form .btn,
.wpbc_structure_form button[type="submit"]{
    background:#4b4b4b!important;
    color:#fff!important;
    border:1px solid #4b4b4b!important;
    padding:7px 16px!important;
    border-radius:2px!important;
    font-weight:700!important;
    box-shadow:0 2px 4px rgba(0,0,0,.25)!important;
}
.wpbc_structure_form input[type="submit"]:hover,
.wpbc_structure_form .btn:hover{background:#222!important}

/* Calendar */
.datepick-inline{
    width:300px!important;
    background:#fff!important;
    border:1px solid #cfcfcf!important;
    box-shadow:0 1px 4px rgba(0,0,0,.12)!important;
}
.datepick-inline table{width:100%!important;border-collapse:collapse!important}
.datepick-inline th,.datepick-inline td{border:1px solid #d1d1d1!important;text-align:center!important}
.datepick-inline td{height:42px!important;padding:0!important;background:#fff!important;color:#001b3f!important}
.datepick-inline td a,.datepick-inline td span{
    display:block!important;
    height:42px!important;
    line-height:42px!important;
    text-align:center!important;
    text-decoration:none!important;
    background:transparent!important;
}

/* COLOR 1: Available green */
.datepick-inline td.date_available,
.datepick-inline td.date_available a,
.datepick-inline td.wpbc_available_day,
.datepick-inline td.wpbc_available_day a,
.datepick-inline td.calendar-links,
.datepick-inline td.calendar-links a{
    background:#5a963e!important;
    color:#fff!important;
    font-weight:800!important;
}

/* COLOR 2: Today/selected yellow */
.datepick-inline td.datepick-current-day,
.datepick-inline td.datepick-current-day a,
.datepick-inline td.datepick-days-cell-over,
.datepick-inline td.datepick-days-cell-over a,
.datepick-inline td.wpbc_today,
.datepick-inline td.wpbc_today a,
.datepick-inline td.datepick-today,
.datepick-inline td.datepick-today a{
    background:#fff3b5!important;
    color:#001b3f!important;
    font-weight:800!important;
}

/* Remove third/fourth colors: booked/pending/partial neutral white */
.datepick-inline td.date_booked,
.datepick-inline td.date_booked a,
.datepick-inline td.wpbc_booked_day,
.datepick-inline td.wpbc_booked_day a,
.datepick-inline td.date2approve,
.datepick-inline td.date2approve a,
.datepick-inline td.wpbc_pending_day,
.datepick-inline td.wpbc_pending_day a,
.datepick-inline td.date_approved,
.datepick-inline td.date_approved a{
    background:#fff!important;
    color:#001b3f!important;
}

/* Legend boxes: keep only available and pending/today style neutral */
.block_hints .wpbc_available_day,
.wpbc_calendar_legend .wpbc_available_day{
    background:#5a963e!important;
}
.block_hints .wpbc_booked_day,
.block_hints .wpbc_pending_day,
.block_hints .wpbc_partially_booked_day,
.wpbc_calendar_legend .wpbc_booked_day,
.wpbc_calendar_legend .wpbc_pending_day,
.wpbc_calendar_legend .wpbc_partially_booked_day{
    background:#fff3b5!important;
    border:1px solid #ddd!important;
}

.wpbc_structure_form input[name*="captcha"]{max-width:130px!important}
.site-footer,.site-below-footer-wrap{background:#fff!important}

/* Hide wizard/next if some old wizard appears */
.wpbc_wizard_step,
.wpbc_wizard_steps,
.wpbc_step,
.wpbc_steps,
.wpbc_booking_form_next,
button.wpbc_button_next,
button:has-text("Next"){
    display:none!important;
}

@media(max-width:900px){
    .wpbc_booking_form_structure.wpbc_form_right{display:block!important}
    .wpbc_structure_calendar,.wpbc_structure_form{width:100%!important;max-width:360px!important}
}
</style>
<?php
}, 99999);

add_action('wp_footer', function () {
?>
<script>
(function(){
  function removeNextWizard(){
    document.querySelectorAll('button,a,input').forEach(function(el){
      var t=(el.textContent||el.value||'').trim().toLowerCase();
      if(t==='next'){
        el.style.display='none';
      }
    });
  }
  document.addEventListener('DOMContentLoaded',removeNextWizard);
  window.addEventListener('load',removeNextWizard);
  setInterval(removeNextWizard,800);
})();
</script>
<?php
}, 99999);
