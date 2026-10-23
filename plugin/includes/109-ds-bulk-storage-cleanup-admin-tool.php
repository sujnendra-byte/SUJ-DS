<?php
/**
 * Code Snippets export — DS Bulk Storage Cleanup (Admin Tool)
 *
 * Snippet ID : 790
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-104
 * Modified   : 2026-09-09 02:40:16
 * Load order : 109 of 144
 */
/**
 * DS Bulk Storage Cleanup (Admin Tool)
 * Admin-only (Tools -> DS Storage Cleanup). For every unsaved, non-marketplace
 * generated image/video/audio file: copies it (and its thumb/display WebP
 * variants, if any) to a trash/ prefix in the SAME R2 bucket first, then
 * deletes the original via the site's own ds_hub_delete_generated_file_now()
 * (the same function the "Delete Now" button and the retention cron use --
 * it already refuses to touch status='saved' rows). Marketplace-submitted
 * or accepted files are excluded via their file_id showing up with
 * submitted/attachment_id set in ds_ai_generations / ds_ai_video_generations
 * / ds_ai_audio_generations. Runs in small batches to avoid timeouts; a row
 * already processed is marked with meta.trashed_by_cleanup so re-running is
 * always safe. Nothing in the trash/ prefix is auto-deleted.
 *
 * Separately: a one-click trim of stale ds_ppt_jobs history entries (job ids
 * that never became a saved draft in ds_ppt_drafts). ds_ppt_generations is
 * left untouched -- its entries have no id that reliably maps to a draft.
 */

add_action('admin_menu', function() {
add_management_page('DS Storage Cleanup', 'DS Storage Cleanup', 'manage_options', 'ds-storage-cleanup', 'ds_cleanup_render_page');
});

function ds_cleanup_render_page() {
if (!current_user_can('administrator')) wp_die('Not allowed.');
$nonce = wp_create_nonce('wp_rest');
$rest = esc_url_raw(rest_url('ds/v1/'));
?>
<div class="wrap">
<h1>DS Storage Cleanup</h1>
<p>Backs up (to a <code>trash/</code> folder in the same storage) then removes unsaved, non-marketplace generated images/videos/audio. Saved items and marketplace submissions are never touched. Nothing in <code>trash/</code> is auto-deleted -- clear it yourself later once you're confident.</p>
<div id="ds-cleanup-preview">Loading preview...</div>
<p><button id="ds-cleanup-start" class="button button-primary" disabled>Start Cleanup</button></p>
<div id="ds-cleanup-progress"></div>
<hr>
<h2>Presentation history log trim</h2>
<p>Removes old generation-history entries (ds_ppt_jobs) that were never saved as a draft. Tiny amount of data, no storage impact -- just tidiness. Saved Drafts are never touched.</p>
<button id="ds-cleanup-ppt" class="button">Trim presentation logs</button>
<div id="ds-cleanup-ppt-result"></div>
</div>
<script>
(function(){
var NONCE = '<?php echo esc_js($nonce); ?>';
var REST = '<?php echo esc_js($rest); ?>';
function api(path, method) {
return fetch(REST + path, {method: method || 'GET', headers: {'X-WP-Nonce': NONCE, 'Content-Type': 'application/json'}, credentials: 'include'}).then(function(r){ return r.json(); });
}
var previewEl = document.getElementById('ds-cleanup-preview');
var startBtn = document.getElementById('ds-cleanup-start');
var progEl = document.getElementById('ds-cleanup-progress');

function loadPreview() {
api('admin/cleanup-preview').then(function(d){
if (!d || !d.success) { previewEl.textContent = 'Could not load preview.'; return; }
var html = '<ul>';
d.types.forEach(function(t){ html += '<li>' + t.type + ': ' + t.deletable + ' files, ' + t.mb + ' MB</li>'; });
html += '</ul><p><strong>Total: ' + d.total_count + ' files, ' + d.total_mb + ' MB</strong> (' + d.protected_count + ' marketplace-linked files across the site will be kept)</p>';
previewEl.innerHTML = html;
startBtn.disabled = (d.total_count === 0);
if (d.total_count === 0) previewEl.innerHTML += '<p>Nothing left to clean up.</p>';
});
}
loadPreview();

var totalProcessed = 0, totalFreedMb = 0, totalErrors = 0, stallCount = 0;
function runBatch() {
var line = document.createElement('div');
line.textContent = 'Processing next batch...';
progEl.appendChild(line);
api('admin/cleanup-batch', 'POST').then(function(d){
if (!d || !d.success) {
line.textContent = 'Batch failed: ' + (d && d.message ? d.message : 'unknown error');
line.style.color = 'red';
return;
}
totalProcessed += d.succeeded;
totalErrors += d.failed;
totalFreedMb += d.freed_mb;
line.textContent = 'Batch done: ' + d.succeeded + ' backed up + removed, ' + d.failed + ' failed. Running total: ' + totalProcessed + ' files, ' + totalFreedMb.toFixed(1) + ' MB.';
if (d.succeeded === 0 && d.failed > 0) { stallCount++; } else { stallCount = 0; }
if (stallCount >= 3) {
var stalled = document.createElement('div');
stalled.innerHTML = '<strong style="color:red">Stopped: the last 3 batches all failed with 0 successes (' + d.remaining + ' files still remaining). Something needs a look before continuing.</strong>';
progEl.appendChild(stalled);
startBtn.textContent = 'Start Cleanup';
startBtn.disabled = false;
} else if (d.remaining > 0) {
setTimeout(runBatch, 400);
} else {
var done = document.createElement('div');
done.innerHTML = '<strong>Done. ' + totalProcessed + ' files processed, ' + totalErrors + ' failed, ' + totalFreedMb.toFixed(1) + ' MB moved to trash/.</strong>';
progEl.appendChild(done);
startBtn.textContent = 'Cleanup complete';
}
});
}
startBtn.addEventListener('click', function(){
startBtn.disabled = true;
progEl.innerHTML = '';
totalProcessed = 0; totalFreedMb = 0; totalErrors = 0;
runBatch();
});

document.getElementById('ds-cleanup-ppt').addEventListener('click', function(){
var btn = this;
btn.disabled = true;
api('admin/cleanup-ppt-logs', 'POST').then(function(d){
document.getElementById('ds-cleanup-ppt-result').textContent = (d && d.success) ? ('Trimmed ' + d.trimmed + ' stale log entries across ' + d.users + ' users.') : 'Failed.';
btn.disabled = false;
});
});
})();
</script>
<?php
}

