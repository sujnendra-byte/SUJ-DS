<?php
/**
 * Code Snippets export — Snippet 13 ds ai pricing [CLONE]
 *
 * Snippet ID : 797
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-111
 * Modified   : 2026-09-09 02:45:58
 * Load order : 116 of 144
 */
/**
 * Snippet Name: DS AI Pricing Page
 * Run: Everywhere
 * Shortcode: [ds_ai_pricing]
 * Payment: Razorpay
 */
add_shortcode('ds_ai_pricing', function() {
	$uid		= get_current_user_id();
	$credits	= $uid ? (int)ds_get_credits($uid) : 0;
	$plan		= $uid ? ds_get_user_plan($uid) : 'free';
	$status		= $uid ? ds_get_user_plan_status($uid) : '';
	$nonce		= wp_create_nonce('wp_rest');
	$rest		= trailingslashit(rest_url('ds/v1'));
	$pricing	= function_exists('ds_get_pricing_plans') ? ds_get_pricing_plans() : array(
		'premium'  => array('label'=>'Premium','credits'=>150,'usd_price'=>'$2.99/mo','inr_price'=>'₹249/mo','plan_id_usd'=>'plan_T52Y8CXO5RLpd6','plan_id_inr'=>'plan_T52ZVGkju6U7vv'),
		'pro'      => array('label'=>'Pro','credits'=>300,'usd_price'=>'$4.99/mo','inr_price'=>'₹450/mo','plan_id_usd'=>'plan_T52aZskjDAa15X','plan_id_inr'=>'plan_T52bNWgqvAMaab'),
		'business' => array('label'=>'Business','credits'=>550,'usd_price'=>'$7.99/mo','inr_price'=>'₹650/mo','plan_id_usd'=>'plan_T52c40mtfhCQt8','plan_id_inr'=>'plan_T52d8DrwB4H5X0'),
	);
	$strip_mo = function($v) { return preg_replace('#/mo\s*$#i', '', $v); };
	ob_start();
	$ds_caps = function_exists('ds_get_saved_caps') ? ds_get_saved_caps() : array('free'=>1,'premium'=>25,'pro'=>50,'business'=>100);
	?>
<!-- Razorpay SDK -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<div id="ds-pr" style="font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#fff;">

<!-- HERO -->
<div style="text-align:center;padding:48px 20px 36px;background:linear-gradient(135deg,#0f0f23,#1a1040);">
<h2 style="font-size:32px;font-weight:900;color:#fff;margin:0 0 10px;">AI Studio Credits</h2>
<p style="font-size:15px;color:rgba(255,255,255,.5);margin:0 0 12px;">Generate images, thumbnails &amp; presentations.</p>
<div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap;margin-bottom:20px;">
<div style="background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.25);border-radius:100px;padding:5px 16px;font-size:12px;color:#6ee7b7;">✓ Top-up credits valid 1 month</div>
<div style="background:rgba(251,191,36,.12);border:1px solid rgba(251,191,36,.25);border-radius:100px;padding:5px 16px;font-size:12px;color:#fcd34d;">⟳ Plan credits reset monthly</div>
<div style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.25);border-radius:100px;padding:5px 16px;font-size:12px;color:#fca5a5;">⚠ Credits expire if plan lapses</div>
</div>
<?php if ($uid): ?>
<div style="display:inline-flex;align-items:center;gap:10px;background:rgba(99,102,241,.12);border:1.5px solid rgba(99,102,241,.25);border-radius:100px;padding:10px 24px;">
<span style="font-size:13px;color:rgba(255,255,255,.5);">Your balance</span>
<span style="font-size:22px;font-weight:900;color:#a5b4fc;" data-credits-display><?php echo number_format($credits); ?> credits</span>
</div>
<?php else: ?>
<a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" style="display:inline-block;padding:13px 32px;border-radius:100px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-weight:700;text-decoration:none;font-size:15px;">Login to Get Started</a>
<?php endif; ?>
</div>

<!-- SUCCESS / ERROR MESSAGE -->
<div id="ds-pay-msg" style="display:none;max-width:500px;margin:20px auto;padding:14px 20px;border-radius:12px;font-size:14px;font-weight:600;text-align:center;"></div>

<!-- PLANS GRID -->
<div style="padding:48px 16px;background:#0c0f22;">
<div style="text-align:center;margin-bottom:36px;">
<h3 style="font-size:24px;font-weight:800;color:#fff;margin:0 0 8px;">Choose Your Plan</h3>
<p style="font-size:14px;color:rgba(255,255,255,.55);margin:0;">Start free. Upgrade anytime.</p>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;max-width:1040px;margin:0 auto;">

<!-- ===== FREE ===== -->
<div style="background:#161b30;border:2px solid rgba(255,255,255,.1);border-radius:22px;padding:30px 22px;text-align:center;display:flex;flex-direction:column;">
<div style="font-size:11px;font-weight:800;color:rgba(255,255,255,.55);text-transform:uppercase;letter-spacing:.12em;margin-bottom:12px;">Free</div>
<div style="font-size:42px;font-weight:900;color:#fff;line-height:1;margin-bottom:4px;"></div>
<div style="font-size:12px;color:rgba(255,255,255,.35);margin-bottom:18px;">No credit card required</div>
<div style="background:rgba(255,255,255,.08);border-radius:14px;padding:14px;margin-bottom:20px;">
<div style="font-size:32px;font-weight:900;color:rgba(255,255,255,.75);">2</div>
<div style="font-size:12px;color:rgba(255,255,255,.55);margin-top:2px;">images/ Per day</div>
<div style="font-size:11px;color:rgba(255,255,255,.35);margin-top:4px;">Cloudflare and Cloudflare FLUX.2 Klein 9B models only</div>
</div>
<ul style="list-style:none;padding:0;margin:0 0 24px;text-align:left;flex:1;">
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>2 free AI images/day</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>5 free downloads/day</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Saved items: <?php echo esc_html($ds_caps['free']); ?></li>
<li style="font-size:13px;color:#d1d5db;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:rgba(255,255,255,.15);font-weight:900;margin-top:1px;flex-shrink:0;">-</span>AI Presentations</li>
<li style="font-size:13px;color:#d1d5db;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#ef4444;font-weight:900;margin-top:1px;flex-shrink:0;">✗</span>AI Video</li>
<li style="font-size:13px;color:#d1d5db;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#ef4444;font-weight:900;margin-top:1px;flex-shrink:0;">✗</span>AI Audio</li>
<li style="font-size:13px;color:#d1d5db;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#ef4444;font-weight:900;margin-top:1px;flex-shrink:0;">✗</span>Template Matcher</li>
<li style="font-size:13px;color:#d1d5db;padding:8px 0;display:flex;gap:8px;align-items:flex-start;"><span style="color:rgba(255,255,255,.15);font-weight:900;margin-top:1px;flex-shrink:0;">-</span>Priority support</li>
</ul>
<?php if (!$uid): ?>
<a href="<?php echo esc_url(wp_registration_url()); ?>" style="display:block;padding:14px;border-radius:12px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.75);font-size:14px;font-weight:800;text-decoration:none;text-align:center;">Sign Up Free</a>
<?php else: ?>
<a href="<?php echo esc_url(home_url('/ai-studio/')); ?>" style="display:block;padding:14px;border-radius:12px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.75);font-size:14px;font-weight:800;text-decoration:none;text-align:center;">Go to Studio</a>
<?php endif; ?>
</div>

<!-- ===== PREMIUM ===== -->
<div style="background:#161b30;border:2px solid rgba(255,255,255,.1);border-radius:22px;padding:30px 22px;text-align:center;display:flex;flex-direction:column;">
<div style="font-size:11px;font-weight:800;color:#6366f1;text-transform:uppercase;letter-spacing:.12em;margin-bottom:12px;">Premium</div>
<div style="font-size:42px;font-weight:900;color:#fff;line-height:1;margin-bottom:2px;"></div>
<div style="font-size:32px;font-weight:900;color:#6366f1;margin-bottom:18px;" data-inr="<?php echo esc_attr($pricing['premium']['inr_price']); ?>" data-usd="<?php echo esc_attr($pricing['premium']['usd_price']); ?>" data-inr-label="false"><?php echo esc_html($pricing['premium']['usd_price']); ?></div>
<div style="background:linear-gradient(135deg,rgba(99,102,241,.16),rgba(99,102,241,.08));border-radius:14px;padding:14px;margin-bottom:20px;">
<div style="font-size:32px;font-weight:900;color:#6366f1;"><?php echo esc_html($pricing['premium']['credits']); ?></div>
<div style="font-size:12px;color:#6366f1;margin-top:2px;">credits / month</div>
</div>
<ul style="list-style:none;padding:0;margin:0 0 24px;text-align:left;flex:1;">
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>All AI image models</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>AI Presentations (PPT)</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>500 free + 50 pro downloads/month</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Reference image (img2img)</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Saved items: <?php echo esc_html($ds_caps['premium']); ?></li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>AI Video</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>AI Audio</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Template Matcher</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Priority support</li>
</ul>
<?php if (!$uid): ?>
<a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" style="display:block;padding:14px;border-radius:12px;background:#6366f1;color:#fff;font-size:14px;font-weight:800;text-decoration:none;text-align:center;">Login to Subscribe</a>
<?php elseif ($plan === 'premium'): ?>
<div style="padding:14px;border-radius:12px;background:rgba(99,102,241,.15);color:#6366f1;font-size:14px;font-weight:800;text-align:center;margin-bottom:8px;">✓ Current Plan</div>
<button onclick="dsCancelSub()" style="width:100%;padding:10px;border-radius:10px;background:transparent;color:#ef4444;font-size:12px;font-weight:700;border:1.5px solid rgba(239,68,68,.3);cursor:pointer;font-family:inherit;">Cancel Subscription</button>
<?php elseif (in_array($plan, array('pro','business'))): ?>
<div style="padding:14px;border-radius:12px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.35);font-size:13px;font-weight:700;text-align:center;">Not available on your plan</div>
<?php else: ?>
<button onclick="dsRzpSubscribe('premium','<?php echo esc_js($pricing['premium']['plan_id_usd']); ?>','<?php echo esc_js($pricing['premium']['plan_id_inr']); ?>','Premium')" style="width:100%;padding:14px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#818cf8);color:#fff;font-size:14px;font-weight:800;border:none;cursor:pointer;font-family:inherit;">Get Premium</button>
<?php endif; ?>
</div>

<!-- ===== PRO - POPULAR ===== -->
<div style="background:#161b30;border:3px solid #6366f1;border-radius:22px;padding:30px 22px;text-align:center;position:relative;box-shadow:0 12px 48px rgba(99,102,241,.22);transform:scale(1.04);display:flex;flex-direction:column;">
<div style="position:absolute;top:-15px;left:50%;transform:translateX(-50%);background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:11px;font-weight:800;padding:5px 20px;border-radius:100px;white-space:nowrap;letter-spacing:.05em;">MOST POPULAR</div>
<div style="font-size:11px;font-weight:800;color:#6366f1;text-transform:uppercase;letter-spacing:.12em;margin-bottom:12px;">Pro</div>
<div style="font-size:42px;font-weight:900;color:#fff;line-height:1;margin-bottom:2px;"></div>
<div style="font-size:32px;font-weight:900;color:#6366f1;margin-bottom:18px;" data-inr="<?php echo esc_attr($pricing['pro']['inr_price']); ?>" data-usd="<?php echo esc_attr($pricing['pro']['usd_price']); ?>" data-inr-label="false"><?php echo esc_html($pricing['pro']['usd_price']); ?></div>
<div style="background:linear-gradient(135deg,rgba(99,102,241,.16),rgba(139,92,246,.12));border-radius:14px;padding:14px;margin-bottom:20px;">
<div style="font-size:32px;font-weight:900;color:#6366f1;"><?php echo esc_html($pricing['pro']['credits']); ?></div>
<div style="font-size:12px;color:#6366f1;margin-top:2px;">credits / month</div>
</div>
<ul style="list-style:none;padding:0;margin:0 0 24px;text-align:left;flex:1;">
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>All AI image models</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>AI Presentations (PPT)</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>1,000 free + 200 pro downloads/month</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Reference image (img2img)</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Bulk generation (4x variants)</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Saved items: <?php echo esc_html($ds_caps['pro']); ?></li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>AI Video</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>AI Audio</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Template Matcher</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Dedicated priority support</li>
</ul>
<?php if (!$uid): ?>
<a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" style="display:block;padding:14px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:14px;font-weight:800;text-decoration:none;text-align:center;">Login to Subscribe</a>
<?php elseif ($plan === 'pro'): ?>
<div style="padding:14px;border-radius:12px;background:rgba(99,102,241,.15);color:#6366f1;font-size:14px;font-weight:800;text-align:center;margin-bottom:8px;">✓ Current Plan</div>
<button onclick="dsCancelSub()" style="width:100%;padding:10px;border-radius:10px;background:transparent;color:#ef4444;font-size:12px;font-weight:700;border:1.5px solid rgba(239,68,68,.3);cursor:pointer;font-family:inherit;">Cancel Subscription</button>
<?php elseif ($plan === 'business'): ?>
<div style="padding:14px;border-radius:12px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.35);font-size:13px;font-weight:700;text-align:center;">Not available on your plan</div>
<?php else: ?>
<button onclick="dsRzpSubscribe('pro','<?php echo esc_js($pricing['pro']['plan_id_usd']); ?>','<?php echo esc_js($pricing['pro']['plan_id_inr']); ?>','Pro')" style="width:100%;padding:14px;border-radius:12px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:14px;font-weight:800;border:none;cursor:pointer;font-family:inherit;box-shadow:0 4px 18px rgba(99,102,241,.4);">Get Pro</button>
<?php endif; ?>
</div>

<!-- ===== BUSINESS ===== -->
<div style="background:#161b30;border:2px solid rgba(255,255,255,.1);border-radius:22px;padding:30px 22px;text-align:center;display:flex;flex-direction:column;">
<div style="font-size:11px;font-weight:800;color:#8b5cf6;text-transform:uppercase;letter-spacing:.12em;margin-bottom:12px;">Business</div>
<div style="font-size:42px;font-weight:900;color:#fff;line-height:1;margin-bottom:2px;"></div>
<div style="font-size:32px;font-weight:900;color:#6366f1;margin-bottom:18px;" data-inr="<?php echo esc_attr($pricing['business']['inr_price']); ?>" data-usd="<?php echo esc_attr($pricing['business']['usd_price']); ?>" data-inr-label="false"><?php echo esc_html($pricing['business']['usd_price']); ?></div>
<div style="background:linear-gradient(135deg,rgba(139,92,246,.16),rgba(124,58,237,.1));border-radius:14px;padding:14px;margin-bottom:20px;">
<div style="font-size:32px;font-weight:900;color:#8b5cf6;"><?php echo esc_html($pricing['business']['credits']); ?></div>
<div style="font-size:12px;color:#8b5cf6;margin-top:2px;">credits / month</div>
</div>
<ul style="list-style:none;padding:0;margin:0 0 24px;text-align:left;flex:1;">
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>All AI image models</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>AI Presentations (PPT)</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Unlimited free + 600 pro downloads/month</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Reference image (img2img)</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Bulk generation (4x variants)</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Saved items: <?php echo esc_html($ds_caps['business']); ?></li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>AI Video</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>AI Audio</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;border-bottom:1px solid rgba(255,255,255,.08);display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Template Matcher</li>
<li style="font-size:13px;color:rgba(255,255,255,.75);padding:8px 0;display:flex;gap:8px;align-items:flex-start;"><span style="color:#10b981;font-weight:900;margin-top:1px;flex-shrink:0;">✓</span>Dedicated priority support</li>
</ul>
<?php if (!$uid): ?>
<a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>" style="display:block;padding:14px;border-radius:12px;background:linear-gradient(135deg,#7c3aed,#8b5cf6);color:#fff;font-size:14px;font-weight:800;text-decoration:none;text-align:center;">Login to Subscribe</a>
<?php elseif ($plan === 'business'): ?>
<div style="padding:14px;border-radius:12px;background:rgba(139,92,246,.15);color:#8b5cf6;font-size:14px;font-weight:800;text-align:center;margin-bottom:8px;">✓ Current Plan</div>
<button onclick="dsCancelSub()" style="width:100%;padding:10px;border-radius:10px;background:transparent;color:#ef4444;font-size:12px;font-weight:700;border:1.5px solid rgba(239,68,68,.3);cursor:pointer;font-family:inherit;">Cancel Subscription</button>
<?php else: ?>
<button onclick="dsRzpSubscribe('business','<?php echo esc_js($pricing['business']['plan_id_usd']); ?>','<?php echo esc_js($pricing['business']['plan_id_inr']); ?>','Business')" style="width:100%;padding:14px;border-radius:12px;background:linear-gradient(135deg,#7c3aed,#8b5cf6);color:#fff;font-size:14px;font-weight:800;border:none;cursor:pointer;font-family:inherit;">Get Business</button>
<?php endif; ?>
</div>

</div>
</div>


<!-- CREDIT TOP-UPS -->
<div style="padding:44px 16px;background:#161b30;">
<div style="text-align:center;margin-bottom:28px;">
<h3 style="font-size:20px;font-weight:800;color:#fff;margin:0 0 8px;">Buy Extra Credits</h3>
<p style="font-size:13px;color:rgba(255,255,255,.55);margin:0;">One-time purchase. Valid for 1 month from the date of purchase. Use on any model.</p>
</div>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;max-width:900px;margin:0 auto;">
<?php
$topups = function_exists('ds_get_topups') ? ds_get_topups() : array(
	array('cr'=>100, 'usd'=>'1.79', 'rs'=>130, 'color'=>'#6366f1'),
	array('cr'=>200, 'usd'=>'2.89', 'rs'=>230, 'color'=>'#8b5cf6'),
	array('cr'=>300, 'usd'=>'3.99', 'rs'=>320, 'color'=>'#7c3aed'),
);
foreach ($topups as $t):
	$per = number_format($t['rs'] / $t['cr'], 2);
	$ppu = number_format($t['usd'] / $t['cr'], 3);
?>
<div style="border:2px solid rgba(255,255,255,.1);border-radius:18px;padding:24px 18px;text-align:center;transition:border-color .2s;"
	onmouseover="this.style.borderColor='<?php echo $t['color']; ?>'"
	onmouseout="this.style.borderColor='rgba(255,255,255,.1)'">
<div style="font-size:36px;font-weight:900;color:<?php echo $t['color']; ?>;line-height:1;margin-bottom:4px;"><?php echo $t['cr']; ?></div>
<div style="font-size:12px;color:rgba(255,255,255,.55);margin-bottom:16px;">credits</div>
<div style="font-size:26px;font-weight:900;color:#fff;margin-bottom:2px;"
	data-inr="₹<?php echo $t['rs']; ?>"
	data-usd="$<?php echo $t['usd']; ?>"
	data-inr-label="false">$<?php echo $t['usd']; ?></div>
<div style="font-size:10px;color:rgba(255,255,255,.35);margin-bottom:12px;line-height:1.5;">Expires after 1 month</div>
<?php if (!$uid): ?>
<a href="<?php echo esc_url(wp_login_url(get_permalink())); ?>"
	style="display:block;padding:12px;border-radius:10px;background:rgba(255,255,255,.08);color:rgba(255,255,255,.55);font-size:13px;font-weight:700;text-decoration:none;text-align:center;">
	Login to Buy
</a>
<?php elseif (in_array($plan, array('premium','pro','business'))): ?>
<button onclick="dsRzpTopup('topup_<?php echo $t['cr']; ?>',<?php echo $t['cr']; ?>,'$<?php echo $t['usd']; ?>')"
	data-usd="Buy $<?php echo $t['usd']; ?>"
	data-inr="Buy ₹<?php echo $t['rs']; ?>"
	style="width:100%;padding:12px;border-radius:10px;background:<?php echo $t['color']; ?>;color:#fff;font-size:13px;font-weight:700;border:none;cursor:pointer;font-family:inherit;">
	Buy $<?php echo $t['usd']; ?>
</button>
<?php else: ?>
<div style="padding:12px;border-radius:10px;background:rgba(255,255,255,.08);border:1.5px dashed #d1d5db;text-align:center;">
<div style="font-size:12px;font-weight:700;color:rgba(255,255,255,.55);"> Active Plan Required</div>
<div style="font-size:11px;color:rgba(255,255,255,.35);margin-top:3px;">Upgrade to Premium, Pro or Business to buy extra credits</div>
</div>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</div>
<!-- CREDIT RULES EXPLAINER -->
<div style="padding:24px 16px;background:#0c0f22;border-top:1px solid rgba(255,255,255,.1);">
<div style="max-width:720px;margin:0 auto;background:#161b30;border-radius:16px;border:1.5px solid rgba(255,255,255,.1);padding:20px 24px;">
<div style="font-size:14px;font-weight:800;color:#fff;margin-bottom:16px;"> How Credits Work</div>
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
<div style="padding:14px;background:rgba(16,185,129,.1);border-radius:12px;border:1px solid rgba(16,185,129,.25);">
<div style="font-size:13px;font-weight:800;color:#6ee7b7;margin-bottom:6px;">✓ Plan Credits</div>
<div style="font-size:12px;color:rgba(255,255,255,.75);line-height:1.6;">Included with Premium, Pro or Business subscription. <strong>Resets every month</strong> on renewal. Unused plan credits do not carry forward.</div>
</div>
<div style="padding:14px;background:rgba(59,130,246,.1);border-radius:12px;border:1px solid rgba(59,130,246,.25);">
<div style="font-size:13px;font-weight:800;color:#93c5fd;margin-bottom:6px;"> Top-up Credits</div>
<div style="font-size:12px;color:rgba(255,255,255,.75);line-height:1.6;">One-time purchase. Expires 1 month after purchase.</div>
</div>
<div style="padding:14px;background:rgba(251,146,60,.1);border-radius:12px;border:1px solid rgba(251,146,60,.25);">
<div style="font-size:13px;font-weight:800;color:#fdba74;margin-bottom:6px;">⚠ If Subscription Lapses</div>
<div style="font-size:12px;color:rgba(255,255,255,.75);line-height:1.6;">Plan credits expire when subscription ends. Top-up credits expire 1 month after purchase date.</div>
</div>
</div>
</div>
</div>

<!-- MODEL COSTS TABLE -->
<div style="padding:44px 16px;background:#0c0f22;">
<div style="text-align:center;margin-bottom:24px;">
<h3 style="font-size:20px;font-weight:800;color:#fff;margin:0 0 8px;">Credits Per Generation</h3>
<p style="font-size:13px;color:rgba(255,255,255,.55);margin:0;max-width:520px;margin:0 auto;">Prices are per image at HD, FHD or 2K output. 4K output costs more and is shown beside each model. The Studio defaults to 4K &mdash; switch output size to lower the cost.</p>
</div>
<div style="max-width:640px;margin:0 auto;background:#161b30;border-radius:18px;border:1.5px solid rgba(255,255,255,.1);overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.04);">
<?php
$live_img = function_exists('ds_get_model_costs') ? ds_get_model_costs() : array();
// Per-resolution overrides come from the same option the charge is calculated from,
// so this table can never drift from what the Studio actually deducts.
$ds_iopt = get_option('ds_image_credit_settings');
$live_ov = ( is_array($ds_iopt) && !empty($ds_iopt['resolution_overrides']) && is_array($ds_iopt['resolution_overrides']) ) ? $ds_iopt['resolution_overrides'] : array();
$ds_keys = array(
	'Cloudflare FLUX-1-Schnell'  => 'cloudflare',
	'Cloudflare FLUX.2 Klein 9B' => 'cloudflare-klein',
	'Flux Schnell'               => 'flux-schnell',
	'Flux Dev'                   => 'flux-dev',
	'Qwen Image'                 => 'qwen-image',
	'Qwen Image Edit'            => 'qwen-edit',
	'Seedream 5'                 => 'seedream',
	'Flux Pro'                   => 'flux-pro',
	'Nano Banana 2'              => 'nanobana',
);
$models = array(
	array('Cloudflare FLUX-1-Schnell', ($live_img['cloudflare'] ?? 2), 'Fastest, free tier', '#10b981', 'Basic'),
	array('Cloudflare FLUX.2 Klein 9B', ($live_img['cloudflare-klein'] ?? 3), 'High resolution output', '#10b981', 'Basic'),
	array('Flux Schnell', ($live_img['flux-schnell'] ?? 4), 'Fast + great quality', '#6366f1', 'Standard'),
	array('Flux Dev', ($live_img['flux-dev'] ?? 4), 'Professional + img2img support', '#f59e0b', 'Standard'),
	array('Qwen Image', ($live_img['qwen-image'] ?? 6), 'Reference-guided generation', '#14b8a6', 'Standard'),
	array('Qwen Image Edit', ($live_img['qwen-edit'] ?? 6), 'Edit an existing image with AI', '#0891b2', 'Standard'),
	array('Seedream 5', ($live_img['seedream'] ?? 6), 'Ultra realistic, ByteDance', '#ef4444', 'Pro'),
	array('Flux Pro', ($live_img['flux-pro'] ?? 8), 'Best quality available', '#8b5cf6', 'Pro'),
	array('Nano Banana 2', ($live_img['nanobana'] ?? 14), 'Google model, creative outputs', '#0ea5e9', 'Pro'),
);
foreach ($models as $i => $m):
	list($name, $cr, $note, $color, $tier) = $m;
	$usd = number_format($cr * 0.01, 2);
	$rs = number_format($cr * 0.83, 1);
	$bg = $i % 2 === 0 ? '#161b30' : '#10142a';
?>
<div style="display:flex;align-items:center;padding:14px 20px;background:<?php echo $bg; ?>;border-bottom:1px solid rgba(255,255,255,.08);">
<div style="flex:1;min-width:0;">
<div style="font-size:13px;font-weight:700;color:#fff;"><?php echo $name; ?></div>
<div style="font-size:11px;color:rgba(255,255,255,.35);margin-top:2px;"><?php echo $note; ?></div>
</div>
<div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
<span style="background:<?php echo $color; ?>18;color:<?php echo $color; ?>;font-size:10px;font-weight:700;padding:3px 10px;border-radius:100px;"><?php echo $tier; ?></span>
<div style="text-align:right;min-width:74px;">
<div style="font-size:16px;font-weight:900;color:#fff;"><?php echo $cr; ?>cr</div>
<?php
$ds_k  = isset($ds_keys[$name]) ? $ds_keys[$name] : '';
$ds_4k = ( $ds_k !== '' && isset($live_ov[$ds_k]['4K']) && $live_ov[$ds_k]['4K'] !== '' ) ? (int) $live_ov[$ds_k]['4K'] : 0;
if ( $ds_4k && $ds_4k !== (int) $cr ): ?>
<div style="font-size:10px;color:rgba(255,255,255,.45);white-space:nowrap;margin-top:2px;"><?php echo $ds_4k; ?>cr at 4K</div>
<?php endif; ?>
</div>
</div>
</div>
<?php endforeach; ?>
<div style="display:flex;align-items:center;padding:14px 20px;background:rgba(14,165,233,.1);border-bottom:none;">
<div style="flex:1;">
<div style="font-size:13px;font-weight:700;color:#fff;">AI Presentation (PPT)</div>
<div style="font-size:11px;color:rgba(255,255,255,.35);margin-top:2px;">Full slide deck with AI content</div>
</div>
<div style="display:flex;align-items:center;gap:10px;">
<span style="background:#0ea5e918;color:#0ea5e9;font-size:10px;font-weight:700;padding:3px 10px;border-radius:100px;">All plans</span>
<div style="text-align:right;">
<div style="font-size:16px;font-weight:900;color:#fff;"><?php $ppt_opt = get_option('ds_ppt_credit_settings'); $ppt_base = (is_array($ppt_opt) && isset($ppt_opt['base_credits']) && $ppt_opt['base_credits'] !== '') ? (int)$ppt_opt['base_credits'] : 5; echo $ppt_base; ?>cr+</div>
<div style="font-size:10px;color:rgba(255,255,255,.35);">+<?php $ppt_rate = (is_array($ppt_opt) && isset($ppt_opt['extra_slide_rate']) && $ppt_opt['extra_slide_rate'] !== '') ? (int)$ppt_opt['extra_slide_rate'] : 1; echo $ppt_rate; ?>cr/slide</div>
</div>
</div>
</div>
</div>
</div>

<!-- AI VIDEO PRICING -->
<div style="padding:0 16px 44px;background:#0c0f22;">
<div style="text-align:center;margin-bottom:24px;">
<h3 style="font-size:20px;font-weight:800;color:#fff;margin:0 0 8px;">AI Video</h3>
<p style="font-size:13px;color:rgba(255,255,255,.55);margin:0 auto;max-width:520px;">Cost depends on model, resolution and clip length. Figures below are for a 5-second clip without audio. Generating with audio doubles the cost.</p>
</div>
<div style="max-width:640px;margin:0 auto;background:#161b30;border-radius:18px;border:1.5px solid rgba(255,255,255,.1);overflow:hidden;">
<div style="display:flex;align-items:center;padding:12px 20px;background:#10142a;border-bottom:1px solid rgba(255,255,255,.08);">
<div style="flex:1;font-size:11px;font-weight:700;color:rgba(255,255,255,.45);letter-spacing:.05em;">MODEL</div>
<div style="width:58px;text-align:right;font-size:11px;font-weight:700;color:rgba(255,255,255,.45);">480p</div>
<div style="width:58px;text-align:right;font-size:11px;font-weight:700;color:rgba(255,255,255,.45);">720p</div>
<div style="width:58px;text-align:right;font-size:11px;font-weight:700;color:rgba(255,255,255,.45);">1080p</div>
</div>
<?php
// Prices come straight from ds_get_video_cost(), the same function that
// deducts the credits, so this table cannot disagree with the charge.
$ds_vids = array(
	array('Seedance 1.5 Pro', 'seedance-1.5-pro', 'Fast, good value'),
	array('Kling 3.0 (4K)',   'kling-v3.0-4k',    'Highest quality &mdash; premium pricing'),
	array('LTX-2.3',          'ltx-2.3',          'Audio generated automatically'),
);
foreach ( $ds_vids as $vi => $v ):
	list($vname, $vkey, $vnote) = $v;
	$vbg = $vi % 2 === 0 ? '#161b30' : '#10142a';
	$vcost = function_exists('ds_get_video_cost')
		? array( ds_get_video_cost($vkey, '480p', 5, false), ds_get_video_cost($vkey, '720p', 5, false), ds_get_video_cost($vkey, '1080p', 5, false) )
		: array('&mdash;','&mdash;','&mdash;');
?>
<div style="display:flex;align-items:center;padding:14px 20px;background:<?php echo $vbg; ?>;border-bottom:1px solid rgba(255,255,255,.08);">
<div style="flex:1;min-width:0;">
<div style="font-size:13px;font-weight:700;color:#fff;"><?php echo $vname; ?></div>
<div style="font-size:11px;color:rgba(255,255,255,.35);margin-top:2px;"><?php echo $vnote; ?></div>
</div>
<div style="width:58px;text-align:right;font-size:14px;font-weight:800;color:#fff;"><?php echo $vcost[0]; ?>cr</div>
<div style="width:58px;text-align:right;font-size:14px;font-weight:800;color:#fff;"><?php echo $vcost[1]; ?>cr</div>
<div style="width:58px;text-align:right;font-size:14px;font-weight:800;color:#fff;"><?php echo $vcost[2]; ?>cr</div>
</div>
<?php endforeach; ?>
<div style="padding:14px 20px;background:rgba(239,68,68,.1);">
<div style="font-size:12px;color:rgba(255,255,255,.75);line-height:1.6;">
<b style="color:#fff;">Before you pick Kling 3.0:</b> even its shortest clip costs more credits than a Premium plan grants in a month. Budget a top-up before starting one.
</div>
</div>
</div>
</div>

<!-- AI AUDIO PRICING -->
<div style="padding:44px 16px;background:#0c0f22;">
<div style="text-align:center;margin-bottom:24px;">
<h3 style="font-size:20px;font-weight:800;color:#fff;margin:0 0 8px;">AI Audio</h3>
<p style="font-size:13px;color:rgba(255,255,255,.55);margin:0;">Music, background music &amp; text-to-speech</p>
</div>
<div style="max-width:640px;margin:0 auto;background:#161b30;border-radius:18px;border:1.5px solid rgba(255,255,255,.1);overflow:hidden;box-shadow:0 2px 16px rgba(0,0,0,.04);">
<?php
$music_cost = function_exists('ds_get_music_cost') ? ds_get_music_cost(60) : 2;
$speech_cost = function_exists('ds_get_speech_cost') ? ds_get_speech_cost(str_repeat('a', 100)) : 2;
$audio_rows = array(
	array('Music / Background Music', $music_cost, 'ACE-Step, ~60s clip — longer clips cost more', '#22c55e', 'Basic'),
	array('Text to Speech', $speech_cost, 'MiniMax Speech, up to 500 characters', '#38bdf8', 'Basic'),
);
foreach ($audio_rows as $i => $m):
	list($aname, $acr, $anote, $acolor, $atier) = $m;
	$abg = $i % 2 === 0 ? '#161b30' : '#10142a';
?>
<div style="display:flex;align-items:center;padding:14px 20px;background:<?php echo $abg; ?>;border-bottom:1px solid rgba(255,255,255,.08);">
<div style="flex:1;min-width:0;">
<div style="font-size:13px;font-weight:700;color:#fff;"><?php echo $aname; ?></div>
<div style="font-size:11px;color:rgba(255,255,255,.35);margin-top:2px;"><?php echo $anote; ?></div>
</div>
<div style="display:flex;align-items:center;gap:10px;flex-shrink:0;">
<span style="background:<?php echo $acolor; ?>18;color:<?php echo $acolor; ?>;font-size:10px;font-weight:700;padding:3px 10px;border-radius:100px;"><?php echo $atier; ?></span>
<div style="text-align:right;min-width:52px;">
<div style="font-size:16px;font-weight:900;color:#fff;"><?php echo $acr; ?>cr</div>
</div>
</div>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- PLAN COMPARISON TABLE -->
<div style="padding:44px 16px;background:#161b30;">
<div style="text-align:center;margin-bottom:24px;">
<h3 style="font-size:20px;font-weight:800;color:#fff;margin:0 0 8px;">Plan Comparison</h3>
</div>
<div style="max-width:760px;margin:0 auto;overflow-x:auto;">
<table style="width:100%;border-collapse:collapse;font-size:13px;">
<thead>
<tr style="background:#0c0f22;">
<th style="text-align:left;padding:13px 16px;font-weight:700;color:rgba(255,255,255,.75);border-bottom:2px solid rgba(255,255,255,.1);">Feature</th>
<th style="text-align:center;padding:13px 12px;font-weight:700;color:rgba(255,255,255,.55);border-bottom:2px solid rgba(255,255,255,.1);">Free</th>
<th style="text-align:center;padding:13px 12px;font-weight:700;color:#6366f1;border-bottom:2px solid rgba(255,255,255,.1);">Premium</th>
<th style="text-align:center;padding:13px 12px;font-weight:800;color:#6366f1;border-bottom:2px solid #6366f1;background:rgba(99,102,241,.15);">Pro</th>
<th style="text-align:center;padding:13px 12px;font-weight:700;color:#8b5cf6;border-bottom:2px solid rgba(255,255,255,.1);">Business</th>
</tr>
</thead>
<tbody>
<?php
$save_caps = get_option('ds_saved_item_caps'); if (!is_array($save_caps)) { $save_caps = array('free'=>1,'premium'=>25,'pro'=>50,'business'=>100); }
$compare = array(
	array('Credits / month', '2 images/per day', $pricing['premium']['credits'] . 'cr', $pricing['pro']['credits'] . 'cr', $pricing['business']['credits'] . 'cr'),
	array('All AI models', '-', 'Yes', 'Yes', 'Yes'),
	array('AI Presentations', '-', 'Yes', 'Yes', 'Yes'),
	array('Template Matcher (PPTX)', '-', 'Yes', 'Yes', 'Yes'),
	array('AI Video', '-', 'Yes', 'Yes', 'Yes'),
	array('AI Audio', '-', 'Yes', 'Yes', 'Yes'),
	array('Marketplace Access', 'Standard', 'Standard','Premium','Premium'),
	array('Reference image (i2i)', '-', '-', 'Yes', 'Yes'),
	array('Bulk generation (4x)', '-', '-', '-', 'Yes'),
	array('Priority support', '-', '-', 'Yes', 'Yes'),
	array('Saved items (images/video/PPT)', $save_caps['free'] ?? 1, $save_caps['premium'] ?? 25, $save_caps['pro'] ?? 50, $save_caps['business'] ?? 100),
	array('Credits rollover', 'No', 'No', 'No', 'No'),
);
foreach ($compare as $i => $row):
	$bg = $i % 2 === 0 ? '#161b30' : '#10142a';
?>
<tr style="background:<?php echo $bg; ?>;">
<td style="padding:11px 16px;color:rgba(255,255,255,.75);border-bottom:1px solid rgba(255,255,255,.08);font-weight:600;"><?php echo $row[0]; ?></td>
<td style="padding:11px 12px;text-align:center;color:rgba(255,255,255,.55);border-bottom:1px solid rgba(255,255,255,.08);"><?php echo $row[1] === '-' ? '<span style="color:rgba(255,255,255,.15);">-</span>' : esc_html($row[1]); ?></td>
<td style="padding:11px 12px;text-align:center;color:#6366f1;border-bottom:1px solid rgba(255,255,255,.08);font-weight:600;"><?php echo $row[2] === '-' ? '<span style="color:rgba(255,255,255,.15);">-</span>' : esc_html($row[2]); ?></td>
<td style="padding:11px 12px;text-align:center;color:#6366f1;border-bottom:1px solid rgba(255,255,255,.08);font-weight:700;background:rgba(99,102,241,.08);"><?php echo $row[3] === '-' ? '<span style="color:rgba(199,210,254,.45);">-</span>' : esc_html($row[3]); ?></td>
<td style="padding:11px 12px;text-align:center;color:#8b5cf6;border-bottom:1px solid rgba(255,255,255,.08);font-weight:600;"><?php echo $row[4] === '-' ? '<span style="color:rgba(255,255,255,.15);">-</span>' : esc_html($row[4]); ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</div>

<!-- FAQ -->
<div style="padding:44px 16px 56px;background:#0c0f22;">
<div style="max-width:580px;margin:0 auto;">
<h3 style="font-size:20px;font-weight:800;color:#fff;text-align:center;margin:0 0 20px;">Questions</h3>
<?php
$faqs = array(
	array('Do credits expire?',
		'Plan credits reset on your renewal date each month and do not carry forward. Top-up credits expire one month after purchase. If you renew or upgrade before the reset, your remaining credits stay usable for a further month.'),
	array('How many free images do I get?',
		'Every new account gets 2 free AI image generations per day using the Cloudflare FLUX model or Cloudflare FLUX.2 Klein 9B model. No credit card required. Upgrade anytime to unlock more models and credits.'),
	array('What is Standard vs Pro Marketplace?',
		'Standard lets you submit images and earn credits on approval. Pro Marketplace gives higher cashback rates and priority review by our team.'),
	array('Can users outside India buy?',
		'Yes. Prices are shown and charged in Indian rupees. Razorpay accepts international cards, UPI and NetBanking; your bank converts at its own rate.'),
	array('Are credits refunded if generation fails?',
		'Yes. Credits are automatically refunded if the AI fails to generate your image.'),
	array('What is img2img (Reference image)?',
		'Available on Pro and Business plans. Upload your own image as a reference and the AI will generate a new image in a similar style or composition.'),
);
foreach ($faqs as $faq):
?>
<div style="border:1.5px solid rgba(255,255,255,.1);border-radius:12px;padding:16px 18px;margin-bottom:10px;background:#161b30;">
<div style="font-size:14px;font-weight:700;color:#fff;margin-bottom:5px;"><?php echo $faq[0]; ?></div>
<div style="font-size:13px;color:rgba(255,255,255,.55);line-height:1.65;"><?php echo $faq[1]; ?></div>
</div>
<?php endforeach; ?>

<!-- BOTTOM CTA -->
<div style="margin-top:28px;text-align:center;padding:30px 24px;background:linear-gradient(135deg,#0f0f23,#1a1040);border-radius:22px;">
<div style="font-size:19px;font-weight:800;color:#fff;margin-bottom:8px;">Ready to create?</div>
<div style="font-size:13px;color:rgba(255,255,255,.45);margin-bottom:22px;">Start free with 2 images/day. No credit card needed.</div>
<div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
<?php if (!$uid): ?>
<a href="<?php echo esc_url(wp_registration_url()); ?>" style="padding:13px 28px;border-radius:100px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:14px;font-weight:800;text-decoration:none;">Start Free</a>
<?php else: ?>
<a href="<?php echo esc_url(home_url('/ai-studio/')); ?>" style="padding:13px 28px;border-radius:100px;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;font-size:14px;font-weight:800;text-decoration:none;">Go to Studio</a>
<?php endif; ?>
</div>
</div>
</div>
</div>

</div><!-- #ds-pr -->
<script>
var DS_COUNTRY='US'; // DEFAULT TO USD FOR INTERNATIONAL - MOVED OUTSIDE
(function(){
fetch('<?php echo esc_js(rest_url('ds/v1/geo')); ?>', {credentials:'same-origin', cache:'no-store'}) /* 17.3: same decision the server uses at checkout */
.then(function(r){return r.json();})
.then(function(d){
DS_COUNTRY=d.country||'US';
if(DS_COUNTRY==='IN'){ // ONLY IF INDIA, SHOW RUPEES
document.querySelectorAll('[data-usd]').forEach(function(el){
el.textContent=el.getAttribute('data-inr');
});
document.querySelectorAll('[data-inr][data-usd]').forEach(function(el){
// For lines with BOTH attributes, swap them
if(el.getAttribute('data-inr-label') === 'false'){
el.setAttribute('data-inr-label', 'true');
el.textContent=el.getAttribute('data-inr');
}
});
}
}).catch(function(){});
/* Instant "confirming payment" screen: shown the moment Razorpay reports
   success, so the user never stares at the pricing page while /thank-you loads. */
window.dsPayLoading = function(kind){
  if (document.getElementById('ds-pay-loading')) return;
  var o = document.createElement('div');
  o.id = 'ds-pay-loading';
  o.setAttribute('role','status');
  o.style.cssText = 'position:fixed;inset:0;z-index:2147483647;display:flex;align-items:center;justify-content:center;background:rgba(9,11,26,.92);backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);font-family:Poppins,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;';
  o.innerHTML =
    '<div style="text-align:center;padding:36px 40px;border-radius:20px;background:#12162b;border:1px solid rgba(99,102,241,.35);box-shadow:0 20px 60px rgba(0,0,0,.5);max-width:360px;width:calc(100% - 40px);">' +
      '<div style="width:56px;height:56px;margin:0 auto 20px;border-radius:50%;border:4px solid rgba(99,102,241,.25);border-top-color:#6366f1;animation:dsPaySpin .8s linear infinite;"></div>' +
      '<div style="color:#fff;font-size:20px;font-weight:800;margin-bottom:8px;">Payment received!</div>' +
      '<div style="color:#a5b4fc;font-size:14px;line-height:1.5;">' + (kind === 'topup' ? 'Adding credits to your account…' : 'Activating your plan…') + '<br>Please don\'t close this page.</div>' +
    '</div>';
  var st = document.createElement('style');
  st.textContent = '@keyframes dsPaySpin{to{transform:rotate(360deg)}}';
  o.appendChild(st);
  document.body.appendChild(o);
};
/* If the browser restores this page from history (Back button), drop the overlay. */
window.addEventListener('pageshow', function(e){ if (e.persisted) { var o = document.getElementById('ds-pay-loading'); if (o) o.remove(); } });
var REST='<?php echo esc_js($rest); ?>';
var NONCE='<?php echo esc_js($nonce); ?>';
var RZP_KEY='<?php echo esc_js(function_exists("ds_rzp_config") ? ds_rzp_config()["key_id"] : ""); ?>';

// CANCEL SUBSCRIPTION
window.dsCancelSub = function() {
var endDate = '<?php echo esc_js(ds_get_subscription_end($uid)); ?>';
var endStr = endDate ? ' Your plan remains active until ' + endDate + '.' : '';
if (!confirm('Cancel your subscription?\n\nYou can still use all credits and benefits until your current billing period ends.' + endStr + '\n\nAfter that you will automatically move to the Free plan.\n\nAre you sure?')) return;
fetch(REST + 'cancel-subscription', {
method: 'POST',
headers: {'Content-Type':'application/json','X-WP-Nonce':NONCE},
})
.then(function(r){ return r.json(); })
.then(function(d) {
if (d.success) {
dsShowMsg('Subscription cancelled. ' + (d.message || 'You can use your plan until the end of your billing period, then you will move to Free.'), true);
setTimeout(function(){ window.location.href = window.location.href.split('?')[0] + '?nocache=' + Date.now(); }, 3000);
} else {
dsShowMsg('Could not cancel: ' + (d.message || 'Please contact support.'), false);
}
})
.catch(function(){ dsShowMsg('Network error. Please try again.', false); });
};

function dsShowMsg(msg, ok) {
var el = document.getElementById('ds-pay-msg');
el.textContent = msg;
el.style.display = 'block';
el.style.background = ok ? 'rgba(16,185,129,.12)' : 'rgba(239,68,68,.12)';
el.style.color = ok ? '#34d399' : '#f87171';
el.style.border = ok ? '1.5px solid rgba(16,185,129,.35)' : '1.5px solid rgba(239,68,68,.35)';
el.scrollIntoView({behavior:'smooth',block:'center'});
}

window.dsRzpSubscribe = function(planKey, planUSD, planINR, label) {
var btn = event.target;
var orig = btn.textContent;
btn.textContent = 'Loading...';
btn.disabled = true;

// Use correct plan based on detected country
var planId = (DS_COUNTRY === 'IN') ? planINR : planUSD;

fetch(REST + 'create-subscription', {
method: 'POST',
headers: {'Content-Type':'application/json','X-WP-Nonce':NONCE},
body: JSON.stringify({plan: planKey, plan_id: planId, country: DS_COUNTRY})
})
.then(function(r){ return r.json(); })
.then(function(d) {
btn.textContent = orig; btn.disabled = false;
if (!d.success) { dsShowMsg(d.message || 'Error creating subscription.', false); return; }
var options = {
key: d.key_id || RZP_KEY,
subscription_id: d.subscription_id,
name: 'Designstager AI',
description: label + ' Plan - Monthly',
prefill: {name: d.user_name, email: d.user_email},
theme: {color: '#6366f1'},
handler: function(response) {
            dsPayLoading('subscription');
            var tyParams = new URLSearchParams({
              type: 'subscription',
              plan: planKey,
              payment_id: response.razorpay_payment_id,
              subscription_id: response.razorpay_subscription_id || d.subscription_id,
              signature: response.razorpay_signature
            });
            window.location.href = '<?php echo esc_js(home_url('/thank-you')); ?>?' + tyParams.toString();
          },
modal: { ondismiss: function() { dsShowMsg('Payment cancelled. No charges were made.', false); } }
};
new Razorpay(options).open();
})
.catch(function() { btn.textContent = orig; btn.disabled = false; dsShowMsg('Network error. Please try again.', false); });
};

window.dsRzpTopup = function(topupKey, credits, price) {
var btn = event.target;
var orig = btn.textContent;
btn.textContent = 'Loading...';
btn.disabled = true;
fetch(REST + 'create-order', {
method: 'POST',
headers: {'Content-Type':'application/json','X-WP-Nonce':NONCE},
body: JSON.stringify({topup: topupKey, country: DS_COUNTRY})
})
.then(function(r){ return r.json(); })
.then(function(d) {
btn.textContent = orig; btn.disabled = false;
if (!d.success) { dsShowMsg(d.message || 'Error. Please try again.', false); return; }
var options = {
key: d.key_id || RZP_KEY,
order_id: d.order_id,
amount: d.amount,
currency: d.currency || 'INR',
name: 'Designstager AI',
description: credits + ' AI Credits',
prefill: {name: d.user_name, email: d.user_email},
theme: {color: '#6366f1'},
handler: function(response) {
            dsPayLoading('topup');
            var tyParams = new URLSearchParams({
              type: 'topup',
              credits: credits,
              payment_id: response.razorpay_payment_id,
              order_id: response.razorpay_order_id,
              signature: response.razorpay_signature
            });
            window.location.href = '<?php echo esc_js(home_url('/thank-you')); ?>?' + tyParams.toString();
          },
modal: { ondismiss: function() { dsShowMsg('Payment cancelled.', false); } }
};
new Razorpay(options).open();
})
.catch(function() { btn.textContent = orig; btn.disabled = false; dsShowMsg('Network error. Please try again.', false); });
};
})();
</script>
<?php return ob_get_clean();
});
