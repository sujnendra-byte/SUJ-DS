<?php
/**
 * Code Snippets export — 23 ds ppt multi template combine [CLONE] [Credit History Log Fix]
 *
 * Snippet ID : 765
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : designstager, ds-order-079
 * Modified   : 2026-09-10 17:00:00
 * Load order : 082 of 144
 */
/**
 * Snippet 23: DS PPT Multi-Template Combine
 * Run: Everywhere
 * NEW SNIPPET — requires Snippet 21 (Smart Template Matcher) to be active,
 * since it reuses ds_tpl_url_to_path() from there. Does not edit Snippet
 * 21, 22, or any existing snippet.
 *
 * What this adds: a "📐 My Picked Designs" floating button (bottom-left,
 * visible whenever the editor is open) that lists every slide you've
 * applied a real template to via "Pick a Design", in deck order, with a
 * "Download Combined PPTX" button that merges all of them into ONE real,
 * valid .pptx — not just stitched text, but each slide's actual design
 * (colors, layout, theme) copied in, the same way PowerPoint's own
 * "Reuse Slides" feature works.
 *
 * SCOPE: this combines only the slides that have a picked real template.
 * Slides still using a coded layout (no "Pick a Design" applied) are NOT
 * included — that would mean merging this engine's output with the
 * separate coded-layout export pipeline (Snippet 16/12b), which is a
 * different, not-yet-built piece. If every slide in your deck has a
 * picked design, this combined file IS effectively your whole deck.
 */
if ( ! defined('ABSPATH') ) exit;

// ════════════════════════════════════════════════════════════════════════
// PART A — The merge engine (pure PHP/ZipArchive/DOMDocument)
// ════════════════════════════════════════════════════════════════════════

if ( ! function_exists('ds_tplm_resolve_rel_target') ) {
function ds_tplm_resolve_rel_target( $basePartPath, $target ) {
    if ( strpos($target, '/') === 0 ) return ltrim($target, '/');
    $baseDir = dirname($basePartPath);
    $combined = ($baseDir === '.' ? '' : $baseDir . '/') . $target;
    $parts = explode('/', $combined);
    $stack = array();
    foreach ( $parts as $p ) {
        if ( $p === '.' || $p === '' ) continue;
        if ( $p === '..' ) { array_pop($stack); continue; }
        $stack[] = $p;
    }
    return implode('/', $stack);
}
}

if ( ! function_exists('ds_tplm_rels_path_for') ) {
function ds_tplm_rels_path_for( $partPath ) {
    $dir = dirname($partPath);
    $base = basename($partPath);
    return ($dir === '.' ? '' : $dir . '/') . '_rels/' . $base . '.rels';
}
}

if ( ! function_exists('ds_tplm_load_xml') ) {
function ds_tplm_load_xml( $xmlString ) {
    if ( $xmlString === false || $xmlString === '' || $xmlString === null ) return null;
    $dom = new DOMDocument();
    $prev = libxml_use_internal_errors(true);
    $ok = $dom->loadXML($xmlString);
    libxml_use_internal_errors($prev);
    return $ok ? $dom : null;
}
}

if ( ! function_exists('ds_tplm_get_rels_of_type') ) {
function ds_tplm_get_rels_of_type( $zip, $partPath, $typeSuffix ) {
    $relsPath = ds_tplm_rels_path_for($partPath);
    $xml = $zip->getFromName($relsPath);
    if ( $xml === false ) return array();
    $dom = ds_tplm_load_xml($xml);
    if ( ! $dom ) return array();
    $out = array();
    foreach ( $dom->getElementsByTagName('Relationship') as $rel ) {
        $type = $rel->getAttribute('Type');
        if ( substr($type, -strlen($typeSuffix)) === $typeSuffix ) {
            $out[] = array(
                'id' => $rel->getAttribute('Id'),
                'target' => ds_tplm_resolve_rel_target($partPath, $rel->getAttribute('Target')),
                'targetMode' => $rel->getAttribute('TargetMode'),
            );
        }
    }
    return $out;
}
}

if ( ! function_exists('ds_tplm_resolve_first_slide_path') ) {
function ds_tplm_resolve_first_slide_path( $zip ) {
    $presXml = $zip->getFromName('ppt/presentation.xml');
    $relsXml = $zip->getFromName('ppt/_rels/presentation.xml.rels');
    if ( ! $presXml || ! $relsXml ) return null;
    $dom = ds_tplm_load_xml($presXml);
    if ( ! $dom ) return null;
    $sldIdNodes = $dom->getElementsByTagNameNS('http://schemas.openxmlformats.org/presentationml/2006/main', 'sldId');
    if ( ! $sldIdNodes->length ) return null;
    $firstRid = $sldIdNodes->item(0)->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');

    $relsDom = ds_tplm_load_xml($relsXml);
    if ( ! $relsDom ) return null;
    foreach ( $relsDom->getElementsByTagName('Relationship') as $rel ) {
        if ( $rel->getAttribute('Id') === $firstRid ) {
            return 'ppt/' . ltrim($rel->getAttribute('Target'), '/');
        }
    }
    return null;
}
}

if ( ! function_exists('ds_tplm_scan_max_numeric') ) {
function ds_tplm_scan_max_numeric( $zip, $dirPrefix, $baseName ) {
    $max = 0;
    for ( $i = 0; $i < $zip->numFiles; $i++ ) {
        $name = $zip->getNameIndex($i);
        if ( preg_match('#^' . preg_quote($dirPrefix, '#') . '/' . preg_quote($baseName, '#') . '(\d+)\.#', $name, $m) ) {
            $max = max($max, (int) $m[1]);
        }
    }
    return $max;
}
}

if ( ! function_exists('ds_tplm_scan_presentation_ids') ) {
function ds_tplm_scan_presentation_ids( $presDom ) {
    $maxSld = 255;
    $maxMaster = 2147483647;
    if ( $presDom ) {
        $P_NS = 'http://schemas.openxmlformats.org/presentationml/2006/main';
        foreach ( $presDom->getElementsByTagNameNS($P_NS, 'sldId') as $n ) {
            $maxSld = max($maxSld, (int) $n->getAttribute('id'));
        }
        foreach ( $presDom->getElementsByTagNameNS($P_NS, 'sldMasterId') as $n ) {
            $maxMaster = max($maxMaster, (int) $n->getAttribute('id'));
        }
    }
    return array($maxSld, $maxMaster);
}
}

