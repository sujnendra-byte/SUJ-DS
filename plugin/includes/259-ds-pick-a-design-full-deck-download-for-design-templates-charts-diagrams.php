<?php
/**
 * Code Snippets export — DS Pick a Design - full deck download for design templates, charts and diagrams
 *
 * Snippet ID : 1057 (staging)
 * Active     : yes
 * Priority   : 12
 * Scope      : global
 * Tags       : ds-templates, pick-a-design
 * Modified   : 2026-10-05 19:58:00
 * Load order : after 082 (snippet 23) and 085 (snippet 21); before 258
 */
/**
 * DS Pick a Design - full deck download for design templates, charts and diagrams
 *
 * "Pick a Design" (snippet 21) + "Download full deck" (snippet 23) were written before the design templates,
 * the charts and the diagrams existed. Snippet 23 builds every slide WITHOUT a picked marketplace design by
 * calling the classic builder directly, so:
 *   - a deck made with a design template (Glassline, Forest, ...) came out in the classic look, and
 *   - chart / diagram slides of a classic deck came out wrong (their data is prepared by filters that only run
 *     on the normal download route).
 *
 * This snippet does not edit snippet 21 or 23. In the browser, the "full deck" request is sent to the NORMAL
 * download route instead (so the template engine, charts, diagrams, logo, deck style, picture off-loading ...
 * all take part exactly as in a normal download), with the picked designs attached. On the server, after the
 * normal deck has been built, the picked marketplace designs are added with the same merge engine snippet 23
 * uses and put in the place of the slides they replace; the same credits are charged. If anything in the new path does not answer,
 * the browser falls back to the old request, so the old behaviour is the worst case.
 */
if ( ! defined('ABSPATH') ) exit;

if ( ! function_exists('ds_pdx_wanted') ) {
function ds_pdx_wanted( $request ) {
    if ( ! ( $request instanceof WP_REST_Request ) ) return false;
    $route = $request->get_route();
    if ( $route !== '/ds/v1/build-pptx-v2' && $route !== '/ds/v1/build-pptx' ) return false;
    if ( strtoupper($request->get_method()) !== 'POST' ) return false;
    return (bool) $request->get_param('ds_combine');
}
}

if ( ! function_exists('ds_pdx_design_cost') ) {
function ds_pdx_design_cost() {
    $o = get_option('ds_ppt_credit_settings');
    return ( is_array($o) && isset($o['template_cost']) && $o['template_cost'] !== '' ) ? (int) $o['template_cost'] : 5;
}
}
if ( ! function_exists('ds_pdx_balance') ) {
function ds_pdx_balance( $uid ) {
    return function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid, 'ds_ai_credits', true);
}
}

/* 1. Before the build: remember the picked designs, and stop early when the credits are not enough
      (same rule and same message as snippet 23). */
add_filter('rest_pre_dispatch', function ( $result, $server, $request ) {
    if ( $result !== null || ! ds_pdx_wanted($request) || ! is_user_logged_in() ) return $result;
    if ( ! function_exists('ds_tpl_url_to_path') || ! function_exists('ds_tplm_merge_pptx_files') || ! function_exists('ds_tplm_prune_unreachable_parts') || ! function_exists('ds_tplm_load_xml') ) return $result;
    $slides = $request->get_param('slides');
    if ( ! is_array($slides) || ! $slides ) return $result;
    $raw = $request->get_param('real_template_map'); $map = array();
    if ( is_array($raw) ) foreach ( $raw as $k => $v ) { if ( is_numeric($k) && is_string($v) && $v !== '' ) $map[(int) $k] = sanitize_text_field($v); }
    $uid = get_current_user_id();
    if ( $map ) {
        $needed = count($map) * ds_pdx_design_cost(); $bal = ds_pdx_balance($uid);
        if ( $bal < $needed ) {
            return rest_ensure_response(array('success' => false, 'ds_combined' => 1,
                'error' => "You need {$needed} credits to download this deck with " . count($map) . " marketplace design(s) applied. You currently have {$bal}."));
        }
    }
    $texts = array();                                  /* the words of each slide, for filling a picked design */
    foreach ( array_values($slides) as $i => $s ) { $texts[$i] = is_array($s) ? $s : array(); unset($texts[$i]['image_data'], $texts[$i]['dg_els']); }
    $GLOBALS['ds_pdx'] = array('map' => $map, 'slides' => $texts, 'title' => sanitize_text_field($request->get_param('title') ?: 'Presentation'), 'tpl' => (bool) $request->get_param('tpl'), 'done' => false);
    return $result;
}, 1, 3);

