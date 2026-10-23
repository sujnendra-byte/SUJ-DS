<?php
/**
 * Code Snippets export — DS Fix – Link 100 PPT Templates (Featured Image + Download File)
 *
 * Snippet ID : 772
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-086
 * Modified   : 2026-09-09 02:43:11
 * Load order : 091 of 144
 */
add_action('admin_init', function () {
    if (!isset($_GET['ds_fix_ppt']) || !current_user_can('manage_options')) return;
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $dir = WP_CONTENT_DIR . '/uploads/2026/Full PPT Slides Auto fill V1/';
    $url = content_url('uploads/2026/Full PPT Slides Auto fill V1/');
    if (!is_dir($dir)) wp_die('PROBLEM: folder not found at ' . $dir);

    // slug => source basename (no extension) for our 100 PowerPoint template listings
    $slug_map = [
    'agile-build-phase-six-steps-powerpoint-template-editable-slides-for' => 'Agile-Build-Phase-Visual-Layout',
    'four-step-agile-build-phase-powerpoint-template-with-a-clean-editable' => 'Agile-Build-Phase',
    'aircraft-safety-audit-report-four-steps-presentation-graphic-for' => 'Aircraft-Safety-Audit-Report-Four-Point-Overview',
    'four-step-infographic-ppt-analysis-and-discovery-template-process' => 'Analysis-and-Discovery-Four-Point-Process-Guide',
    'analytics-report-cover-slide-powerpoint-template-editable-design' => 'Analytics-Report-Title-Cover-Slide',
    'announcement-diagram-three-steps-powerpoint-diagram-a-clean-editable' => 'Announcement-Diagram-Three-Step',
    'announcement-five-steps-powerpoint-template-editable-slides-for' => 'Announcement-Five-Point-Circular-Visual-Layout',
    'five-step-announcement-powerpoint-template-with-a-radial-diagram' => 'Announcement-Five-Point-Diagram',
    'arrow-diagram-four-steps-presentation-graphic-for-sequential-process' => 'Arrow-Diagram-Four-Step-PowerPoint-Template',
    'four-step-infographic-ppt-arrow-diagram-template-process-guide' => 'Arrow-Diagram-Four-Step-Process-Guide',
    'editable-arrow-diagram-four-steps-powerpoint-template-for-sequential' => 'Arrow-Diagram-Four-Step-Summary-Chart',
    'arrow-diagram-six-steps-powerpoint-diagram-a-structured-concept' => 'Arrow-Diagram-Six-Step-Concept-Framework',
    'arrow-diagram-six-steps-powerpoint-template-editable-slides-for' => 'Arrow-Diagram-Six-Step-Overview-Diagram',
    'eight-step-auto-insurance-diagram-powerpoint-template-with-a-clean' => 'Auto-Insurance-Diagram-Eight-Step',
    'bi-competency-five-steps-presentation-graphic-for-business' => 'BI-Competency-Concept-Framework',
    'eight-step-infographic-ppt-bi-competency-template-visual-layout' => 'BI-Competency-Visual-Layout',
    'editable-baby-health-tips-four-steps-powerpoint-template-for' => 'Baby-Health-Tips-Four-Point-Overview',
    'big-data-analytics-eight-steps-powerpoint-diagram-a-structured' => 'Big-Data-Analytics-Concept-Framework',
    'big-data-analytics-ten-steps-powerpoint-template-editable-slides-for' => 'Big-Data-Analytics-Overview-Diagram',
    'five-step-big-data-analytics-powerpoint-template-with-a-step-by-step' => 'Big-Data-Analytics-Process-Guide',
    'big-data-analytics-six-steps-presentation-graphic-for-big-data-and' => 'Big-Data-Analytics-Strategy-Map',
    'four-step-infographic-ppt-big-data-analytics-template-summary-chart' => 'Big-Data-Analytics-Summary-Chart',
    'editable-big-data-analytics-four-steps-powerpoint-template-for-big' => 'Big-Data-Analytics-Visual-Layout',
    'blockchain-components-seven-steps-powerpoint-diagram-a-clean-icon' => 'Blockchain-Components-Seven-Step-Visual-Layout',
    'box-diagram-three-steps-powerpoint-template-editable-slides-for' => 'Box-Diagram-Three-Step-Process-Guide',
    'six-step-building-diagram-powerpoint-template-with-a-clean-editable' => 'Building-Diagram-Six-Step',
    'business-augmentation-model-four-steps-presentation-graphic-for' => 'Business-Augmentation-Model-Four-Point-Case-Study',
    'four-step-infographic-ppt-business-continuity-template-plans' => 'Business-Continuity-Plans',
    'editable-business-continuity-five-steps-powerpoint-template-for-risk' => 'Business-Continuity-Visual-Layout',
    'business-continuity-five-steps-powerpoint-diagram-a-clean-editable' => 'Business-Continuity',
    'business-process-four-steps-powerpoint-template-editable-slides-for' => 'Business-Process-Four-Step-Percentage-Chart',
    'four-step-business-strategy-and-initiatives-powerpoint-template-with' => 'Business-Strategy-and-Initiatives-Concept-Framework',
    'business-strategy-and-initiatives-five-steps-presentation-graphic-for' => 'Business-Strategy-and-Initiatives-Visual-Layout',
    'five-step-infographic-ppt-business-to-business-marketing-template' => 'Business-to-Business-Marketing-Five-Step-Stacked-Circle-Diagram',
    'editable-business-to-consumer-four-steps-powerpoint-template-for-b2c' => 'Business-to-Consumer-Four-Step-Pinwheel-Visual-Layout',
    'business-to-consumer-six-steps-powerpoint-diagram-a-gear-shaped' => 'Business-to-Consumer-Six-Step-Gear-Diagram',
    'company-vision-powerpoint-title-slide-cover-template' => 'Call-Out-Your-Company-Vision-Title-Slide',
    'two-step-call-your-doctor-powerpoint-template-with-a-two-panel' => 'Call-Your-Doctor-Two-Point-Layout',
    'capability-services-five-steps-presentation-graphic-for-service' => 'Capability-Services-Five-Step-List',
    'five-step-infographic-ppt-case-studies-template-circle-diagram' => 'Case-Studies-Five-Step-Circle-Diagram',
    'editable-case-studies-five-steps-powerpoint-template-for-case-study' => 'Case-Studies-Five-Step-Timeline-Visual-Layout',
    'case-studies-six-steps-powerpoint-diagram-a-hexagon-diagram-layout' => 'Case-Studies-Six-Step-Hexagon-Diagram',
    'chain-diagram-eight-steps-powerpoint-template-editable-slides-for' => 'Chain-Diagram-Eight-Step-Concept-Framework',
    'eight-step-chain-diagram-powerpoint-template-with-an-overview-diagram' => 'Chain-Diagram-Eight-Step-Overview-Diagram',
    'chain-diagram-eight-steps-presentation-graphic-for-connected-process' => 'Chain-Diagram-Eight-Step-Visual-Layout',
    'eight-step-infographic-ppt-chain-diagram-template' => 'Chain-Diagram-Eight-Step',
    'editable-chain-diagram-four-steps-powerpoint-template-for-connected' => 'Chain-Diagram-Four-Step',
    'chain-diagram-seven-steps-powerpoint-diagram-a-clean-editable-layout' => 'Chain-Diagram-Seven-Step',
    'chain-diagram-six-steps-powerpoint-template-editable-slides-for' => 'Chain-Diagram-Six-Step-Visual-Layout',
    'six-step-chain-diagram-powerpoint-template-with-a-clean-editable' => 'Chain-Diagram-Six-Step',
    'chain-diagram-four-steps-presentation-graphic-for-connected-process' => 'Chain-Diagram',
    'eight-step-infographic-ppt-change-management-template-concept' => 'Change-Management-Concept-Framework',
    'editable-change-management-four-steps-powerpoint-template-for' => 'Change-Management-Four-Phase-Visual-Layout',
    'check-in-regularly-four-steps-powerpoint-diagram-a-clean-icon-based' => 'Check-In-Regularly-Four-Point-Visual-Layout',
    'circle-diagram-sixteen-steps-powerpoint-template-editable-slides-for' => 'Circle-Diagram-16-Step-Concept-Framework',
    'sixteen-step-circle-diagram-powerpoint-template-with-a-clean-icon' => 'Circle-Diagram-16-Step-Visual-Layout',
    'circle-diagram-sixteen-steps-presentation-graphic-for-business' => 'Circle-Diagram-16-Step',
    'eight-step-infographic-ppt-circle-diagram-template-concept-framework' => 'Circle-Diagram-Eight-Step-Concept-Framework-Orbit-Layout',
    'editable-circle-diagram-eight-steps-powerpoint-template-for-business' => 'Circle-Diagram-Eight-Step-Concept-Framework',
    'circle-diagram-eight-steps-powerpoint-diagram-an-overview-diagram' => 'Circle-Diagram-Eight-Step-Overview-Diagram',
    'circle-diagram-eight-steps-powerpoint-template-editable-slides-for' => 'Circle-Diagram-Eight-Step-Pinwheel-Layout',
    'eight-step-circle-diagram-powerpoint-template-with-a-clean-icon-based' => 'Circle-Diagram-Eight-Step-Visual-Layout',
    'circle-diagram-eight-steps-presentation-graphic-for-business-process' => 'Circle-Diagram-Eight-Step',
    'five-step-infographic-ppt-circle-diagram-template-analysis-template' => 'Circle-Diagram-Five-Step-Analysis-Template',
    'editable-circle-diagram-five-steps-powerpoint-template-for-business' => 'Circle-Diagram-Five-Step-Concept-Framework',
    'circle-diagram-five-steps-powerpoint-diagram-a-circular-cycle-layout' => 'Circle-Diagram-Five-Step-Cycle-Diagram',
    'circle-diagram-five-steps-powerpoint-template-editable-slides-for' => 'Circle-Diagram-Five-Step-Data-Visualization',
    'five-step-circle-diagram-powerpoint-template-with-a-detailed' => 'Circle-Diagram-Five-Step-Detailed-Breakdown',
    'circle-diagram-five-steps-presentation-graphic-for-business-process' => 'Circle-Diagram-Five-Step-Overview-Diagram',
    'five-step-infographic-ppt-circle-diagram-template-process-guide' => 'Circle-Diagram-Five-Step-Process-Guide',
    'editable-circle-diagram-five-steps-powerpoint-template-for-business-2' => 'Circle-Diagram-Five-Step-Strategy-Map',
    'circle-diagram-five-steps-powerpoint-diagram-a-summary-chart-layout' => 'Circle-Diagram-Five-Step-Summary-Chart',
    'circle-diagram-five-steps-powerpoint-template-editable-slides-for-2' => 'Circle-Diagram-Five-Step-Visual-Layout',
    'five-step-circle-diagram-powerpoint-template-with-a-clean-editable' => 'Circle-Diagram-Five-Step',
    'circle-diagram-four-steps-presentation-graphic-for-business-process' => 'Circle-Diagram-Four-Step-Analysis-Template',
    'four-step-infographic-ppt-circle-diagram-template-complete-guide' => 'Circle-Diagram-Four-Step-Complete-Guide',
    'editable-circle-diagram-four-steps-powerpoint-template-for-business' => 'Circle-Diagram-Four-Step-Concept-Framework',
    'circle-diagram-four-steps-powerpoint-diagram-a-data-visualization' => 'Circle-Diagram-Four-Step-Data-Visualization',
    'circle-diagram-four-steps-powerpoint-template-editable-slides-for' => 'Circle-Diagram-Four-Step-Detailed-Breakdown',
    'four-step-circle-diagram-powerpoint-template-with-an-overview-diagram' => 'Circle-Diagram-Four-Step-Overview-Diagram',
    'circle-diagram-four-steps-presentation-graphic-for-business-process-2' => 'Circle-Diagram-Four-Step-Process-Guide',
    'four-step-infographic-ppt-circle-diagram-template-strategy-map' => 'Circle-Diagram-Four-Step-Strategy-Map',
    'editable-circle-diagram-four-steps-powerpoint-template-for-business-2' => 'Circle-Diagram-Four-Step',
    'circle-diagram-six-steps-powerpoint-diagram-a-structured-concept' => 'Circle-Diagram-Six-Step-Concept-Framework',
    'circle-diagram-six-steps-powerpoint-template-editable-slides-for' => 'Circle-Diagram-Six-Step-Overview-Diagram',
    'six-step-circle-diagram-powerpoint-template-with-a-step-by-step' => 'Circle-Diagram-Six-Step-Process-Guide',
    'circle-diagram-six-steps-presentation-graphic-for-business-process' => 'Circle-Diagram-Six-Step-Summary-Chart',
    'six-step-infographic-ppt-circle-diagram-template-visual-layout' => 'Circle-Diagram-Six-Step-Visual-Layout',
    'editable-circle-diagram-six-steps-powerpoint-template-for-business' => 'Circle-Diagram-Six-Step',
    'circle-diagram-three-steps-powerpoint-diagram-a-structured-concept' => 'Circle-Diagram-Three-Step-Concept-Framework',
    'circle-diagram-three-steps-powerpoint-template-editable-slides-for' => 'Circle-Diagram-Three-Step-Overview-Diagram',
    'three-step-circle-diagram-powerpoint-template-with-a-clean-icon-based' => 'Circle-Diagram-Three-Step-Visual-Layout',
    'circle-diagram-three-steps-presentation-graphic-for-business-process' => 'Circle-Diagram-Three-Step',
    'two-step-infographic-ppt-circle-diagram-template-summary-chart' => 'Circle-Diagram-Two-Step-Summary-Chart',
    'editable-circle-with-puzzle-diagram-six-steps-powerpoint-template-for' => 'Circle-with-Puzzle-Diagram-Six-Step',
    'circular-diagram-five-steps-powerpoint-diagram-a-clean-editable-layout' => 'Circular-Diagram-Five-Step',
    'circular-diagram-four-steps-powerpoint-template-editable-slides-for' => 'Circular-Diagram-Four-Step',
    'six-step-circular-diagram-powerpoint-template-with-a-clean-editable' => 'Circular-Diagram-Six-Step',
    'delivery-with-devops-three-steps-presentation-graphic-for-devops' => 'Delivery-with-DevOps-Three-Step-Triangle-Diagram',
    'six-step-infographic-ppt-develop-devops-strategy-template-zigzag' => 'Develop-DevOps-Strategy-Six-Step-Zigzag-Diagram',
    ];

    $out = [];
    $out[] = 'Slug map entries: ' . count($slug_map);

    $img_set = 0; $ver_created = 0; $already_img = 0; $already_ver = 0;
    $not_found_post = []; $missing_pptx = []; $missing_image = [];

    foreach ($slug_map as $slug => $basename) {
        $post = get_page_by_path($slug, OBJECT, 'dlm_download');
        if (!$post) {
            $not_found_post[] = $slug;
            continue;
        }
        $pid = $post->ID;

        // --- Featured image ---
        $image_file = null;
        foreach (['png', 'jpg', 'jpeg', 'PNG', 'JPG', 'JPEG'] as $ext) {
            $candidate = $dir . $basename . '.' . $ext;
            if (file_exists($candidate)) { $image_file = $candidate; break; }
        }
        if (has_post_thumbnail($pid)) {
            $already_img++;
        } elseif ($image_file) {
            $mime = (stripos($image_file, '.png') !== false) ? 'image/png' : 'image/jpeg';
            $aid = wp_insert_attachment([
                'post_mime_type' => $mime,
                'post_title'     => get_the_title($pid),
                'post_status'    => 'inherit',
            ], $image_file, $pid);
            if (!is_wp_error($aid)) {
                wp_update_attachment_metadata($aid, wp_generate_attachment_metadata($aid, $image_file));
                set_post_thumbnail($pid, $aid);
                $img_set++;
            }
        } else {
            $missing_image[] = $slug;
        }

        // --- Downloadable file (dlm_download_version) ---
        $pptx_file = $dir . $basename . '.pptx';
        $existing_version = get_posts([
            'post_type'   => 'dlm_download_version',
            'post_parent' => $pid,
            'post_status' => 'any',
            'numberposts' => 1,
        ]);
        if ($existing_version) {
            $already_ver++;
        } elseif (file_exists($pptx_file)) {
            $file_url = $url . rawurlencode($basename . '.pptx');
            $vid = wp_insert_post([
                'post_type'   => 'dlm_download_version',
                'post_parent' => $pid,
                'post_status' => 'publish',
                'post_title'  => '1.0',
            ]);
            if ($vid && !is_wp_error($vid)) {
                update_post_meta($vid, '_version', '1.0');
                update_post_meta($vid, '_files', [$file_url]);
                $ver_created++;
            }
        } else {
            $missing_pptx[] = $slug;
        }
    }

    $out[] = "Posts not found by slug: " . count($not_found_post);
    if ($not_found_post) $out[] = 'NOT FOUND: ' . implode(', ', $not_found_post);
    $out[] = "Featured images set now: $img_set (already had one: $already_img)";
    $out[] = "Download file versions created now: $ver_created (already had one: $already_ver)";
    if ($missing_image) $out[] = 'MISSING IMAGE FILE (' . count($missing_image) . '): ' . implode(', ', $missing_image);
    if ($missing_pptx) $out[] = 'MISSING PPTX FILE (' . count($missing_pptx) . '): ' . implode(', ', $missing_pptx);

    wp_die(implode('<br>', $out));
});