if ( ! function_exists('ds_tplm_copy_part') ) {
function ds_tplm_copy_part( $dest, $src, $srcPartPath, $newPartDir, $newPartName, $renameTable ) {
    $content = $src->getFromName($srcPartPath);
    if ( $content === false ) return false;
    $dest->addFromString($newPartDir . '/' . $newPartName, $content);

    $relsPath = ds_tplm_rels_path_for($srcPartPath);
    $relsXml = $src->getFromName($relsPath);
    if ( $relsXml !== false ) {
        $dom = ds_tplm_load_xml($relsXml);
        if ( $dom ) {
            $toDrop = array();
            foreach ( $dom->getElementsByTagName('Relationship') as $rel ) {
                if ( $rel->getAttribute('TargetMode') === 'External' ) continue;
                $absTarget = ds_tplm_resolve_rel_target($srcPartPath, $rel->getAttribute('Target'));
                if ( isset($renameTable[$absTarget]) ) {
                    $rel->setAttribute('Target', $renameTable[$absTarget]);
                } else {
                    // Link types this engine doesn't graft over (speaker
                    // notes, comments, tags, embedded charts, legacy
                    // drawings, etc). Their target part never gets copied
                    // into the merged file, so left as-is this becomes a
                    // link pointing at nothing -- PowerPoint hard-fails on
                    // that, Google Slides/LibreOffice just ignore it. Safe
                    // to drop: nothing in the slide's own markup points at
                    // these by id.
                    $toDrop[] = $rel;
                }
            }
            foreach ( $toDrop as $rel ) {
                $rel->parentNode->removeChild($rel);
            }
            $newRelsPath = ds_tplm_rels_path_for($newPartDir . '/' . $newPartName);
            $dest->addFromString($newRelsPath, $dom->saveXML());
        }
    }
    return true;
}
}

