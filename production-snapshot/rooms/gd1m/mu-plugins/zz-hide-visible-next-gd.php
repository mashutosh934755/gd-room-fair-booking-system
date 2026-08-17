<?php
/*
Plugin Name: ZZ Hide Visible Next GD
Description: Hide visible wizard/next controls from GD booking pages.
Version: 1.0
*/
if (!defined('ABSPATH')) exit;

add_action('wp_head', function () {
?>
<style>
/* Hide common Booking Calendar wizard/step controls */
.wpbc_wizard_step,
.wpbc_wizard_steps,
.wpbc_step,
.wpbc_steps,
.wpbc__field_timeslot + hr,
.wpbc_booking_form_next,
.wpbc_button_next,
.wpbc-next,
.wpbc_next,
.wpbc-form-next,
.wpbc_times_selector + hr,
.wpbc_times_selector ~ .wpbc_buttons,
.wpbc_structure_times + hr,
.wpbc_structure_times ~ .wpbc_buttons,
button.wpbc_button_next,
input.wpbc_button_next,
a.wpbc_button_next {
    display: none !important;
}

/* If old wizard frame is present, keep inner calendar/form visible but hide its footer button row */
.wpbc_booking_form_structure .wpbc_buttons,
.wpbc_booking_form_structure .wpbc_navigation,
.wpbc_booking_form_structure .wpbc_step_buttons {
    display: none !important;
}
</style>
<?php
}, 99999);

add_action('wp_footer', function () {
?>
<script>
(function(){
  function hideNext(){
    document.querySelectorAll('button,input[type="button"],input[type="submit"],a,div,span').forEach(function(el){
      var text = ((el.textContent || el.value || '') + '').trim().toLowerCase();
      if (text === 'next') {
        el.style.display = 'none';
        var parent = el.closest('.wpbc_buttons,.wpbc_navigation,.wpbc_step_buttons,.wpbc_booking_form_next');
        if (parent) parent.style.display = 'none';
      }
    });
  }
  document.addEventListener('DOMContentLoaded', hideNext);
  window.addEventListener('load', hideNext);
  setInterval(hideNext, 500);
})();
</script>
<?php
}, 99999);
