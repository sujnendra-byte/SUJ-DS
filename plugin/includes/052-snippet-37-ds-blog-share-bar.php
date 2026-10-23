<?php
/**
 * Code Snippets export — Snippet 37: DS Blog Share Bar
 *
 * Snippet ID : 738
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-052
 * Modified   : 2026-09-09 02:41:16
 * Load order : 052 of 144
 */
/**
 * Snippet 36: DS Blog Share Bar
 * Run: Everywhere
 * Adds share buttons to every ds_blog_post single page
 */

if ( ! defined('ABSPATH') ) exit;

add_filter('the_content', function($content) {
    if ( ! is_singular('ds_blog_post') ) return $content;

    $url   = urlencode(get_permalink());
    $title = urlencode(get_the_title());
    $thumb = get_the_post_thumbnail_url(get_the_ID(), 'large');

    $share = '
    <div style="margin:40px 0 20px;padding:24px;background:#191c25;border-radius:14px;border-left:4px solid #E8481C;font-family:Poppins,sans-serif;">
        <div style="font-size:13px;font-weight:800;color:#e5e7eb;margin-bottom:14px;text-transform:uppercase;letter-spacing:.5px;">Share this article</div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="https://www.facebook.com/sharer/sharer.php?u=' . $url . '"
               target="_blank" rel="noopener"
               style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:#1877f2;color:#fff;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;font-family:Poppins,sans-serif;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                Facebook
            </a>
            <a href="https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title . '"
               target="_blank" rel="noopener"
               style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:#000;color:#fff;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;font-family:Poppins,sans-serif;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                X (Twitter)
            </a>
            <a href="https://www.linkedin.com/shareArticle?mini=true&url=' . $url . '&title=' . $title . '"
               target="_blank" rel="noopener"
               style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:#0077b5;color:#fff;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;font-family:Poppins,sans-serif;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
                LinkedIn
            </a>
            <a href="https://wa.me/?text=' . $title . '%20' . $url . '"
               target="_blank" rel="noopener"
               style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:#25d366;color:#fff;border-radius:8px;text-decoration:none;font-size:13px;font-weight:700;font-family:Poppins,sans-serif;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp
            </a>
            <button onclick="navigator.clipboard.writeText(decodeURIComponent(\'' . $url . '\')).then(function(){this.textContent=\'Copied!\';var b=this;setTimeout(function(){b.textContent=\'Copy Link\';},2000);}.bind(this))"
               style="display:inline-flex;align-items:center;gap:7px;padding:9px 18px;background:#191c25;color:#fff;border-radius:8px;border:1px solid rgba(255,255,255,.15);font-size:13px;font-weight:700;font-family:Poppins,sans-serif;cursor:pointer;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                Copy Link
            </button>
        </div>
    </div>';

    return $content . $share;
});