if ( ! function_exists('ds_tplm_register_new_slide_and_master') ) {
function ds_tplm_register_new_slide_and_master( &$presDom, &$presRelsDom, $newSlideName, $newMasterName, &$maxSldId, &$maxMasterId ) {
    $P_NS = 'http://schemas.openxmlformats.org/presentationml/2006/main';
    $R_NS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

    $existingIds = array();
    foreach ( $presRelsDom->getElementsByTagName('Relationship') as $rel ) {
        if ( preg_match('/^rId(\d+)$/', $rel->getAttribute('Id'), $m) ) $existingIds[] = (int) $m[1];
    }
    $nextRid = (count($existingIds) ? max($existingIds) : 0) + 1;
    $root = $presRelsDom->documentElement;

    $relSlide = $presRelsDom->createElement('Relationship');
    $slideRid = 'rId' . $nextRid++;
    $relSlide->setAttribute('Id', $slideRid);
    $relSlide->setAttribute('Type', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide');
    $relSlide->setAttribute('Target', 'slides/' . $newSlideName);
    $root->appendChild($relSlide);

    $relMaster = $presRelsDom->createElement('Relationship');
    $masterRid = 'rId' . $nextRid++;
    $relMaster->setAttribute('Id', $masterRid);
    $relMaster->setAttribute('Type', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster');
    $relMaster->setAttribute('Target', 'slideMasters/' . $newMasterName);
    $root->appendChild($relMaster);

    $sldIdLst = $presDom->getElementsByTagNameNS($P_NS, 'sldIdLst')->item(0);
    $maxSldId++;
    $newSldId = $presDom->createElementNS($P_NS, 'p:sldId');
    $newSldId->setAttribute('id', (string) $maxSldId);
    $newSldId->setAttributeNS($R_NS, 'r:id', $slideRid);
    $sldIdLst->appendChild($newSldId);

    $masterLst = $presDom->getElementsByTagNameNS($P_NS, 'sldMasterIdLst')->item(0);
    $maxMasterId++;
    $newMasterIdNode = $presDom->createElementNS($P_NS, 'p:sldMasterId');
    $newMasterIdNode->setAttribute('id', (string) $maxMasterId);
    $newMasterIdNode->setAttributeNS($R_NS, 'r:id', $masterRid);
    $masterLst->appendChild($newMasterIdNode);
}
}

if ( ! function_exists('ds_tplm_ensure_content_type_override') ) {
function ds_tplm_ensure_content_type_override( &$ctDom, $partPath, $contentType ) {
    $root = $ctDom->documentElement;
    foreach ( $ctDom->getElementsByTagName('Override') as $o ) {
        if ( $o->getAttribute('PartName') === '/' . $partPath ) return;
    }
    $el = $ctDom->createElement('Override');
    $el->setAttribute('PartName', '/' . $partPath);
    $el->setAttribute('ContentType', $contentType);
    $root->appendChild($el);
}
}

if ( ! function_exists('ds_tplm_ensure_default_extension') ) {
function ds_tplm_ensure_default_extension( &$ctDom, $ext, $contentType ) {
    $root = $ctDom->documentElement;
    foreach ( $ctDom->getElementsByTagName('Default') as $d ) {
        if ( strtolower($d->getAttribute('Extension')) === strtolower($ext) ) return;
    }
    $el = $ctDom->createElement('Default');
    $el->setAttribute('Extension', $ext);
    $el->setAttribute('ContentType', $contentType);
    $root->appendChild($el);
}
}

if ( ! defined('DS_TPLM_CT_SLIDE') )  define('DS_TPLM_CT_SLIDE',  'application/vnd.openxmlformats-officedocument.presentationml.slide+xml');
if ( ! defined('DS_TPLM_CT_LAYOUT') ) define('DS_TPLM_CT_LAYOUT', 'application/vnd.openxmlformats-officedocument.presentationml.slideLayout+xml');
if ( ! defined('DS_TPLM_CT_MASTER') ) define('DS_TPLM_CT_MASTER', 'application/vnd.openxmlformats-officedocument.presentationml.slideMaster+xml');
if ( ! defined('DS_TPLM_CT_THEME') )  define('DS_TPLM_CT_THEME',  'application/vnd.openxmlformats-officedocument.theme+xml');

if ( ! function_exists('ds_tplm_media_content_type') ) {
function ds_tplm_media_content_type( $ext ) {
    $ext = strtolower($ext);
    $map = array('png' => 'image/png', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'gif' => 'image/gif', 'emf' => 'image/x-emf', 'svg' => 'image/svg+xml');
    return isset($map[$ext]) ? $map[$ext] : 'application/octet-stream';
}
}

if ( ! function_exists('ds_tplm_graft_one_slide') ) {
function ds_tplm_graft_one_slide( $dest, $src, &$counters, &$ctDom, &$presDom, &$presRelsDom, &$maxSldId, &$maxMasterId ) {
    $slidePath = ds_tplm_resolve_first_slide_path($src);
    if ( ! $slidePath ) return false;
    return ds_tplm_graft_one_slide_at_path($dest, $src, $slidePath, $counters, $ctDom, $presDom, $presRelsDom, $maxSldId, $maxMasterId);
}
}

if ( ! function_exists('ds_tplm_graft_one_slide_at_path') ) {
function ds_tplm_graft_one_slide_at_path( $dest, $src, $slidePath, &$counters, &$ctDom, &$presDom, &$presRelsDom, &$maxSldId, &$maxMasterId ) {

    $layoutRels = ds_tplm_get_rels_of_type($src, $slidePath, 'slideLayout');
    if ( empty($layoutRels) ) return false;
    $layoutPath = $layoutRels[0]['target'];

    $masterRels = ds_tplm_get_rels_of_type($src, $layoutPath, 'slideMaster');
    if ( empty($masterRels) ) return false;
    $masterPath = $masterRels[0]['target'];

    $themeRels = ds_tplm_get_rels_of_type($src, $masterPath, 'theme');
    $themePath = ! empty($themeRels) ? $themeRels[0]['target'] : null;

    $masterLayoutRels = ds_tplm_get_rels_of_type($src, $masterPath, 'slideLayout');
    $allLayoutPaths = array();
    foreach ( $masterLayoutRels as $lr ) $allLayoutPaths[$lr['target']] = true;
    $allLayoutPaths[$layoutPath] = true;

    $counters['slide']++;       $newSlideName  = 'slide' . $counters['slide'] . '.xml';
    $counters['slideMaster']++; $newMasterName = 'slideMaster' . $counters['slideMaster'] . '.xml';
    $newThemeName = null;
    if ( $themePath ) { $counters['theme']++; $newThemeName = 'theme' . $counters['theme'] . '.xml'; }

    $layoutNameMap = array();
    foreach ( array_keys($allLayoutPaths) as $lp ) {
        $counters['slideLayout']++;
        $layoutNameMap[$lp] = 'slideLayout' . $counters['slideLayout'] . '.xml';
    }

    $mediaNameMap = array();
    $partsToScan = array_merge(array($slidePath, $masterPath), array_keys($layoutNameMap));
    if ( $themePath ) $partsToScan[] = $themePath;
    foreach ( $partsToScan as $partPath ) {
        foreach ( ds_tplm_get_rels_of_type($src, $partPath, 'image') as $imgRel ) {
            if ( $imgRel['targetMode'] === 'External' ) continue;
            $srcMediaPath = $imgRel['target'];
            if ( isset($mediaNameMap[$srcMediaPath]) ) continue;
            $ext = pathinfo($srcMediaPath, PATHINFO_EXTENSION);
            $counters['media']++;
            $mediaNameMap[$srcMediaPath] = 'image' . $counters['media'] . '.' . $ext;
        }
    }

    $slideRename = array($layoutPath => '../slideLayouts/' . $layoutNameMap[$layoutPath]);
    foreach ( $mediaNameMap as $sp => $np ) $slideRename[$sp] = '../media/' . $np;
    ds_tplm_copy_part($dest, $src, $slidePath, 'ppt/slides', $newSlideName, $slideRename);
    ds_tplm_ensure_content_type_override($ctDom, 'ppt/slides/' . $newSlideName, DS_TPLM_CT_SLIDE);

    foreach ( $layoutNameMap as $lp => $newLayoutName ) {
        $layoutRename = array($masterPath => '../slideMasters/' . $newMasterName);
        foreach ( $mediaNameMap as $sp => $np ) $layoutRename[$sp] = '../media/' . $np;
        ds_tplm_copy_part($dest, $src, $lp, 'ppt/slideLayouts', $newLayoutName, $layoutRename);
        ds_tplm_ensure_content_type_override($ctDom, 'ppt/slideLayouts/' . $newLayoutName, DS_TPLM_CT_LAYOUT);
    }

    $masterRename = array();
    if ( $themePath ) $masterRename[$themePath] = '../theme/' . $newThemeName;
    foreach ( $layoutNameMap as $lp => $newLayoutName ) $masterRename[$lp] = '../slideLayouts/' . $newLayoutName;
    foreach ( $mediaNameMap as $sp => $np ) $masterRename[$sp] = '../media/' . $np;
    ds_tplm_copy_part($dest, $src, $masterPath, 'ppt/slideMasters', $newMasterName, $masterRename);
    ds_tplm_ensure_content_type_override($ctDom, 'ppt/slideMasters/' . $newMasterName, DS_TPLM_CT_MASTER);

    // Every source template was authored independently, so each one's master
    // numbers its own <p:sldLayoutId> starting from the same default value.
    // Once several get grafted into one deck, different masters end up
    // declaring the SAME internal layout id for different layouts.
    // PowerPoint's own file model requires these to be unique across the
    // whole presentation and refuses to open the result ("can't read") when
    // they collide -- Google Slides/LibreOffice/python-pptx never check
    // this, which is why only PowerPoint ever complains. Give this grafted
    // master's layout id(s) a fresh value derived from its own (already
    // unique) master counter so it can never collide with another master.
    $newMasterXml = $src->getFromName($masterPath);
    if ( $newMasterXml !== false ) {
        $mDom = ds_tplm_load_xml($newMasterXml);
        if ( $mDom ) {
            $uniqueBase = 2147483648 + 10000 + ($counters['slideMaster'] * 100);
            $offset = 0;
            foreach ( $mDom->getElementsByTagNameNS('http://schemas.openxmlformats.org/presentationml/2006/main', 'sldLayoutId') as $n ) {
                $n->setAttribute('id', (string) ($uniqueBase + $offset));
                $offset++;
            }
            $dest->addFromString('ppt/slideMasters/' . $newMasterName, $mDom->saveXML());
        }
    }

    if ( $themePath ) {
        $themeRename = array();
        foreach ( $mediaNameMap as $sp => $np ) $themeRename[$sp] = '../media/' . $np;
        ds_tplm_copy_part($dest, $src, $themePath, 'ppt/theme', $newThemeName, $themeRename);
        ds_tplm_ensure_content_type_override($ctDom, 'ppt/theme/' . $newThemeName, DS_TPLM_CT_THEME);
    }

    foreach ( $mediaNameMap as $srcMediaPath => $newMediaName ) {
        $content = $src->getFromName($srcMediaPath);
        if ( $content !== false ) {
            $dest->addFromString('ppt/media/' . $newMediaName, $content);
            $ext = pathinfo($newMediaName, PATHINFO_EXTENSION);
            ds_tplm_ensure_default_extension($ctDom, $ext, ds_tplm_media_content_type($ext));
        }
    }

    ds_tplm_register_new_slide_and_master($presDom, $presRelsDom, $newSlideName, $newMasterName, $maxSldId, $maxMasterId);

    return true;
}
}

if ( ! function_exists('ds_tplm_merge_pptx_files') ) {
/**
 * Merges N independent single-slide .pptx files into one multi-slide
 * .pptx, copying each one's own layout/master/theme/media so every slide
 * keeps its real design. Returns an array of booleans (one per input
 * file — true if it was grafted successfully) or false on total failure.
 */
	if ( ! function_exists('ds_tplm_prune_unreachable_parts') ) {
/**
 * Removing the seed's <p:extLst> (to fix the stale-section-list bug) can
 * orphan whatever that extension wired in — webextension task panes being
 * the one we've actually seen, but the same logic applies to anything
 * similar. Rather than special-case each possible orphan by name, this
 * does a real reachability sweep from the package root (the same thing
 * any OOXML reader effectively does) and drops anything not actually
 * connected to the document anymore, plus its own .rels file and its
 * Content_Types declaration.
 */
		if ( ! function_exists('ds_tplm_resolve_nth_slide_path') ) {
function ds_tplm_resolve_nth_slide_path( $zip, $n ) {
    $presXml = $zip->getFromName('ppt/presentation.xml');
    $relsXml = $zip->getFromName('ppt/_rels/presentation.xml.rels');
    if ( ! $presXml || ! $relsXml ) return null;
    $dom = ds_tplm_load_xml($presXml);
    if ( ! $dom ) return null;
    $sldIdNodes = $dom->getElementsByTagNameNS('http://schemas.openxmlformats.org/presentationml/2006/main', 'sldId');
    if ( $n >= $sldIdNodes->length ) return null;
    $targetRid = $sldIdNodes->item($n)->getAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/relationships', 'id');

    $relsDom = ds_tplm_load_xml($relsXml);
    if ( ! $relsDom ) return null;
    foreach ( $relsDom->getElementsByTagName('Relationship') as $rel ) {
        if ( $rel->getAttribute('Id') === $targetRid ) {
            return 'ppt/' . ltrim($rel->getAttribute('Target'), '/');
        }
    }
    return null;
}
}

if ( ! function_exists('ds_tplm_make_empty_skeleton') ) {
/**
 * A minimal, valid, zero-slide .pptx — just enough structure for
 * ds_tplm_graft_one_slide_at_path() to graft a single extracted slide
 * into. Used as the destination shell when splitting one multi-slide
 * coded build back into individual single-slide files.
 */
function ds_tplm_make_empty_skeleton( $destPath ) {
    if ( file_exists($destPath) ) @unlink($destPath);
    $zip = new ZipArchive();
    if ( $zip->open($destPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true ) return false;

    $zip->addFromString('[Content_Types].xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">' .
        '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>' .
        '<Default Extension="xml" ContentType="application/xml"/>' .
        '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>' .
        '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>' .
        '<Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>' .
        '</Types>');

    $zip->addFromString('_rels/.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' .
        '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/>' .
        '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>' .
        '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>' .
        '</Relationships>');

    $zip->addFromString('docProps/core.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"' .
        ' xmlns:dc="http://purl.org/dc/elements/1.1/"' .
        ' xmlns:dcterms="http://purl.org/dc/terms/"' .
        ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
        '<dc:title>Presentation</dc:title>' .
'<dc:creator>Designstager.com</dc:creator>' .
'<cp:lastModifiedBy>Designstager.com</cp:lastModifiedBy>' .
'<dcterms:created xsi:type="dcterms:W3CDTF">' . gmdate('Y-m-d\TH:i:s\Z') . '</dcterms:created>' .
        '<dcterms:modified xsi:type="dcterms:W3CDTF">' . gmdate('Y-m-d\TH:i:s\Z') . '</dcterms:modified>' .
        '</cp:coreProperties>');

    $zip->addFromString('docProps/app.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"' .
        ' xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">' .
        '<Application>Microsoft Office PowerPoint</Application>' .
        '<Slides>0</Slides>' .
        '</Properties>');

    $zip->addFromString('ppt/presentation.xml',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<p:presentation xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"' .
        ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"' .
        ' xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main">' .
        '<p:sldMasterIdLst/><p:sldIdLst/>' .
        '<p:sldSz cx="12192000" cy="6858000"/><p:notesSz cx="6858000" cy="9144000"/>' .
        '</p:presentation>');

    $zip->addFromString('ppt/_rels/presentation.xml.rels',
        '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
        '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>');

    $zip->close();
    return true;
}
}

if ( ! function_exists('ds_tplm_extract_single_slide') ) {
/**
 * Pulls slide #$n (0-indexed) out of a multi-slide .pptx — along with its
 * own layout/master/theme/media, same as the merge engine's graft step —
 * into a brand-new standalone single-slide .pptx at $destPath.
 */
function ds_tplm_extract_single_slide( $srcPath, $n, $destPath ) {
    if ( ! ds_tplm_make_empty_skeleton($destPath) ) return false;

    $src = new ZipArchive();
    if ( $src->open($srcPath) !== true ) return false;
    $dest = new ZipArchive();
    if ( $dest->open($destPath) !== true ) { $src->close(); return false; }

    $slidePath = ds_tplm_resolve_nth_slide_path($src, $n);
    if ( ! $slidePath ) { $src->close(); $dest->close(); return false; }

    $counters = array('slide' => 0, 'slideLayout' => 0, 'slideMaster' => 0, 'theme' => 0, 'media' => 0);
    $ctDom           = ds_tplm_load_xml($dest->getFromName('[Content_Types].xml'));
    $destPresDom     = ds_tplm_load_xml($dest->getFromName('ppt/presentation.xml'));
    $destPresRelsDom = ds_tplm_load_xml($dest->getFromName('ppt/_rels/presentation.xml.rels'));
    $maxSldId = 255;
    $maxMasterId = 2147483647;

    $ok = ds_tplm_graft_one_slide_at_path($dest, $src, $slidePath, $counters, $ctDom, $destPresDom, $destPresRelsDom, $maxSldId, $maxMasterId);

    $dest->addFromString('[Content_Types].xml', $ctDom->saveXML());
    $dest->addFromString('ppt/presentation.xml', $destPresDom->saveXML());
    $dest->addFromString('ppt/_rels/presentation.xml.rels', $destPresRelsDom->saveXML());

    $src->close();
    $dest->close();
    return $ok;
}
}
function ds_tplm_prune_unreachable_parts( $dest, &$ctDom ) {
    $reachable = array();
    $toVisit = array();

    $rootRelsXml = $dest->getFromName('_rels/.rels');
    if ( $rootRelsXml !== false ) {
        $dom = ds_tplm_load_xml($rootRelsXml);
        if ( $dom ) {
            foreach ( $dom->getElementsByTagName('Relationship') as $rel ) {
                if ( $rel->getAttribute('TargetMode') === 'External' ) continue;
                $toVisit[] = ds_tplm_resolve_rel_target('', $rel->getAttribute('Target'));
            }
        }
    }

    while ( ! empty($toVisit) ) {
        $part = array_pop($toVisit);
        if ( isset($reachable[$part]) ) continue;
        $reachable[$part] = true;
        $relsXml = $dest->getFromName(ds_tplm_rels_path_for($part));
        if ( $relsXml === false ) continue;
        $dom = ds_tplm_load_xml($relsXml);
        if ( ! $dom ) continue;
        foreach ( $dom->getElementsByTagName('Relationship') as $rel ) {
            if ( $rel->getAttribute('TargetMode') === 'External' ) continue;
            $target = ds_tplm_resolve_rel_target($part, $rel->getAttribute('Target'));
            if ( ! isset($reachable[$target]) ) $toVisit[] = $target;
        }
    }

    $alwaysKeep = array('[Content_Types].xml', '_rels/.rels');
    $toRemove = array();
    for ( $i = 0; $i < $dest->numFiles; $i++ ) {
        $name = $dest->getNameIndex($i);
        if ( in_array($name, $alwaysKeep) ) continue;
        if ( substr($name, -5) === '.rels' ) continue;
        if ( ! isset($reachable[$name]) ) $toRemove[] = $name;
    }

    foreach ( $toRemove as $name ) {
        $dest->deleteName($name);
        $relsPath = ds_tplm_rels_path_for($name);
        if ( $dest->locateName($relsPath) !== false ) $dest->deleteName($relsPath);
        foreach ( $ctDom->getElementsByTagName('Override') as $o ) {
            if ( $o->getAttribute('PartName') === '/' . $name ) {
                $o->parentNode->removeChild($o);
                break;
            }
        }
    }

    return $toRemove;
}
}
	
function ds_tplm_merge_pptx_files( $srcPaths, $destPath ) {
    if ( count($srcPaths) < 1 ) return false;
    if ( ! copy($srcPaths[0], $destPath) ) return false;

    $dest = new ZipArchive();
    if ( $dest->open($destPath) !== true ) return false;

    $counters = array(
        'slide'       => ds_tplm_scan_max_numeric($dest, 'ppt/slides', 'slide'),
        'slideLayout' => ds_tplm_scan_max_numeric($dest, 'ppt/slideLayouts', 'slideLayout'),
        'slideMaster' => ds_tplm_scan_max_numeric($dest, 'ppt/slideMasters', 'slideMaster'),
        'theme'       => ds_tplm_scan_max_numeric($dest, 'ppt/theme', 'theme'),
        'media'       => ds_tplm_scan_max_numeric($dest, 'ppt/media', 'image'),
    );

    $ctDom       = ds_tplm_load_xml($dest->getFromName('[Content_Types].xml'));
    $presDom     = ds_tplm_load_xml($dest->getFromName('ppt/presentation.xml'));
    $presRelsDom = ds_tplm_load_xml($dest->getFromName('ppt/_rels/presentation.xml.rels'));
    if ( ! $ctDom || ! $presDom || ! $presRelsDom ) { $dest->close(); return false; }

    list($maxSldId, $maxMasterId) = ds_tplm_scan_presentation_ids($presDom);
	
	// Fix sldLayoutId values in the seed file's existing slide masters.
// Slides grafted in later get fixed by ds_tplm_graft_one_slide_at_path,
// but the seed (srcPaths[0]) is copied raw and never goes through graft.
// If its slideMaster XML came from a marketplace/external PPTX, it may
// carry layout IDs that collide with master IDs the merge engine assigns
// (both sequences start near 2147483648). Safe layout IDs live at
// 2147493748+ — far above the master ID range.
// IMPORTANT: use 2147483648 + 10000 + (i * 100), NOT 2147493748 + (i * 100).
// These look similar but differ by 100 for i=1, meaning the wrong formula
// collides with the graft engine which uses the same base formula starting
// its counter right after the seed master count.
$P_NS_FIX = 'http://schemas.openxmlformats.org/presentationml/2006/main';
$seedMasterIdx = 0;
for ( $mi = 1; $mi <= $counters['slideMaster']; $mi++ ) {
    $seedMasterXml = $dest->getFromName('ppt/slideMasters/slideMaster' . $mi . '.xml');
    if ( $seedMasterXml === false ) continue;
    $seedMasterDom = ds_tplm_load_xml($seedMasterXml);
    if ( ! $seedMasterDom ) continue;
    $seedMasterIdx++;
    $safeBase = 2147483648 + 10000 + ($seedMasterIdx * 100);
    $offset = 0;
    foreach ( $seedMasterDom->getElementsByTagNameNS($P_NS_FIX, 'sldLayoutId') as $n ) {
        $n->setAttribute('id', (string) ($safeBase + $offset));
        $offset++;
    }
    $dest->addFromString('ppt/slideMasters/slideMaster' . $mi . '.xml', $seedMasterDom->saveXML());
}

    // The seed file's <p:extLst> (PowerPoint "sections" feature) lists only
    // its own original slide ID. Once more slides are grafted in, that list
    // becomes inconsistent with the real sldIdLst — PowerPoint validates
    // this strictly and will refuse to open the file outright, even though
    // the slide data itself is perfectly fine (LibreOffice/python-pptx never
    // even check this). Sections are a pure organizational UI convenience,
    // not load-bearing, so the safe fix is to just drop the stale list.
    $P_NS = 'http://schemas.openxmlformats.org/presentationml/2006/main';
    $presRoot = $presDom->documentElement;
    $extLstNode = $presDom->getElementsByTagNameNS($P_NS, 'extLst')->item(0);
    if ( $extLstNode ) {
        $presRoot->removeChild($extLstNode);
    }

    $results = array(true);
    for ( $i = 1; $i < count($srcPaths); $i++ ) {
        $src = new ZipArchive();
        if ( $src->open($srcPaths[$i]) !== true ) { $results[] = false; continue; }
        $ok = ds_tplm_graft_one_slide($dest, $src, $counters, $ctDom, $presDom, $presRelsDom, $maxSldId, $maxMasterId);
        $src->close();
        $results[] = $ok;
    }

    $dest->addFromString('[Content_Types].xml', $ctDom->saveXML());
    $dest->addFromString('ppt/presentation.xml', $presDom->saveXML());
    $dest->addFromString('ppt/_rels/presentation.xml.rels', $presRelsDom->saveXML());

    // The seed's docProps/app.xml is also carried over wholesale and
    // declares a stale <Slides>1</Slides> (plus font/theme/title lists
    // sized for just its own 1 slide) — a second mismatch PowerPoint
    // validates strictly against the real slide count. Fix the count,
    // drop the now-inaccurate lists (display-only metadata, not load-bearing).
    $finalSlideCount = $presDom->getElementsByTagNameNS(
        'http://schemas.openxmlformats.org/presentationml/2006/main', 'sldId'
    )->length;
    $appXml = $dest->getFromName('docProps/app.xml');
    if ( $appXml !== false ) {
        $appDom = ds_tplm_load_xml($appXml);
        if ( $appDom ) {
            $slidesNode = $appDom->getElementsByTagName('Slides')->item(0);
            if ( $slidesNode ) {
                $slidesNode->nodeValue = (string) $finalSlideCount;
            }
            foreach ( array('HeadingPairs', 'TitlesOfParts') as $tag ) {
                $node = $appDom->getElementsByTagName($tag)->item(0);
                if ( $node ) $node->parentNode->removeChild($node);
            }
            $dest->addFromString('docProps/app.xml', $appDom->saveXML());
        }
    } else {
        $dest->addFromString('docProps/app.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"' .
            ' xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">' .
            '<Application>Microsoft Office PowerPoint</Application>' .
            '<Slides>' . $finalSlideCount . '</Slides>' .
            '</Properties>');
        ds_tplm_ensure_content_type_override($ctDom, 'docProps/app.xml', 'application/vnd.openxmlformats-officedocument.extended-properties+xml');
    }
    if ( $dest->getFromName('docProps/core.xml') === false ) {
        $dest->addFromString('docProps/core.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' .
            '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties"' .
            ' xmlns:dc="http://purl.org/dc/elements/1.1/"' .
            ' xmlns:dcterms="http://purl.org/dc/terms/"' .
            ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">' .
            '<dc:title>Presentation</dc:title>' .
'<dc:creator>Designstager.com</dc:creator>' .
'<cp:lastModifiedBy>Designstager.com</cp:lastModifiedBy>' .
'<dcterms:created xsi:type="dcterms:W3CDTF">' . gmdate('Y-m-d\TH:i:s\Z') . '</dcterms:created>' . '</dcterms:created>' .
            '<dcterms:modified xsi:type="dcterms:W3CDTF">' . gmdate('Y-m-d\TH:i:s\Z') . '</dcterms:modified>' .
            '</cp:coreProperties>');
        ds_tplm_ensure_content_type_override($ctDom, 'docProps/core.xml', 'application/vnd.openxmlformats-package.core-properties+xml');
    }

    $dest->close();

    // addFromString() writes from this session can't be read back reliably
    // within the same ZipArchive session — closing and reopening makes
    // everything genuinely readable again, which the sweep below needs.
    $dest2 = new ZipArchive();
    if ( $dest2->open($destPath) === true ) {
        $ctDom2 = ds_tplm_load_xml($dest2->getFromName('[Content_Types].xml'));
        if ( $ctDom2 ) {
            ds_tplm_prune_unreachable_parts($dest2, $ctDom2);
            $dest2->addFromString('[Content_Types].xml', $ctDom2->saveXML());
        }
        $dest2->close();

        /* Run the shared Iconify injector over the finished combined deck.
           Without this, a marketplace/template deck shipped with raw emoji
           still sitting in the text while a normal deck got real icons. */
        if ( file_exists($destPath) ) {
            $ds_bytes = file_get_contents($destPath);
            $ds_out   = apply_filters('ds_combined_pptx_bytes', $ds_bytes, array(), '');
            if ( is_string($ds_out) && strlen($ds_out) > 1000 ) {
                file_put_contents($destPath, $ds_out);
            }
        }
    }

    return $results;
}
}

// ════════════════════════════════════════════════════════════════════════
// PART B — REST endpoint
// ════════════════════════════════════════════════════════════════════════

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/merge-real-templates', array(
        'methods'             => 'POST',
        'callback'            => 'ds_tplm_rest_merge',
        'permission_callback' => function () { return is_user_logged_in(); },
    ));
});

