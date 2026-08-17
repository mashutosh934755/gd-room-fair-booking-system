<?php
/**
 * Plugin Name: BU GD Booking Date Limit - Final Frontend Backend
 * Description: Allows GD booking only from today up to next 3 days. Blocks backend submission also.
 * Version: 4.0
 */

if (!defined('ABSPATH')) {
    exit;
}

function bu_gd_booking_allowed_message() {
    return 'Booking is allowed only from today up to the next 3 days.';
}

function bu_gd_normalize_date_string($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }

    $patterns = [
        '/\b(\d{4})-(\d{2})-(\d{2})\b/' => 'Y-m-d',
        '/\b(\d{2})-(\d{2})-(\d{4})\b/' => 'd-m-Y',
        '/\b(\d{2})\/(\d{2})\/(\d{4})\b/' => 'd/m/Y',
        '/\b([A-Za-z]+)\s+(\d{1,2}),\s*(\d{4})\b/' => 'F j, Y',
    ];

    foreach ($patterns as $regex => $format) {
        if (preg_match($regex, $value, $m)) {
            $date_text = $m[0];
            $dt = DateTime::createFromFormat($format, $date_text, wp_timezone());

            if ($dt instanceof DateTime) {
                $errors = DateTime::getLastErrors();
                if ($errors === false || ($errors['warning_count'] == 0 && $errors['error_count'] == 0)) {
                    $dt->setTime(0, 0, 0);
                    return $dt;
                }
            }
        }
    }

    return null;
}

function bu_gd_collect_post_values($data, &$values) {
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            bu_gd_collect_post_values($value, $values);
        } else {
            $values[] = [
                'key' => strtolower((string) $key),
                'value' => (string) $value,
            ];
        }
    }
}

function bu_gd_is_booking_submission() {
    if (($_SERVER["REQUEST_METHOD"] ?? "") !== 'POST') {
        return false;
    }

    $all = json_encode($_POST);
    $all_l = strtolower((string) $all);

    return (
        strpos($all_l, 'booking') !== false ||
        strpos($all_l, 'reservation') !== false ||
        strpos($all_l, 'time slot') !== false ||
        strpos($all_l, 'time_slots') !== false ||
        strpos($all_l, 'time') !== false ||
        strpos($all_l, 'date') !== false ||
        strpos($all_l, 'appointment') !== false
    );
}

function bu_gd_backend_validate_booking_date() {
    if (!bu_gd_is_booking_submission()) {
        return;
    }

    $values = [];
    bu_gd_collect_post_values($_POST, $values);

    $today = new DateTime('today', wp_timezone());
    $max = new DateTime('today', wp_timezone());
    $max->modify('+3 days');

    $found_date = false;
    $invalid = false;

    foreach ($values as $item) {
        $key = $item['key'];
        $value = $item['value'];

        $looks_like_date_field = (
            strpos($key, 'date') !== false ||
            strpos($key, 'day') !== false ||
            preg_match('/\b\d{4}-\d{2}-\d{2}\b/', $value) ||
            preg_match('/\b\d{2}-\d{2}-\d{4}\b/', $value) ||
            preg_match('/\b\d{2}\/\d{2}\/\d{4}\b/', $value) ||
            preg_match('/\b[A-Za-z]+\s+\d{1,2},\s*\d{4}\b/', $value)
        );

        if (!$looks_like_date_field) {
            continue;
        }

        $dt = bu_gd_normalize_date_string($value);

        if ($dt instanceof DateTime) {
            $found_date = true;

            if ($dt < $today || $dt > $max) {
                $invalid = true;
                break;
            }
        }
    }

    if ($found_date && $invalid) {
        status_header(400);
        nocache_headers();

        $msg = esc_html(bu_gd_booking_allowed_message());

        wp_die(
            '<div style="font-family:Arial,sans-serif;max-width:720px;margin:60px auto;padding:28px;border:1px solid #ddd;border-radius:14px;text-align:center;">
                <h2 style="color:#c80d2e;margin-top:0;">Booking Not Allowed</h2>
                <p style="font-size:18px;font-weight:700;">' . $msg . '</p>
                <p>Please go back and select a valid booking date.</p>
                <button onclick="history.back()" style="background:#356aa0;color:#fff;border:0;border-radius:10px;padding:12px 24px;font-size:16px;font-weight:700;cursor:pointer;">Go Back</button>
            </div>',
            'Booking Not Allowed',
            ['response' => 400]
        );
    }
}
add_action('init', 'bu_gd_backend_validate_booking_date', 0);

