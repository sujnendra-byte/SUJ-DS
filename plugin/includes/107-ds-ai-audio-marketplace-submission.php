<?php
/**
 * Code Snippets export — DS AI Audio Marketplace Submission
 *
 * Snippet ID : 788
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-102
 * Modified   : 2026-09-09 02:46:40
 * Load order : 107 of 144
 */
/**
* Snippet Name: DS AI Audio Marketplace Submission
* Run: Everywhere
*
* Backend-only. Mirrors the existing DS AI Video Marketplace Submission
* (R2 Fix) and DS AI Video (creator-from-image) snippets: registers a new
* REST route that turns a generated audio file into a pending Creator
* Uploads listing. Audio never has a legacy local WP attachment (unlike
* video/images it is served purely through the shared ds-hub R2 file
* store), so this always takes the materialize-from-hub path.
*
* New route: POST /ds/v1/creator-from-audio
* Reuses AS-IS, unmodified: ds_hub_materialize_attachment() (DS Storage
* and API Settings hub) and ds_maybe_create_dlm_category() if present.
*/

if ( ! defined('ABSPATH') ) exit;

add_action('rest_api_init', function() {
    register_rest_route( 'ds/v1', '/creator-from-audio', [
        'methods'             => 'POST',
        'callback'            => 'dsaa_create_upload_from_audio',
        'permission_callback' => function(){ return is_user_logged_in(); },
    ] );
});

function dsaa_create_upload_from_audio( $request ) {
    $user_id       = get_current_user_id();
    $attachment_id = (int) $request->get_param( 'attachment_id' );
    $hub_file_id   = (int) $request->get_param( 'file_id' );

    // Consent is required before a file can be listed on the Marketplace.
    $consent = (bool) $request->get_param( 'consent' );
    if ( ! $consent ) {
        return new WP_Error( 'consent_required', 'You must confirm the rights checkbox before submitting.', [ 'status' => 400 ] );
    }

    // Audio is always hub/R2-backed -- materialize a real WP attachment
    // just-in-time so the listing-creation logic below (identical shape
    // to the image/video flows) can work unchanged. Ownership is verified
    // by ds_hub_materialize_attachment() itself.
    if ( ! $attachment_id && $hub_file_id && function_exists( 'ds_hub_materialize_attachment' ) ) {
        $materialized = ds_hub_materialize_attachment( $hub_file_id, $user_id, array() );
        if ( is_wp_error( $materialized ) ) {
            return new WP_Error( 'materialize_failed', $materialized->get_error_message(), [ 'status' => 400 ] );
        }
        $attachment_id = $materialized;
    }

    if ( ! $attachment_id ) {
        return new WP_Error( 'no_attachment', 'No audio provided.', [ 'status' => 400 ] );
    }

    // Prevent duplicate submissions (same check as images/video)
    $already_submitted = get_post_meta( $attachment_id, 'ds_creator_upload_id', true );
    if ( $already_submitted && get_post( $already_submitted ) ) {
        return new WP_Error( 'already_submitted', 'This audio has already been submitted to Creator Uploads.', [ 'status' => 400 ] );
    }

    $attachment = get_post( $attachment_id );
    if ( ! $attachment || $attachment->post_type !== 'attachment' ) {
        return new WP_Error( 'invalid_attachment', 'Invalid audio.', [ 'status' => 400 ] );
    }
    if ( strpos( (string) get_post_mime_type( $attachment_id ), 'audio/' ) !== 0 ) {
        return new WP_Error( 'not_audio', 'That attachment is not an audio file.', [ 'status' => 400 ] );
    }

    $prompt   = (string) get_post_meta( $attachment_id, 'ds_ai_prompt', true );
    $model    = (string) get_post_meta( $attachment_id, 'ds_ai_model',  true );

    if ( $model === 'speech-02-hd' ) {
        return new WP_Error( 'speech_not_allowed', 'Text to Speech files cannot be submitted to the Marketplace.', [ 'status' => 400 ] );
    }
    $title    = $prompt ? wp_trim_words( $prompt, 12, '...' ) : 'AI Generated Audio';
    $file_url = wp_get_attachment_url( $attachment_id );

    // Create the ds_creator_upload post -- identical shape to the image/video flow
    $post_id = wp_insert_post( [
        'post_type'   => 'ds_creator_upload',
        'post_title'  => $title,
        'post_status' => 'publish',
        'post_author' => $user_id,
    ] );

    if ( is_wp_error( $post_id ) ) {
        return new WP_Error( 'insert_failed', 'Could not create upload.', [ 'status' => 500 ] );
    }

    set_post_thumbnail( $post_id, $attachment_id );

    update_post_meta( $post_id, 'ds_creator_status',            'pending' );
    update_post_meta( $post_id, 'ds_creator_upload_source',     'ai_generated' );
    update_post_meta( $post_id, 'ds_creator_thumbnail_id',      $attachment_id );
    update_post_meta( $post_id, 'ds_creator_file_id',           $attachment_id );
    update_post_meta( $post_id, 'ds_creator_file_name',         basename( $file_url ) );
    update_post_meta( $post_id, 'ds_creator_file_size',         0 );
    update_post_meta( $post_id, 'ds_creator_category',          'AI Audio' );
    update_post_meta( $post_id, 'ds_creator_short_description', $prompt ? substr( $prompt, 0, 150 ) : '' );
    update_post_meta( $post_id, 'ds_creator_full_description',  $prompt ?: '' );
    update_post_meta( $post_id, 'ds_creator_keywords',          wp_json_encode( $model ? [ $model ] : [] ) );
    update_post_meta( $post_id, 'ds_creator_tags',              wp_json_encode( [] ) );
    update_post_meta( $post_id, 'ds_creator_total_earnings',    0 );
    update_post_meta( $post_id, 'ds_creator_download_count',    0 );
    update_post_meta( $post_id, 'ds_creator_total_revenue',     0 );
    update_post_meta( $post_id, 'ds_creator_edit_history',      '[]' );
    update_post_meta( $post_id, 'ds_creator_cashback_amount', 1 );

    if ( function_exists( 'ds_maybe_create_dlm_category' ) ) {
        ds_maybe_create_dlm_category( 'AI Audio' );
    }

    // Mark the attachment as submitted to creator portal
    update_post_meta( $attachment_id, 'ds_creator_upload_id', $post_id );

    return rest_ensure_response( [
        'success'          => true,
        'pending_cashback' => 1,
        'upload_id'        => $post_id,
        'attachment_id'    => $attachment_id,
        'message'          => 'Submitted to Creator Uploads for review.',
    ] );
}
