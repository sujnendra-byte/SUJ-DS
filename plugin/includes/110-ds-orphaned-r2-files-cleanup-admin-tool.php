<?php
/**
 * Code Snippets export — DS Orphaned R2 Files Cleanup (Admin Tool)
 *
 * Snippet ID : 791
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-105
 * Modified   : 2026-09-09 02:45:24
 * Load order : 110 of 144
 */
/**
 * DS Orphaned R2 Files Cleanup (Admin Tool)
 * Admin-only (Tools -> DS R2 Orphan Cleanup). Scans the currently-configured
 * R2 bucket and compares every object key against every r2_key / thumb_r2_key
 * / disp_r2_key referenced anywhere in wp_ds_generated_files (ANY status --
 * saved, marketplace-linked, temporary, expired, or already-deleted rows are
 * all counted as "referenced", for safety). Any bucket object NOT referenced
 * by that table, and not already under the trash/ prefix, is treated as an
 * orphan. Orphans are backed up to trash/ (same pattern as the other cleanup
 * tool) before the original is removed -- nothing in trash/ is auto-deleted.
 * Candidate list is cached in a transient between preview and batch calls so
 * repeated bucket listings aren't needed on every batch.
 */

add_action('admin_menu', function() {
add_management_page('DS R2 Orphan Cleanup', 'DS R2 Orphan Cleanup', 'manage_options', 'ds-orphan-cleanup', 'ds_orphan_render_page');
});

function ds_orphan_render_page() {
if (!current_user_can('administrator')) wp_die('Not allowed.');
$nonce = wp_create_nonce('wp_rest');
$rest = esc_url_raw(rest_url('ds/v1/'));
?>
<div class="wrap">
<h1>DS R2 Orphan Cleanup</h1>
<p>Scans your R2 bucket for files that have no matching record in the site's database -- leftovers from a storage migration, a failed upload, etc. Backs up each one to a <code>trash/</code> folder in the same bucket before removing it -- nothing in <code>trash/</code> is auto-deleted. Anything referenced by an image, video, audio, or presentation record -- saved or not, marketplace or not, even already-marked-deleted -- is left alone.</p>
<div id="ds-orphan-preview">Scanning bucket, this can take a moment...</div>
<div id="ds-orphan-sample"></div>
<p><button id="ds-orphan-start" class="button button-primary" disabled>Start Cleanup</button> <button id="ds-orphan-rescan" class="button">Re-scan</button></p>
<div id="ds-orphan-progress"></div>
</div>
<script>
(function(){
var NONCE = '<?php echo esc_js($nonce); ?>';
var REST = '<?php echo esc_js($rest); ?>';
function api(path, method) {
return fetch(REST + path, {method: method || 'GET', headers: {'X-WP-Nonce': NONCE, 'Content-Type': 'application/json'}, credentials: 'include'}).then(function(r){ return r.json(); });
}
var previewEl = document.getElementById('ds-orphan-preview');
var sampleEl = document.getElementById('ds-orphan-sample');
var startBtn = document.getElementById('ds-orphan-start');
var rescanBtn = document.getElementById('ds-orphan-rescan');
var progEl = document.getElementById('ds-orphan-progress');

function loadPreview() {
previewEl.textContent = 'Scanning bucket, this can take a moment...';
sampleEl.innerHTML = '';
startBtn.disabled = true;
api('admin/orphan-preview').then(function(d){
if (!d || !d.success) { previewEl.textContent = 'Could not load preview: ' + (d && d.message ? d.message : 'unknown error'); return; }
previewEl.innerHTML = '<p><strong>' + d.count + ' orphaned files, ' + d.mb + ' MB</strong> found with no database record (scanned ' + d.scanned_total + ' bucket objects against ' + d.referenced_total + ' linked files).</p>';
startBtn.disabled = (d.count === 0);
if (d.count === 0) { previewEl.innerHTML += '<p>Nothing to clean up.</p>'; return; }
if (d.sample && d.sample.length) {
var html = '<p>Sample of files that would move to trash/ (first ' + d.sample.length + '):</p><ul>';
d.sample.forEach(function(s){ html += '<li>' + s.key + ' -- ' + s.mb + ' MB</li>'; });
html += '</ul>';
sampleEl.innerHTML = html;
}
});
}
loadPreview();
rescanBtn.addEventListener('click', loadPreview);

var totalProcessed = 0, totalFreedMb = 0, totalErrors = 0, stallCount = 0;
function runBatch() {
var line = document.createElement('div');
line.textContent = 'Processing next batch...';
progEl.appendChild(line);
api('admin/orphan-batch', 'POST').then(function(d){
if (!d || !d.success) {
line.textContent = 'Batch failed: ' + (d && d.message ? d.message : 'unknown error');
line.style.color = 'red';
return;
}
totalProcessed += d.succeeded;
totalErrors += d.failed;
totalFreedMb += d.freed_mb;
line.textContent = 'Batch done: ' + d.succeeded + ' moved to trash, ' + d.failed + ' failed. Running total: ' + totalProcessed + ' files, ' + totalFreedMb.toFixed(1) + ' MB.';
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
done.innerHTML = '<strong>Done. ' + totalProcessed + ' files moved to trash/, ' + totalErrors + ' failed, ' + totalFreedMb.toFixed(1) + ' MB.</strong>';
progEl.appendChild(done);
startBtn.textContent = 'Cleanup complete';
}
});
}
startBtn.addEventListener('click', function(){
startBtn.disabled = true;
progEl.innerHTML = '';
totalProcessed = 0; totalFreedMb = 0; totalErrors = 0; stallCount = 0;
runBatch();
});
})();
</script>
<?php
}