add_action('wp_footer', function () {
    if (is_admin()) {
        return;
    }
    ?>
<script>
(function(){
  const alertMessage = "Booking is allowed only from today up to the next 3 days.";

  function pad(n){ return String(n).padStart(2,'0'); }

  function today(){
    const d = new Date();
    d.setHours(0,0,0,0);
    return d;
  }

  function maxDate(){
    const d = today();
    d.setDate(d.getDate() + 3);
    d.setHours(0,0,0,0);
    return d;
  }

  function ymd(d){
    return d.getFullYear() + "-" + pad(d.getMonth()+1) + "-" + pad(d.getDate());
  }

  function parseDate(v){
    if(!v) return null;
    v = String(v).trim();

    let m = v.match(/^(\d{4})-(\d{2})-(\d{2})$/);
    if(m) return new Date(+m[1], +m[2]-1, +m[3]);

    m = v.match(/^(\d{2})-(\d{2})-(\d{4})$/);
    if(m) return new Date(+m[3], +m[2]-1, +m[1]);

    m = v.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
    if(m) return new Date(+m[3], +m[2]-1, +m[1]);

    m = v.match(/^([A-Za-z]+)\s+(\d{1,2}),\s*(\d{4})$/);
    if(m) {
      const d = new Date(v);
      if(!isNaN(d.getTime())) {
        d.setHours(0,0,0,0);
        return d;
      }
    }

    return null;
  }

  function isValid(d){
    if(!d || isNaN(d.getTime())) return true;
    d.setHours(0,0,0,0);
    return d >= today() && d <= maxDate();
  }

  function isDateInput(el){
    if(!el) return false;

    const type = (el.type || '').toLowerCase();
    const name = (el.name || '').toLowerCase();
    const id = (el.id || '').toLowerCase();
    const cls = (el.className || '').toString().toLowerCase();
    const ph = (el.placeholder || '').toLowerCase();
    const parent = el.closest('label,p,div,li,.field,.form-group,.booking-form,.wpforms-field');
    const pt = parent ? (parent.innerText || '').toLowerCase() : '';

    return (
      type === 'date' ||
      name.includes('date') ||
      id.includes('date') ||
      cls.includes('date') ||
      ph.includes('date') ||
      pt.includes('date')
    );
  }

  function validateInput(el, showPopup){
    if(!isDateInput(el)) return true;
    if(!el.value) return true;

    const d = parseDate(el.value);
    if(!isValid(d)){
      el.value = "";
      if(showPopup) alert(alertMessage);
      return false;
    }

    return true;
  }

  function applyInputLimit(){
    document.querySelectorAll('input').forEach(function(el){
      if(!isDateInput(el)) return;

      el.setAttribute('data-gd-date-limit','1');

      if((el.type || '').toLowerCase() === 'date'){
        el.setAttribute('min', ymd(today()));
        el.setAttribute('max', ymd(maxDate()));
      }

      el.addEventListener('change', function(){ validateInput(el, true); }, true);
      el.addEventListener('blur', function(){ validateInput(el, true); }, true);
    });
  }

  function patchJqueryDatepicker(){
    if(!window.jQuery || !jQuery.fn || !jQuery.fn.datepicker) return;

    const $ = window.jQuery;

    $('input').each(function(){
      const el = this;
      if(!isDateInput(el)) return;

      try {
        $(el).datepicker('option', 'minDate', 0);
        $(el).datepicker('option', 'maxDate', 3);
        $(el).datepicker('option', 'beforeShowDay', function(date){
          date.setHours(0,0,0,0);
          const ok = date >= today() && date <= maxDate();
          return [ok, ok ? 'gd-date-ok' : 'gd-date-disabled-hard', ok ? '' : alertMessage];
        });
      } catch(e) {}
    });
  }

  function findMonthYear(calendar){
    const months = {
      january:0, february:1, march:2, april:3, may:4, june:5,
      july:6, august:7, september:8, october:9, november:10, december:11
    };

    let month = null;
    let year = null;

    const title = calendar.querySelector('.ui-datepicker-title') || calendar;
    const txt = (title.textContent || '').toLowerCase();

    Object.keys(months).forEach(function(m){
      if(txt.includes(m)) month = months[m];
    });

    const ym = txt.match(/(20\d{2})/);
    if(ym) year = parseInt(ym[1],10);

    return {month, year};
  }

  function hardDisableCalendar(){
    document.querySelectorAll('.ui-datepicker, .datepicker, .calendar, table').forEach(function(calendar){
      const my = findMonthYear(calendar);

      calendar.querySelectorAll('td').forEach(function(td){
        const cellText = (td.textContent || '').trim();
        const day = parseInt(cellText, 10);

        if(!day || isNaN(day)) return;

        let month = td.getAttribute('data-month');
        let year = td.getAttribute('data-year');

        month = month !== null ? parseInt(month,10) : my.month;
        year = year !== null ? parseInt(year,10) : my.year;

        if(month === null || year === null || isNaN(month) || isNaN(year)) return;

        const d = new Date(year, month, day);
        d.setHours(0,0,0,0);

        const disabled = d < today() || d > maxDate();

        const a = td.querySelector('a,span') || td;

        if(disabled){
          td.classList.add('gd-date-disabled-hard');
          td.classList.remove('gd-date-ok');
          td.style.pointerEvents = 'none';

          a.removeAttribute('href');
          a.removeAttribute('onclick');
          a.setAttribute('aria-disabled','true');
          a.setAttribute('title', alertMessage);
          a.style.pointerEvents = 'none';
          a.style.background = '#e5e7eb';
          a.style.color = '#9ca3af';
          a.style.cursor = 'not-allowed';
          a.style.opacity = '0.55';
        } else {
          td.classList.remove('gd-date-disabled-hard');
          td.classList.add('gd-date-ok');
          td.style.pointerEvents = '';
          a.style.pointerEvents = '';
          a.style.cursor = '';
          a.style.opacity = '';
        }
      });
    });
  }

  function blockSubmit(){
    document.querySelectorAll('form').forEach(function(form){
      if(form.getAttribute('data-gd-date-submit-check') === '1') return;
      form.setAttribute('data-gd-date-submit-check','1');

      form.addEventListener('submit', function(e){
        let ok = true;

        form.querySelectorAll('input').forEach(function(el){
          if(!validateInput(el, false)) ok = false;
        });

        if(!ok){
          e.preventDefault();
          e.stopPropagation();
          alert(alertMessage);
          return false;
        }
      }, true);
    });
  }

  function run(){
    applyInputLimit();
    patchJqueryDatepicker();
    hardDisableCalendar();
    blockSubmit();
  }

  document.addEventListener('DOMContentLoaded', function(){
    run();

    let count = 0;
    const timer = setInterval(function(){
      run();
      count++;
      if(count > 40) clearInterval(timer);
    }, 300);

    const mo = new MutationObserver(function(){
      hardDisableCalendar();
      patchJqueryDatepicker();
    });

    mo.observe(document.body, {childList:true, subtree:true});
  });
})();
</script>

<style>
.gd-date-disabled-hard,
.gd-date-disabled-hard a,
.gd-date-disabled-hard span{
  pointer-events:none !important;
  background:#e5e7eb !important;
  color:#9ca3af !important;
  cursor:not-allowed !important;
  opacity:.55 !important;
  font-weight:600 !important;
}
</style>
    <?php
});
