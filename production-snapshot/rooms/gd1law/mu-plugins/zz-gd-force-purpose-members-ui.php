<?php
/*
Plugin Name: ZZ GD Force Purpose Members UI
Description: Replaces old Remarks textarea UI with Purpose / Need and Group Members frontend UI.
Version: 1.0
*/
if (!defined('ABSPATH')) exit;

add_action('wp_head', function () {
?>
<style>
.bu-gd-purpose-box,
.bu-gd-members-box{
    max-width:640px;
    margin:12px 0 18px;
}
.bu-gd-purpose-box label,
.bu-gd-members-box h3{
    display:block;
    color:#336699;
    font-weight:800;
    font-size:16px;
    margin:0 0 8px;
}
.bu-gd-purpose-box textarea{
    width:100%!important;
    min-height:70px!important;
    border:1px solid #cbd5e1!important;
    border-radius:8px!important;
    padding:10px!important;
    font-size:14px!important;
}
.bu-gd-members-box{
    border:1px solid #d7dee8;
    background:#f8fafc;
    padding:14px;
    border-radius:10px;
}
.bu-gd-members-note{
    margin:0 0 12px!important;
    color:#4b5563!important;
    font-size:13px!important;
    line-height:1.5!important;
    font-weight:600!important;
}
.bu-gd-member-row{
    display:grid;
    grid-template-columns:1fr 1.15fr 1fr auto;
    gap:8px;
    align-items:end;
    margin:10px 0;
    padding:10px;
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius:9px;
}
.bu-gd-member-row label{
    grid-column:1/-1;
    font-weight:800;
    color:#336699;
    font-size:13px;
}
.bu-gd-member-row input{
    width:100%!important;
    max-width:100%!important;
    min-height:38px!important;
    border:1px solid #cbd5e1!important;
    border-radius:7px!important;
    padding:8px!important;
}
.bu-gd-add-member,
.bu-gd-remove-member{
    border:0!important;
    border-radius:7px!important;
    padding:8px 12px!important;
    cursor:pointer!important;
    font-weight:800!important;
}
.bu-gd-add-member{
    background:#336699!important;
    color:#fff!important;
    margin-top:8px!important;
}
.bu-gd-remove-member{
    background:#c4122f!important;
    color:#fff!important;
}
.bu-gd-old-details-hidden{
    display:none!important;
}
@media(max-width:800px){
    .bu-gd-member-row{grid-template-columns:1fr}
}
</style>
<?php
}, 99999);

add_action('wp_head', function () {
?>

<style id="zz-gd-final-label-black-fix">
.bu-gd-custom-form p,
.bu-gd-custom-form label,
.bu-gd-purpose-box label,
.bu-gd-members-box h3,
.bu-gd-members-box label,
.bu-gd-members-note,
.bu-gd-member-row label,
.booking_form p,
.booking_form label,
.wpbc_booking_form_structure p,
.wpbc_booking_form_structure label {
    color:#111 !important;
    font-weight:800 !important;
}
.bu-gd-members-note {
    color:#111 !important;
    font-weight:600 !important;
}
.bu-gd-members-box h3 {
    color:#111 !important;
}
</style>

<?php
}, 100006);

