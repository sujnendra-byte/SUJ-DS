<?php
/**
 * Code Snippets export — 39 DS PPT — Browse Templates Smart Sort
 *
 * Snippet ID : 812
 * Active     : yes
 * Priority   : 20
 * Scope      : global
 * Tags       : designstager, ds-order-126
 * Modified   : 2026-09-09 02:45:11
 * Load order : 135 of 144
 */
/**
 * DS PPT — Browse Templates Smart Sort
 *
 * Affects ONLY the AI Presentation "Browse Templates" gallery
 * (REST route ds/v1/mkt-templates, snippet 30).
 * Does NOT touch "Pick a Design from Marketplace" (snippet 21) or anything else.
 *
 * 1. Gate    — only templates with _ds_tpl_enabled = 1 ever appear.
 * 2. Sort    — once the user pastes content, matching shapes float to the top.
 * 3. Headings— "Recommended" / "All Files" inserted client-side.
 *
 * Safe to deactivate at any time: the gallery reverts to its old behaviour.
 */
if ( ! defined('ABSPATH') ) exit;

// ═══ 1. Remember when we're inside the Browse Templates REST call ═══
if ( ! function_exists('dsrec_req') ) {
function dsrec_req( $set = null ) {
    static $r = null;
    if ( $set !== null ) $r = $set;
    return $r;
}
}
add_filter('rest_pre_dispatch', function( $result, $server, $request ) {
    if ( strpos( (string) $request->get_route(), '/ds/v1/mkt-templates' ) !== false ) {
        dsrec_req( $request );
    }
    return $result;
}, 10, 3);

// ═══ 2. Turn the pasted text into slides ═══
if ( ! function_exists('dsrec_parse_slides') ) {
function dsrec_parse_slides( $text ) {
    $text = trim( wp_strip_all_tags( (string) $text ) );
    if ( $text === '' ) return array();
    $text = str_replace( array("\r\n", "\r"), "\n", $text );

    // Split into slides on "Slide 1:" markers, if the user used them.
    $chunks = preg_split('/^\s*slide\s*\d+\s*[:.\-]?\s*$/mi', $text);
    $chunks = array_values( array_filter( array_map('trim', $chunks), 'strlen' ) );
    if ( ! $chunks ) $chunks = array( $text );

    $marker = '/^(point|step|strategy|phase|stage|milestone|item|pillar)\s*\d+\s*[:.\-]?\s*/i';
    $slides = array();

    foreach ( $chunks as $chunk ) {
        $lines  = array_values( array_filter( array_map('trim', explode("\n", $chunk)), 'strlen' ) );
        if ( ! $lines ) continue;
        $blocks = preg_split('/\n\s*\n/', $chunk);
        $blocks = array_values( array_filter( array_map('trim', $blocks), 'strlen' ) );

        $title    = preg_replace('/^title\s*:\s*/i', '', $lines[0]);
        $subtitle = '';
        foreach ( $lines as $l ) {
            if ( preg_match('/^sub\s*header\s*:\s*/i', $l) ) {
                $subtitle = preg_replace('/^sub\s*header\s*:\s*/i', '', $l);
                break;
            }
        }

        $marked = 0;
        foreach ( $lines as $l ) if ( preg_match($marker, $l) ) $marked++;

        $content = array();
        if ( $marked >= 2 ) {
            // Explicit "Point 1:" / "Strategy 3" markers.
            $cur = null;
            foreach ( $lines as $l ) {
                if ( preg_match($marker, $l) ) {
                    if ( $cur !== null ) $content[] = $cur;
                    $h   = trim( preg_replace($marker, '', $l) );
                    $cur = ( $h !== '' ) ? $h : trim($l);
                } elseif ( $cur !== null && strpos($cur, ':') === false ) {
                    $cur .= ': ' . $l;
                }
            }
            if ( $cur !== null ) $content[] = $cur;
        } else {
            // Blank-line blocks: first block is the title, the rest are points.
            foreach ( array_slice($blocks, 1) as $b ) {
                $bl = array_values( array_filter( array_map('trim', explode("\n", $b)), 'strlen' ) );
                if ( ! $bl ) continue;
                $content[] = count($bl) > 1
                    ? ( $bl[0] . ': ' . implode(' ', array_slice($bl, 1)) )
                    : $bl[0];
            }
        }

        // Keep the slide even with no points — a lone "Header" or "SWOT" line
        // is still a meaningful signal.
        $slides[] = array('title' => $title, 'subtitle' => $subtitle, 'content' => $content);
    }
    return $slides;
}
}

