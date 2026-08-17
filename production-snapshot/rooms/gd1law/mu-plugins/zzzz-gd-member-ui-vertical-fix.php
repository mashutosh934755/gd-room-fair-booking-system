<?php
/*
Plugin Name: ZZZZ GD Member UI Vertical Stable Fix
Description: Stable group member UI: initially hidden, one-by-one add, vertical full-width fields, remove works.
Version: 2.0
*/
if (!defined('ABSPATH')) exit;

add_action('wp_head', function () {
    if (is_admin()) return;
?>
<style>
.bu-gd-members-box{
    width:100% !important;
    max-width:100% !important;
}

.gd-member-card-fixed{
    display:none;
    width:100% !important;
    max-width:100% !important;
    box-sizing:border-box !important;
    background:#ffffff !important;
    border:1px solid #d9e1ea !important;
    border-radius:12px !important;
    padding:16px !important;
    margin:14px 0 !important;
}

.gd-member-card-fixed.gd-member-visible{
    display:block !important;
}

.gd-member-card-fixed .gd-member-title-fixed{
    display:block !important;
    width:100% !important;
    color:#111 !important;
    font-weight:800 !important;
    font-size:17px !important;
    margin:0 0 12px 0 !important;
}

.gd-member-card-fixed .gd-member-fields-wrap{
    display:flex !important;
    flex-direction:column !important;
    gap:10px !important;
    width:100% !important;
}

.gd-member-card-fixed input[type="text"],
.gd-member-card-fixed input[type="email"],
.gd-member-card-fixed input:not([type]){
    display:block !important;
    width:100% !important;
    max-width:100% !important;
    min-width:100% !important;
    box-sizing:border-box !important;
    height:46px !important;
    padding:10px 12px !important;
    border:1px solid #c8d3df !important;
    border-radius:8px !important;
    font-size:15px !important;
    margin:0 !important;
}

.gd-member-card-fixed .gd-member-remove-fixed{
    display:inline-block !important;
    width:auto !important;
    min-width:120px !important;
    height:44px !important;
    margin-top:12px !important;
    padding:10px 18px !important;
    border:none !important;
    border-radius:8px !important;
    background:#c4122f !important;
    color:#fff !important;
    font-weight:800 !important;
    cursor:pointer !important;
}

.gd-member-add-fixed,
.bu-gd-add-member{
    display:inline-block !important;
    margin-top:10px !important;
    background:#336699 !important;
    color:#fff !important;
    border:none !important;
    border-radius:8px !important;
    padding:10px 16px !important;
    font-weight:800 !important;
    cursor:pointer !important;
}

@media (max-width: 768px){
    .gd-member-card-fixed{
        padding:14px !important;
    }
}
</style>
<?php
}, 100090);