add_action('wp_footer', function () {
?>
<script>
(function(){
  function findDetailsTextarea(){
    var areas = Array.from(document.querySelectorAll('textarea'));
    return areas.find(function(ta){
      var name = (ta.getAttribute('name') || '').toLowerCase();
      var labelText = '';
      var p = ta.closest('p, div');
      if(p) labelText = (p.textContent || '').toLowerCase();

      return name.indexOf('details') !== -1 ||
             labelText.indexOf('remarks') !== -1 ||
             labelText.indexOf('student details') !== -1;
    });
  }

  function buildUI(detailsTa){
    if(!detailsTa || document.querySelector('.bu-gd-purpose-box')) return;

    var oldWrap = detailsTa.closest('p, div') || detailsTa;
    oldWrap.classList.add('bu-gd-old-details-hidden');

    var holder = document.createElement('div');
    holder.className = 'bu-gd-purpose-members-wrapper';

    holder.innerHTML = `
      <div class="bu-gd-purpose-box">
        <label>Purpose / Need*</label>
        <textarea id="bu_gd_purpose_need" placeholder="Write purpose or need for GD/Meeting Room"></textarea>
      </div>

      <div class="bu-gd-members-box">
        <h3>Group Members</h3>
        <p class="bu-gd-members-note">
          Add only those members who will actually use the GD/Meeting Room with you. For each member, provide Name, Email and Enrollment No.
          Maximum 5 additional members may be added. Total 6 people including requester.<br>
          <strong>One Booking Per Day:</strong> The requester and every group member can participate in only one GD/Meeting Room booking per day across all GD rooms. Once included in a booking, the person cannot make or join another GD Room booking on the same date.
        </p>

        ${[1,2,3,4,5].map(function(i){
          return `
            <div class="bu-gd-member-row" data-member="${i}">
              <label>Member ${i}</label>
              <input type="text" class="bu-member-name" placeholder="Name">
              <input type="email" class="bu-member-email" placeholder="Email">
              <input type="text" class="bu-member-enrollment" placeholder="Enrollment No.">
              <button type="button" class="bu-gd-remove-member">Remove</button>
            </div>
          `;
        }).join('')}

        <button type="button" class="bu-gd-add-member">+ Add member</button>
      </div>
    `;

    oldWrap.parentNode.insertBefore(holder, oldWrap);

    setupRows(holder);
  }

  function setupRows(holder){
    if(!holder || holder.dataset.memberUiBound === '1') return;
    holder.dataset.memberUiBound = '1';

    var rows = Array.from(holder.querySelectorAll('.bu-gd-member-row'));
    var addBtn = holder.querySelector('.bu-gd-add-member');

    rows.forEach(function(row){ row.style.display = 'none'; });

    if(addBtn){
      addBtn.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();

        var hidden = rows.find(function(r){ return r.style.display === 'none'; });
        if(hidden){
          hidden.style.display = '';
        }

        if(!rows.some(function(r){ return r.style.display === 'none'; })){
          addBtn.style.display = 'none';
        } else {
          addBtn.style.display = '';
        }

        return false;
      }, true);
    }

    rows.forEach(function(row){
      var remove = row.querySelector('.bu-gd-remove-member');
      if(remove){
        remove.addEventListener('click', function(e){
          e.preventDefault();
          e.stopPropagation();

          row.querySelectorAll('input').forEach(function(i){ i.value = ''; });
          row.style.display = 'none';

          if(addBtn){
            addBtn.style.display = '';
          }

          syncToDetails();
          return false;
        }, true);
      }
    });
  }

  function syncToDetails(){
    var detailsTa = findDetailsTextarea();
    if(!detailsTa) return;

    var purpose = document.querySelector('#bu_gd_purpose_need');
    var lines = [];

    if(purpose && purpose.value.trim()){
      lines.push('Purpose / Need: ' + purpose.value.trim());
    }

    document.querySelectorAll('.bu-gd-member-row').forEach(function(row){
      if(row.style.display === 'none') return;

      var name = (row.querySelector('.bu-member-name') || {}).value || '';
      var email = (row.querySelector('.bu-member-email') || {}).value || '';
      var enr = (row.querySelector('.bu-member-enrollment') || {}).value || '';

      name = name.trim();
      email = email.trim();
      enr = enr.trim();

      if(name || email || enr){
        lines.push([name, email, enr].filter(Boolean).join(' - '));
      }
    });

    detailsTa.value = lines.join("\n");
  }

  function collectData(){
    var first = document.querySelector('input[name*="name1"], input[name*="name"]');
    var second = document.querySelector('input[name*="secondname"]');
    var email = document.querySelector('input[type="email"], input[name*="email"]');
    var enrollment = document.querySelector('input[name*="enrollmentno"], input[name*="enrollment"]');

    syncToDetails();

    var detailsTa = findDetailsTextarea();

    return {
      name: ((first ? first.value : '') + ' ' + (second ? second.value : '')).trim(),
      email: email ? email.value.trim() : '',
      enrollment: enrollment ? enrollment.value.trim() : '',
      details: detailsTa ? detailsTa.value.trim() : ''
    };
  }

  function todayDate(){
    var d = new Date();
    return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0');
  }

  function duplicateExists(data){ return false; }

  function showDuplicateMessage(res){
    var msg = 'This booking cannot be completed because there is already a GD room booking for today.';

    function norm(v){
      return (v || '').toString().trim();
    }

    function currentRequester(){
      var first = document.querySelector('input[name*="name1"], input[name*="name"]');
      var second = document.querySelector('input[name*="secondname"]');
      var email = document.querySelector('input[type="email"], input[name*="email"]');
      var enr = document.querySelector('input[name*="enrollmentno"], input[name*="enrollment"]');

      return {
        name: norm((first ? first.value : '') + ' ' + (second ? second.value : '')),
        email: norm(email ? email.value : '').toLowerCase(),
        enrollment: norm(enr ? enr.value : '').toLowerCase().replace(/[^a-z0-9]/g,'')
      };
    }

    function findMemberNameFromMatched(matchedText){
      var rows = Array.from(document.querySelectorAll('.bu-gd-member-row, .gd-member-card-fixed'));
      var matched = (matchedText || '').toLowerCase();

      for(var i=0; i<rows.length; i++){
        var row = rows[i];
        var inputs = Array.from(row.querySelectorAll('input'));
        var name = '';
        var email = '';
        var enr = '';

        inputs.forEach(function(input){
          var cls = (input.className || '').toLowerCase();
          var nm = (input.getAttribute('name') || '').toLowerCase();
          var ph = (input.getAttribute('placeholder') || '').toLowerCase();
          var val = norm(input.value);

          if(cls.indexOf('name') !== -1 || nm.indexOf('name') !== -1 || ph.indexOf('name') !== -1) name = val;
          if(cls.indexOf('email') !== -1 || nm.indexOf('email') !== -1 || ph.indexOf('email') !== -1) email = val;
          if(cls.indexOf('enrollment') !== -1 || nm.indexOf('enrollment') !== -1 || ph.indexOf('enrollment') !== -1 || ph.indexOf('enro') !== -1) enr = val;
        });

        var enrClean = enr.toLowerCase().replace(/[^a-z0-9]/g,'');
        var emailClean = email.toLowerCase();

        if(emailClean && matched.indexOf(emailClean) !== -1){
          return name || email;
        }

        if(enrClean && matched.replace(/[^a-z0-9]/g,'').indexOf(enrClean) !== -1){
          return name || enr;
        }

        if(name && matched.indexOf(name.toLowerCase()) !== -1){
          return name;
        }
      }

      return '';
    }

    if(res && res.matches && res.matches.length){
      var m = res.matches[0] || {};
      var room = m.room || 'another GD room';
      var start = m.start_display || m.start_time || '';
      var end = m.end_display || m.end_time || '';
      var matchedArr = m.matched || [];
      var matchedText = matchedArr.join(', ');

      var req = currentRequester();
      var isRequester = false;

      var matchedLower = matchedText.toLowerCase();
      var matchedClean = matchedText.toLowerCase().replace(/[^a-z0-9]/g,'');

      if(req.email && matchedLower.indexOf(req.email) !== -1){
        isRequester = true;
      }

      if(req.enrollment && matchedClean.indexOf(req.enrollment) !== -1){
        isRequester = true;
      }

      if(req.name && matchedLower.indexOf(req.name.toLowerCase()) !== -1){
        isRequester = true;
      }

      var timeText = '';
      if(start && end){
        timeText = ' from ' + start + ' to ' + end;
      }

      if(isRequester){
        var requesterName = req.name ? ' (' + req.name + ')' : '';
        msg = 'You' + requesterName + ' already have a booking in ' + room + timeText + ' today. Multiple bookings are not allowed.';
      } else {
        var memberName = findMemberNameFromMatched(matchedText);
        var memberText = memberName ? ' (' + memberName + ')' : '';
        msg = 'This group member' + memberText + ' already has a booking in ' + room + timeText + ' today. Please remove that member or choose another date.';
      }
    }

    alert(msg);
  }

  function init(){
    var detailsTa = findDetailsTextarea();
    buildUI(detailsTa);
  }

  function beforeSubmit(e){ syncToDetails(); return true; }

  document.addEventListener('click', function(e){
    var btn = e.target.closest('input[type="submit"], button[type="submit"], .wpbc_button_light, .btn');
    if(!btn) return;

    var text = ((btn.value || btn.textContent || '') + '').trim().toLowerCase();
    if(text && text !== 'send' && text !== 'submit' && !btn.matches('input[type="submit"], button[type="submit"]')) return;

    return beforeSubmit(e);
  }, true);

  document.addEventListener('submit', beforeSubmit, true);

  document.addEventListener('input', syncToDetails, true);
  document.addEventListener('DOMContentLoaded', init);
  window.addEventListener('load', init);
  setInterval(init, 1000);
})();
</script>
<?php
}, 100000);