if ( ! function_exists('ds_tplm_rest_merge') ) {
function ds_tplm_rest_merge( WP_REST_Request $req ) {
    if ( ! function_exists('ds_tpl_url_to_path') ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Snippet 21 (Smart Template Matcher) must be active for this to work.'));
    }

    $urls = $req->get_param('download_urls');
    if ( ! is_array($urls) || count($urls) < 1 ) {
        return rest_ensure_response(array('success' => false, 'error' => 'No picked designs to combine'));
    }

    $paths = array();
    $skipped = 0;
    foreach ( $urls as $u ) {
        $u = sanitize_text_field($u);
        $p = ds_tpl_url_to_path($u);
        if ( $p && file_exists($p) ) {
            $paths[] = $p;
        } else {
            $skipped++;
        }
    }
    if ( count($paths) < 1 ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Could not locate any of the picked design files'));
    }

    $upload_dir = wp_upload_dir();
    $out_dir = trailingslashit($upload_dir['basedir']) . 'ds-ppt-templates';
    if ( ! file_exists($out_dir) ) wp_mkdir_p($out_dir);
    $fname = 'Designstager-merged-' . get_current_user_id() . '-' . time() . '-' . wp_generate_password(5, false, false) . '.pptx';
    $dest_path = trailingslashit($out_dir) . $fname;

    $results = ds_tplm_merge_pptx_files($paths, $dest_path);
    if ( ! $results || ! file_exists($dest_path) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Merge failed'));
    }

    $failed = is_array($results) ? count(array_filter($results, function ($r) { return ! $r; })) : 0;
    $dest_url = trailingslashit($upload_dir['baseurl']) . 'ds-ppt-templates/' . $fname;

    return rest_ensure_response(array(
        'success'      => true,
        'download_url' => $dest_url,
        'slide_count'  => is_array($results) ? count($results) : count($paths),
        'failed_count' => $failed + $skipped,
    ));
}
}
// ════════════════════════════════════════════════════════════════════════
// PART C — Full-deck combine: every slide ends up in the final file, not
// just the ones with a picked design. Slides without a picked template
// get rendered through the EXISTING coded-layout builder (Snippet 16's
// ds_pptv2_full_build — called directly as a function, not over HTTP, the
// same way Snippet 16 already calls it internally) as their own single-
// slide file, then everything is merged in original order using the same
// ds_tplm_merge_pptx_files() engine already proven above.
//
// Requires Snippet 16 (DS PPT Variant PPTX Renderers) to be active, in
// addition to Snippet 21.
// ════════════════════════════════════════════════════════════════════════

