<?php
/**
 * Code Snippets export — DS Referral System — My Referrals tab (account popup)
 *
 * Snippet ID : 906
 * Active     : yes
 * Priority   : 10
 * Scope      : front-end
 * Tags       : ds-referrals
 * Modified   : 2026-09-30 04:00:14
 * Load order : 168 of 162
 */
/**
 * DS Referral System — "My Referrals" tab in the account popup
 * Run: Everywhere (front-end)
 *
 * Adds a "My Referrals" tab next to "Credit History" in the account popup and (by default)
 * removes the "Plans" tab. It does not edit the popup snippet: it adds the tab from the page
 * footer after the popup has loaded. Needs "DS Referral System — Core + Admin".
 * Turn the Plans replacement on/off in WP Admin > Referrals > Settings.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
	if (!is_user_logged_in() || !function_exists('dsrf_settings')) return;
	$s = dsrf_settings();
	if (!$s['enabled']) return;
	$rest  = esc_url_raw(rest_url('ds/v1/referrals'));
	$nonce = wp_create_nonce('wp_rest');
	?>
<style>
#ds-tab-referrals{padding:16px 20px 22px}
.dsrf-hero{background:linear-gradient(135deg,#eef2ff,#f5f3ff);border:1.5px solid #e0e7ff;border-radius:16px;padding:16px;margin-bottom:12px}
.dsrf-hero h4{margin:0 0 4px;font-size:15px;font-weight:800;color:#0f0f23;line-height:1.3}
.dsrf-hero p{margin:0;font-size:12px;color:#6b7280;line-height:1.55}
.dsrf-break{display:flex;flex-wrap:wrap;gap:6px;margin-top:10px}
.dsrf-break span{font-size:10.5px;font-weight:700;padding:3px 9px;border-radius:100px;background:#fff;color:#4f46e5;border:1px solid #e0e7ff}
.dsrf-label{font-size:10px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin:14px 0 7px}
.dsrf-linkbox{display:flex;align-items:center;gap:6px;background:#f8f9ff;border:1.5px solid #e8eaf0;border-radius:12px;padding:5px 5px 5px 12px}
.dsrf-linkbox input{flex:1;min-width:0;border:none;background:transparent;font-size:12.5px;color:#374151;font-family:inherit;outline:none;padding:6px 0}
.dsrf-btn{border:none;cursor:pointer;font-family:inherit;font-size:12px;font-weight:700;padding:8px 14px;border-radius:9px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;white-space:nowrap;transition:opacity .15s}
.dsrf-btn:hover{opacity:.88}.dsrf-btn:disabled{opacity:.6;cursor:default}
.dsrf-code{display:flex;align-items:center;justify-content:space-between;margin-top:8px;font-size:11.5px;color:#6b7280}
.dsrf-code b{font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:13px;letter-spacing:.08em;color:#0f0f23;background:#f3f4f6;padding:3px 9px;border-radius:7px;margin-left:6px}
.dsrf-code a{color:#6366f1;font-weight:700;cursor:pointer;text-decoration:none}
.dsrf-share{display:grid;grid-template-columns:repeat(5,1fr);gap:7px}
.dsrf-share a,.dsrf-share button{display:flex;flex-direction:column;align-items:center;gap:4px;padding:9px 4px;border-radius:11px;background:#f8f9ff;border:1.5px solid #eef0f6;font-size:10px;font-weight:600;color:#4b5563;text-decoration:none;cursor:pointer;font-family:inherit;transition:border-color .15s}
.dsrf-share a:hover,.dsrf-share button:hover{border-color:#c7d2fe}
.dsrf-share svg{width:18px;height:18px}
.dsrf-invite{display:flex;gap:6px}
.dsrf-invite input{flex:1;min-width:0;border:1.5px solid #e8eaf0;border-radius:10px;padding:9px 12px;font-size:12.5px;font-family:inherit;outline:none;color:#111827;background:#fff}
.dsrf-invite input:focus{border-color:#a5b4fc}
.dsrf-msg{font-size:11.5px;margin-top:6px;min-height:0}
.dsrf-msg.ok{color:#059669}.dsrf-msg.err{color:#dc2626}
.dsrf-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:7px}
.dsrf-stat{background:#f8f9ff;border-radius:12px;padding:10px 4px;text-align:center}
.dsrf-stat b{display:block;font-size:18px;font-weight:900;line-height:1.2}
.dsrf-stat span{font-size:9.5px;color:#9ca3af;font-weight:600}
.dsrf-earned{margin-top:7px;background:#ecfdf5;border-radius:12px;padding:9px 12px;font-size:12px;color:#065f46;display:flex;justify-content:space-between;align-items:center}
.dsrf-earned b{font-size:14px}
.dsrf-how{display:grid;grid-template-columns:repeat(3,1fr);gap:7px}
.dsrf-how div{background:#f9fafb;border-radius:11px;padding:10px 8px;font-size:10.5px;color:#6b7280;line-height:1.4;text-align:center}
.dsrf-how i{display:flex;align-items:center;justify-content:center;width:22px;height:22px;margin:0 auto 5px;border-radius:50%;background:#e0e7ff;color:#4f46e5;font-style:normal;font-weight:800;font-size:11px}
.dsrf-row{border:1.5px solid #f1f2f6;border-radius:13px;padding:11px 12px;margin-bottom:8px}
.dsrf-row-top{display:flex;align-items:center;gap:10px}
.dsrf-av{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#a5b4fc,#c4b5fd);color:#fff;font-weight:800;font-size:14px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.dsrf-av.inv{background:#e5e7eb;color:#6b7280}
.dsrf-who{flex:1;min-width:0}
.dsrf-who div{font-size:12.5px;font-weight:700;color:#0f0f23;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dsrf-who small{display:block;font-size:10.5px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dsrf-rw{font-size:12px;font-weight:800;white-space:nowrap}
.dsrf-steps{display:flex;gap:5px;margin-top:9px;flex-wrap:wrap}
.dsrf-step{display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:600;padding:3px 8px;border-radius:100px;background:#f3f4f6;color:#9ca3af}
.dsrf-step.done{background:#dcfce7;color:#166534}
.dsrf-step.wait{background:#fef3c7;color:#92400e}
.dsrf-step.no{background:#fee2e2;color:#991b1b}
.dsrf-step svg{width:11px;height:11px}
.dsrf-empty{text-align:center;padding:22px 10px;color:#9ca3af;font-size:12px}
.dsrf-empty div{font-size:30px;margin-bottom:6px}
.dsrf-filter{display:flex;gap:6px;margin-bottom:8px;flex-wrap:wrap}
.dsrf-filter button{border:1.5px solid #eef0f6;background:#fff;border-radius:100px;padding:4px 11px;font-size:11px;font-weight:600;color:#6b7280;cursor:pointer;font-family:inherit}
.dsrf-filter button.on{background:#6366f1;border-color:#6366f1;color:#fff}
@media (max-width:420px){.dsrf-share{grid-template-columns:repeat(3,1fr)}.dsrf-stats{grid-template-columns:repeat(2,1fr)}.dsrf-how{grid-template-columns:1fr}}
</style>
<script>
(function(){
  var REST='<?php echo esc_js($rest); ?>';
  var NONCE='<?php echo esc_js($nonce); ?>';
  var HIDE_PLANS=<?php echo $s['hide_plans_tab'] ? 'true' : 'false'; ?>;
  var DATA=null, FILTER='all', loading=false;

  function el(id){return document.getElementById(id);}
  function esc(s){return String(s==null?'':s).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];});}
  function nonce(){return (window.wpApiSettings&&wpApiSettings.nonce)?wpApiSettings.nonce:NONCE;}
  var CHECK='<svg viewBox="0 0 24 24" fill="none"><path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>';
  var CLOCK='<svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/></svg>';
  var CROSS='<svg viewBox="0 0 24 24" fill="none"><path d="M7 7l10 10M17 7L7 17" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>';

  function mount(){
    var modal=el('ds-acct-modal'), body=el('ds-modal-body');
    if(!modal||!body||el('ds-tab-referrals')) return !!el('ds-tab-referrals');
    var btns=modal.querySelectorAll('.ds-tab-btn'); if(!btns.length) return false;
    var histBtn=null, plansBtn=null;
    btns.forEach(function(b){var oc=b.getAttribute('onclick')||''; if(oc.indexOf("'history'")>-1)histBtn=b; if(oc.indexOf("'plans'")>-1)plansBtn=b;});
    var nb=document.createElement('button');
    nb.className='ds-tab-btn'; nb.type='button'; nb.textContent='My Referrals';
    nb.setAttribute('onclick',"dsTab('referrals',this)");
    var after=histBtn||btns[btns.length-1];
    after.parentNode.insertBefore(nb, after.nextSibling);
    if(HIDE_PLANS){
      // the popup can be printed more than once (desktop + mobile menu) — clean every copy
      document.querySelectorAll('.ds-tab-btn').forEach(function(b){ if((b.getAttribute('onclick')||'').indexOf("'plans'")>-1 && b.parentNode) b.parentNode.removeChild(b); });
      document.querySelectorAll('[id="ds-tab-plans"]').forEach(function(p){ if(p.parentNode) p.parentNode.removeChild(p); });
    }
    var pane=document.createElement('div');
    pane.className='ds-tab-pane'; pane.id='ds-tab-referrals';
    pane.innerHTML='<div id="dsrf-root"><div class="dsrf-empty">Loading your referrals…</div></div>';
    body.appendChild(pane);

    var orig=window.dsTab;
    window.dsTab=function(name,btn){
      if(typeof orig==='function'){ orig(name,btn); }
      else {
        document.querySelectorAll('.ds-tab-pane').forEach(function(p){p.classList.remove('active');});
        document.querySelectorAll('.ds-tab-btn').forEach(function(b){b.classList.remove('active');});
        var t=el('ds-tab-'+name); if(t)t.classList.add('active'); if(btn)btn.classList.add('active');
      }
      if(name==='referrals') load();
    };
    var origOpen=window.dsAcctOpen;
    if(typeof origOpen==='function'){
      window.dsAcctOpen=function(e){ origOpen(e); var p=el('ds-tab-referrals'); if(p&&p.classList.contains('active')) load(); };
    }
    return true;
  }

  function load(){
    if(loading) return; loading=true;
    fetch(REST,{credentials:'same-origin',headers:{'X-WP-Nonce':nonce()}})
      .then(function(r){return r.json();})
      .then(function(d){ loading=false; if(!d||!d.code){ el('dsrf-root').innerHTML='<div class="dsrf-empty">Could not load referrals. Please refresh the page.</div>'; return; } DATA=d; render(); })
      .catch(function(){ loading=false; el('dsrf-root').innerHTML='<div class="dsrf-empty">Could not load referrals. Please check your connection.</div>'; });
  }

  function shareText(){ return 'I use '+(DATA.site||'DesignStager')+' to create AI presentations, images and videos. Sign up with my link: '; }

  function render(){
    var d=DATA, st=d.stats||{}, h='';
    h+='<div class="dsrf-hero"><h4>Invite friends, earn rewards</h4><p>'+esc(d.reward_text)+' The reward is added '+(d.wait_days>0?('once your friend completes '+d.wait_days+' days of their subscription'):'as soon as your friend subscribes')+'.</p>';
    if(d.breakdown&&d.breakdown.length>1){ h+='<div class="dsrf-break">'; d.breakdown.forEach(function(b){h+='<span>'+esc(b.plan)+': '+esc(b.reward)+'</span>';}); h+='</div>'; }
    h+='</div>';

    h+='<div class="dsrf-label">Your referral link</div>';
    h+='<div class="dsrf-linkbox"><input id="dsrf-link" type="text" readonly value="'+esc(d.link)+'" onclick="this.select()"><button class="dsrf-btn" id="dsrf-copy" type="button">Copy link</button></div>';
    h+='<div class="dsrf-code"><span>Your code<b>'+esc(d.code)+'</b></span><a id="dsrf-copy-code">Copy code</a></div>';

    var u=encodeURIComponent(d.link), t=encodeURIComponent(shareText());
    h+='<div class="dsrf-label">Share</div><div class="dsrf-share">';
    h+='<a target="_blank" rel="noopener" href="https://wa.me/?text='+t+u+'"><svg viewBox="0 0 24 24" fill="#25D366"><path d="M12 2a10 10 0 00-8.6 15.1L2 22l5-1.3A10 10 0 1012 2zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.9s.7-2 1-2.3c.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.3 0 .5l-.4.6-.3.4c-.1.1-.3.3-.1.6.2.3.7 1.1 1.4 1.8.9.8 1.7 1.1 2 1.2.3.1.4.1.6-.1l.8-1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3.1.2.1.8-.1 1.4z"/></svg>WhatsApp</a>';
    h+='<a target="_blank" rel="noopener" href="https://twitter.com/intent/tweet?text='+t+'&url='+u+'"><svg viewBox="0 0 24 24" fill="#111"><path d="M17.8 3h3.1l-6.8 7.7L22 21h-6.2l-4.9-6.3L5.3 21H2.2l7.2-8.3L1.8 3h6.3l4.4 5.8L17.8 3zm-1.1 16.2h1.7L7.2 4.7H5.4l11.3 14.5z"/></svg>X</a>';
    h+='<a target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u='+u+'"><svg viewBox="0 0 24 24" fill="#1877F2"><path d="M22 12a10 10 0 10-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.3v7A10 10 0 0022 12z"/></svg>Facebook</a>';
    h+='<a target="_blank" rel="noopener" href="https://www.linkedin.com/sharing/share-offsite/?url='+u+'"><svg viewBox="0 0 24 24" fill="#0A66C2"><path d="M20.4 20.5h-3.6v-5.6c0-1.3 0-3-1.8-3s-2.1 1.4-2.1 2.9v5.7H9.3V9h3.4v1.6c.5-.9 1.6-1.8 3.4-1.8 3.6 0 4.3 2.4 4.3 5.5v6.2zM5.3 7.4a2.1 2.1 0 110-4.2 2.1 2.1 0 010 4.2zM7.1 20.5H3.5V9h3.6v11.5zM22.2 0H1.8C.8 0 0 .8 0 1.7v20.6c0 .9.8 1.7 1.8 1.7h20.4c1 0 1.8-.8 1.8-1.7V1.7C24 .8 23.2 0 22.2 0z"/></svg>LinkedIn</a>';
    h+='<button type="button" id="dsrf-native"><svg viewBox="0 0 24 24" fill="none"><circle cx="18" cy="5" r="2.5" stroke="#6366f1" stroke-width="2"/><circle cx="6" cy="12" r="2.5" stroke="#6366f1" stroke-width="2"/><circle cx="18" cy="19" r="2.5" stroke="#6366f1" stroke-width="2"/><path d="M8.2 10.8l7.6-4.4M8.2 13.2l7.6 4.4" stroke="#6366f1" stroke-width="2"/></svg>More</button>';
    h+='</div>';

    h+='<div class="dsrf-label">Invite by e-mail</div>';
    h+='<form class="dsrf-invite" id="dsrf-invite-form"><input type="email" id="dsrf-email" placeholder="friend@example.com" required><button class="dsrf-btn" id="dsrf-send" type="submit">Send invite</button></form><div class="dsrf-msg" id="dsrf-msg"></div>';

    h+='<div class="dsrf-label">Your results</div><div class="dsrf-stats">';
    h+='<div class="dsrf-stat"><b style="color:#6366f1">'+(st.clicks||0)+'</b><span>Link visits</span></div>';
    h+='<div class="dsrf-stat"><b style="color:#0ea5e9">'+(st.signed_up||0)+'</b><span>Signed up</span></div>';
    h+='<div class="dsrf-stat"><b style="color:#f59e0b">'+(st.subscribed||0)+'</b><span>Subscribed</span></div>';
    h+='<div class="dsrf-stat"><b style="color:#10b981">'+(st.rewarded||0)+'</b><span>Rewarded</span></div>';
    h+='</div>';
    if(st.credits_earned>0||st.cash_earned){
      h+='<div class="dsrf-earned"><span>Total earned from referrals</span><b>'+(st.credits_earned>0?('+'+st.credits_earned+' credits'):'')+(st.credits_earned>0&&st.cash_earned?' · ':'')+esc(st.cash_earned||'')+'</b></div>';
    }

    var items=d.items||[];
    h+='<div class="dsrf-label">My referrals ('+items.length+')</div>';
    if(!items.length){
      h+='<div class="dsrf-how"><div><i>1</i>Share your link with friends</div><div><i>2</i>They sign up and subscribe to a plan</div><div><i>3</i>You get your reward'+(d.wait_days>0?' after '+d.wait_days+' days':'')+'</div></div>';
      h+='<div class="dsrf-empty"><div>🤝</div>No referrals yet. Share your link to get started!</div>';
    } else {
      var f=[['all','All'],['signed','Signed up'],['subscribed','Subscribed'],['rewarded','Rewarded'],['invited','Invited']];
      h+='<div class="dsrf-filter">'; f.forEach(function(x){h+='<button type="button" data-f="'+x[0]+'" class="'+(FILTER===x[0]?'on':'')+'">'+x[1]+'</button>';}); h+='</div>';
      h+='<div id="dsrf-list">'+listHtml(items, d)+'</div>';
    }
    el('dsrf-root').innerHTML=h;
    bind();
  }

  function listHtml(items, d){
    var out='', shown=0;
    items.forEach(function(it){
      if(FILTER==='signed'&&!it.signed_up) return;
      if(FILTER==='subscribed'&&!it.subscribed) return;
      if(FILTER==='rewarded'&&it.status!=='rewarded') return;
      if(FILTER==='invited'&&it.status!=='invited') return;
      shown++;
      var name=it.name||it.email, init=(name||'?').charAt(0).toUpperCase();
      var sub=it.signed_up?(it.email+(it.signed_on?' · joined '+it.signed_on:'')):('Invited '+(it.invited_on||''));
      var rw='';
      if(it.status==='rewarded') rw='<span class="dsrf-rw" style="color:#059669">'+esc(it.reward)+'</span>';
      out+='<div class="dsrf-row"><div class="dsrf-row-top"><div class="dsrf-av'+(it.signed_up?'':' inv')+'">'+esc(init)+'</div><div class="dsrf-who"><div>'+esc(name)+'</div><small>'+esc(sub)+'</small></div>'+rw+'</div><div class="dsrf-steps">';
      // step 1: signed up
      out+=it.signed_up?'<span class="dsrf-step done">'+CHECK+'Signed up</span>':'<span class="dsrf-step wait">'+CLOCK+'Invite sent</span>';
      // step 2: subscribed
      if(it.subscribed) out+='<span class="dsrf-step done">'+CHECK+'Subscribed'+(it.plan?' · '+esc(it.plan):'')+'</span>';
      else if(it.signed_up&&it.status!=='rejected') out+='<span class="dsrf-step">'+CLOCK+'Not subscribed yet</span>';
      // step 3: reward
      if(it.status==='rewarded') out+='<span class="dsrf-step done">'+CHECK+'Reward '+esc(it.rewarded_on)+'</span>';
      else if(it.status==='subscribed') out+='<span class="dsrf-step wait">'+CLOCK+'Reward on '+esc(it.eligible_on)+'</span>';
      else if(it.status==='rejected') out+='<span class="dsrf-step no">'+CROSS+esc(it.note||'Not eligible')+'</span>';
      out+='</div></div>';
    });
    if(!shown) out='<div class="dsrf-empty">Nothing here yet.</div>';
    return out;
  }

  function copy(text, btn, label){
    function done(){ if(!btn) return; var o=btn.textContent; btn.textContent='Copied!'; setTimeout(function(){btn.textContent=label||o;},1600); }
    if(navigator.clipboard&&window.isSecureContext){ navigator.clipboard.writeText(text).then(done).catch(fallback); } else fallback();
    function fallback(){ var ta=document.createElement('textarea'); ta.value=text; ta.style.position='fixed'; ta.style.opacity='0'; document.body.appendChild(ta); ta.select(); try{document.execCommand('copy');}catch(e){} document.body.removeChild(ta); done(); }
  }

  function bind(){
    var c=el('dsrf-copy'); if(c) c.onclick=function(){copy(DATA.link,c,'Copy link');};
    var cc=el('dsrf-copy-code'); if(cc) cc.onclick=function(){copy(DATA.code,cc,'Copy code');};
    var nat=el('dsrf-native');
    if(nat) nat.onclick=function(){
      if(navigator.share){ navigator.share({title:DATA.site, text:shareText(), url:DATA.link}).catch(function(){}); }
      else { location.href='mailto:?subject='+encodeURIComponent('Join me on '+DATA.site)+'&body='+encodeURIComponent(shareText()+DATA.link); }
    };
    var fm=el('dsrf-invite-form');
    if(fm) fm.onsubmit=function(e){
      e.preventDefault();
      var em=el('dsrf-email').value.trim(), btn=el('dsrf-send'), msg=el('dsrf-msg');
      if(!em) return;
      btn.disabled=true; btn.textContent='Sending…'; msg.className='dsrf-msg'; msg.textContent='';
      fetch(REST+'/invite',{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':nonce()},body:JSON.stringify({email:em})})
        .then(function(r){return r.json().then(function(j){return {ok:r.ok,j:j};});})
        .then(function(res){
          btn.disabled=false; btn.textContent='Send invite';
          if(res.ok&&res.j.success){ msg.className='dsrf-msg ok'; msg.textContent=res.j.message||'Invite sent.'; setTimeout(load,600); }
          else { msg.className='dsrf-msg err'; msg.textContent=(res.j&&res.j.message)||'Could not send the invite.'; }
        })
        .catch(function(){ btn.disabled=false; btn.textContent='Send invite'; msg.className='dsrf-msg err'; msg.textContent='Network error. Please try again.'; });
    };
    document.querySelectorAll('.dsrf-filter button').forEach(function(b){
      b.onclick=function(){ FILTER=b.getAttribute('data-f'); document.querySelectorAll('.dsrf-filter button').forEach(function(x){x.classList.toggle('on',x===b);}); el('dsrf-list').innerHTML=listHtml(DATA.items||[],DATA); };
    });
  }

  if(!mount()){
    var tries=0, iv=setInterval(function(){ tries++; if(mount()||tries>40) clearInterval(iv); },250);
    document.addEventListener('DOMContentLoaded', mount);
  }
})();
</script>
	<?php
}, 100);