// ═══ 3. Slides → recommended template IDs ═══
if ( ! function_exists('dsrec_recommended_ids') ) {
function dsrec_recommended_ids( $text, $limit = 48 ) {
    if ( ! function_exists('ds_tpl_classify_slide') ) return array();   // snippet 21 inactive

    $slides = dsrec_parse_slides($text);
    if ( ! $slides ) return array();

    $weights = array();
    foreach ( $slides as $s ) {
        $n     = count($s['content']);
        $cands = ds_tpl_classify_slide( $s['title'], $s['subtitle'], $s['content'] );

        // No points at all → treat as a cover/title slide.
        if ( $n === 0 ) {
            $has_semantic = false;
            foreach ( $cands as $c ) if ( ! empty($c['semantic']) ) $has_semantic = true;
            if ( ! $has_semantic ) $cands[] = array('shape' => 'header', 'semantic' => false);
        }

        foreach ( $cands as $c ) {
            $shape = $c['shape'];

            // Rank by how strong a signal the shape is, NOT by the classifier's
            // order — otherwise a title containing "plan" makes timeline outrank
            // an exact point-count match.
            if ( $n > 0 && ( $shape === 'steps_' . $n || $shape === 'generic_grid_' . $n ) ) {
                $p = 100;                                    // exact point count wins
            } elseif ( in_array($shape, array('swot','pros_cons','pestle','business_model_canvas'), true) ) {
                $p = 90;                                     // explicit keyword
            } elseif ( $shape === 'header' ) {
                $p = 90;
            } elseif ( in_array($shape, array('timeline','roadmap','milestone'), true) ) {
                $p = 50;                                     // often a loose title-word match
            } else {
                $p = 10;
            }

            if ( ! isset($weights[$shape]) ) $weights[$shape] = array('w' => 0, 'semantic' => ! empty($c['semantic']));
            $weights[$shape]['w'] += $p;
            if ( ! empty($c['semantic']) ) $weights[$shape]['semantic'] = true;
        }
    }
    if ( ! $weights ) return array();
    uasort($weights, function($a, $b) { return $b['w'] <=> $a['w']; });

    $ids = array();
    foreach ( $weights as $shape => $meta ) {
        $posts = get_posts(array(
            'post_type'        => 'dlm_download',
            'post_status'      => 'publish',
            'posts_per_page'   => $limit,
            'fields'           => 'ids',
            'suppress_filters' => true,
            'meta_query'       => array(
                array('key' => '_ds_tpl_enabled', 'value' => '1'),
                array('key' => '_ds_tpl_shape',   'value' => $shape),
            ),
        ));
        foreach ( $posts as $pid ) {
            if ( ! $meta['semantic'] && get_post_meta($pid, '_ds_tpl_semantic', true) ) continue;
            if ( ! in_array($pid, $ids, true) ) $ids[] = (int) $pid;
        }
        if ( count($ids) >= $limit ) break;
    }
    return array_slice($ids, 0, $limit);
}
}

// ═══ 4. Gate + ordering on the gallery query ═══
add_action('pre_get_posts', function( $q ) {
    static $busy = false;

    $req = dsrec_req();
    if ( ! $req || $busy ) return;
    if ( $q->get('post_type') !== 'dlm_download' ) return;
    if ( $q->get('fields') === 'ids' ) return;   // our own lookup queries — leave alone

    // Gate: only ticked templates.
    $mq   = (array) $q->get('meta_query');
    $mq[] = array('key' => '_ds_tpl_enabled', 'value' => '1');
    $q->set('meta_query', $mq);

    // Ordering: recommended first, pagination intact.
    $uc = (string) $req->get_param('uc');
    if ( $uc !== '' ) {
        $busy = true;                     // stop the nested get_posts() re-entering here
        $ids  = dsrec_recommended_ids($uc);
        $busy = false;
        $q->set('dsrec_ids', $ids);
    }
}, 99);

