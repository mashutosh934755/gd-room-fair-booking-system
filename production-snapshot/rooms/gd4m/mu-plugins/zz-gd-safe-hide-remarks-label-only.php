<?php
/*
Plugin Name: ZZ GD Safe Hide Remarks Label Only
Description: Safely hides old Remarks label text without hiding booking form.
Version: 1.0
*/
if (!defined('ABSPATH')) exit;

add_action('wp_footer', function () {
?>
<script>
(function(){
  var oldAlert = window.alert;
  window.alert = function(msg){
    if(typeof msg === 'string'){
      msg = msg.replace(/\\n/g, '\n');
    }
    return oldAlert(msg);
  };

  function safeHideRemarksLabel(){
    document.querySelectorAll('body *').forEach(function(el){
      if(el.dataset && el.dataset.remarksCleaned === '1') return;

      var txt = (el.textContent || '').replace(/\s+/g,' ').trim();

      if(
        txt === 'Remarks (All Student Details: Name and Enrollment No.)*:' ||
        txt === 'Remarks (All Student Details: Name and Enrollment No.):'
      ){
        el.dataset.remarksCleaned = '1';
        el.style.display = 'none';
      }
    });

    document.querySelectorAll('textarea').forEach(function(ta){
      var name = (ta.getAttribute('name') || '').toLowerCase();
      var wrap = ta.closest('p');
      var txt = wrap ? (wrap.textContent || '').replace(/\s+/g,' ').trim().toLowerCase() : '';

      if(name.indexOf('details') !== -1 || txt.indexOf('remarks') !== -1){
        if(wrap && wrap.querySelector('.bu-gd-purpose-box')){
          return;
        }
        /* textarea को delete/hide नहीं करना, क्योंकि इसी में Purpose + Members sync होता है */
      }
    });
  }

  document.addEventListener('DOMContentLoaded', safeHideRemarksLabel);
  window.addEventListener('load', safeHideRemarksLabel);
  setInterval(safeHideRemarksLabel, 1000);
})();
</script>
<?php
}, 100050);
