<?php
/**
 * Code Snippets export — DS Marketplace Admin Approve
 *
 * Snippet ID : 706
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-020
 * Modified   : 2026-09-09 02:43:14
 * Load order : 020 of 144
 */
/**
 * Snippet Name: DS Marketplace Admin Approve
 * Run: Admin Only
 */
add_action('add_meta_boxes', function() {
    add_meta_box('ds_approve_box', 'DS Marketplace Approval', function($post) {
        $paid      = get_post_meta($post->ID, 'ds_marketplace_cashback_paid', true);
        $cashback  = (int)get_post_meta($post->ID, 'ds_marketplace_cashback', true);
        $submitter = (int)get_post_meta($post->ID, 'ds_marketplace_submitter', true);
        $status    = get_post_meta($post->ID, 'ds_marketplace_status', true);
        $nonce     = wp_create_nonce('wp_rest');
        $rest      = trailingslashit(rest_url('ds/v1'));
        if (!$cashback && !$submitter) {
            // Try finding from attachment
            $atts = get_posts(array('post_type'=>'attachment','meta_query'=>array(array('key'=>'ds_marketplace_download_id','value'=>$post->ID,'compare'=>'=')),'posts_per_page'=>1,'fields'=>'ids'));
            if (!empty($atts)) {
                $cashback  = (int)get_post_meta($atts[0],'ds_marketplace_cashback',true);
                $submitter = (int)get_post_meta($atts[0],'ds_marketplace_submitter',true);
            }
        }
        $user = $submitter ? get_userdata($submitter) : null;
        echo '<p><strong>Status:</strong> ' . esc_html($status ?: 'pending') . '</p>';
        echo '<p><strong>Submitter:</strong> ' . ($user ? esc_html($user->display_name . ' (' . $user->user_email . ')') : 'Unknown') . '</p>';
        echo '<p><strong>Cashback:</strong> ' . $cashback . ' credits</p>';
        if ($paid) {
            echo '<p style="color:green;font-weight:700;">✓ Already approved and paid out</p>';
        } else {
            echo '<button type="button" id="ds-approve-btn" style="background:#10b981;color:#fff;padding:10px 20px;border:none;border-radius:6px;font-size:14px;font-weight:700;cursor:pointer;">
                ✓ Approve + Pay ' . $cashback . ' Credits to ' . ($user ? esc_html($user->display_name) : 'User') . '
            </button>';
            echo '<p id="ds-approve-msg" style="margin-top:8px;font-weight:600;"></p>';
            echo '<script>
            document.getElementById("ds-approve-btn").onclick = function() {
                var btn = this;
                btn.textContent = "Processing..."; btn.disabled = true;
                fetch("' . esc_js($rest) . 'approve-marketplace", {
                    method: "POST",
                    headers: {"Content-Type":"application/json","X-WP-Nonce":"' . esc_js($nonce) . '"},
                    body: JSON.stringify({post_id: ' . $post->ID . '})
                })
                .then(function(r){return r.json();})
                .then(function(d){
                    var msg = document.getElementById("ds-approve-msg");
                    if(d.success){
                        btn.textContent = "✓ Approved";
                        btn.style.background = "#6b7280";
                        msg.style.color = "green";
                        msg.textContent = d.credits_added + " credits added to " + d.user + ". New balance: " + d.new_balance + " credits.";
                        setTimeout(function(){location.reload();}, 2000);
                    } else {
                        btn.textContent = "Approve + Pay Credits";
                        btn.disabled = false;
                        msg.style.color = "red";
                        msg.textContent = "Error: " + (d.error || "Unknown error");
                    }
                })
                .catch(function(){ btn.disabled=false; btn.textContent="Approve + Pay Credits"; });
            };
            </script>';
        }
    }, 'dlm_download', 'side', 'high');
});