if (!function_exists('ds_cleanup_protected_ids')) {
function ds_cleanup_protected_ids() {
global $wpdb;
$protected = array();
$metaKeys = array('ds_ai_generations', 'ds_ai_video_generations', 'ds_ai_audio_generations');
foreach ($metaKeys as $metaKey) {
$rows = $wpdb->get_results($wpdb->prepare("SELECT meta_value FROM {$wpdb->usermeta} WHERE meta_key=%s", $metaKey));
foreach ($rows as $row) {
$arr = maybe_unserialize($row->meta_value);
if (!is_array($arr)) continue;
foreach ($arr as $g) {
if (!is_array($g)) continue;
$fid = (int)($g['file_id'] ?? 0);
if (!$fid) continue;
if (!empty($g['submitted']) || !empty($g['attachment_id'])) $protected[$fid] = true;
}
}
}
return $protected;
}
}

if (!function_exists('ds_cleanup_candidates')) {
function ds_cleanup_candidates() {
global $wpdb;
$table = $wpdb->prefix . 'ds_generated_files';
$protected = ds_cleanup_protected_ids();
$rows = $wpdb->get_results("SELECT id, file_type, file_size, r2_key, meta, status FROM $table WHERE status IN ('temporary','deleted')");
$out = array();
foreach ($rows as $r) {
if (isset($protected[(int)$r->id])) continue;
$m = json_decode((string)$r->meta, true);
if (is_array($m) && !empty($m['trashed_by_cleanup'])) continue;
$out[] = $r;
}
return array($out, count($protected));
}
}

