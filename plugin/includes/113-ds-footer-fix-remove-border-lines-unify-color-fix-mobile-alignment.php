<?php
/**
 * Code Snippets export — DS Footer Fix - Remove Border Lines, Unify Color, Fix Mobile Alignment
 *
 * Snippet ID : 794
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-108
 * Modified   : 2026-09-09 02:40:40
 * Load order : 113 of 144
 */
/*
 * Snippet Name: DS Footer Fix - Remove Border Lines, Unify Color, Fix Mobile Alignment
 * Run: Everywhere
 * Purely visual CSS override for the site-wide footer (Astra theme footer builder rows):
 * 1) removes the light separator line above the footer widget row and above the copyright row,
 * 2) makes the footer background one consistent near-black tone instead of the navy/near-black mix,
 * 3) fixes footer widget columns collapsing/mis-aligning on mobile (they were staying at their
 *    desktop grid position instead of stacking full-width and left-aligned).
 * Pure addition (a CSS-only override) - does not modify any existing snippet, template, or theme file.
 */

add_action('wp_head', function() {
    ?>
    <style id="ds-footer-fix">
    /* 1) Remove the separator lines above the footer widget row and above the copyright row */
    .site-footer .site-primary-footer-wrap,
    .site-footer .site-below-footer-wrap {
        border-top: none !important;
    }

    /* 2) Unify footer background to one consistent near-black tone (matches the rest of the dark site) */
    .site-footer,
    .site-footer .site-primary-footer-wrap,
    .site-footer .site-below-footer-wrap {
        background-color: #0a0a0a !important;
    }

    /* 3) Mobile: stack footer columns full-width and left-aligned instead of staying at their
          desktop grid position (which was leaving a blank gap on the left). */
    @media (max-width: 544px) {
        .site-footer .ast-builder-footer-grid-columns {
            display: block !important;
        }
        .site-footer .site-footer-section {
            width: 100% !important;
            max-width: 100% !important;
            text-align: left !important;
            margin-left: 0 !important;
            margin-right: 0 !important;
            margin-bottom: 28px !important;
        }
        .site-footer .site-footer-section:last-child {
            margin-bottom: 0 !important;
        }
        .site-footer .site-footer-section * {
            text-align: left !important;
        }
    }
    </style>
    <?php
}, 100);
