<?php
/**
 * Plugin Name: BU GD Force Unique Time Slots - Law Library
 * Description: Shows 12-hour labels but sends unique 24-hour slot values to avoid duplicate AM/PM booking.
 * Version: 2.0
 */

add_action('wp_footer', function () {
    if (is_admin()) return;
    ?>
<script>
(function(){
  const slots = [
    {value:'08:00 - 10:00', label:'08:00 AM - 10:00 AM'},
    {value:'10:00 - 12:00', label:'10:00 AM - 12:00 PM'},
    {value:'12:00 - 14:00', label:'12:00 PM - 02:00 PM'},
    {value:'14:00 - 16:00', label:'02:00 PM - 04:00 PM'},
    {value:'16:00 - 18:00', label:'04:00 PM - 06:00 PM'},
    {value:'18:00 - 20:00', label:'06:00 PM - 08:00 PM'},
    {value:'20:00 - 22:00', label:'08:00 PM - 10:00 PM'},
    {value:'22:00 - 23:59', label:'10:00 PM - 12:00 AM'},
    {value:'00:00 - 02:00', label:'12:00 AM - 02:00 AM'}
  ];

  function isTimeSlotSelect(select){
    const labelText = (select.closest('label,p,div,li,.field,.form-group,.wpforms-field') || document.body).innerText.toLowerCase();
    const optionsText = Array.from(select.options).map(o => o.textContent).join(' ').toLowerCase();

    return (
      labelText.includes('time slots') ||
      labelText.includes('time slot') ||
      optionsText.includes('08:00 am') ||
      optionsText.includes('09:00 am') ||
      optionsText.includes('11:00 pm') ||
      optionsText.includes('12:00 am - 02:00 am')
    );
  }

  function rewriteSlots(){
    document.querySelectorAll('select').forEach(function(select){
      if(!isTimeSlotSelect(select)) return;

      const oldValue = select.value;
      select.innerHTML = '';

      slots.forEach(function(slot){
        const opt = document.createElement('option');
        opt.value = slot.value;
        opt.textContent = slot.label;
        opt.setAttribute('data-label', slot.label);
        select.appendChild(opt);
      });

      const matched = slots.find(s => s.value === oldValue || s.label === oldValue);
      if(matched){
        select.value = matched.value;
      } else {
        select.value = slots[0].value;
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    rewriteSlots();
    setTimeout(rewriteSlots, 300);
    setTimeout(rewriteSlots, 900);
    setTimeout(rewriteSlots, 1800);
  });
})();
</script>
    <?php
});