if (!function_exists('ds_orphan_get_referenced_keys')) {
function ds_orphan_get_referenced_keys() {
global $wpdb;
$table = $wpdb->prefix . 'ds_generated_files';
$keys = array();
$rows = $wpdb->get_results("SELECT r2_key, meta FROM $table");
foreach ($rows as $r) {
if (!empty($r->r2_key)) $keys[$r->r2_key] = true;
$m = json_decode((string)$r->meta, true);
if (is_array($m)) {
if (!empty($m['thumb_r2_key'])) $keys[$m['thumb_r2_key']] = true;
if (!empty($m['disp_r2_key'])) $keys[$m['disp_r2_key']] = true;
}
}
return $keys;
}
}

if (!function_exists('ds_orphan_list_bucket')) {
function ds_orphan_list_bucket($client, $maxPages = 50) {
$objects = array();
$token = '';
$pages = 0;
do {
$resp = $client->list_objects_page('', 1000, $token);
if (is_wp_error($resp)) { return new WP_Error('ds_orphan_list_failed', $resp->get_error_message()); }
$code = wp_remote_retrieve_response_code($resp);
if ($code >= 300) { return new WP_Error('ds_orphan_list_http', 'R2 list request failed with HTTP ' . $code); }
$body = wp_remote_retrieve_body($resp);
libxml_use_internal_errors(true);
$xml = simplexml_load_string($body);
if ($xml === false) { return new WP_Error('ds_orphan_xml', 'Could not parse bucket listing response.'); }
if (isset($xml->Contents)) {
foreach ($xml->Contents as $c) {
$key = (string) $c->Key;
if (strpos($key, 'trash/') === 0) continue;
$objects[] = array('key' => $key, 'size' => (int) $c->Size);
}
}
$truncated = isset($xml->IsTruncated) && (string)$xml->IsTruncated === 'true';
$token = ($truncated && isset($xml->NextContinuationToken)) ? (string) $xml->NextContinuationToken : '';
$pages++;
} while ($truncated && $token !== '' && $pages < $maxPages);
return $objects;
}
}

if (!function_exists('ds_orphan_find_candidates')) {
function ds_orphan_find_candidates() {
$client = ds_hub_get_r2_client();
if (is_wp_error($client)) return $client;
$objects = ds_orphan_list_bucket($client);
if (is_wp_error($objects)) return $objects;
$referenced = ds_orphan_get_referenced_keys();
$orphans = array();
foreach ($objects as $o) {
if (!isset($referenced[$o['key']])) $orphans[] = $o;
}
return array($orphans, count($objects), count($referenced));
}
}

