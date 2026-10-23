<?php
/**
 * Code Snippets export — Snippet hide ai cards
 *
 * Snippet ID : 701
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-015
 * Modified   : 2026-09-09 02:46:08
 * Load order : 015 of 144
 */
/**
 * Snippet Name: DS Hide AI Studio Cards
 * Run: Site Wide
 * Hides the 3 top cards (AI Images, AI Thumbnails, AI Presentation)
 * on the AI Studio page using CSS injected only on that page.
 */
add_action( 'wp_head', function () {
    // Only run on the ai-studio page (or ai-tools)
    if ( ! is_page( array( 'ai-studio', 'ai-tools' ) ) ) return;
    echo '<style>
/* Hide the 3 feature cards above the AI generator */
.elementor-section:has(h2.elementor-heading-title),
.e-con:has(h2.elementor-heading-title),
.elementor-widget-heading:has(.elementor-heading-title) {
    /* Only hides sections containing those specific heading texts */
}
/* Most reliable: hide by card structure — 3-column grid above the dark box */
body.page-slug-ai-studio .elementor-top-section:nth-of-type(2),
body.page-slug-ai-tools .elementor-top-section:nth-of-type(2) {
    display: none !important;
}
</style>
<script>
// JS fallback — hides cards that contain "AI Thumbnails" text
document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".elementor-widget-heading").forEach(function(el) {
        var txt = el.textContent.trim();
        if (txt === "AI Thumbnails" || txt === "AI Images" || txt === "AI Presentation") {
            var section = el.closest(".elementor-section, .e-con, [data-element_type=section]");
            if (section) {
                // Go up to the top-level section containing all 3 cards
                var parent = section.parentElement;
                while (parent && !parent.classList.contains("elementor-top-section") && parent !== document.body) {
                    parent = parent.parentElement;
                }
                if (parent && parent !== document.body) {
                    parent.style.display = "none";
                }
            }
        }
    });
});
</script>';
} );