/* 2. After the build (and after the charts / diagrams have been written into the file): swap in the picked designs. */
if ( ! function_exists('ds_pdx_finish') ) {
function ds_pdx_finish( $res ) {
    if ( empty($GLOBALS['ds_pdx']) || ! empty($GLOBALS['ds_pdx']['done']) || is_wp_error($res) ) return $res;
    $d = ( $res instanceof WP_REST_Response ) ? $res->get_data() : $res;
    if ( ! is_array($d) || empty($d['success']) || empty($d['url']) || ! is_string($d['url']) ) return $res;
    $GLOBALS['ds_pdx']['done'] = true;
    $ctx = $GLOBALS['ds_pdx']; $map = $ctx['map']; $slides = $ctx['slides']; $n = count($slides);
    $u = explode('?', $d['url']); $deck = ds_tpl_url_to_path($u[0]);
    if ( ! $deck || substr($deck, -5) !== '.pptx' || ! file_exists($deck) ) return $res;
    @ini_set('memory_limit', '1536M'); @set_time_limit(0);
    $uid = get_current_user_id(); $tmp = array(); $real = 0; $coded = 0; $total = 0; $dest = '';
    try {
        $upload = wp_upload_dir();
        $out_dir = trailingslashit($upload['basedir']) . 'ds-ppt-templates';
        if ( ! file_exists($out_dir) ) wp_mkdir_p($out_dir);

        /* the file of every picked marketplace design, filled with the slide's own text */
        $picked = array();                                    /* slide index => .pptx */
        ksort($map);
        foreach ( $map as $i => $rawv ) {
            if ( $i < 0 || $i >= $n ) continue;
            $p = '';
            if ( strpos($rawv, 'pending:') === 0 ) {
                $tid = (int) substr($rawv, 8);
                if ( $tid && function_exists('ds_tpl_fetch_template_src_path') && function_exists('ds_tpl_inject_into_pptx') ) {
                    list($src_p, $tmp_p) = ds_tpl_fetch_template_src_path($tid);
                    if ( $src_p && file_exists($src_p) ) {
                        $dest_p = trailingslashit($out_dir) . 'tpl-' . $tid . '-' . $uid . '-' . time() . '-' . wp_generate_password(5, false, false) . '.pptx';
                        $r = ds_tpl_inject_into_pptx($src_p, $dest_p, $slides[$i]);
                        if ( $r !== false && ! is_wp_error($r) && file_exists($dest_p) ) { $p = $dest_p; $tmp[] = $dest_p; }
                    }
                    if ( ! empty($tmp_p) ) @unlink($tmp_p);
                }
            } else {
                $rp = ds_tpl_url_to_path($rawv);
                if ( $rp && substr($rp, -5) === '.pptx' && file_exists($rp) && strpos(wp_normalize_path(realpath($rp)), wp_normalize_path(realpath($upload['basedir']))) === 0 ) $p = $rp;
            }
            if ( $p ) $picked[$i] = $p;
        }

        /* The finished deck is the base (so charts, diagrams, pictures and the template look stay exactly as in a
           normal download); the picked designs are added behind it with snippet 23's merge engine ... */
        $srcs = array($deck); $order = array();
        foreach ( $picked as $i => $p ) { $srcs[] = $p; $order[] = $i; }
        /* how many slides the built deck has: one per slide, or one more when the builder added its own
           "upsell" slide before the last slide (then the separate upsell file of snippet 23 is not added again) */
        $P = 'http://schemas.openxmlformats.org/presentationml/2006/main'; $R = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
        $zb = new ZipArchive(); $base0 = -1;
        if ( $zb->open($deck) === true ) { $pd = ds_tplm_load_xml($zb->getFromName('ppt/presentation.xml')); if ( $pd ) $base0 = $pd->getElementsByTagNameNS($P, 'sldId')->length; $zb->close(); }
        $own_promo = ( $n >= 2 && $base0 === $n + 1 );
        if ( $base0 !== $n && ! $own_promo ) return $res;     /* not one slide per slide: the old request takes over */
        $promo = trailingslashit($out_dir) . 'promo-cta.pptx'; $has_promo = ! $own_promo && file_exists($promo);
        if ( $has_promo ) $srcs[] = $promo;
        $fname = 'Designstager-fulldeck-' . $uid . '-' . time() . '-' . wp_generate_password(5, false, false) . '.pptx';
        $dest = trailingslashit($out_dir) . $fname;

        /* design-template slides are final as built: the emoji-to-icon step of the classic combine is not for them */
        $hook = 'ds_combined_pptx_bytes'; $saved = null;
        if ( ! empty($ctx['tpl']) && isset($GLOBALS['wp_filter'][$hook]) ) { $saved = $GLOBALS['wp_filter'][$hook]; unset($GLOBALS['wp_filter'][$hook]); }
        try { $results = ds_tplm_merge_pptx_files($srcs, $dest); }
        finally { if ( $saved !== null ) $GLOBALS['wp_filter'][$hook] = $saved; }
        foreach ( $tmp as $t ) @unlink($t);
        $tmp = array();
        if ( ! is_array($results) || ! file_exists($dest) ) { if ( $dest ) @unlink($dest); return $res; }

        /* ... and then put in the place of the slides they replace. */
        $z = new ZipArchive();
        if ( $z->open($dest) !== true ) { @unlink($dest); return $res; }
        $pres = ds_tplm_load_xml($z->getFromName('ppt/presentation.xml'));
        $rels = ds_tplm_load_xml($z->getFromName('ppt/_rels/presentation.xml.rels'));
        $lst  = $pres ? $pres->getElementsByTagNameNS($P, 'sldIdLst')->item(0) : null;
        if ( ! $pres || ! $rels || ! $lst ) { $z->close(); @unlink($dest); return $res; }
        $nodes = array(); foreach ( $lst->getElementsByTagNameNS($P, 'sldId') as $nd ) $nodes[] = $nd;
        $added = 0; for ( $k = 1; $k < count($results); $k++ ) if ( $results[$k] ) $added++;
        $base = count($nodes) - $added;                       /* slides of the built deck */
        if ( $base !== $base0 ) { $z->close(); @unlink($dest); return $res; }
        /* $final[$k] = the slide shown at place $k; $at[$i] = the place of deck slide $i */
        $final = array(); for ( $k = 0; $k < $base; $k++ ) $final[$k] = $nodes[$k];
        $at = array(); for ( $i = 0; $i < $n; $i++ ) $at[$i] = ( $own_promo && $i === $n - 1 ) ? $base - 1 : $i;
        $drop = array(); $pos = $base; $promo_node = null;
        for ( $k = 1; $k < count($results); $k++ ) {
            if ( ! $results[$k] ) continue;
            $nd = $nodes[$pos++];
            if ( $k - 1 < count($order) ) { $i = $at[$order[$k - 1]]; $drop[] = $final[$i]; $final[$i] = $nd; $real++; }
            else $promo_node = $nd;
        }
        $coded = $n - $real;
        $seq = array_values($final);
        if ( $promo_node ) array_splice($seq, max(count($seq) - 1, 0), 0, array($promo_node));
        $drop_rid = array(); foreach ( $drop as $nd ) $drop_rid[$nd->getAttributeNS($R, 'id')] = 1;
        while ( $lst->firstChild ) $lst->removeChild($lst->firstChild);
        foreach ( $seq as $nd ) $lst->appendChild($nd);
        $kill = array();
        foreach ( $rels->getElementsByTagName('Relationship') as $rel ) if ( isset($drop_rid[$rel->getAttribute('Id')]) ) $kill[] = $rel;
        foreach ( $kill as $rel ) $rel->parentNode->removeChild($rel);
        $total = count($seq);
        $z->addFromString('ppt/presentation.xml', $pres->saveXML());
        $z->addFromString('ppt/_rels/presentation.xml.rels', $rels->saveXML());
        $app = $z->getFromName('docProps/app.xml');
        if ( $app !== false ) { $ad = ds_tplm_load_xml($app); if ( $ad ) { $sn = $ad->getElementsByTagName('Slides')->item(0); if ( $sn ) { $sn->nodeValue = (string) $total; $z->addFromString('docProps/app.xml', $ad->saveXML()); } } }
        $z->close();
        if ( $drop ) {                                        /* remove the parts of the replaced slides */
            $z2 = new ZipArchive();
            if ( $z2->open($dest) === true ) {
                $ct = ds_tplm_load_xml($z2->getFromName('[Content_Types].xml'));
                if ( $ct ) { ds_tplm_prune_unreachable_parts($z2, $ct); $z2->addFromString('[Content_Types].xml', $ct->saveXML()); }
                $z2->close();
            }
        }
    } catch ( Throwable $e ) {
        foreach ( $tmp as $t ) @unlink($t);
        if ( $dest && file_exists($dest) ) @unlink($dest);
        error_log('[DS PDX] ' . $e->getMessage() . ' @' . $e->getLine());
        return $res;
    }

    $charge = 0;
    if ( $real > 0 ) {                                        /* same charge and history entry as snippet 23 */
        $charge = $real * ds_pdx_design_cost();
        if ( function_exists('ds_deduct_credits') ) {
            if ( ! ds_deduct_credits($uid, $charge) ) {
                if ( $dest && file_exists($dest) ) @unlink($dest);
                return $res;
            }
        } else update_user_meta($uid, 'ds_ai_credits', max(0, (int) get_user_meta($uid, 'ds_ai_credits', true) - $charge));
        $log = get_user_meta($uid, 'ds_ppt_generations', true) ?: array();
        if ( ! is_array($log) ) $log = array();
        array_unshift($log, array('date' => current_time('mysql'), 'prompt' => $ctx['title'] . ' (Marketplace Designs Applied)', 'credits' => $charge, 'num_slides' => $real));
        update_user_meta($uid, 'ds_ppt_generations', $log);
    }
    $out = array(
        'success'         => true,
        'ds_combined'     => 1,
        'download_url'    => trailingslashit($upload['baseurl']) . 'ds-ppt-templates/' . $fname,
        'slide_count'     => $total,
        'requested_count' => $n,
        'real_count'      => $real,
        'coded_count'     => $coded,
        'credits_charged' => $charge,
        'credits_balance' => ds_pdx_balance($uid),
    );
    if ( $res instanceof WP_REST_Response ) { $res->set_data($out); return $res; }
    return $out;
}
}
/* classic decks: after the chart (5) and diagram (4) steps, before the cloud-storage step (10) */
add_filter('rest_request_after_callbacks', function ( $res, $handlers, $req ) { return ds_pdx_wanted($req) ? ds_pdx_finish($res) : $res; }, 8, 3);
/* design-template decks are answered in rest_pre_dispatch: after the diagram (0) and chart (1) steps */
add_filter('rest_post_dispatch', function ( $res, $server, $req ) { return ds_pdx_wanted($req) ? ds_pdx_finish($res) : $res; }, 3, 3);

