<?php
/**
 * Code Snippets export — DS Contact Page Dark Theme
 *
 * Snippet ID : 782
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-096
 * Modified   : 2026-09-09 02:43:17
 * Load order : 101 of 144
 */
/**
 * Snippet Name: DS Contact Page Dark Theme
 * Run: Everywhere (scoped by /contact URL check below)
 * Purpose: The Contact page (page-id-1494) was still using Astra's
 *          default light template (white section background, dark
 *          navy headings/text, white form card) below the already-dark
 *          hero. This forces that section dark to match the rest of
 *          the site.
 */
if ( ! defined('ABSPATH') ) exit;

add_action( 'wp_head', function () {
    if ( strpos( $_SERVER['REQUEST_URI'], '/contact' ) === false ) return;
    echo '<style id="ds-contact-dark-theme">
        body.page.page-id-1494,
        html body.page-id-1494 {
            background: #10131c !important;
        }
        body.page-id-1494 .site-main,
        body.page-id-1494 .entry-content,
        body.page-id-1494 .page-content {
            background: #10131c !important;
        }
        body.page-id-1494 {
            --ast-global-color-2: #ffffff;
            --ast-global-color-3: #cbd5e1;
            --wp--preset--color--ast-global-color-2: #ffffff;
            --wp--preset--color--ast-global-color-3: #cbd5e1;
        }
        body.page-id-1494 .srfm-form-container.srfm-bg-color {
            --srfm-bg-color: #191c25 !important;
        }
        body.page-id-1494 .spectra-background-color-hover.spectra-box-shadow {
            --spectra-background-color: #191c25 !important;
            --spectra-background-color-hover: #191c25 !important;
        }
        body.page-id-1494.ast-theme-transparent-header .site-logo-img img,
        body.page-id-1494 .site-logo-img img {
            filter: none !important;
        }
        body.page-id-1494 .main-header-menu .sub-menu .menu-link,
        body.page-id-1494 .ast-builder-menu .sub-menu .menu-link {
            color: #1a1a1a !important;
        }
        body.page-id-1494 .spectra-background-gradient {
            --spectra-background-gradient: linear-gradient(180deg, #10131c 0%, #191c25 100%) !important;
        }
    </style>';
}, 100 );
