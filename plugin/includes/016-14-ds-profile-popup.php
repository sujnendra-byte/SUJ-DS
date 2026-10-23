<?php
/**
 * Code Snippets export — 14 ds profile popup
 *
 * Snippet ID : 702
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-016
 * Modified   : 2026-09-09 02:45:27
 * Load order : 016 of 144
 */
/**
 * Snippet Name: DS AI Profile Popup
 * Run: Everywhere
 * Shortcode: [ds_profile_popup]
 * Adds floating profile button + popup with credits, plan, subscription management
 */
add_shortcode('ds_profile_popup', 'ds_profile_popup_shortcode');
function ds_profile_popup_shortcode() {
    if (!is_user_logged_in()) return '';
    $uid      = get_current_user_id();
    $user     = wp_get_current_user();
    $credits  = (int) ds_get_credits($uid);
    $plan     = ds_get_user_plan($uid);
    $status   = ds_get_user_plan_status($uid);
    $end_date = ds_get_subscription_end($uid);
    $pending  = ds_get_pending_downgrade($uid);
    $plans    = ds_get_plans();
    $plan_order = ds_get_plan_order();

    $plan_label   = $plans[$plan]['label']   ?? 'Free';
    $plan_credits = $plans[$plan]['credits'] ?? 0;
    $avatar       = get_avatar_url($uid, array('size'=>80));
    $nonce        = wp_create_nonce('wp_rest');
    $rest_url     = trailingslashit(rest_url('ds/v1'));

    // Upgrade/buy URLs
    $urls = array(
        'pricing'  => home_url('/pricing/'),
    );

    $plan_colors = array(
        'free'     => '#6b7280',
        'premium'  => '#6366f1',
        'pro'      => '#8b5cf6',
        'business' => '#0ea5e9',
    );
    $plan_color = $plan_colors[$plan] ?? '#6b7280';
    $current_order = $plan_order[$plan] ?? 0;

    // Status message
    $status_msg = '';
    if ($status === 'cancelled' && $end_date) {
        $status_msg = 'Plan active until ' . date('F j, Y', strtotime($end_date)) . '. After that you will be moved to Free.';
    } elseif ($pending && $end_date) {
        $pd = $plans[$pending]['label'] ?? $pending;
        $status_msg = 'Downgrading to ' . $pd . ' on ' . date('F j, Y', strtotime($end_date)) . '.';
    } elseif ($status === 'expired') {
        $status_msg = 'Your subscription has expired. Please renew to continue.';
    }

    ob_start();
?>
<!-- PROFILE TRIGGER BUTTON (shown in header/nav via shortcode) -->
<button id="dsProfBtn" onclick="dsProfOpen()" aria-label="Profile"
  style="display:inline-flex;align-items:center;gap:8px;background:transparent;border:none;cursor:pointer;padding:6px 10px;border-radius:100px;transition:background .15s;"
  onmouseover="this.style.background='rgba(99,102,241,.08)'" onmouseout="this.style.background='transparent'">
  <img src="<?php echo esc_url($avatar); ?>" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid <?php echo $plan_color; ?>;" alt="Profile"/>
  <span style="font-size:13px;font-weight:700;color:#0f0f23;" id="dsProfCrBtn"><?php echo number_format($credits); ?>cr</span>
  <span style="font-size:10px;font-weight:800;padding:2px 8px;border-radius:100px;background:<?php echo $plan_color; ?>18;color:<?php echo $plan_color; ?>;" id="dsProfPlanBtn"><?php echo esc_html($plan_label); ?></span>
</button>

<!-- POPUP OVERLAY -->
<div id="dsProfOverlay" onclick="if(event.target===this)dsProfClose()"
  style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:99998;align-items:flex-start;justify-content:flex-end;padding:70px 20px 0;">

  <!-- POPUP CARD -->
  <div id="dsProfCard" style="background:#fff;border-radius:22px;width:340px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 80px rgba(0,0,0,.18);animation:dsProfIn .2s ease;">

    <!-- HEADER -->
    <div style="padding:24px 22px 0;display:flex;align-items:center;gap:14px;">
      <img src="<?php echo esc_url($avatar); ?>" style="width:52px;height:52px;border-radius:50%;object-fit:cover;border:2.5px solid <?php echo $plan_color; ?>;flex-shrink:0;" alt="Avatar"/>
      <div style="flex:1;min-width:0;">
        <div style="font-size:15px;font-weight:800;color:#0f0f23;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html($user->display_name ?: $user->user_login); ?></div>
        <div style="font-size:12px;color:#9ca3af;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html($user->user_email); ?></div>
      </div>
      <button onclick="dsProfClose()" style="width:30px;height:30px;border-radius:50%;border:1.5px solid #e5e7eb;background:#f9fafb;cursor:pointer;font-size:15px;color:#6b7280;flex-shrink:0;display:flex;align-items:center;justify-content:center;">x</button>
    </div>

    <!-- CREDIT BALANCE -->
    <div style="margin:18px 22px 0;background:linear-gradient(135deg,#0f0f23,#1a1040);border-radius:16px;padding:18px 20px;">
      <div style="font-size:11px;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.08em;margin-bottom:6px;">Credit Balance</div>
      <div style="display:flex;align-items:baseline;gap:8px;">
        <span style="font-size:38px;font-weight:900;color:#fff;line-height:1;" id="dsProfCr"><?php echo number_format($credits); ?></span>
        <span style="font-size:14px;color:rgba(255,255,255,.5);">credits</span>
      </div>
      <div style="margin-top:10px;display:flex;align-items:center;justify-content:space-between;">
        <div>
          <div style="font-size:10px;color:rgba(255,255,255,.35);">= $<?php echo number_format($credits * 0.01, 2); ?> value</div>
        </div>
        <a href="<?php echo esc_url($urls['pricing']); ?>" style="font-size:11px;font-weight:700;color:#a5b4fc;text-decoration:none;border:1px solid rgba(165,180,252,.3);padding:4px 12px;border-radius:100px;" onclick="dsProfClose()">Buy Credits</a>
      </div>
    </div>

    <!-- PLAN BADGE -->
    <div style="margin:14px 22px 0;border:1.5px solid <?php echo $plan_color; ?>30;border-radius:14px;padding:14px 16px;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
        <div>
          <div style="font-size:10px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:3px;">Current Plan</div>
          <div style="display:flex;align-items:center;gap:8px;">
            <span style="font-size:18px;font-weight:900;color:<?php echo $plan_color; ?>;" id="dsProfPlanLbl"><?php echo esc_html($plan_label); ?></span>
            <span style="font-size:10px;padding:2px 8px;border-radius:100px;background:<?php echo $plan_color; ?>18;color:<?php echo $plan_color; ?>;font-weight:700;" id="dsProfStatusBadge"><?php echo ucfirst($status); ?></span>
          </div>
        </div>
        <?php if ($plan !== 'free' && $plan_credits > 0): ?>
        <div style="text-align:right;">
          <div style="font-size:10px;color:#9ca3af;">Monthly credits</div>
          <div style="font-size:16px;font-weight:800;color:#374151;"><?php echo $plan_credits; ?>cr</div>
        </div>
        <?php endif; ?>
      </div>
      <?php if ($plan !== 'free' && $end_date): ?>
      <div style="font-size:11px;color:#9ca3af;border-top:1px solid #f3f4f6;padding-top:8px;" id="dsProfEndDate">
        <?php echo $status === 'cancelled' ? 'Ends' : 'Renews'; ?> <?php echo date('F j, Y', strtotime($end_date)); ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- STATUS MESSAGE (cancelled / downgrade / expired) -->
    <?php if ($status_msg): ?>
    <div id="dsProfStatusMsg" style="margin:12px 22px 0;background:#fef3c7;border:1.5px solid #fcd34d;border-radius:12px;padding:12px 14px;font-size:12px;color:#92400e;line-height:1.6;">
      <?php echo esc_html($status_msg); ?>
    </div>
    <?php else: ?>
    <div id="dsProfStatusMsg" style="display:none;margin:12px 22px 0;background:#fef3c7;border:1.5px solid #fcd34d;border-radius:12px;padding:12px 14px;font-size:12px;color:#92400e;line-height:1.6;"></div>
    <?php endif; ?>

    <!-- UPGRADE / PLAN ACTIONS -->
    <div style="margin:14px 22px 0;">
      <div style="font-size:11px;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;">Plans</div>
      <?php
      $plan_list = array(
        array('id'=>'premium',  'label'=>'Premium',  'price'=>'$2.99',  'credits'=>150),
        array('id'=>'pro',      'label'=>'Pro',      'price'=>'$4.99',  'credits'=>350),
        array('id'=>'business', 'label'=>'Business', 'price'=>'$7.20',  'credits'=>600),
      );
      foreach ($plan_list as $p):
        $p_color  = $plan_colors[$p['id']];
        $p_order  = $plan_order[$p['id']] ?? 0;
        $is_current = ($p['id'] === $plan);
        $is_upgrade   = ($p_order > $current_order);
        $is_downgrade = ($p_order < $current_order);
        $is_locked    = $is_current && $status === 'active';
        $is_pending_down = ($pending === $p['id']);
      ?>
      <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 12px;border-radius:10px;background:<?php echo $is_current ? $p_color.'10' : '#f9fafb'; ?>;border:1.5px solid <?php echo $is_current ? $p_color.'40' : '#f3f4f6'; ?>;margin-bottom:6px;">
        <div>
          <div style="font-size:13px;font-weight:700;color:<?php echo $is_current ? $p_color : '#374151'; ?>;"><?php echo $p['label']; ?> <span style="font-size:11px;font-weight:400;color:#9ca3af;"><?php echo $p['price']; ?>/mo</span></div>
          <div style="font-size:11px;color:#9ca3af;"><?php echo $p['credits']; ?> credits/month</div>
        </div>
        <?php if ($is_current && $status === 'active' && !$pending): ?>
          <span style="font-size:10px;font-weight:800;padding:4px 10px;border-radius:100px;background:<?php echo $p_color; ?>;color:#fff;">Current</span>
        <?php elseif ($is_pending_down): ?>
          <span style="font-size:10px;font-weight:700;padding:4px 10px;border-radius:100px;background:#fef3c7;color:#92400e;">Pending</span>
        <?php elseif ($is_upgrade || ($is_current && ($status === 'cancelled' || $status === 'expired'))): ?>
          <button onclick="dsProfSubscribe('<?php echo esc_js($p['id']); ?>','<?php echo esc_js($p['label']); ?>')"
            style="font-size:11px;font-weight:800;padding:6px 12px;border-radius:100px;background:<?php echo $p_color; ?>;color:#fff;border:none;cursor:pointer;font-family:inherit;white-space:nowrap;">
            <?php echo $is_upgrade ? 'Upgrade' : 'Subscribe'; ?>
          </button>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- CANCEL SUBSCRIPTION -->
    <?php if ($plan !== 'free' && $status === 'active'): ?>
    <div style="margin:14px 22px 0;">
      <button id="dsProfCancelBtn" onclick="dsProfCancel()"
        style="width:100%;padding:11px;border-radius:10px;border:1.5px solid #fee2e2;background:#fff;color:#ef4444;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;">
        Cancel Subscription
      </button>
    </div>
    <?php endif; ?>

    <!-- QUICK LINKS -->
    <div style="margin:16px 22px;display:grid;grid-template-columns:1fr 1fr;gap:8px;">
      <a href="<?php echo esc_url(home_url('/ai-studio/')); ?>" onclick="dsProfClose()"
        style="display:flex;align-items:center;gap:8px;padding:11px 12px;border-radius:10px;background:#f8f9ff;border:1.5px solid #e8eaf0;text-decoration:none;color:#374151;font-size:12px;font-weight:700;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="8" height="8" rx="2" stroke="#6366f1" stroke-width="2"/><rect x="13" y="3" width="8" height="8" rx="2" stroke="#6366f1" stroke-width="2"/><rect x="3" y="13" width="8" height="8" rx="2" stroke="#6366f1" stroke-width="2"/><rect x="13" y="13" width="8" height="8" rx="2" stroke="#6366f1" stroke-width="2"/></svg>
        AI Studio
      </a>
      <a href="<?php echo esc_url(home_url('/my-generated-images/')); ?>" onclick="dsProfClose()"
        style="display:flex;align-items:center;gap:8px;padding:11px 12px;border-radius:10px;background:#f8f9ff;border:1.5px solid #e8eaf0;text-decoration:none;color:#374151;font-size:12px;font-weight:700;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="18" height="18" rx="3" stroke="#6366f1" stroke-width="2"/><path d="M3 16l5-5 4 4 3-3 4 5" stroke="#6366f1" stroke-width="2" stroke-linecap="round"/></svg>
        My Images
      </a>
      <a href="<?php echo esc_url($urls['pricing']); ?>" onclick="dsProfClose()"
        style="display:flex;align-items:center;gap:8px;padding:11px 12px;border-radius:10px;background:#f8f9ff;border:1.5px solid #e8eaf0;text-decoration:none;color:#374151;font-size:12px;font-weight:700;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" stroke="#6366f1" stroke-width="2" stroke-linejoin="round"/></svg>
        Buy Credits
      </a>
      <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>"
        style="display:flex;align-items:center;gap:8px;padding:11px 12px;border-radius:10px;background:#fff5f5;border:1.5px solid #fee2e2;text-decoration:none;color:#ef4444;font-size:12px;font-weight:700;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/><polyline points="16 17 21 12 16 7" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/><line x1="21" y1="12" x2="9" y2="12" stroke="#ef4444" stroke-width="2" stroke-linecap="round"/></svg>
        Sign Out
      </a>
    </div>

    <!-- CANCEL CONFIRM MODAL (inside card) -->
    <div id="dsProfCancelConfirm" style="display:none;margin:0 22px 22px;background:#fff5f5;border:1.5px solid #fecaca;border-radius:14px;padding:16px;">
      <div style="font-size:14px;font-weight:700;color:#0f0f23;margin-bottom:6px;">Cancel subscription?</div>
      <div style="font-size:12px;color:#6b7280;margin-bottom:14px;line-height:1.6;">Your <strong><?php echo esc_html($plan_label); ?></strong> plan will stay active until <?php echo $end_date ? date('F j, Y', strtotime($end_date)) : 'end of period'; ?>. After that you will be moved to Free.</div>
      <div style="display:flex;gap:8px;">
        <button onclick="dsProfCancelConfirm()" style="flex:1;padding:10px;border-radius:8px;background:#ef4444;color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:inherit;" id="dsProfCancelConfirmBtn">Yes, Cancel</button>
        <button onclick="dsProfCancelHide()" style="flex:1;padding:10px;border-radius:8px;background:#f3f4f6;color:#374151;font-size:13px;font-weight:600;border:none;cursor:pointer;font-family:inherit;">Keep Plan</button>
      </div>
    </div>

  </div><!-- /card -->
</div><!-- /overlay -->

<style>
@keyframes dsProfIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
#dsProfCard::-webkit-scrollbar{width:4px;}
#dsProfCard::-webkit-scrollbar-thumb{background:#e5e7eb;border-radius:4px;}
</style>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<script>
(function(){
var REST='<?php echo esc_js($rest_url); ?>';
var NONCE='<?php echo esc_js($nonce); ?>';

window.dsProfOpen=function(){
  var o=document.getElementById('dsProfOverlay');
  if(o){o.style.display='flex';}
  // Refresh credits live
  fetch(REST+'credits',{headers:{'X-WP-Nonce':NONCE}})
  .then(function(r){return r.json();})
  .then(function(d){
    if(d.credits!==undefined){
      var cr=d.credits;
      var el=document.getElementById('dsProfCr');
      if(el)el.textContent=cr.toLocaleString();
      var btn=document.getElementById('dsProfCrBtn');
      if(btn)btn.textContent=cr.toLocaleString()+'cr';
      // Update plan label
      if(d.plan_label){
        var pl=document.getElementById('dsProfPlanLbl');
        if(pl)pl.textContent=d.plan_label;
        var pb=document.getElementById('dsProfPlanBtn');
        if(pb)pb.textContent=d.plan_label;
      }
      // Show status message if returned
      if(d.status==='cancelled'&&d.end_date){
        var msg='Plan active until '+dsFormatDate(d.end_date)+'. After that you will be moved to Free.';
        dsProfShowMsg(msg);
      }
    }
  }).catch(function(){});
};

window.dsProfClose=function(){
  var o=document.getElementById('dsProfOverlay');
  if(o)o.style.display='none';
};

window.dsProfCancel=function(){
  document.getElementById('dsProfCancelConfirm').style.display='block';
  var btn=document.getElementById('dsProfCancelBtn');
  if(btn)btn.style.display='none';
};
window.dsProfCancelHide=function(){
  document.getElementById('dsProfCancelConfirm').style.display='none';
  var btn=document.getElementById('dsProfCancelBtn');
  if(btn)btn.style.display='block';
};

window.dsProfCancelConfirm=function(){
  var btn=document.getElementById('dsProfCancelConfirmBtn');
  if(btn){btn.textContent='Cancelling...';btn.disabled=true;}
  fetch(REST+'cancel-subscription',{
    method:'POST',
    headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    body:JSON.stringify({})
  })
  .then(function(r){return r.json();})
  .then(function(d){
    document.getElementById('dsProfCancelConfirm').style.display='none';
    if(d.success){
      dsProfShowMsg(d.message||'Subscription cancelled successfully.');
      var badge=document.getElementById('dsProfStatusBadge');
      if(badge)badge.textContent='Cancelled';
      var cancelBtn=document.getElementById('dsProfCancelBtn');
      if(cancelBtn)cancelBtn.style.display='none';
    } else {
      dsProfShowMsg(d.message||'Error cancelling. Please contact support.');
    }
  })
  .catch(function(){
    document.getElementById('dsProfCancelConfirm').style.display='none';
    dsProfShowMsg('Network error. Please try again or contact support.');
  });
};
	window.dsProfSubscribe=function(planKey,label){
  var btn=event.target;
  var orig=btn.textContent;
  btn.textContent='Loading...';
  btn.disabled=true;
  fetch(REST+'create-subscription',{
    method:'POST',
    headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
    body:JSON.stringify({plan:planKey})
  })
  .then(function(r){return r.json();})
  .then(function(d){
    btn.textContent=orig;btn.disabled=false;
    if(!d.success){dsProfShowMsg(d.message||'Error creating subscription.');return;}
    dsProfClose();
    var options={
      key:d.key_id,
      subscription_id:d.subscription_id,
      name:'Designstager AI',
      description:label+' Plan - Monthly',
      prefill:{name:d.user_name,email:d.user_email},
      theme:{color:'#6366f1'},
      handler:function(response){
        fetch(REST+'verify-payment',{
          method:'POST',
          headers:{'Content-Type':'application/json','X-WP-Nonce':NONCE},
          body:JSON.stringify({
            type:'subscription',plan:planKey,
            razorpay_payment_id:response.razorpay_payment_id,
            razorpay_subscription_id:response.razorpay_subscription_id,
            razorpay_signature:response.razorpay_signature
          })
        })
        .then(function(r){return r.json();})
        .then(function(v){
          if(v.success){
            window.location.href=window.location.href.split('?')[0]+'?nocache='+Date.now();
          } else {
            alert('Payment received but verification failed. Please contact support.');
          }
        });
      },
      modal:{ondismiss:function(){}}
    };
    new Razorpay(options).open();
  })
  .catch(function(){btn.textContent=orig;btn.disabled=false;dsProfShowMsg('Network error. Please try again.');});
};

function dsProfShowMsg(msg){
  var el=document.getElementById('dsProfStatusMsg');
  if(el){el.textContent=msg;el.style.display='block';}
}

function dsFormatDate(dateStr){
  if(!dateStr)return '';
  var d=new Date(dateStr);
  var months=['January','February','March','April','May','June','July','August','September','October','November','December'];
  return months[d.getMonth()]+' '+d.getDate()+', '+d.getFullYear();
}

// Close on Escape
document.addEventListener('keydown',function(e){
  if(e.key==='Escape')dsProfClose();
});
})();
</script>
<?php return ob_get_clean();
}
