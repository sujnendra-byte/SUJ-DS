<?php
/**
 * Code Snippets export — DS Homepage Split Section
 *
 * Snippet ID : 697
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-011
 * Modified   : 2026-09-09 02:43:50
 * Load order : 011 of 144
 */
/**
 * Designstager — DS Homepage Split Section
 * Snippet Name: DS Homepage Split Section
 * Run: Everywhere
 * Usage: [ds_homepage_split]
 */

add_shortcode('ds_homepage_split', function() {

    // Get 4 latest marketplace items for preview
    $downloads = get_posts(array(
        'post_type'      => 'dlm_download',
        'posts_per_page' => 4,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));

    ob_start(); ?>

<div class="ds-split-section">

    <!-- ===== LEFT: AI STUDIO (55%) ===== -->
    <div class="ds-split-left">

        <!-- Glow effect -->
        <div class="ds-split-glow"></div>

        <!-- Badge -->
        <div class="ds-split-badge">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="#E8442A"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6L12 2z"/></svg>
            New — AI Studio
        </div>

        <!-- Heading -->
        <h2 class="ds-split-title">Create Anything<br>with <span class="ds-split-highlight">AI</span></h2>
        <p class="ds-split-desc">Generate stunning images and professional presentations using 7 AI models — Flux Pro, DALL·E 3, and more. In any language.</p>

        <!-- Feature Pills -->
        <div class="ds-split-features">
            <div class="ds-split-feature">
                <div class="ds-split-feature-icon">🎨</div>
                <div>
                    <div class="ds-split-feature-title">AI Images</div>
                    <div class="ds-split-feature-desc">7 models including Flux Pro & DALL·E 3</div>
                </div>
            </div>
            <div class="ds-split-feature">
                <div class="ds-split-feature-icon">📊</div>
                <div>
                    <div class="ds-split-feature-title">AI Presentations</div>
                    <div class="ds-split-feature-desc">PPTX & PDF with AI images on every slide</div>
                </div>
            </div>
            <div class="ds-split-feature">
                <div class="ds-split-feature-icon">🌐</div>
                <div>
                    <div class="ds-split-feature-title">8 Indian Languages</div>
                    <div class="ds-split-feature-desc">Hindi, Kannada, Tamil, Telugu & more</div>
                </div>
            </div>
        </div>

        <!-- Models Row -->
        <div class="ds-split-models">
            <span>Flux Pro</span>
            <span>DALL·E 3</span>
            <span>Flux Dev</span>
            <span>Flux Schnell</span>
            <span>+3 more</span>
        </div>

        <!-- CTA -->
        <div class="ds-split-cta">
            <?php $ds_aistudio_url = is_user_logged_in() ? home_url('/ai-studio') : home_url('/login/?redirect_to=' . urlencode(home_url('/ai-studio'))); ?>
            <a href="<?php echo esc_url($ds_aistudio_url); ?>" class="ds-split-btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"><path d="M12 2l2.4 7.4H22l-6.2 4.5 2.4 7.4L12 17l-6.2 4.3 2.4-7.4L2 9.4h7.6L12 2z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round" fill="currentColor"/></svg>
                Try AI Studio Free
            </a>
            <div class="ds-split-free-note">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#10B981" stroke-width="2"/><path d="M8 12l3 3 5-5" stroke="#10B981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                2 AI images/day free + marketplace access
            </div>
        </div>

    </div>

    <!-- ===== RIGHT: MARKETPLACE (45%) ===== -->
    <div class="ds-split-right">

        <!-- Header -->
        <div class="ds-split-market-header">
            <div>
                <h3 class="ds-split-market-title">Marketplace</h3>
                <p class="ds-split-market-sub">Browse 500+ premium design templates</p>
            </div>
            <a href="<?php echo home_url('/marketplace'); ?>" class="ds-split-market-link">
                View All
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M5 12h14M12 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </a>
        </div>

        <!-- Preview Grid -->
        <div class="ds-split-market-grid">
            <?php if (!empty($downloads)) :
                foreach ($downloads as $dl) :
                    $thumb    = get_the_post_thumbnail_url($dl->ID, 'medium');
                    $cats     = get_the_terms($dl->ID, 'dlm_download_category');
                    $cat_name = $cats ? $cats[0]->name : '';
                    $tags     = get_the_terms($dl->ID, 'dlm_download_tag');
                    $tag_slugs = $tags ? wp_list_pluck($tags, 'slug') : array();
                    $is_premium = in_array('premium', $tag_slugs);
                    if (!$thumb) continue;
                ?>
                <div class="ds-split-market-card">
                    <div class="ds-split-market-thumb">
                        <img src="<?php echo esc_url($thumb); ?>"
                             alt="<?php echo esc_attr($dl->post_title); ?>"
                             loading="lazy"/>
                        <span class="ds-split-market-cat"><?php echo esc_html($cat_name); ?></span>
                        <span class="ds-split-market-tag <?php echo $is_premium ? 'ds-tag-premium' : 'ds-tag-free'; ?>">
                            <?php echo $is_premium ? '★ Premium' : 'Standard'; ?>
                        </span>
                    </div>
                    <div class="ds-split-market-info">
                        <p><?php echo esc_html(wp_trim_words($dl->post_title, 5)); ?></p>
                    </div>
                </div>
                <?php endforeach;
            else : ?>
                <!-- Placeholder if no downloads -->
                <?php for ($i = 0; $i < 4; $i++) : ?>
                <div class="ds-split-market-card ds-split-placeholder">
                    <div class="ds-split-market-thumb ds-thumb-placeholder">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" opacity="0.3"><rect x="3" y="3" width="18" height="18" rx="3" stroke="currentColor" stroke-width="1.5"/><circle cx="8.5" cy="8.5" r="1.5" fill="currentColor"/><path d="M3 15l5-5 4 4 3-3 6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                    </div>
                </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>

        <!-- Stats -->
        <div class="ds-split-market-stats">
            <div class="ds-split-market-stat">
                <div class="ds-split-stat-num">500+</div>
                <div class="ds-split-stat-label">Templates</div>
            </div>
            <div class="ds-split-market-stat">
                <div class="ds-split-stat-num">Free</div>
                <div class="ds-split-stat-label">Standard Access</div>
            </div>
            <div class="ds-split-market-stat">
                <div class="ds-split-stat-num">PPTX</div>
                <div class="ds-split-stat-label">PDF & More</div>
            </div>
        </div>

        <!-- CTA -->
        <a href="<?php echo home_url('/marketplace'); ?>" class="ds-split-market-btn">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><rect x="3" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5" stroke="currentColor" stroke-width="1.5"/></svg>
            Browse Marketplace
        </a>

    </div>

</div>

<style>
/* ===== SPLIT SECTION ===== */
.ds-split-section {
    display: grid;
    grid-template-columns: 55% 45%;
    min-height: 580px;
    overflow: hidden;
}

/* ===== LEFT — AI STUDIO ===== */
.ds-split-left {
    background: linear-gradient(135deg, #0d0f1a 0%, #1C1F2E 60%, #151828 100%);
    padding: 56px 48px;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Glow */
.ds-split-glow {
    position: absolute;
    top: -100px;
    right: -100px;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(232,68,42,0.12) 0%, transparent 70%);
    pointer-events: none;
}

/* Badge */
.ds-split-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(232,68,42,0.12);
    color: #E8442A;
    border: 1px solid rgba(232,68,42,0.3);
    border-radius: 20px;
    padding: 5px 14px;
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 20px;
    width: fit-content;
}

/* Title */
.ds-split-title {
    font-size: 42px;
    font-weight: 800;
    color: #ffffff;
    line-height: 1.15;
    margin: 0 0 14px;
}
.ds-split-highlight {
    color: #E8442A;
    position: relative;
}
.ds-split-highlight::after {
    content: '';
    position: absolute;
    bottom: 2px;
    left: 0;
    right: 0;
    height: 3px;
    background: #E8442A;
    border-radius: 2px;
    opacity: 0.4;
}

.ds-split-desc {
    font-size: 15px;
    color: #94A3B8;
    line-height: 1.6;
    margin: 0 0 28px;
    max-width: 420px;
}

/* Features */
.ds-split-features {
    display: flex;
    flex-direction: column;
    gap: 14px;
    margin-bottom: 24px;
}
.ds-split-feature {
    display: flex;
    align-items: center;
    gap: 14px;
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.07);
    border-radius: 12px;
    padding: 12px 16px;
    transition: border-color 0.2s;
}
.ds-split-feature:hover { border-color: rgba(232,68,42,0.3); }
.ds-split-feature-icon { font-size: 22px; min-width: 32px; text-align: center; }
.ds-split-feature-title { font-size: 13px; font-weight: 700; color: #ffffff; margin-bottom: 2px; }
.ds-split-feature-desc { font-size: 12px; color: #64748B; }

/* Models */
.ds-split-models {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 28px;
}
.ds-split-models span {
    background: rgba(232,68,42,0.08);
    color: #E8442A;
    border: 1px solid rgba(232,68,42,0.2);
    border-radius: 6px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 700;
}

/* CTA */
.ds-split-cta {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.ds-split-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #E8442A;
    color: #ffffff !important;
    padding: 13px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none !important;
    transition: all 0.15s;
    box-shadow: 0 8px 24px rgba(232,68,42,0.35);
}
.ds-split-btn-primary:hover {
    background: #c73520;
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(232,68,42,0.45);
}
.ds-split-free-note {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #10B981;
    font-weight: 500;
}

/* ===== RIGHT — MARKETPLACE ===== */
.ds-split-right {
    background: #f8f9fa;
    padding: 48px 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    border-left: 1px solid #eeeeee;
}

.ds-split-market-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 20px;
}
.ds-split-market-title {
    font-size: 24px;
    font-weight: 800;
    color: #1a1a1a;
    margin: 0 0 4px;
}
.ds-split-market-sub {
    font-size: 13px;
    color: #888;
    margin: 0;
}
.ds-split-market-link {
    display: flex;
    align-items: center;
    gap: 4px;
    color: #E8442A !important;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none !important;
    white-space: nowrap;
    margin-top: 4px;
}
.ds-split-market-link:hover { text-decoration: underline !important; }

/* Market Grid */
.ds-split-market-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
    margin-bottom: 20px;
}
.ds-split-market-card {
    background: #ffffff;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #eeeeee;
    transition: transform 0.2s, box-shadow 0.2s;
    cursor: pointer;
}
.ds-split-market-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
}
.ds-split-market-thumb {
    position: relative;
    height: 110px;
    overflow: hidden;
    background: #f0f0f0;
}
.ds-split-market-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}
.ds-split-market-card:hover .ds-split-market-thumb img { transform: scale(1.05); }
.ds-split-market-cat {
    position: absolute;
    bottom: 6px;
    left: 6px;
    background: rgba(0,0,0,0.6);
    color: #fff;
    font-size: 9px;
    font-weight: 600;
    padding: 2px 7px;
    border-radius: 4px;
    backdrop-filter: blur(4px);
}
.ds-split-market-tag {
    position: absolute;
    top: 6px;
    right: 6px;
    font-size: 9px;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 4px;
}
.ds-tag-premium { background: #4C0519; color: #FB7185; }
.ds-tag-free { background: #064E3B; color: #10B981; }
.ds-split-market-info { padding: 8px 10px; }
.ds-split-market-info p { font-size: 11px; font-weight: 600; color: #1a1a1a; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

/* Placeholder */
.ds-thumb-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f0f0f0 !important;
    color: #ccc;
}

/* Stats */
.ds-split-market-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-bottom: 20px;
}
.ds-split-market-stat {
    background: #ffffff;
    border: 1px solid #eeeeee;
    border-radius: 10px;
    padding: 12px;
    text-align: center;
}
.ds-split-stat-num {
    font-size: 18px;
    font-weight: 800;
    color: #1a1a1a;
    margin-bottom: 2px;
}
.ds-split-stat-label {
    font-size: 10px;
    color: #888;
    font-weight: 500;
}

/* Market CTA */
.ds-split-market-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #1C1F2E;
    color: #ffffff !important;
    padding: 13px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
    text-decoration: none !important;
    transition: all 0.15s;
    text-align: center;
}
.ds-split-market-btn:hover {
    background: #2A2D3E;
    transform: translateY(-2px);
}

/* ===== MOBILE ===== */
@media (max-width: 768px) {
    .ds-split-section {
        grid-template-columns: 1fr !important;
    }
    .ds-split-left {
        padding: 40px 24px;
        order: 1;
    }
    .ds-split-right {
        padding: 40px 24px;
        order: 2;
        border-left: none !important;
        border-top: 1px solid #eeeeee;
    }
    .ds-split-title { font-size: 32px !important; }
    .ds-split-market-grid { grid-template-columns: 1fr 1fr !important; }
}
</style>

<?php return ob_get_clean();
});
