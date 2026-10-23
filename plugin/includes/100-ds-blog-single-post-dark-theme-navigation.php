<?php
/**
 * Code Snippets export — DS Blog Single Post Dark Theme + Navigation
 *
 * Snippet ID : 781
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-095
 * Modified   : 2026-09-09 02:46:35
 * Load order : 100 of 144
 */
/**
 * Snippet Name: DS Blog Single Post Dark Theme + Navigation
 * Run: Everywhere (scoped by is_singular('ds_blog_post') checks below)
 * Purpose: Individual blog post pages (e.g. /blog/how-to-improve-...) were
 *          still using the Astra theme's default light template (light
 *          blue page background, white content card, dark navy text),
 *          while the rest of the site is dark-themed. This forces those
 *          pages dark to match, fixes the "References & Further Reading"
 *          callout box that AI-generated posts include, and adds a
 *          "Back to Blog" link plus Previous/Next post navigation, which
 *          did not exist before.
 */

if ( ! defined('ABSPATH') ) exit;

// ─── 1. Dark theme CSS for single blog posts ───
add_action('wp_head', function () {
	if ( ! is_singular('ds_blog_post') ) return;
	echo '<style id="ds-blog-single-dark-theme">
		body.single-ds_blog_post {
			--ast-global-color-2: #ffffff;
			--ast-global-color-3: #cbd5e1;
			--ast-global-color-4: #10131c;
			--ast-global-color-5: #191c25;
			background: #10131c !important;
		}
		body.single-ds_blog_post .entry-content,
		body.single-ds_blog_post .page-content,
		body.single-ds_blog_post main {
			background: #191c25 !important;
		}
		body.single-ds_blog_post .ds-blog-references {
			background: #10131c !important;
		}
		body.single-ds_blog_post .ds-blog-references h3 {
			color: #ffffff !important;
		}
		body.single-ds_blog_post .ds-blog-references li,
		body.single-ds_blog_post .ds-blog-references li span {
			color: #cbd5e1 !important;
		}
		body.single-ds_blog_post .ds-blog-references a {
			color: #E8481C !important;
		}
		.ds-blog-back-link {
			display: inline-flex; align-items: center; gap: 6px;
			color: #E8481C; font-weight: 700; font-size: 13px;
			text-decoration: none; margin-bottom: 20px;
		}
		.ds-blog-back-link:hover { color: #fff; }
		.ds-blog-postnav {
			display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap;
			margin-top: 40px; padding-top: 24px; border-top: 1px solid rgba(255,255,255,.1);
		}
		.ds-blog-postnav > a {
			flex: 1 1 45%; min-width: 200px;
			color: #e5e7eb; text-decoration: none; font-size: 13px; font-weight: 600;
			padding: 14px 16px; border-radius: 8px; background: #10131c;
			border: 1px solid rgba(255,255,255,.08); transition: all .15s; box-sizing: border-box;
		}
		.ds-blog-postnav > a:hover { border-color: #E8481C; }
		.ds-blog-postnav .ds-nav-next { text-align: right; margin-left: auto; }
		.ds-blog-postnav .ds-nav-label {
			display: block; font-size: 10px; text-transform: uppercase; letter-spacing: .5px;
			color: #8b97af; margin-bottom: 4px;
		}
		.ds-blog-postnav .ds-nav-title { color: #fff; font-weight: 700; }
             body.single-ds_blog_post .navigation.post-navigation {
                 display: none !important;
             }
	</style>';
}, 100);

// ─── 2. Back to Blog link + Previous/Next post navigation ───
add_filter('the_content', function ($content) {
	if ( ! is_singular('ds_blog_post') || ! in_the_loop() || ! is_main_query() ) return $content;

	global $wpdb, $post;
	$current_date = $post->post_date;

	$back = '<a href="' . esc_url( home_url('/blog/') ) . '" class="ds-blog-back-link">&larr; Back to Blog</a>';

	$prev = $wpdb->get_row( $wpdb->prepare(
		"SELECT ID, post_title FROM {$wpdb->posts} WHERE post_date < %s AND post_type = 'ds_blog_post' AND post_status = 'publish' ORDER BY post_date DESC LIMIT 1",
		$current_date
	) );
	$next = $wpdb->get_row( $wpdb->prepare(
		"SELECT ID, post_title FROM {$wpdb->posts} WHERE post_date > %s AND post_type = 'ds_blog_post' AND post_status = 'publish' ORDER BY post_date ASC LIMIT 1",
		$current_date
	) );

	$nav = '<div class="ds-blog-postnav">';
	if ( $prev ) {
		$nav .= '<a href="' . esc_url( get_permalink( $prev->ID ) ) . '"><span class="ds-nav-label">&larr; Previous</span><span class="ds-nav-title">' . esc_html( $prev->post_title ) . '</span></a>';
	} else {
		$nav .= '<span></span>';
	}
	if ( $next ) {
		$nav .= '<a href="' . esc_url( get_permalink( $next->ID ) ) . '" class="ds-nav-next"><span class="ds-nav-label">Next &rarr;</span><span class="ds-nav-title">' . esc_html( $next->post_title ) . '</span></a>';
	}
	$nav .= '</div>';

	return $back . $content . $nav;
}, 20);