add_action('rest_api_init', function() {

register_rest_route('ds/v1', '/admin/cleanup-preview', array(
'methods' => 'GET',
'permission_callback' => function() { return current_user_can('administrator'); },
'callback' => function() {
list($candidates, $protectedCount) = ds_cleanup_candidates();
$stats = array();
foreach ($candidates as $r) {
if (!isset($stats[$r->file_type])) $stats[$r->file_type] = array('count' => 0, 'bytes' => 0);
$stats[$r->file_type]['count']++;
$stats[$r->file_type]['bytes'] += (int)$r->file_size;
}
$types = array();
$totalCount = 0; $totalBytes = 0;
foreach ($stats as $type => $s) {
$types[] = array('type' => $type, 'deletable' => $s['count'], 'mb' => round($s['bytes'] / 1048576, 1));
$totalCount += $s['count'];
$totalBytes += $s['bytes'];
}
return rest_ensure_response(array(
'success' => true,
'types' => $types,
'total_count' => $totalCount,
'total_mb' => round($totalBytes / 1048576, 1),
'protected_count' => $protectedCount,
));
},
));

register_rest_route('ds/v1', '/admin/cleanup-batch', array(
'methods' => 'POST',
'permission_callback' => function() { return current_user_can('administrator'); },
'callback' => function() {
global $wpdb;
@set_time_limit(120);
$table = $wpdb->prefix . 'ds_generated_files';
list($candidates, $protectedCount) = ds_cleanup_candidates();

if (!count($candidates)) {
return rest_ensure_response(array('success' => true, 'succeeded' => 0, 'failed' => 0, 'freed_mb' => 0, 'remaining' => 0));
}

$batch = array_slice($candidates, 0, 5);
$client = ds_hub_get_r2_client();
$succeeded = 0; $failed = 0; $freedBytes = 0;

foreach ($batch as $row) {
if (is_wp_error($client)) { $failed++; continue; }

$meta = json_decode((string)$row->meta, true);
if (!is_array($meta)) $meta = array();
$keys = array($row->r2_key);
if (!empty($meta['thumb_r2_key'])) $keys[] = $meta['thumb_r2_key'];
if (!empty($meta['disp_r2_key'])) $keys[] = $meta['disp_r2_key'];

if ($row->status === 'deleted') {
// Already removed from R2 by something else before this tool ever ran (e.g. the site's existing Delete Now button or retention cron) -- nothing left to back up, so just mark it handled and move on.
$meta['trashed_by_cleanup'] = 1;
$wpdb->update($table, array('meta' => wp_json_encode($meta)), array('id' => (int)$row->id));
$succeeded++;
continue;
}

$backupOk = true;
foreach ($keys as $k) {
if (empty($k)) continue;
$getUrl = $client->presign_get_url($k, 120);
if (is_wp_error($getUrl)) { if ($k === $row->r2_key) $backupOk = false; continue; }
$resp = wp_remote_get($getUrl, array('timeout' => 90));
if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) >= 300) { if ($k === $row->r2_key) $backupOk = false; continue; }
$body = wp_remote_retrieve_body($resp);
$ctype = wp_remote_retrieve_header($resp, 'content-type');
$put = $client->put_object('trash/' . $k, $body, $ctype ? $ctype : 'application/octet-stream');
unset($body, $resp);
if (is_wp_error($put) && $k === $row->r2_key) $backupOk = false;
}

if (!$backupOk) {
$failed++;
$wpdb->update($table, array('delete_error' => 'cleanup backup failed', 'last_delete_attempt_at' => current_time('mysql', true)), array('id' => (int)$row->id));
continue;
}

$meta['trashed_by_cleanup'] = 1;
$wpdb->update($table, array('meta' => wp_json_encode($meta)), array('id' => (int)$row->id));

$del = ds_hub_delete_generated_file_now((int)$row->id);
if (is_wp_error($del)) { $failed++; continue; }

$succeeded++;
$freedBytes += (int)$row->file_size;
}

list($remainingCandidates) = ds_cleanup_candidates();

return rest_ensure_response(array(
'success' => true,
'succeeded' => $succeeded,
'failed' => $failed,
'freed_mb' => round($freedBytes / 1048576, 1),
'remaining' => count($remainingCandidates),
));
},
));

register_rest_route('ds/v1', '/admin/cleanup-ppt-logs', array(
'methods' => 'POST',
'permission_callback' => function() { return current_user_can('administrator'); },
'callback' => function() {
global $wpdb;
$userIds = $wpdb->get_col("SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'ds_ppt_jobs'");
$trimmed = 0; $usersTouched = 0;

foreach ($userIds as $uid) {
$drafts = get_user_meta($uid, 'ds_ppt_drafts', true);
$savedSet = is_array($drafts) ? array_flip(array_keys($drafts)) : array();

$jobs = get_user_meta($uid, 'ds_ppt_jobs', true);
if (!is_array($jobs)) continue;

$newJobs = array();
$anyTrim = false;
foreach ($jobs as $j) {
$jid = is_array($j) ? ($j['id'] ?? null) : null;
if ($jid !== null && isset($savedSet[$jid])) { $newJobs[] = $j; continue; }
$trimmed++;
$anyTrim = true;
}
if ($anyTrim) {
update_user_meta($uid, 'ds_ppt_jobs', $newJobs);
$usersTouched++;
}
}

return rest_ensure_response(array('success' => true, 'trimmed' => $trimmed, 'users' => $usersTouched));
},
));

});
