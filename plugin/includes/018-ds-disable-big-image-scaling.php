<?php
/**
 * Code Snippets export — DS Disable Big Image Scaling
 *
 * Snippet ID : 704
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-018
 * Modified   : 2026-09-09 02:43:45
 * Load order : 018 of 144
 */
/**
 * Snippet Name: DS Disable Big Image Scaling
 * Run: Everywhere
 * Description: Disables WordPress's automatic scaling of images over 2560px.
 * AI-generated 4K images now save at full resolution instead of -scaled versions.
 */
add_filter('big_image_size_threshold', '__return_false');