add_action('wp_footer', function () {
    if (is_admin()) return;
?>
<script>
(function(){
  if(window.BU_GD_MEMBER_STABLE_FIX_LOADED) return;
  window.BU_GD_MEMBER_STABLE_FIX_LOADED = true;

  function textOf(el){
    return ((el && (el.textContent || el.value)) || '').replace(/\s+/g,' ').trim();
  }

  function getGroupBox(){
    var box = document.querySelector('.bu-gd-members-box');
    if(box) return box;

    var all = Array.from(document.querySelectorAll('div, section, fieldset, form'));
    return all.find(function(el){
      return /Group Members/i.test(textOf(el)) && /\+?\s*Add member/i.test(textOf(el));
    }) || null;
  }

  function getInputs(card){
    return Array.from(card.querySelectorAll('input[type="text"], input[type="email"], input:not([type])'))
      .filter(function(i){
        return i.type !== 'hidden';
      });
  }

  function getButtons(root){
    return Array.from(root.querySelectorAll('button, a, input[type="button"], input[type="submit"]'));
  }

  function getAddButton(box){
    return getButtons(box).find(function(b){
      return /\+?\s*Add member/i.test(textOf(b));
    }) || null;
  }

  function getRemoveButton(card){
    return getButtons(card).find(function(b){
      return /remove/i.test(textOf(b));
    }) || null;
  }

  function getCards(box){
    var cards = Array.from(box.querySelectorAll('div, fieldset, section, li')).filter(function(el){
      var txt = textOf(el);
      var inputs = getInputs(el);
      return /Member\s*\d+/i.test(txt) && inputs.length >= 3;
    });

    // deepest only
    cards = cards.filter(function(el){
      return !cards.some(function(other){
        return other !== el && el.contains(other);
      });
    });

    return cards;
  }

  function decorateCard(card, index){
    if(card.dataset.gdStableDecorated === '1') return;
    card.dataset.gdStableDecorated = '1';

    card.classList.add('gd-member-card-fixed');
    card.classList.remove('gd-member-visible');
    card.dataset.gdUserOpened = '0';

    var titleNode = Array.from(card.querySelectorAll('strong,b,h3,h4,label,div,p,span')).find(function(n){
      return /^Member\s*\d+/i.test(textOf(n));
    });

    if(titleNode){
      titleNode.classList.add('gd-member-title-fixed');
    }

    var inputs = getInputs(card).slice(0,3);

    if(inputs[0]) inputs[0].placeholder = 'Name';
    if(inputs[1]) inputs[1].placeholder = 'Email';
    if(inputs[2]) inputs[2].placeholder = 'Enrollment No.';

    var wrap = document.createElement('div');
    wrap.className = 'gd-member-fields-wrap';

    inputs.forEach(function(inp){
      wrap.appendChild(inp);
    });

    if(titleNode && titleNode.parentNode === card){
      if(titleNode.nextSibling){
        card.insertBefore(wrap, titleNode.nextSibling);
      } else {
        card.appendChild(wrap);
      }
    } else {
      card.insertBefore(wrap, card.firstChild);
    }

    var removeBtn = getRemoveButton(card);
    if(removeBtn){
      removeBtn.classList.add('gd-member-remove-fixed');

      removeBtn.addEventListener('click', function(e){
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        getInputs(card).forEach(function(input){
          input.value = '';
          input.dispatchEvent(new Event('input', {bubbles:true}));
          input.dispatchEvent(new Event('change', {bubbles:true}));
        });

        card.classList.remove('gd-member-visible');
        card.dataset.gdUserOpened = '0';

        var box = getGroupBox();
        var addBtn = box ? getAddButton(box) : null;
        if(addBtn) addBtn.style.display = '';

        return false;
      }, true);
    }
  }

  function initOnce(){
    var box = getGroupBox();
    if(!box) return;

    var cards = getCards(box);
    if(!cards.length) return;

    cards.forEach(decorateCard);

    var addBtn = getAddButton(box);
    if(!addBtn) return;

    addBtn.classList.add('gd-member-add-fixed');

    if(addBtn.dataset.gdStableBound === '1') return;
    addBtn.dataset.gdStableBound = '1';

    addBtn.addEventListener('click', function(e){
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();

      var latestBox = getGroupBox();
      var latestCards = latestBox ? getCards(latestBox) : cards;

      var hidden = latestCards.find(function(card){
        return card.dataset.gdUserOpened !== '1';
      });

      if(hidden){
        hidden.dataset.gdUserOpened = '1';
        hidden.classList.add('gd-member-visible');

        var firstInput = getInputs(hidden)[0];
        if(firstInput) firstInput.focus();
      }

      if(!latestCards.some(function(card){ return card.dataset.gdUserOpened !== '1'; })){
        addBtn.style.display = 'none';
      }

      return false;
    }, true);
  }

  document.addEventListener('DOMContentLoaded', function(){
    initOnce();
    setTimeout(initOnce, 300);
    setTimeout(initOnce, 1000);
  });

  window.addEventListener('load', function(){
    initOnce();
    setTimeout(initOnce, 500);
  });
})();
</script>
<?php
}, 100090);