if (!function_exists('ds_orphan_get_or_scan')) {
function ds_orphan_get_or_scan($forceRescan = false) {
$transientKey = 'ds_orphan_candidates_v1';
if (!$forceRescan) {
$cached = get_transient($transientKey);
if (is_array($cached)) return $cached;
}
$result = ds_orphan_find_candidates();
if (is_wp_error($result)) return $result;
list($orphans, $scannedTotal, $referencedTotal) = $result;
$data = array('orphans' => $orphans, 'scanned_total' => $scannedTotal, 'referenced_total' => $referencedTotal);
set_transient($transientKey, $data, 2 * HOUR_IN_SECONDS);
return $data;
}
}

add_action('rest_api_init', function() {

register_rest_route('ds/v1', '/admin/orphan-preview', array(
'methods' => 'GET',
'permission_callback' => function() { return current_user_can('administrator'); },
'callback' => function() {
@set_time_limit(90);
$data = ds_orphan_get_or_scan(true);
if (is_wp_error($data)) {
return rest_ensure_response(array('success' => false, 'message' => $data->get_error_message()));
}
$orphans = $data['orphans'];
$totalBytes = 0;
foreach ($orphans as $o) $totalBytes += $o['size'];
$sample = array_slice($orphans, 0, 50);
$sampleOut = array();
foreach ($sample as $s) $sampleOut[] = array('key' => $s['key'], 'mb' => round($s['size'] / 1048576, 2));
return rest_ensure_response(array(
'success' => true,
'count' => count($orphans),
'mb' => round($totalBytes / 1048576, 1),
'scanned_total' => $data['scanned_total'],
'referenced_total' => $data['referenced_total'],
'sample' => $sampleOut,
));
},
));

register_rest_route('ds/v1', '/admin/orphan-batch', array(
'methods' => 'POST',
'permission_callback' => function() { return current_user_can('administrator'); },
'callback' => function() {
@set_time_limit(120);
$transientKey = 'ds_orphan_candidates_v1';
$data = ds_orphan_get_or_scan(false);
if (is_wp_error($data)) {
return rest_ensure_response(array('success' => false, 'message' => $data->get_error_message()));
}
$orphans = $data['orphans'];

if (!count($orphans)) {
return rest_ensure_response(array('success' => true, 'succeeded' => 0, 'failed' => 0, 'freed_mb' => 0, 'remaining' => 0));
}

$batch = array_slice($orphans, 0, 5);
$client = ds_hub_get_r2_client();
$succeeded = 0; $failed = 0; $freedBytes = 0;

foreach ($batch as $o) {
if (is_wp_error($client)) { $failed++; continue; }
$key = $o['key'];

$getUrl = $client->presign_get_url($key, 120);
if (is_wp_error($getUrl)) { $failed++; continue; }
$resp = wp_remote_get($getUrl, array('timeout' => 90));
if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) >= 300) { $failed++; continue; }
$body = wp_remote_retrieve_body($resp);
$ctype = wp_remote_retrieve_header($resp, 'content-type');
$put = $client->put_object('trash/' . $key, $body, $ctype ? $ctype : 'application/octet-stream');
unset($body, $resp);
if (is_wp_error($put)) { $failed++; continue; }

$del = $client->delete_object($key);
if (is_wp_error($del)) { $failed++; continue; }

$succeeded++;
$freedBytes += (int) $o['size'];
}

$attempted = array();
foreach ($batch as $o) $attempted[$o['key']] = true;
$remainingOrphans = array();
foreach ($orphans as $o) { if (!isset($attempted[$o['key']])) $remainingOrphans[] = $o; }
$data['orphans'] = $remainingOrphans;
set_transient($transientKey, $data, 2 * HOUR_IN_SECONDS);

return rest_ensure_response(array(
'success' => true,
'succeeded' => $succeeded,
'failed' => $failed,
'freed_mb' => round($freedBytes / 1048576, 1),
'remaining' => count($remainingOrphans),
));
},
));

});
