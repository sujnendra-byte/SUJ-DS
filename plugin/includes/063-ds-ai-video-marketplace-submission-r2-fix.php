<?php
/**
 * Code Snippets export — DS AI Video Marketplace Submission (R2 Fix)
 *
 * Snippet ID : 749
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-063
 * Modified   : 2026-09-09 02:42:49
 * Load order : 063 of 144
 */
/**
 * Snippet Name: DS AI Video Marketplace Submission
 * Run: Everywhere
 *
 * Adds "Submit to Marketplace" for AI-generated videos, mirroring the
 * existing image flow (ds_create_upload_from_image / Snippet 1 backend)
 * exactly:
 *   - Creates a pending ds_creator_upload post that POINTS at the video's
 *     existing attachment (no copy yet, same as images).
 *   - Marks it 'ai_generated' so, on admin approval, the existing DLM
 *     conversion code (Snippet 4) automatically COPIES the file into an
 *     independent attachment for the marketplace listing — same
 *     protective behavior images already get, so a user deleting their
 *     own "My Creations" video later can't break a live listing.
 *   - Stores ds_creator_cashback_amount = 3 on the post, which the small
 *     patch to Snippet 3 (given separately) reads instead of the
 *     hardcoded "1" — so video pays out 3 credits on approval, images
 *     keep paying 1.
 *
 * Reuses AS-IS, unmodified: /ds/v1/mark-submitted (generic — already
 * keys everything off attachment_id, not content type).
 *
 * New route: POST /ds/v1/creator-from-video
 */

if ( ! defined('ABSPATH') ) exit;

add_action('rest_api_init', function() {
    register_rest_route( 'ds/v1', '/creator-from-video', [
        'methods'             => 'POST',
        'callback'            => 'ds_create_upload_from_video',
        'permission_callback' => function(){ return is_user_logged_in(); },
    ] );
});

