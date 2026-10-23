<?php
/**
 * Code Snippets export — 20 DS PPT Public Share
 *
 * Snippet ID : 719
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-033
 * Modified   : 2026-09-09 02:44:32
 * Load order : 033 of 144
 */
/**
 * Snippet 20: DS PPT Public Share
 * Run: Everywhere
 * Lets a logged-in user generate a public, no-login-required view link
 * for their presentation, and serves that link to anyone who opens it.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/create-share-link', array(
        'methods'  => 'POST',
        'callback' => 'ds_ppt_create_share_link',
        'permission_callback' => function(){ return is_user_logged_in(); },
    ));
    register_rest_route('ds/v1', '/shared-ppt/(?P<token>[A-Za-z0-9]+)', array(
        'methods'  => 'GET',
        'callback' => 'ds_ppt_get_shared',
        'permission_callback' => '__return_true',
    ));
});

if ( ! function_exists('ds_ppt_create_share_link') ) {
function ds_ppt_create_share_link( WP_REST_Request $req ) {
    $uid    = get_current_user_id();
    $title  = sanitize_text_field( $req->get_param('title') ?: 'Presentation' );
    $design = sanitize_text_field( $req->get_param('design') ?: 'navy-pro' );
    $slides = $req->get_param('slides');
    $token  = sanitize_text_field( $req->get_param('share_token') );

    if ( ! is_array($slides) || empty($slides) ) {
        return rest_ensure_response(array('success'=>false,'error'=>'No slide data'));
    }

    // Reuse the existing link if this user already shared this deck once.
    $existing = $token ? get_option('ds_share_'.$token) : false;
    if ( ! $existing || $existing['owner'] != $uid ) {
        $token = wp_generate_password(20, false, false);
    }

    update_option('ds_share_'.$token, array(
        'owner'   => $uid,
        'title'   => $title,
        'design'  => $design,
        'slides'  => $slides,
        'created' => time(),
    ), false);

    return rest_ensure_response(array(
        'success'     => true,
        'share_token' => $token,
        'share_url'   => home_url('/?ds_view='.$token),
    ));
}
}

if ( ! function_exists('ds_ppt_get_shared') ) {
function ds_ppt_get_shared( WP_REST_Request $req ) {
    $token = sanitize_text_field( $req->get_param('token') );
    $rec   = get_option('ds_share_'.$token);
    if ( !empty($rec['created']) && (time() - $rec['created']) > 90*DAY_IN_SECONDS ) {
        $rec = false; // expire after 90 days
    }
    if ( ! $rec || empty($rec['slides']) ) {
        return rest_ensure_response(array('success'=>false,'error'=>'Link not found'));
    }
    return rest_ensure_response(array(
        'success' => true,
        'title'   => $rec['title'],
        'design'  => $rec['design'],
        'slides'  => $rec['slides'],
    ));
}
}

/* ===== Public viewer page: yoursite.com/?ds_view=TOKEN ===== */
add_action('template_redirect', function () {
    if ( empty($_GET['ds_view']) ) return;
    $token = sanitize_text_field( wp_unslash($_GET['ds_view']) );
    $rec   = get_option('ds_share_'.$token);
if ( !empty($rec['created']) && (time() - $rec['created']) > 90*DAY_IN_SECONDS ) {
        $rec = false; // expire after 90 days
    }
    header('Content-Type: text/html; charset=utf-8');

    if ( ! $rec || empty($rec['slides']) ) {
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Link not found</title></head><body style="background:#111;color:#9ca3af;font-family:-apple-system,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;margin:0;"><div style="text-align:center;"><h2 style="color:#e5e7eb;">This link is invalid or has expired</h2></div></body></html>';
        exit;
    }

    $payload = wp_json_encode(array(
        'title'  => $rec['title'],
        'design' => $rec['design'],
        'slides' => $rec['slides'],
    ));
    ?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo esc_html($rec['title']); ?></title>
<style>
*{box-sizing:border-box;}
body{margin:0;background:#0a0a0a;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;overflow:hidden;}
#dsv-stage{position:relative;width:90vw;max-width:1100px;aspect-ratio:16/9;border-radius:6px;overflow:hidden;box-shadow:0 10px 40px rgba(0,0,0,.6);}
#dsv-nav{display:flex;align-items:center;gap:18px;margin-top:18px;color:#9ca3af;font-size:13px;font-family:inherit;}
#dsv-nav button{background:#1f1f1f;border:1px solid #2a2a2a;color:#e5e7eb;border-radius:6px;width:34px;height:34px;cursor:pointer;font-size:16px;}
#dsv-nav button:hover{background:#2a2a2a;}
#dsv-nav button:disabled{opacity:.3;cursor:default;}
.dsv-imgzone{position:absolute;background-size:cover;background-position:center;}
</style>
</head>
<body>
<div id="dsv-stage"></div>
<div id="dsv-nav">
  <button id="dsv-prev">&#8249;</button>
  <span id="dsv-count">1 / 1</span>
  <button id="dsv-next">&#8250;</button>
</div>
<script>
var DATA=<?php echo $payload; ?>;
var TH={
  'navy-pro':{bg:'#1B2A4A',tc:'#FFFFFF',bc:'#C8D8F0',ac:'#4A9EFF'},
  'dark-modern':{bg:'#0D1117',tc:'#FFFFFF',bc:'#C9D1D9',ac:'#58A6FF'},
  'clean-corporate':{bg:'#FFFFFF',tc:'#1A365D',bc:'#2D3748',ac:'#2B6CB0'},
  'bold-red':{bg:'#1A0000',tc:'#FFFFFF',bc:'#FFD0D0',ac:'#FF4444'},
  'corporate-dark':{bg:'#111827',tc:'#F9FAFB',bc:'#D1D5DB',ac:'#6366F1'},
  'clean-white':{bg:'#F8FAFC',tc:'#0F172A',bc:'#334155',ac:'#0EA5E9'},
  'gradient-pro':{bg:'#0F0C29',tc:'#FFFFFF',bc:'#E0D7FF',ac:'#A78BFA'},
  'bold-impact':{bg:'#111111',tc:'#FFFFFF',bc:'#CCCCCC',ac:'#FFD700'}
};
var t=TH[DATA.design]||TH['navy-pro'];
var idx=0;
function esc(s){return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}
/* The picture comes from whoever shared the deck and goes inside a style="" attribute:
   only a real image (base64 data or a plain web address) may pass, never quotes or HTML. */
function safeImg(u){u=String(u||'');return /^(data:image\/[a-z0-9.+-]+;base64,[A-Za-z0-9+\/=\s]+|https?:\/\/[^\s"'()<>\\]+)$/i.test(u)?u:'';}
function imgZone(img,css){
  img=safeImg(img);
  var bg=img?'background-image:url('+img+');':'background:linear-gradient(135deg,#1a3a5c,#0d1f35);';
  return '<div class="dsv-imgzone" style="'+css+bg+'"></div>';
}
function splitItem(item){
  var i=item.indexOf(':');
  if(i===-1)return{h:item,d:''};
  return{h:item.slice(0,i).trim(),d:item.slice(i+1).trim()};
}
function contentGrid(items){
  if(!items||!items.length)return'';
  var cols=items.length>=4?2:items.length;
  var cells=items.map(function(it){
    var p=splitItem(it);
    return '<div style="background:rgba(255,255,255,.06);border-radius:8px;padding:14px 16px;">'
      +'<div style="font-weight:800;color:'+t.tc+';font-size:.95em;margin-bottom:5px;">'+esc(p.h)+'</div>'
      +(p.d?'<div style="color:'+t.bc+';font-size:.8em;line-height:1.5;">'+esc(p.d)+'</div>':'')
      +'</div>';
  }).join('');
  return '<div style="display:grid;grid-template-columns:repeat('+cols+',1fr);gap:12px;margin-top:14px;">'+cells+'</div>';
}
function render(s,v){
  var img=s.image_data||null;
  var H='<div style="position:absolute;inset:0;background:'+t.bg+';">';
  if(s.type==='title'){
    if(img)H+=imgZone(img,'inset:0;');
    H+='<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:5%;background:'+(img?'rgba(0,0,0,.35)':'transparent')+';">'
      +'<div style="font-size:2.4em;font-weight:900;color:'+t.tc+';margin-bottom:.3em;">'+esc(s.title)+'</div>'
      +(s.subtitle?'<div style="font-size:1em;color:'+t.bc+';">'+esc(s.subtitle)+'</div>':'')
      +'</div>';
  } else if(s.type==='quote'){
    H+='<div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:8% 12%;">'
      +'<div style="font-size:1.3em;font-style:italic;color:'+t.tc+';line-height:1.5;">&ldquo;'+esc((s.content||[]).join(' '))+'&rdquo;</div>'
      +(s.title?'<div style="margin-top:.8em;color:'+t.ac+';font-weight:700;font-size:.85em;">&mdash; '+esc(s.title)+'</div>':'')
      +'</div>';
  } else if(s.type==='stat'){
    H+='<div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;padding:6% 8%;">'
      +'<div style="font-size:1.6em;font-weight:800;color:'+t.tc+';margin-bottom:.2em;">'+esc(s.title)+'</div>'
      +(s.subtitle?'<div style="font-size:.8em;color:'+t.ac+';font-weight:700;margin-bottom:.8em;">'+esc(s.subtitle)+'</div>':'')
      +'<div style="display:flex;gap:30px;flex-wrap:wrap;">'+(s.content||[]).map(function(it){
        var p=splitItem(it);
        return '<div><div style="font-size:1.8em;font-weight:900;color:'+t.ac+';">'+esc(p.h)+'</div><div style="font-size:.7em;color:'+t.bc+';margin-top:4px;max-width:140px;">'+esc(p.d)+'</div></div>';
      }).join('')+'</div>'
      +'</div>';
  } else if(s.type==='timeline'){
    var steps=(s.content||[]).map(function(it){
      var p=splitItem(it);
      return '<div style="flex:1;text-align:center;padding:0 10px;position:relative;">'
        +'<div style="width:14px;height:14px;border-radius:50%;background:'+t.ac+';margin:0 auto 10px;"></div>'
        +'<div style="font-weight:800;color:'+t.ac+';font-size:.8em;margin-bottom:4px;">'+esc(p.h)+'</div>'
        +'<div style="color:'+t.bc+';font-size:.7em;line-height:1.4;">'+esc(p.d)+'</div>'
        +'</div>';
    }).join('');
    H+='<div style="position:absolute;inset:0;padding:6% 8%;">'
      +'<div style="font-size:1.5em;font-weight:800;color:'+t.tc+';margin-bottom:.2em;">'+esc(s.title)+'</div>'
      +(s.subtitle?'<div style="font-size:.78em;color:'+t.ac+';font-weight:700;margin-bottom:1.4em;">'+esc(s.subtitle)+'</div>':'')
      +'<div style="display:flex;gap:10px;">'+steps+'</div>'
      +'</div>';
  } else if(img){
    var textSide='<div style="position:absolute;top:0;bottom:0;'+(v==='v2'?'left:43%;right:0;':'left:0;right:43%;')+'display:flex;flex-direction:column;justify-content:center;padding:5%;overflow:hidden;">'
      +'<div style="font-size:1.5em;font-weight:800;color:'+t.tc+';margin-bottom:.25em;">'+esc(s.title)+'</div>'
      +(s.subtitle?'<div style="font-size:.78em;color:'+t.ac+';font-weight:700;margin-bottom:.5em;">'+esc(s.subtitle)+'</div>':'')
      +contentGrid(s.content)
      +'</div>';
    H+=(v==='v2')?imgZone(img,'top:0;bottom:0;left:0;width:42%;')+textSide:textSide+imgZone(img,'top:0;bottom:0;right:0;width:42%;');
  } else {
    H+='<div style="position:absolute;inset:0;display:flex;flex-direction:column;justify-content:center;padding:6%;overflow:hidden;">'
      +'<div style="font-size:1.6em;font-weight:800;color:'+t.tc+';margin-bottom:.3em;">'+esc(s.title)+'</div>'
      +(s.subtitle?'<div style="font-size:.8em;color:'+t.ac+';font-weight:700;margin-bottom:.5em;">'+esc(s.subtitle)+'</div>':'')
      +contentGrid(s.content)
      +'</div>';
  }
  return H+'</div>';
}
function show(i){
  idx=Math.max(0,Math.min(i,DATA.slides.length-1));
  document.getElementById('dsv-stage').innerHTML=render(DATA.slides[idx],DATA.slides[idx].variant||'v1');
  document.getElementById('dsv-count').textContent=(idx+1)+' / '+DATA.slides.length;
  document.getElementById('dsv-prev').disabled=(idx===0);
  document.getElementById('dsv-next').disabled=(idx===DATA.slides.length-1);
}
document.getElementById('dsv-prev').onclick=function(){show(idx-1);};
document.getElementById('dsv-next').onclick=function(){show(idx+1);};
document.addEventListener('keydown',function(e){if(e.key==='ArrowLeft')show(idx-1);if(e.key==='ArrowRight')show(idx+1);});
show(0);
</script>
</body>
</html>
<?php
    exit;
});