add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/combine-full-deck', array(
        'methods'             => 'POST',
        'callback'            => 'ds_tplm_rest_combine_full_deck',
        'permission_callback' => function () { return is_user_logged_in(); },
    ));
});

if ( ! function_exists('ds_tplm_rest_combine_full_deck') ) {
function ds_tplm_rest_combine_full_deck( WP_REST_Request $req ) {
    // Merging real marketplace .pptx templates with the coded deck holds several
    // decks open at once (ZipArchive + DOM), which blew past WordPress's 512M
    // ceiling: "Allowed memory size of 536870912 bytes exhausted". That is an
    // uncatchable fatal, so REST returned a bare 500. PHP itself allows 3072M
    // on this host, so lift the ceiling for this one request.
    @ini_set('memory_limit', '1536M');

    if ( ! function_exists('ds_tpl_url_to_path') ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Snippet 21 (Smart Template Matcher) must be active for this to work.'));
    }
    if ( ! function_exists('ds_pptv2_build') ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Snippet 16 (Variant PPTX Renderers) must be active for this to work.'));
    }

    $slides   = $req->get_param('slides');
    $variants = $req->get_param('slide_variants') ?: array();
    $template = sanitize_text_field($req->get_param('template') ?: 'navy-pro');
    $layout   = sanitize_text_field($req->get_param('layout') ?: '16:9');
    $title    = sanitize_text_field($req->get_param('title') ?: 'Presentation');
    $realMap  = $req->get_param('real_template_map');
    if ( ! is_array($realMap) ) $realMap = array();

    if ( ! is_array($slides) || empty($slides) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'No slides to combine'));
    }

    // Credit gate: each slide that actually got a marketplace design applied
    // costs credits (same per-design rate as Pick-a-Design elsewhere). Plain
    // AI-generated slides with no marketplace design are free, as before.
    $uid = get_current_user_id();
    $applied_count = count($realMap);
    if ( $applied_count > 0 ) {
        $__ppt_opt_combine = get_option('ds_ppt_credit_settings');
        $design_cost = ( is_array($__ppt_opt_combine) && isset($__ppt_opt_combine['template_cost']) && $__ppt_opt_combine['template_cost'] !== '' ) ? (int) $__ppt_opt_combine['template_cost'] : 5;
        $needed = $applied_count * $design_cost;
        $bal = function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid, 'ds_ai_credits', true);
        if ( $bal < $needed ) {
            return rest_ensure_response(array(
                'success' => false,
                'error'   => "You need {$needed} credits to download this deck with {$applied_count} marketplace design(s) applied. You currently have {$bal}.",
            ));
        }
    }

    // Split into "has a picked real template" vs "still coded", remembering
    // each coded slide's ORIGINAL position.
    $codedSlides   = array(); // original_index => slide  (insertion order == relative order)
    $codedVariants = array(); // 'slide_K' => variant, K = position within the coded-only list
    $realPathByIndex = array();

    foreach ( $slides as $i => $slide ) {
        if ( isset($realMap[$i]) && $realMap[$i] ) {
            $raw = sanitize_text_field($realMap[$i]);
if ( strpos($raw, 'pending:') === 0 ) {
    $template_id = (int) str_replace('pending:', '', $raw);
    list($src_p, $tmp_p) = ds_tpl_fetch_template_src_path($template_id);
    if ( $src_p && file_exists($src_p) ) {
        $upload_dir = wp_upload_dir();
        $out_dir    = trailingslashit($upload_dir['basedir']) . 'ds-ppt-templates';
        if ( ! file_exists($out_dir) ) wp_mkdir_p($out_dir);
        $fname    = 'tpl-' . $template_id . '-' . get_current_user_id() . '-' . time() . '-' . wp_generate_password(5,false,false) . '.pptx';
        $dest_p   = trailingslashit($out_dir) . $fname;
        $result   = ds_tpl_inject_into_pptx($src_p, $dest_p, $slides[$i]);
        if ($tmp_p) @unlink($tmp_p);
        if ( $result !== false && ! is_wp_error($result) && file_exists($dest_p) ) {
            $realPathByIndex[$i] = $dest_p;
            $tmpFiles[] = $dest_p;
            continue;
        }
    }
} else {
    $p = ds_tpl_url_to_path($raw);
    if ( $p && file_exists($p) ) {
        $realPathByIndex[$i] = $p;
        continue;
    }
}
        }
        $k = count($codedSlides);
        $codedSlides[$i] = $slide;
        $vkey = 'slide_' . $i;
        $codedVariants['slide_' . $k] = isset($variants[$vkey]) ? $variants[$vkey] : 'v1';
    }

    // Build ALL coded slides in ONE call — ds_pptv2_build/ds_pptv2_full_build
    // is only safe to call once per request (it uses some shared state
    // internally for image/icon tracking that isn't reset between calls),
    // so we render the whole coded sub-deck in a single shot, then split
    // it back into individual slides ourselves afterward.
    $tmpFiles = array();
    $codedPathByIndex = array();

    if ( ! empty($codedSlides) ) {
        $codedSlidesReindexed = array_values($codedSlides);
        $originalIndexOrder   = array_keys($codedSlides);

        $codedReq = new WP_REST_Request('POST', '/ds/v1/build-pptx-v2');
        $codedReq->set_param('slides', $codedSlidesReindexed);
        $codedReq->set_param('slide_variants', $codedVariants);
        $codedReq->set_param('template', $template);
        $codedReq->set_param('layout', $layout);
        $codedReq->set_param('title', $title);
		$fontFamily = sanitize_text_field($req->get_param('font_family') ?: '');
        if($fontFamily) $codedReq->set_param('font_family', $fontFamily);

        $resp = ds_pptv2_build($codedReq);
        $data = ( $resp instanceof WP_REST_Response ) ? $resp->get_data() : $resp;

        if ( is_array($data) && ! empty($data['success']) && ! empty($data['url']) ) {
            $codedDeckPath = ds_tpl_url_to_path($data['url']);
            if ( $codedDeckPath && file_exists($codedDeckPath) ) {
                $tmpDir = get_temp_dir();
                foreach ( $codedSlidesReindexed as $k => $_ ) {
                    $origIdx = $originalIndexOrder[$k];
                    $extractPath = trailingslashit($tmpDir) . 'ds-extract-' . get_current_user_id() . '-' . time() . '-' . $k . '-' . wp_generate_password(5, false, false) . '.pptx';
                    if ( ds_tplm_extract_single_slide($codedDeckPath, $k, $extractPath) ) {
                        $codedPathByIndex[$origIdx] = $extractPath;
                        $tmpFiles[] = $extractPath;
                    }
                }
            }
        }
    }

    // Reassemble in the ORIGINAL deck order.
    $paths = array();
    $realCount = 0;
    $builtCount = 0;
    foreach ( $slides as $i => $slide ) {
        if ( isset($realPathByIndex[$i]) ) {
            $paths[] = $realPathByIndex[$i];
            $realCount++;
        } elseif ( isset($codedPathByIndex[$i]) ) {
            $paths[] = $codedPathByIndex[$i];
            $builtCount++;
        }
    }

    if ( count($paths) < 1 ) {
        foreach ( $tmpFiles as $tf ) { @unlink($tf); }
        return rest_ensure_response(array('success' => false, 'error' => 'Could not build any slides'));
    }

    $upload_dir = wp_upload_dir();
    $out_dir = trailingslashit($upload_dir['basedir']) . 'ds-ppt-templates';
    if ( ! file_exists($out_dir) ) wp_mkdir_p($out_dir);
    $fname = 'Designstager-fulldeck-' . get_current_user_id() . '-' . time() . '-' . wp_generate_password(5, false, false) . '.pptx';
    $dest_path = trailingslashit($out_dir) . $fname;

    // Insert the fixed upsell slide as the second-to-last slide of every
    // combined download.
    $promoPath = trailingslashit($out_dir) . 'promo-cta.pptx';
    if ( file_exists($promoPath) ) {
        array_splice($paths, max(count($paths) - 1, 0), 0, array($promoPath));
    }

    $results = ds_tplm_merge_pptx_files($paths, $dest_path);

    foreach ( $tmpFiles as $tf ) { @unlink($tf); }

    if ( ! $results || ! file_exists($dest_path) ) {
        return rest_ensure_response(array('success' => false, 'error' => 'Merge failed'));
    }

    $dest_url = trailingslashit($upload_dir['baseurl']) . 'ds-ppt-templates/' . $fname;

    // Deduct now that the deck has actually been built successfully -- only
    // for the slides that really ended up with a marketplace design (never
    // charge for a design that failed to resolve/render).
    if ( $realCount > 0 ) {
        $__ppt_opt_combine2 = get_option('ds_ppt_credit_settings');
        $design_cost2 = ( is_array($__ppt_opt_combine2) && isset($__ppt_opt_combine2['template_cost']) && $__ppt_opt_combine2['template_cost'] !== '' ) ? (int) $__ppt_opt_combine2['template_cost'] : 5;
        $charge = $realCount * $design_cost2;
        if ( function_exists('ds_deduct_credits') ) {
            // Refused when another request spent the credits in the meantime.
            if ( ! ds_deduct_credits($uid, $charge) ) {
                @unlink($dest_path);
                return rest_ensure_response(array('success' => false, 'error' => "You need {$charge} credits to download this deck."));
            }
        } else {
            $cur_bal = (int) get_user_meta($uid, 'ds_ai_credits', true);
            update_user_meta($uid, 'ds_ai_credits', max(0, $cur_bal - $charge));
        }

        // Log this deduction so it shows up in the Credit History tab --
        // ds_deduct_credits() only mutates the numeric balance, it does not
        // create any visible history entry on its own.
        $ppt_log = get_user_meta($uid, 'ds_ppt_generations', true) ?: array();
        array_unshift($ppt_log, array(
            'date'       => current_time('mysql'),
            'prompt'     => $title . ' (Marketplace Designs Applied)',
            'credits'    => $charge,
            'num_slides' => $realCount,
        ));
        update_user_meta($uid, 'ds_ppt_generations', $ppt_log);
    }

    return rest_ensure_response(array(
        'success'         => true,
        'download_url'    => $dest_url,
        'slide_count'     => count($paths),
        'requested_count' => count($slides),
        'real_count'      => $realCount,
        'coded_count'     => $builtCount,
        'credits_charged' => isset($charge) ? $charge : 0,
        'credits_balance' => function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid, 'ds_ai_credits', true),
    ));
}
}