/* 3. Browser: send the "full deck" request through the normal download route. */
add_action('wp_footer', function () {
    if ( ! is_user_logged_in() ) return;
    global $post;
    if ( ! is_a($post, 'WP_Post') ) return;
    if ( ! has_shortcode($post->post_content, 'ds_ai_ppt') && ! has_shortcode($post->post_content, 'ds_ai_studio') ) return;
    ?>
<script id="ds-pick-design-full-deck">
(function(){
if (window.__dsPdx || typeof window.fetch !== 'function') return; window.__dsPdx = 1;
var prev = window.fetch;
window.fetch = function(url, opts){
  var us = typeof url === 'string' ? url : (url && url.url) || '';
  if (us.indexOf('/ds/v1/combine-full-deck') === -1 || !opts || typeof opts.body !== 'string') return prev.apply(this, arguments);
  var self = this, args = arguments, b = null, done = false;
  function old(){ return prev.apply(self, args); }
  try { b = JSON.parse(opts.body); } catch (e) { return old(); }
  var G = window.G, S = window.S;
  var src = (G && G.pptData && Array.isArray(G.pptData.slides)) ? G.pptData.slides : (S && S.data && Array.isArray(S.data.slides) ? S.data.slides : null);
  if (!b || !Array.isArray(b.slides) || !src || src.length !== b.slides.length) return old();
  var body = {title:b.title, template:b.template, layout:b.layout, slides:src, slide_variants:(S && S.choices) || b.slide_variants || {}, ds_combine:1, real_template_map:b.real_template_map || {}};
  if (b.font_family) body.font_family = b.font_family;
  var bu = us.replace('/combine-full-deck', '/build-pptx');
  var shrink; try { shrink = typeof window.dspShrinkImages === 'function' ? window.dspShrinkImages(src) : null; } catch (e) { shrink = null; }
  return Promise.resolve(shrink)['catch'](function(){})
    .then(function(){ return window.fetch(bu, {method:'POST', headers:opts.headers, credentials:opts.credentials, body:JSON.stringify(body)}); })
    .then(function(r){ return r.json(); })
    .then(function(d){
      if (d && d.ds_combined) { done = true; return new Response(JSON.stringify(d), {status:200, headers:{'Content-Type':'application/json'}}); }
      if (window.console) console.warn('[DS Pick a Design] new full-deck path gave no result, using the old one', d && (d.error || d.message));
      return old();
    })
    ['catch'](function(e){ if (done) throw e; if (window.console) console.warn('[DS Pick a Design] new full-deck path failed, using the old one', e); return old(); });
};
})();
</script>
    <?php
}, 99999);
