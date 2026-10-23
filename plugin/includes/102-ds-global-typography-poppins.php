<?php
/**
 * Code Snippets export — DS Global Typography - Poppins
 *
 * Snippet ID : 783
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-097
 * Modified   : 2026-09-09 02:44:16
 * Load order : 102 of 144
 */
/**
 * Snippet Name: DS Global Typography - Poppins
 * Run: Everywhere (frontend only)
 * Purpose: Site-wide font-family switch to Poppins for all visible text
 *          (headings, body, buttons, nav, widgets) while leaving icon
 *          fonts (Font Awesome, dashicons, Elementor icons) untouched.
 *          Weight mapping: H1 (main titles) = 900 Black,
 *          H2-H6 (section/point headers) = 700 Bold,
 *          body/paragraph text = 400 Regular.
 */
add_action('wp_head', function () {
    if (is_admin()) return;
    echo '<style id="ds-global-typography-poppins">
        body,
        body *:not(i):not(.dashicons):not([class*="eicon-"]):not([class*="fa-"]):not([class^="fa"]) {
            font-family: \'Poppins\', sans-serif !important;
        }
        h1, .elementor-widget-heading h1.elementor-heading-title {
            font-weight: 900 !important;
        }
        h2, h3, h4, h5, h6,
        .elementor-widget-heading h2.elementor-heading-title,
        .elementor-widget-heading h3.elementor-heading-title,
        .elementor-widget-heading h4.elementor-heading-title,
        .elementor-widget-heading h5.elementor-heading-title,
        .elementor-widget-heading h6.elementor-heading-title {
            font-weight: 700 !important;
        }
        p, li, .elementor-widget-text-editor, .elementor-widget-text-editor p {
            font-weight: 400 !important;
        }
    </style>';
}, 999);