function ds_create_upload_from_video( $request ) {
    $user_id       = get_current_user_id();
    $attachment_id = (int) $request->get_param( 'attachment_id' );
    $hub_file_id = (int) $request->get_param( 'file_id' );
    
    // R2-stored videos (via the Storage & Retention system) don't have a
    // real WP attachment until now -- materialize one just-in-time so every
    // line below this point (which all expects a genuine attachment_id)
    // keeps working completely unchanged, whether the source video came
    // from local disk (legacy) or R2 (current). Ownership is verified by
    // ds_hub_materialize_attachment() itself (the file row's user_id must
    // match the current user), so this path does not need the legacy
    // $gens-based ownership check further below.
    $via_r2 = false;
    if ( ! $attachment_id && $hub_file_id && function_exists( 'ds_hub_materialize_attachment' ) ) {
    	$gens_lookup = get_user_meta( $user_id, 'ds_ai_video_generations', true ) ?: array();
    	$extra_meta = array();
    	foreach ( $gens_lookup as $gl ) {
    		if ( is_array( $gl ) && (int) ( $gl['file_id'] ?? 0 ) === $hub_file_id ) {
    			$extra_meta = array( 'prompt' => $gl['prompt'] ?? '', 'model' => $gl['model'] ?? '' );
    			break;
    		}
    	}
    	$materialized = ds_hub_materialize_attachment( $hub_file_id, $user_id, $extra_meta );
    	if ( is_wp_error( $materialized ) ) {
    		return new WP_Error( 'materialize_failed', $materialized->get_error_message(), [ 'status' => 400 ] );
    	}
    	$attachment_id = $materialized;
    	$via_r2 = true;
    }
    

    if ( ! $attachment_id ) {
        return new WP_Error( 'no_attachment', 'No video provided.', [ 'status' => 400 ] );
    }

    // Prevent duplicate submissions (same check as images)
    $already_submitted = get_post_meta( $attachment_id, 'ds_creator_upload_id', true );
    if ( $already_submitted && get_post( $already_submitted ) ) {
        return new WP_Error( 'already_submitted', 'This video has already been submitted to Creator Uploads.', [ 'status' => 400 ] );
    }

    // Get attachment info — must be a video attachment
    $attachment = get_post( $attachment_id );
    if ( ! $attachment || $attachment->post_type !== 'attachment' ) {
        return new WP_Error( 'invalid_attachment', 'Invalid video.', [ 'status' => 400 ] );
    }
    if ( strpos( (string) get_post_mime_type( $attachment_id ), 'video/' ) !== 0 ) {
        return new WP_Error( 'not_a_video', 'That attachment is not a video.', [ 'status' => 400 ] );
    }

    // Only let a user submit their own generated video
    if ( ! $via_r2 ) {
	    $owns_it = false;
	    $gens = get_user_meta( $user_id, 'ds_ai_video_generations', true ) ?: [];
	    foreach ( $gens as $g ) {
	        if ( (int) ( $g['attachment_id'] ?? 0 ) === $attachment_id ) { $owns_it = true; break; }
	    }
	    if ( ! $owns_it ) {
	        return new WP_Error( 'not_yours', 'You can only submit your own generated videos.', [ 'status' => 403 ] );
	    }
    }

    $prompt   = (string) get_post_meta( $attachment_id, 'ds_ai_prompt', true );
    $model    = (string) get_post_meta( $attachment_id, 'ds_ai_model',  true );
    $title    = $prompt ? wp_trim_words( $prompt, 12, '...' ) : 'AI Generated Video';
    $file_url = wp_get_attachment_url( $attachment_id );

    // Create the ds_creator_upload post — identical shape to the image flow
    $post_id = wp_insert_post( [
        'post_type'   => 'ds_creator_upload',
        'post_title'  => $title,
        'post_status' => 'publish',
        'post_author' => $user_id,
    ] );

    if ( is_wp_error( $post_id ) ) {
        return new WP_Error( 'insert_failed', 'Could not create upload.', [ 'status' => 500 ] );
    }

    // Video attachments don't get an automatic image thumbnail like photos
    // do — set_post_thumbnail still works (WP allows it on any attachment),
    // it just won't render a preview image in admin lists/screens that
    // expect a real image. The "Design File" link still works either way.
    set_post_thumbnail( $post_id, $attachment_id );

    update_post_meta( $post_id, 'ds_creator_status',            'pending' );
    update_post_meta( $post_id, 'ds_creator_upload_source',     'ai_generated' ); // -> triggers copy-on-approval in Snippet 4
    update_post_meta( $post_id, 'ds_creator_thumbnail_id',      $attachment_id );
    update_post_meta( $post_id, 'ds_creator_file_id',           $attachment_id );
    update_post_meta( $post_id, 'ds_creator_file_name',         basename( $file_url ) );
    update_post_meta( $post_id, 'ds_creator_file_size',         0 );
    update_post_meta( $post_id, 'ds_creator_category',          'AI Videos' );
    update_post_meta( $post_id, 'ds_creator_short_description', $prompt ? substr( $prompt, 0, 150 ) : '' );
    update_post_meta( $post_id, 'ds_creator_full_description',  $prompt ?: '' );
    update_post_meta( $post_id, 'ds_creator_keywords',          wp_json_encode( $model ? [ $model ] : [] ) );
    update_post_meta( $post_id, 'ds_creator_tags',              wp_json_encode( [] ) );
    update_post_meta( $post_id, 'ds_creator_total_earnings',    0 );
    update_post_meta( $post_id, 'ds_creator_download_count',    0 );
    update_post_meta( $post_id, 'ds_creator_total_revenue',     0 );
    update_post_meta( $post_id, 'ds_creator_edit_history',      '[]' );

    // Video-specific: 3-credit cashback instead of the image default of 1.
    // Read by the small patch to Snippet 3's approval handler.
    update_post_meta( $post_id, 'ds_creator_cashback_amount', 3 );

    if ( function_exists( 'ds_maybe_create_dlm_category' ) ) {
        ds_maybe_create_dlm_category( 'AI Videos' );
    }

    // Mark the attachment as submitted to creator portal
    update_post_meta( $attachment_id, 'ds_creator_upload_id', $post_id );

    return rest_ensure_response( [
        'success'          => true,
        'pending_cashback' => 3,
        'upload_id'        => $post_id,
        'attachment_id'     => $attachment_id,
        'message'          => 'Submitted to Creator Uploads for review.',
    ] );
}