add_filter('posts_orderby', function( $orderby, $q ) {
    $ids = $q->get('dsrec_ids');
    if ( empty($ids) || ! is_array($ids) ) return $orderby;
    global $wpdb;
    $list = implode(',', array_map('intval', array_reverse($ids)));
    return "FIELD({$wpdb->posts}.ID, {$list}) DESC, " . $orderby;
}, 10, 2);

// ═══ 5. Tiny endpoint so the UI knows where to put the headings ═══
add_action('rest_api_init', function() {
    register_rest_route('ds/v1', '/tpl-rec-meta', array(
        'methods'             => 'GET',
        'permission_callback' => function() { return is_user_logged_in(); },
        'callback'            => function( WP_REST_Request $r ) {
            $uc  = (string) $r->get_param('uc');
            $ids = $uc !== '' ? dsrec_recommended_ids($uc) : array();
            $out = array('count' => count($ids));
            if ( $r->get_param('debug') && current_user_can('manage_options') ) {
                $out['slides'] = dsrec_parse_slides($uc);
                $out['titles'] = array_map(function( $id ) {
                    return get_the_title($id) . ' [' . get_post_meta($id, '_ds_tpl_shape', true) . ']';
                }, $ids);
            }
            return rest_ensure_response($out);
        },
    ));
});