// ==== TEMP DIAGNOSTIC (remove) ====
add_action('rest_api_init', function(){
  register_rest_route('ds/v1','/combine-diag', array(
    'methods'=>'POST',
    'permission_callback'=>function(){ return current_user_can('manage_options'); },
    'callback'=>function($req){
      set_transient('ds_cdiag_last','running',600);
      register_shutdown_function(function(){
        $e = error_get_last();
        if ($e && in_array($e['type'], array(E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR,E_USER_ERROR), true)) {
          set_transient('ds_cdiag_last', 'FATAL '.$e['type'].': '.$e['message'].' @ '.$e['file'].':'.$e['line'], 600);
        }
      });
      try{
        $r = ds_tplm_rest_combine_full_deck($req);
        set_transient('ds_cdiag_last','completed',600);
        return rest_ensure_response(array('ok'=>true,'data'=>($r instanceof WP_REST_Response)?$r->get_data():$r));
      }catch(\Throwable $e){
        set_transient('ds_cdiag_last','THROWN '.get_class($e).': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine(),600);
        return rest_ensure_response(array('ok'=>false,'err'=>get_class($e).': '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine()));
      }
    }
  ));
  register_rest_route('ds/v1','/combine-diag-last', array(
    'methods'=>'GET',
    'permission_callback'=>function(){ return current_user_can('manage_options'); },
    'callback'=>function(){ return rest_ensure_response(array('last'=>get_transient('ds_cdiag_last'),'mem'=>ini_get('memory_limit'),'max_time'=>ini_get('max_execution_time'))); }
  ));
});
