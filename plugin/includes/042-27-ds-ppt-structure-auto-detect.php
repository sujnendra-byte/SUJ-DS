<?php
/**
 * Code Snippets export — 27 DS PPT Structure Auto-Detect
 *
 * Snippet ID : 728
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-042
 * Modified   : 2026-09-09 02:43:22
 * Load order : 042 of 144
 */
/**
 * REPLACEMENT for Snippet 27 DS PPT Structure Auto-Detect
 * ---------------------------------------------------------
 * This file contains TWO functions that replace the ones with the same
 * names currently in Snippet 27. Everything else in that snippet —
 * ds_preso_build_directive(), ds_preso_inject_outline(), and the
 * add_filter('http_request_args', ...) call at the very bottom —
 * stays exactly as it is. Do not duplicate those; just swap these two.
 *
 * WHAT CHANGED AND WHY:
 *
 * 1. ds_preso_outlines() — now returns all 84 presentation types from
 *    your "80+ Presentation Types, 20 Slides Each" spreadsheet, using
 *    the exact slide names from each sheet, in the exact order given.
 *    The 3 types that already existed (Pitch Deck, Company Overview,
 *    Marketing Plan) are replaced with the spreadsheet's versions,
 *    which have more detailed/different section lists than what was
 *    hand-written before. The "Startup Investor Pitch Deck" type is
 *    replaced by the spreadsheet's own "Startup Pitch Deck" (its
 *    closest equivalent), plus its own separate "Seed Funding Deck"
 *    and "Series A-B-C Investor Deck" types now exist as distinct
 *    options rather than being folded into one.
 *
 * 2. ds_preso_detect_type() — the matching logic changed from "first
 *    type in the list whose keyword matches wins" to "the LONGEST
 *    matching keyword wins", i.e. the most specific match. With only
 *    4 types this didn't matter, but at 84 types many keywords share
 *    words ("pitch deck" is a plain substring of "startup pitch
 *    deck"), so specificity has to decide the winner instead of
 *    insertion order. Verified: every single one of the 84 types
 *    correctly self-identifies when its own exact name is typed as
 *    the topic, with zero collisions.
 */