// ═══ 6. Insert the "Recommended" / "All Files" headings ═══
add_action('wp_footer', function() {
    if ( ! is_user_logged_in() ) return;
    ?>
    <style>
      .dsrec-head{grid-column:1/-1;font-size:19px;font-weight:800;color:#fff;
                  margin:14px 0 2px;letter-spacing:.2px;}
      .dsrec-head:first-child{margin-top:0;}
    </style>
    <script>
    (function(){
      var REST='<?php echo esc_js( esc_url_raw( rest_url('ds/v1/tpl-rec-meta') ) ); ?>';
      var NONCE='<?php echo esc_js( wp_create_nonce('wp_rest') ); ?>';
      var busy=false,lastKey='',builtWith='';
		window.dsrecDebug=function(){
        var uc=((document.getElementById('ds30-content-input')||{}).value||'').slice(0,4000);
        var u='<?php echo esc_js( esc_url_raw( rest_url('ds/v1/mkt-templates') ) ); ?>?page=1&per_page=48&uc='+encodeURIComponent(uc);
        console.log('[dsrec] content length:',uc.length);
        fetch(u,{headers:{'X-WP-Nonce':NONCE}})
          .then(function(r){return r.json();})
          .then(function(d){
            console.log('[dsrec] _dsrec =',d._dsrec);
            console.log('[dsrec] server order:',(d.items||[]).slice(0,12).map(function(t){return t.title;}));
            console.log('[dsrec] DOM order:',
              Array.prototype.slice.call(document.querySelectorAll('#ds30-grid-v2 .ds30-card-v2-title'))
                   .slice(0,12).map(function(e){return e.textContent;}));
          })
          .catch(function(e){console.log('[dsrec] failed',e);});
      };
		
		/* Make snippet 30's own gallery request carry the user's text, so the
         server can order recommended templates first. Harmless if snippet 30
         already appends uc= itself. */
      (function(){
        var _f=window.fetch;
        window.fetch=function(input,init){
          try{
            var url=(typeof input==='string')?input:(input&&input.url);
            if(url&&url.indexOf('/mkt-templates')!==-1&&url.indexOf('uc=')===-1){
              var uc=((document.getElementById('ds30-content-input')||{}).value||'').slice(0,4000);
              if(uc.trim()){
                url=url.replace(/per_page=\d+/,'per_page=48');   // 48 is the server cap
                url+=(url.indexOf('?')===-1?'?':'&')+'uc='+encodeURIComponent(uc);
                console.log('[dsrec] patched gallery URL →',url);
                input=(typeof input==='string')?url:new Request(url,input);
              }
            }
          }catch(e){}
          return _f.apply(this,arguments.length>1?[input,init]:[input]);
        };
      })();

      function head(txt){
        var d=document.createElement('div');
        d.className='dsrec-head';d.textContent=txt;return d;
      }
      var recCache={};

      function apply(n){
        var grid=document.getElementById('ds30-grid-v2');
        if(!grid)return;
        var cards=grid.querySelectorAll('.ds30-card-v2');
        var want=(n>0&&cards.length)?('R'+n+'of'+cards.length):'none';
        if(grid.getAttribute('data-dsrec')===want)return;   // already correct — stops a redraw loop
        grid.querySelectorAll('.dsrec-head').forEach(function(e){e.remove();});
        if(n>0&&cards.length){
          grid.insertBefore(head('Recommended'),cards[0]);
          if(cards.length>n)grid.insertBefore(head('All Files'),cards[n]);
        }
        grid.setAttribute('data-dsrec',want);
      }

      function refresh(){
        var grid=document.getElementById('ds30-grid-v2');
        if(!grid)return;
        var uc=((document.getElementById('ds30-content-input')||{}).value||'').slice(0,4000);
        if(!uc.trim()){apply(0);return;}
        if(recCache[uc]!==undefined){apply(recCache[uc]);return;}   // no refetch on every mutation
        if(busy)return;
        busy=true;
        fetch(REST+'?uc='+encodeURIComponent(uc),{headers:{'X-WP-Nonce':NONCE}})
          .then(function(r){return r.json();})
          .then(function(d){busy=false;recCache[uc]=(d&&d.count)||0;apply(recCache[uc]);})
          .catch(function(){busy=false;});
      }
      
      var t=null;
      function schedule(){clearTimeout(t);t=setTimeout(refresh,250);}

      /* The gallery builds its grid once and never rebuilds it, so a fresh
         request carrying the user's text is needed. Poking the search box is
         the only reload snippet 30 exposes. */
      function maybeReload(){
        var scr=document.getElementById('ds30-screen');
        if(!scr||scr.className.indexOf('open')===-1)return;
        var uc=((document.getElementById('ds30-content-input')||{}).value||'').slice(0,4000);
        if(uc===builtWith)return;
        builtWith=uc;
        var se=document.getElementById('ds30-search-v2');
        if(se)se.dispatchEvent(new Event('input',{bubbles:true}));
      }

      document.addEventListener('DOMContentLoaded',function(){
        var tries=0,iv=setInterval(function(){
          var grid=document.getElementById('ds30-grid-v2');
          if(grid){
            clearInterval(iv);
            new MutationObserver(schedule).observe(grid,{childList:true});

            var scr=document.getElementById('ds30-screen');
            if(scr)new MutationObserver(maybeReload).observe(scr,{attributes:true,attributeFilter:['class']});

            var ta=document.getElementById('ds30-content-input');
            if(ta)ta.addEventListener('input',function(){
              lastKey='';schedule();
              clearTimeout(ta._dsrecT);
              ta._dsrecT=setTimeout(maybeReload,700);
            });
            schedule();
          } else if(++tries>60){clearInterval(iv);}
        },500);
      });
    })();
    </script>
    <?php
}, 99);

// ═══ 7. Reorder the gallery response — recommended items first ═══
add_filter('rest_post_dispatch', function( $response, $server, $request ) {
    if ( strpos( (string) $request->get_route(), '/ds/v1/mkt-templates' ) === false ) return $response;

    $uc = (string) $request->get_param('uc');
    $d  = $response->get_data();
    if ( ! is_array($d) || empty($d['items']) || ! is_array($d['items']) ) return $response;

    $ids = $uc !== '' ? dsrec_recommended_ids($uc) : array();

    if ( $ids ) {
        $rank = array_flip($ids);                       // id => position
        $hit = array(); $rest = array();
        foreach ( $d['items'] as $it ) {
            $id = isset($it['id']) ? (int) $it['id'] : 0;
            if ( isset($rank[$id]) ) $hit[ $rank[$id] ] = $it;
            else                     $rest[] = $it;
        }
        ksort($hit);
        $d['items'] = array_merge( array_values($hit), $rest );
    }

    // Debug: visible in the Network tab, harmless in production.
    $d['_dsrec'] = array('uc_len' => strlen($uc), 'rec' => count($ids));

    $response->set_data($d);
    return $response;
}, 10, 3);