if ( ! function_exists('ds_preso_outlines') ) {
function ds_preso_outlines() {
    return array(
        'business_and_corporate' => array(
            'label'    => 'Business & Corporate',
            'keywords' => array( 'business & corporate' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'pitch_deck' => array(
            'label'    => 'Pitch Deck',
            'keywords' => array( 'pitch deck', 'sales pitch', 'business pitch', 'pitch presentation', 'pitching my', 'pitch for my' ),
            'sections' => array( 'Cover', 'Vision', 'Problem', 'Solution', 'Product Overview', 'Product Demo', 'Market Opportunity', 'Target Audience', 'Business Model', 'Revenue Streams', 'Competitive Analysis', 'Go-to-Market Strategy', 'Traction', 'Financial Projections', 'Team', 'Roadmap', 'Funding Ask', 'Use of Funds', 'Future Vision', 'Thank You' ),
        ),
        'company_overview' => array(
            'label'    => 'Company Overview',
            'keywords' => array( 'company overview', 'company profile business', 'about us', 'about our company', 'corporate overview', 'business overview', 'organization profile', 'who we are' ),
            'sections' => array( 'Cover', 'Company Snapshot', 'Mission & Vision', 'History', 'Products & Services', 'Industries Served', 'Business Model', 'Market Presence', 'Customers', 'Competitive Advantage', 'Operations', 'Technology', 'Achievements', 'Financial Highlights', 'Leadership', 'CSR', 'Growth Strategy', 'Future Plans', 'Contact', 'Q&A' ),
        ),
        'company_profile' => array(
            'label'    => 'Company Profile',
            'keywords' => array( 'company profile' ),
            'sections' => array( 'Cover', 'About Us', 'Vision & Mission', 'Company History', 'Core Values', 'Products & Services', 'Industries Served', 'Capabilities', 'Our Process', 'Infrastructure', 'Leadership Team', 'Clients', 'Portfolio', 'Awards', 'Certifications', 'Global Presence', 'Why Choose Us', 'Future Plans', 'Contact', 'Q&A' ),
        ),
        'business_proposal' => array(
            'label'    => 'Business Proposal',
            'keywords' => array( 'business proposal' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'sales_presentation' => array(
            'label'    => 'Sales Presentation',
            'keywords' => array( 'sales presentation' ),
            'sections' => array( 'Cover', 'Agenda', 'Customer Challenges', 'Solution', 'Product Overview', 'Features', 'Benefits', 'Pricing', 'ROI', 'Case Studies', 'Testimonials', 'Competitive Comparison', 'Implementation', 'Support', 'Success Metrics', 'Why Choose Us', 'Offer', 'Next Steps', 'Contact', 'Q&A' ),
        ),
        'product_presentation' => array(
            'label'    => 'Product Presentation',
            'keywords' => array( 'product presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'service_presentation' => array(
            'label'    => 'Service Presentation',
            'keywords' => array( 'service presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'marketing_plan' => array(
            'label'    => 'Marketing Plan',
            'keywords' => array( 'marketing plan', 'marketing strategy plan', 'marketing campaign plan', 'digital marketing plan', 'marketing proposal', 'marketing plan presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Marketing Objectives', 'Market Analysis', 'Customer Personas', 'SWOT Analysis', 'Competitive Analysis', 'Brand Positioning', 'Marketing Mix', 'Campaign Strategy', 'Content Plan', 'Digital Strategy', 'Budget', 'Timeline', 'KPIs', 'Risk Management', 'Growth Strategy', 'Recommendations', 'Next Steps', 'Q&A' ),
        ),
        'business_plan' => array(
            'label'    => 'Business Plan',
            'keywords' => array( 'business plan' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Business Description', 'Mission & Vision', 'Products & Services', 'Market Analysis', 'Target Customers', 'Business Model', 'Marketing Strategy', 'Sales Strategy', 'Operations Plan', 'Organization', 'Financial Plan', 'Funding Requirements', 'Risk Analysis', 'Growth Strategy', 'Milestones', 'Implementation', 'Appendix', 'Q&A' ),
        ),
        'annual_report' => array(
            'label'    => 'Annual Report',
            'keywords' => array( 'annual report' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'quarterly_business_review_qbr' => array(
            'label'    => 'Quarterly Business Review (QBR)',
            'keywords' => array( 'quarterly business review (qbr)', 'qbr' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'investor_presentation' => array(
            'label'    => 'Investor Presentation',
            'keywords' => array( 'investor presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Problem', 'Solution', 'Market Opportunity', 'Business Model', 'Revenue Streams', 'Traction', 'Competitive Advantage', 'Technology', 'Financial Highlights', 'Growth Strategy', 'Roadmap', 'Team', 'Advisors', 'Funding Ask', 'Use of Funds', 'Risk Analysis', 'Future Vision', 'Q&A' ),
        ),
        'financial_report' => array(
            'label'    => 'Financial Report',
            'keywords' => array( 'financial report' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'strategic_plan' => array(
            'label'    => 'Strategic Plan',
            'keywords' => array( 'strategic plan' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'corporate_profile' => array(
            'label'    => 'Corporate Profile',
            'keywords' => array( 'corporate profile' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'capability_statement' => array(
            'label'    => 'Capability Statement',
            'keywords' => array( 'capability statement' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'partnership_proposal' => array(
            'label'    => 'Partnership Proposal',
            'keywords' => array( 'partnership proposal' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'franchise_presentation' => array(
            'label'    => 'Franchise Presentation',
            'keywords' => array( 'franchise presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'project_proposal' => array(
            'label'    => 'Project Proposal',
            'keywords' => array( 'project proposal' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'project_status_report' => array(
            'label'    => 'Project Status Report',
            'keywords' => array( 'project status report' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'startup_pitch_deck' => array(
            'label'    => 'Startup Pitch Deck',
            'keywords' => array( 'startup pitch deck', 'startup investor pitch', 'pitch to investors', 'investor deck', 'funding pitch', 'seed pitch', 'vc pitch', 'venture capital', 'raise funding', 'raising funding', 'fundraising deck', 'startup investor', 'seeking investment', 'angel investor' ),
            'sections' => array( 'Cover', 'Vision', 'Problem', 'Solution', 'Product Demo', 'Market Size', 'Business Model', 'Competition', 'Go-to-Market Strategy', 'Traction', 'Customers', 'Technology', 'Financials', 'Team', 'Advisors', 'Roadmap', 'Funding Ask', 'Use of Funds', 'Exit Strategy', 'Thank You' ),
        ),
        'seed_funding_deck' => array(
            'label'    => 'Seed Funding Deck',
            'keywords' => array( 'seed funding deck' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'series_a_b_c_investor_deck' => array(
            'label'    => 'Series A-B-C Investor Deck',
            'keywords' => array( 'series a-b-c investor deck' ),
            'sections' => array( 'Cover', 'Series A/B/C Investor Deck - Executive Summary', 'Series A/B/C Investor Deck - Objectives', 'Series A/B/C Investor Deck - Background', 'Series A/B/C Investor Deck - Problem Statement', 'Series A/B/C Investor Deck - Current Situation', 'Series A/B/C Investor Deck - Solution Overview', 'Series A/B/C Investor Deck - Key Features', 'Series A/B/C Investor Deck - Benefits', 'Series A/B/C Investor Deck - Target Audience', 'Series A/B/C Investor Deck - Market Analysis', 'Series A/B/C Investor Deck - Competitive Analysis', 'Series A/B/C Investor Deck - Strategy', 'Series A/B/C Investor Deck - Implementation Plan', 'Series A/B/C Investor Deck - Timeline', 'Series A/B/C Investor Deck - Budget / Resources', 'Series A/B/C Investor Deck - Risks & Mitigation', 'Series A/B/C Investor Deck - Expected Outcomes', 'Series A/B/C Investor Deck - Next Steps', 'Q&A' ),
        ),
        'demo_day_presentation' => array(
            'label'    => 'Demo Day Presentation',
            'keywords' => array( 'demo day presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'investment_proposal' => array(
            'label'    => 'Investment Proposal',
            'keywords' => array( 'investment proposal' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'exit_strategy_presentation' => array(
            'label'    => 'Exit Strategy Presentation',
            'keywords' => array( 'exit strategy presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'brand_presentation' => array(
            'label'    => 'Brand Presentation',
            'keywords' => array( 'brand presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'go_to_market_strategy' => array(
            'label'    => 'Go-to-Market Strategy',
            'keywords' => array( 'go-to-market strategy' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'campaign_presentation' => array(
            'label'    => 'Campaign Presentation',
            'keywords' => array( 'campaign presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'product_launch' => array(
            'label'    => 'Product Launch',
            'keywords' => array( 'product launch' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'customer_success_story' => array(
            'label'    => 'Customer Success Story',
            'keywords' => array( 'customer success story' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'case_study' => array(
            'label'    => 'Case Study',
            'keywords' => array( 'case study' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'pricing_presentation' => array(
            'label'    => 'Pricing Presentation',
            'keywords' => array( 'pricing presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'sales_enablement_deck' => array(
            'label'    => 'Sales Enablement Deck',
            'keywords' => array( 'sales enablement deck' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'client_proposal' => array(
            'label'    => 'Client Proposal',
            'keywords' => array( 'client proposal' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'training_presentation' => array(
            'label'    => 'Training Presentation',
            'keywords' => array( 'training presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'workshop_presentation' => array(
            'label'    => 'Workshop Presentation',
            'keywords' => array( 'workshop presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'course_presentation' => array(
            'label'    => 'Course Presentation',
            'keywords' => array( 'course presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'lesson_plan' => array(
            'label'    => 'Lesson Plan',
            'keywords' => array( 'lesson plan' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'educational_presentation' => array(
            'label'    => 'Educational Presentation',
            'keywords' => array( 'educational presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'webinar_presentation' => array(
            'label'    => 'Webinar Presentation',
            'keywords' => array( 'webinar presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'orientation_presentation' => array(
            'label'    => 'Orientation Presentation',
            'keywords' => array( 'orientation presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'project_kickoff' => array(
            'label'    => 'Project Kickoff',
            'keywords' => array( 'project kickoff' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'project_update' => array(
            'label'    => 'Project Update',
            'keywords' => array( 'project update' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'project_review' => array(
            'label'    => 'Project Review',
            'keywords' => array( 'project review' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'roadmap_presentation' => array(
            'label'    => 'Roadmap Presentation',
            'keywords' => array( 'roadmap presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'risk_assessment' => array(
            'label'    => 'Risk Assessment',
            'keywords' => array( 'risk assessment' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'timeline_presentation' => array(
            'label'    => 'Timeline Presentation',
            'keywords' => array( 'timeline presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'employee_onboarding' => array(
            'label'    => 'Employee Onboarding',
            'keywords' => array( 'employee onboarding' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'hr_policy_presentation' => array(
            'label'    => 'HR Policy Presentation',
            'keywords' => array( 'hr policy presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'performance_review' => array(
            'label'    => 'Performance Review',
            'keywords' => array( 'performance review' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'recruitment_presentation' => array(
            'label'    => 'Recruitment Presentation',
            'keywords' => array( 'recruitment presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'company_culture_deck' => array(
            'label'    => 'Company Culture Deck',
            'keywords' => array( 'company culture deck' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'software_demo' => array(
            'label'    => 'Software Demo',
            'keywords' => array( 'software demo' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'product_roadmap' => array(
            'label'    => 'Product Roadmap',
            'keywords' => array( 'product roadmap' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'system_architecture' => array(
            'label'    => 'System Architecture',
            'keywords' => array( 'system architecture' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'technical_documentation' => array(
            'label'    => 'Technical Documentation',
            'keywords' => array( 'technical documentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'cybersecurity_awareness' => array(
            'label'    => 'Cybersecurity Awareness',
            'keywords' => array( 'cybersecurity awareness' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'research_presentation' => array(
            'label'    => 'Research Presentation',
            'keywords' => array( 'research presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'survey_results' => array(
            'label'    => 'Survey Results',
            'keywords' => array( 'survey results' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'data_analysis' => array(
            'label'    => 'Data Analysis',
            'keywords' => array( 'data analysis' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'market_research' => array(
            'label'    => 'Market Research',
            'keywords' => array( 'market research' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'swot_analysis' => array(
            'label'    => 'SWOT Analysis',
            'keywords' => array( 'swot analysis', 'swot' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'competitive_analysis' => array(
            'label'    => 'Competitive Analysis',
            'keywords' => array( 'competitive analysis' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'conference_presentation' => array(
            'label'    => 'Conference Presentation',
            'keywords' => array( 'conference presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'keynote_presentation' => array(
            'label'    => 'Keynote Presentation',
            'keywords' => array( 'keynote presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'seminar_presentation' => array(
            'label'    => 'Seminar Presentation',
            'keywords' => array( 'seminar presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'workshop_deck' => array(
            'label'    => 'Workshop Deck',
            'keywords' => array( 'workshop deck' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'event_proposal' => array(
            'label'    => 'Event Proposal',
            'keywords' => array( 'event proposal' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'portfolio_presentation' => array(
            'label'    => 'Portfolio Presentation',
            'keywords' => array( 'portfolio presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'design_portfolio' => array(
            'label'    => 'Design Portfolio',
            'keywords' => array( 'design portfolio' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'photography_portfolio' => array(
            'label'    => 'Photography Portfolio',
            'keywords' => array( 'photography portfolio' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'architecture_portfolio' => array(
            'label'    => 'Architecture Portfolio',
            'keywords' => array( 'architecture portfolio' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'creative_agency_deck' => array(
            'label'    => 'Creative Agency Deck',
            'keywords' => array( 'creative agency deck' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'medical_presentation' => array(
            'label'    => 'Medical Presentation',
            'keywords' => array( 'medical presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'healthcare_report' => array(
            'label'    => 'Healthcare Report',
            'keywords' => array( 'healthcare report' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'ngo_presentation' => array(
            'label'    => 'NGO Presentation',
            'keywords' => array( 'ngo presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'fundraising_presentation' => array(
            'label'    => 'Fundraising Presentation',
            'keywords' => array( 'fundraising presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'grant_proposal' => array(
            'label'    => 'Grant Proposal',
            'keywords' => array( 'grant proposal' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'resume_presentation' => array(
            'label'    => 'Resume Presentation',
            'keywords' => array( 'resume presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'personal_portfolio' => array(
            'label'    => 'Personal Portfolio',
            'keywords' => array( 'personal portfolio' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'career_profile' => array(
            'label'    => 'Career Profile',
            'keywords' => array( 'career profile' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
        'interview_presentation' => array(
            'label'    => 'Interview Presentation',
            'keywords' => array( 'interview presentation' ),
            'sections' => array( 'Cover', 'Executive Summary', 'Objectives', 'Background', 'Problem Statement', 'Current Situation', 'Solution Overview', 'Key Features', 'Benefits', 'Target Audience', 'Market Analysis', 'Competitive Analysis', 'Strategy', 'Implementation Plan', 'Timeline', 'Budget / Resources', 'Risks & Mitigation', 'Expected Outcomes', 'Next Steps', 'Q&A' ),
        ),
    );
}
}

if ( ! function_exists('ds_preso_detect_type') ) {
function ds_preso_detect_type( $topic ) {
    $t = strtolower( (string) $topic );
    $best_type = null;
    $best_len  = 0;
    foreach ( ds_preso_outlines() as $type_id => $def ) {
        foreach ( $def['keywords'] as $kw ) {
            if ( strlen($kw) > $best_len && strpos($t, $kw) !== false ) {
                $best_len  = strlen($kw);
                $best_type = $type_id;
            }
        }
    }
    return $best_type;
}
}
