<?php
/**
 * Code Snippets export — DS Charts — chart slides (Add Slide chart wizard, editor, native PPTX charts)
 *
 * Snippet ID : 898
 * Active     : yes
 * Priority   : 10
 * Scope      : global
 * Tags       : ppt, charts
 * Modified   : 2026-10-15 15:42:05 (V41 update from staging)
 * Load order : 165 of 165
 */
/**
 * DS Charts — chart slides for the AI presentation editor
 *
 *  - Add Slide with AI → "Generate a chart slide": pick a chart type, type the
 *    numbers (or let AI create them) plus any text; AI writes the title,
 *    subtitle and insights. Costs the same as Add Slide.
 *  - Chart editor: change type / shape / angle / values, add or delete rows.
 *  - Auto chart slides after generated slides that carry percentages.
 *  - Downloads get REAL PowerPoint charts (right-click → Edit Data works),
 *    for Classic decks and template decks.
 *
 * Endpoint: POST /ds/v1/chart-slide
 * Works with: snippet 17 (variant picker), 16 (build-pptx-v2), T00 engine.
 */
if ( ! defined('ABSPATH') ) exit;
if ( ! defined('DS_CHARTS_V1') ) {
define('DS_CHARTS_V1', '1.0.0');

/* ============================================================
 * colour + data helpers (twins of DS_CHART in core.js)
 * ============================================================ */
function ds_chart_hex( $c, $def = '4A9EFF' ) {
    if ( preg_match('/#?([0-9a-f]{6}|[0-9a-f]{3})\b/i', (string) $c, $m) ) {
        $h = $m[1]; if ( strlen($h) === 3 ) $h = $h[0].$h[0].$h[1].$h[1].$h[2].$h[2];
        return strtoupper($h);
    }
    return $def;
}
function ds_chart_rgb( $c ) { $c = ds_chart_hex($c); return array(hexdec(substr($c,0,2)), hexdec(substr($c,2,2)), hexdec(substr($c,4,2))); }
function ds_chart_lum( $c ) { $r = ds_chart_rgb($c); return (0.299*$r[0] + 0.587*$r[1] + 0.114*$r[2]) / 255; }
function ds_chart_mix( $a, $b, $t ) { $A = ds_chart_rgb($a); $B = ds_chart_rgb($b); $o = ''; for ( $i = 0; $i < 3; $i++ ) $o .= sprintf('%02X', (int) round($A[$i] + ($B[$i]-$A[$i])*$t)); return $o; }
function ds_chart_to_hsl( $c ) {
    $r = array_map(function($v){ return $v/255; }, ds_chart_rgb($c));
    $mx = max($r); $mn = min($r); $h = 0; $s = 0; $l = ($mx+$mn)/2; $d = $mx-$mn;
    if ( $d ) {
        $s = $l > 0.5 ? $d/(2-$mx-$mn) : $d/($mx+$mn);
        if ( $mx == $r[0] ) $h = ($r[1]-$r[2])/$d + ($r[1] < $r[2] ? 6 : 0);
        elseif ( $mx == $r[1] ) $h = ($r[2]-$r[0])/$d + 2;
        else $h = ($r[0]-$r[1])/$d + 4;
        $h *= 60;
    }
    return array($h, $s, $l);
}
function ds_chart_from_hsl( $h, $s, $l ) {
    $h = fmod(fmod($h, 360) + 360, 360) / 360;
    $f = function($p, $q, $t){ if ($t < 0) $t += 1; if ($t > 1) $t -= 1; if ($t < 1/6) return $p+($q-$p)*6*$t; if ($t < 1/2) return $q; if ($t < 2/3) return $p+($q-$p)*(2/3-$t)*6; return $p; };
    if ( ! $s ) { $r = $g = $b = $l; }
    else { $q = $l < 0.5 ? $l*(1+$s) : $l+$s-$l*$s; $p = 2*$l-$q; $r = $f($p,$q,$h+1/3); $g = $f($p,$q,$h); $b = $f($p,$q,$h-1/3); }
    $o = ''; foreach ( array($r,$g,$b) as $v ) $o .= sprintf('%02X', (int) round(max(0, min(1, $v))*255));
    return $o;
}
function ds_chart_first_hex( $bg ) { return preg_match('/#[0-9a-f]{6}|#[0-9a-f]{3}\b/i', (string) $bg, $m) ? ds_chart_hex($m[0]) : ds_chart_hex($bg, '1B2A4A'); }
function ds_chart_palette( $ac, $dark, $n ) {
    $offs = array(array(0,0),array(180,0),array(0,-0.16),array(40,0.02),array(180,-0.14),array(-40,0.04),array(0,0.13),array(90,0),array(-90,0),array(140,-0.05),array(-140,0.05),array(20,-0.1));
    list($H, $S, $L) = ds_chart_to_hsl($ac);
    $out = array(ds_chart_hex($ac));
    $s = max(0.5, min($dark ? 0.85 : 0.72, $S ?: 0.6));
    $l = $dark ? max(0.55, min(0.66, $L)) : max(0.42, min(0.52, $L));
    for ( $i = 1; $i < max($n, 1); $i++ ) {
        $o = $offs[$i % count($offs)]; $lap = (int) floor($i / count($offs)); $hh = $H + $o[0] + $lap*12;
        $li = max(0.28, min(0.8, $l + $o[1])); $c = ds_chart_from_hsl($hh, $s, $li);
        for ( $g = 0; $g < 8 && ! $dark && ds_chart_lum($c) > 0.6; $g++ ) { $li -= 0.04; $c = ds_chart_from_hsl($hh, $s, $li); }
        for ( $g = 0; $g < 8 && $dark && ds_chart_lum($c) < 0.36; $g++ ) { $li += 0.04; $c = ds_chart_from_hsl($hh, $s, $li); }
        $out[] = $c;
    }
    return array_slice($out, 0, max($n, 1));
}
function ds_chart_fam( $style ) {
    $F = array('pie'=>'pie','donut'=>'pie','exploded'=>'pie','half'=>'pie','ring'=>'pie','column'=>'bar','bar'=>'bar','line'=>'line','smooth'=>'line','area'=>'line','gauge'=>'gauge','progress'=>'gauge','rings'=>'gauge');
    return isset($F[$style]) ? $F[$style] : '';
}
function ds_chart_num( $v ) { $s = preg_replace('/[^0-9.\-]/', '', (string) $v); return ( $s === '' || ! is_numeric($s) ) ? null : (float) $s; }
function ds_chart_norm( $ch ) {
    $ch = is_array($ch) ? $ch : array();
    $style = isset($ch['style']) && ds_chart_fam($ch['style']) ? $ch['style'] : 'donut';
    $fam = ds_chart_fam($style);
    $L = isset($ch['labels']) && is_array($ch['labels']) ? array_values($ch['labels']) : array();
    $V = isset($ch['values']) && is_array($ch['values']) ? array_values($ch['values']) : array();
    $rows = array();
    for ( $i = 0; $i < max(count($L), count($V)); $i++ ) {
        $v = ds_chart_num($V[$i] ?? null); if ( $v === null ) continue;
        $l = trim(wp_strip_all_tags((string) ($L[$i] ?? '')));
        $rows[] = array('l' => mb_substr($l !== '' ? $l : ('Item ' . ($i+1)), 0, 60), 'v' => $fam === 'pie' ? max(0, $v) : $v);
        if ( count($rows) >= 12 ) break;
    }
    $unit = mb_substr(trim(wp_strip_all_tags((string) ($ch['unit'] ?? '%'))), 0, 8);
    $mx = ds_chart_num($ch['max'] ?? null);
    if ( ! $mx || $mx <= 0 ) {
        if ( $unit === '%' ) { $mx = 100; foreach ( $rows as $r ) if ( $r['v'] > $mx ) $mx = $r['v']; }
        else { $mx = 0; foreach ( $rows as $r ) if ( $r['v'] > $mx ) $mx = $r['v']; if ( $mx <= 0 ) $mx = 100; }
    }
    $ang = ((int) ($ch['angle'] ?? 0) % 360 + 360) % 360;
    return array('kind' => $fam, 'style' => $style, 'angle' => $ang, 'unit' => $unit, 'max' => (float) $mx, 'rows' => $rows);
}
/* clean chart data for storage in a slide */
function ds_chart_clean( $ch ) {
    $d = ds_chart_norm($ch);
    return array('style' => $d['style'], 'angle' => $d['angle'], 'unit' => $d['unit'], 'max' => isset($ch['max']) && ds_chart_num($ch['max']) ? ds_chart_num($ch['max']) : '',
                 'labels' => array_map(function($r){ return $r['l']; }, $d['rows']), 'values' => array_map(function($r){ return $r['v']; }, $d['rows']));
}
function ds_chart_fmt( $v, $unit ) {
    $a = abs($v);
    if ( $a >= 1e9 ) $s = (round($v/1e8)/10) . 'B'; elseif ( $a >= 1e6 ) $s = (round($v/1e5)/10) . 'M'; elseif ( $a >= 1e4 ) $s = (round($v/100)/10) . 'K';
    else $s = (string) (round($v*100)/100);
    if ( $unit === '' ) return $s;
    if ( preg_match('/^[$€£₹¥]$/u', $unit) ) return ($v < 0 ? '-' : '') . $unit . ltrim($s, '-');
    if ( $unit === '%' ) return $s . '%';
    return $s . ' ' . $unit;
}
function ds_chart_nice_max( $m ) {
    if ( $m <= 0 ) return 10;
    $p = pow(10, floor(log10($m))); $f = $m / $p;
    foreach ( array(1,1.2,1.5,2,2.5,3,4,5,6,8,10) as $n ) if ( $f <= $n + 1e-9 ) return $n * $p;
    return 10 * $p;
}
function ds_chart_x( $s ) { return htmlspecialchars((string) $s, ENT_QUOTES | ENT_XML1, 'UTF-8'); }

/* ============================================================
 * geometry twins (DS_CHART.pieGeo / gaugeGeo)
 * ============================================================ */
function ds_chart_pie_geo( $w, $h, $style, $legend, $n ) {
    $LW = $legend === 'right' ? min($w*0.42, 340) : 0; $LH = $legend === 'bottom' ? min($h*0.26, 20 + ceil($n/3)*36) : 0;
    $aw = $w - $LW; $ah = $h - $LH; $g = array('LW'=>$LW,'LH'=>$LH,'aw'=>$aw,'ah'=>$ah);
    if ( $style === 'half' ) { $g['R'] = min($aw/2, $ah*0.92) * 0.94; $g['cx'] = $aw/2; $g['cy'] = $ah/2 + $g['R']*0.45; if ( $g['cy'] + 0.02*$g['R'] > $ah ) $g['cy'] = $ah - 2; }
    else { $g['R'] = min($aw, $ah)/2 * 0.92; $g['cx'] = $aw/2; $g['cy'] = $ah/2; }
    $g['ri'] = $style === 'donut' ? 0.58 : ( $style === 'ring' ? 0.8 : ( $style === 'half' ? 0.56 : 0 ) );
    return $g;
}
function ds_chart_gauge_geo( $w, $h, $n ) {
    $n = max(1, min(4, $n)); $cw = $w/$n; $R = min($cw*0.45, $h*0.56);
    return array('n'=>$n, 'cw'=>$cw, 'R'=>$R, 'th'=>$R*0.24, 'cy'=>$h*0.5 + $R*0.28);
}

/* ============================================================
 * DrawingML pieces
 * ============================================================ */
function ds_chart_emu( $v ) { return (int) round((float) $v * 9525); }
function ds_chart_fill( $hex, $alpha = null ) { return '<a:solidFill><a:srgbClr val="' . ds_chart_hex($hex) . '">' . ( $alpha !== null ? '<a:alpha val="' . (int) $alpha . '"/>' : '' ) . '</a:srgbClr></a:solidFill>'; }
function ds_chart_font() { $f = isset($GLOBALS['DS_CHART_FONT']) && $GLOBALS['DS_CHART_FONT'] ? $GLOBALS['DS_CHART_FONT'] : ( ! empty($GLOBALS['DS_PPT_FONT_FAMILY']) ? $GLOBALS['DS_PPT_FONT_FAMILY'] : 'Arial' ); return preg_match('/^[A-Za-z0-9 ]{2,40}$/', $f) ? $f : 'Arial'; }
function ds_chart_txpr( $px, $hex, $bold = false, $alpha = null ) {
    return '<c:txPr><a:bodyPr/><a:lstStyle/><a:p><a:pPr><a:defRPr sz="' . (int) round($px*75) . '" b="' . ( $bold ? 1 : 0 ) . '">' . ds_chart_fill($hex, $alpha) . '<a:latin typeface="' . ds_chart_x(ds_chart_font()) . '"/></a:defRPr></a:pPr><a:endParaRPr lang="en-US"/></a:p></c:txPr>';
}
function ds_chart_numfmt( $unit, $scale = true ) {
    $pre = ''; $suf = '';
    if ( preg_match('/^[$€£₹¥]$/u', $unit) ) $pre = '"' . $unit . '"';
    elseif ( $unit === '%' ) $suf = '"%"';
    elseif ( $unit !== '' ) $suf = '" ' . str_replace('"', '', $unit) . '"';
    if ( ! $scale || $unit === '%' ) return $pre . 'General' . $suf;
    return '[>=1000000]' . $pre . '0.0,,"M"' . $suf . ';[>=10000]' . $pre . '0.0,"K"' . $suf . ';' . $pre . 'General' . $suf;
}
function ds_chart_cat( $labels, $col = 'A', $r0 = 2 ) {
    $n = count($labels); $x = '<c:cat><c:strRef><c:f>Sheet1!$' . $col . '$' . $r0 . ':$' . $col . '$' . ($r0 + $n - 1) . '</c:f><c:strCache><c:ptCount val="' . $n . '"/>';
    foreach ( array_values($labels) as $i => $l ) $x .= '<c:pt idx="' . $i . '"><c:v>' . ds_chart_x($l) . '</c:v></c:pt>';
    return $x . '</c:strCache></c:strRef></c:cat>';
}
function ds_chart_val( $vals, $col = 'B', $r0 = 2, $fmt = 'General' ) {
    $n = count($vals); $x = '<c:val><c:numRef><c:f>Sheet1!$' . $col . '$' . $r0 . ':$' . $col . '$' . ($r0 + $n - 1) . '</c:f><c:numCache><c:formatCode>' . ds_chart_x($fmt) . '</c:formatCode><c:ptCount val="' . $n . '"/>';
    foreach ( array_values($vals) as $i => $v ) $x .= '<c:pt idx="' . $i . '"><c:v>' . ( 0 + $v ) . '</c:v></c:pt>';
    return $x . '</c:numCache></c:numRef></c:val>';
}
function ds_chart_tx( $cell, $name ) { return '<c:tx><c:strRef><c:f>Sheet1!' . $cell . '</c:f><c:strCache><c:ptCount val="1"/><c:pt idx="0"><c:v>' . ds_chart_x($name) . '</c:v></c:pt></c:strCache></c:strRef></c:tx>'; }
function ds_chart_manual( $x, $y, $w, $h, $inner = true ) {
    $c = function($v){ return round(max(0, min(1, $v)), 4); };
    return '<c:layout><c:manualLayout>' . ( $inner ? '<c:layoutTarget val="inner"/>' : '' ) . '<c:xMode val="edge"/><c:yMode val="edge"/><c:x val="' . $c($x) . '"/><c:y val="' . $c($y) . '"/><c:w val="' . $c($w) . '"/><c:h val="' . $c($h) . '"/></c:manualLayout></c:layout>';
}
function ds_chart_space( $plot, $legend, $o ) {
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<c:chartSpace xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<c:date1904 val="0"/><c:lang val="en-US"/><c:roundedCorners val="0"/>'
        . '<c:chart><c:autoTitleDeleted val="1"/><c:plotArea>' . $plot . '<c:spPr><a:noFill/><a:ln><a:noFill/></a:ln></c:spPr></c:plotArea>' . $legend . '<c:plotVisOnly val="1"/><c:dispBlanksAs val="gap"/></c:chart>'
        . '<c:spPr><a:noFill/><a:ln><a:noFill/></a:ln></c:spPr>'
        . ds_chart_txpr(15, $o['tc'])
        . '<c:externalData r:id="rId1"><c:autoUpdate val="0"/></c:externalData>'
        . '</c:chartSpace>';
}
function ds_chart_axes( $d, $o, $top, $dir ) {
    $mc = $o['mc']; $tc = $o['tc']; $fs = max(15, min(21, $o['h']*0.046));
    $grid = '<c:majorGridlines><c:spPr><a:ln w="9525">' . ds_chart_fill($mc, 22000) . '<a:prstDash val="dash"/></a:ln></c:spPr></c:majorGridlines>';
    $hbar = $dir === 'bar';
    $cat = '<c:catAx><c:axId val="5001"/><c:scaling><c:orientation val="' . ( $hbar ? 'maxMin' : 'minMax' ) . '"/></c:scaling><c:delete val="0"/><c:axPos val="' . ( $hbar ? 'l' : 'b' ) . '"/>'
         . '<c:numFmt formatCode="General" sourceLinked="0"/><c:majorTickMark val="none"/><c:minorTickMark val="none"/><c:tickLblPos val="nextTo"/>'
         . '<c:spPr><a:noFill/><a:ln w="9525">' . ( $hbar ? '<a:noFill/>' : ds_chart_fill($mc, 50000) ) . '</a:ln></c:spPr>'
         . ds_chart_txpr($fs * ( $hbar ? 1 : 0.9 ), $tc, $hbar) . '<c:crossAx val="5002"/><c:crosses val="autoZero"/><c:auto val="1"/><c:lblAlgn val="ctr"/><c:lblOffset val="100"/><c:noMultiLvlLbl val="0"/></c:catAx>';
    $val = '<c:valAx><c:axId val="5002"/><c:scaling><c:orientation val="minMax"/><c:max val="' . $top . '"/><c:min val="0"/></c:scaling><c:delete val="' . ( $hbar ? 1 : 0 ) . '"/><c:axPos val="' . ( $hbar ? 'b' : 'l' ) . '"/>' . $grid
         . '<c:numFmt formatCode="' . ds_chart_x(ds_chart_numfmt($d['unit'])) . '" sourceLinked="0"/><c:majorTickMark val="none"/><c:minorTickMark val="none"/><c:tickLblPos val="nextTo"/>'
         . '<c:spPr><a:noFill/><a:ln><a:noFill/></a:ln></c:spPr>' . ds_chart_txpr($fs * 0.85, $mc)
         . '<c:crossAx val="5001"/><c:crosses val="autoZero"/><c:crossBetween val="' . ( $d['kind'] === 'line' ? 'midCat' : 'between' ) . '"/><c:majorUnit val="' . ( $top / 4 ) . '"/></c:valAx>';
    return $cat . $val;
}

/* ============================================================
 * chart part + workbook
 * ============================================================ */
function ds_chart_xlsx( $sheet_rows ) {
    /* $sheet_rows: array of rows; a cell is a string, a number, or array('f'=>formula,'v'=>cached) */
    if ( ! class_exists('ZipArchive') ) return '';
    $tmp = wp_tempnam('ds-chart'); if ( ! $tmp ) $tmp = tempnam(sys_get_temp_dir(), 'dsc');
    $z = new ZipArchive(); if ( $z->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true ) return '';
    $cols = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; $sd = '';
    foreach ( $sheet_rows as $ri => $row ) {
        $r = $ri + 1; $sd .= '<row r="' . $r . '">';
        foreach ( array_values($row) as $ci => $cell ) {
            $ref = $cols[$ci] . $r;
            if ( is_array($cell) ) $sd .= '<c r="' . $ref . '"><f>' . ds_chart_x($cell['f']) . '</f><v>' . ( 0 + $cell['v'] ) . '</v></c>';
            elseif ( is_int($cell) || is_float($cell) ) $sd .= '<c r="' . $ref . '"><v>' . ( 0 + $cell ) . '</v></c>';
            elseif ( $cell !== null && $cell !== '' ) $sd .= '<c r="' . $ref . '" t="inlineStr"><is><t>' . ds_chart_x($cell) . '</t></is></c>';
        }
        $sd .= '</row>';
    }
    $z->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
    $z->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
    $z->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets></workbook>');
    $z->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
    $z->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>' . $sd . '</sheetData></worksheet>');
    $z->close();
    $b = file_get_contents($tmp); @unlink($tmp);
    return $b ?: '';
}
function ds_chart_queue( $xml, $xlsx ) {
    if ( ! isset($GLOBALS['ds_chart_q']) ) $GLOBALS['ds_chart_q'] = array();
    $GLOBALS['ds_chart_q'][] = array('xml' => $xml, 'xlsx' => $xlsx);
    return count($GLOBALS['ds_chart_q']);
}
function ds_chart_sid() { $GLOBALS['ds_chart_sid'] = isset($GLOBALS['ds_chart_sid']) ? $GLOBALS['ds_chart_sid'] + 1 : 7001; return $GLOBALS['ds_chart_sid']; }
function ds_chart_frame_xml( $k, $x, $y, $w, $h ) {
    $id = ds_chart_sid();
    return '<p:graphicFrame><p:nvGraphicFramePr><p:cNvPr id="' . $id . '" name="Chart ' . $k . '"/><p:cNvGraphicFramePr><a:graphicFrameLocks noGrp="1"/></p:cNvGraphicFramePr><p:nvPr/></p:nvGraphicFramePr>'
         . '<p:xfrm><a:off x="' . ds_chart_emu($x) . '" y="' . ds_chart_emu($y) . '"/><a:ext cx="' . ds_chart_emu($w) . '" cy="' . ds_chart_emu($h) . '"/></p:xfrm>'
         . '<a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/chart"><c:chart xmlns:c="http://schemas.openxmlformats.org/drawingml/2006/chart" r:id="rIdDsc' . $k . '"/></a:graphicData></a:graphic></p:graphicFrame>';
}
/* plain text box, design units */
function ds_chart_tb( $x, $y, $w, $h, $paras, $o = array() ) {
    /* $paras: list of array(text, px, hex, bold, alpha?) */
    $id = ds_chart_sid(); $algn = $o['align'] ?? 'l'; $anchor = $o['anchor'] ?? 'ctr'; $font = ds_chart_x(ds_chart_font());
    $ps = '';
    foreach ( $paras as $i => $p ) {
        $ps .= '<a:p><a:pPr algn="' . $algn . '"><a:lnSpc><a:spcPct val="' . (int) ( $o['ln'] ?? 110000 ) . '"/></a:lnSpc>' . ( $i ? '<a:spcBef><a:spcPts val="' . (int) ( $p[5] ?? 400 ) . '"/></a:spcBef>' : '' ) . '</a:pPr>'
             . '<a:r><a:rPr lang="en-US" sz="' . (int) round($p[1]*75) . '" b="' . ( ! empty($p[3]) ? 1 : 0 ) . '" dirty="0">' . ds_chart_fill($p[2], $p[4] ?? null) . '<a:latin typeface="' . $font . '"/><a:cs typeface="' . $font . '"/></a:rPr><a:t>' . ds_chart_x($p[0]) . '</a:t></a:r></a:p>';
    }
    return '<p:sp><p:nvSpPr><p:cNvPr id="' . $id . '" name="Text ' . $id . '"/><p:cNvSpPr txBox="1"/><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="' . ds_chart_emu($x) . '" y="' . ds_chart_emu($y) . '"/><a:ext cx="' . ds_chart_emu($w) . '" cy="' . ds_chart_emu($h) . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:noFill/></p:spPr>'
         . '<p:txBody><a:bodyPr wrap="square" lIns="' . ds_chart_emu($o['pad'] ?? 0) . '" tIns="0" rIns="' . ds_chart_emu($o['pad'] ?? 0) . '" bIns="0" anchor="' . $anchor . '"><a:normAutofit/></a:bodyPr><a:lstStyle/>' . $ps . '</p:txBody></p:sp>';
}
function ds_chart_rect( $x, $y, $w, $h, $hex, $alpha = null, $r = 0 ) {
    $id = ds_chart_sid();
    $geom = $r > 0 ? '<a:prstGeom prst="roundRect"><a:avLst><a:gd name="adj" fmla="val ' . (int) min(50000, round($r / max(1, min($w, $h)) * 100000)) . '"/></a:avLst></a:prstGeom>' : '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>';
    return '<p:sp><p:nvSpPr><p:cNvPr id="' . $id . '" name="Shape ' . $id . '"/><p:cNvSpPr/><p:nvPr/></p:nvSpPr><p:spPr><a:xfrm><a:off x="' . ds_chart_emu($x) . '" y="' . ds_chart_emu($y) . '"/><a:ext cx="' . ds_chart_emu($w) . '" cy="' . ds_chart_emu($h) . '"/></a:xfrm>' . $geom . ds_chart_fill($hex, $alpha) . '<a:ln><a:noFill/></a:ln></p:spPr><p:txBody><a:bodyPr/><a:lstStyle/><a:p><a:endParaRPr lang="en-US"/></a:p></p:txBody></p:sp>';
}
function ds_chart_on( $c ) { return ds_chart_lum($c) > 0.62 ? '111827' : 'FFFFFF'; }

/**
 * Native chart(s) for one chart box.
 * $x,$y,$w,$h: design units of a 1280x720 slide.
 * $o: colors[], tc, mc, bgc, legend
 */
function ds_chart_frames( $x, $y, $w, $h, $chart, $o ) {
    $d = ds_chart_norm($chart); $rows = $d['rows']; $n = count($rows);
    $tc = ds_chart_hex($o['tc'] ?? 'FFFFFF'); $bgc = ds_chart_hex($o['bgc'] ?? '1B2A4A');
    $mc = ds_chart_hex($o['mc'] ?? ds_chart_mix($tc, $bgc, 0.35));
    $cols = ! empty($o['colors']) && is_array($o['colors']) ? array_map('ds_chart_hex', array_values($o['colors'])) : ds_chart_palette('4A9EFF', ds_chart_lum($bgc) < 0.5, max($n, 1));
    while ( count($cols) < $n ) $cols = array_merge($cols, $cols);
    $O = array('tc'=>$tc, 'mc'=>$mc, 'bgc'=>$bgc, 'w'=>$w, 'h'=>$h);
    if ( ! $n ) return ds_chart_tb($x, $y, $w, $h, array(array('Add data to see the chart', 18, $mc, false)), array('align'=>'ctr'));
    $labels = array_map(function($r){ return $r['l']; }, $rows); $vals = array_map(function($r){ return $r['v']; }, $rows);
    $fmt = ds_chart_numfmt($d['unit']);
    $out = '';

    if ( $d['kind'] === 'pie' ) {
        $legend = $o['legend'] ?? 'right';
        $g = ds_chart_pie_geo($w, $h, $d['style'], $legend, $n); $tot = array_sum($vals) ?: 1;
        $half = $d['style'] === 'half'; $dough = in_array($d['style'], array('donut','ring','half'), true);
        $fw = $w; $fh = $half ? max($h, $g['cy'] + $g['R'] + 2) : $h;
        $L = $labels; $V = $vals; $sheet = array(array('', 'Value'));
        foreach ( $rows as $r ) $sheet[] = array($r['l'], $r['v']);
        if ( $half ) { $L[] = ' '; $V[] = $tot; $sheet[] = array(' ', array('f' => 'SUM(B2:B' . ($n + 1) . ')', 'v' => $tot)); }
        $span = $half ? 180 : 360; $dpts = ''; $dl = '';
        foreach ( $V as $i => $v ) {
            if ( $half && $i === $n ) { $dpts .= '<c:dPt><c:idx val="' . $i . '"/><c:bubble3D val="0"/><c:spPr><a:noFill/><a:ln><a:noFill/></a:ln></c:spPr></c:dPt>'; $dl .= '<c:dLbl><c:idx val="' . $i . '"/><c:delete val="1"/></c:dLbl>'; continue; }
            $c = $cols[$i % count($cols)];
            $dpts .= '<c:dPt><c:idx val="' . $i . '"/><c:bubble3D val="0"/><c:spPr>' . ds_chart_fill($c) . ( $d['style'] === 'exploded' ? '<a:ln><a:noFill/></a:ln>' : '<a:ln w="' . (int) max(19050, $g['R']*0.012*9525) . '">' . ds_chart_fill($bgc) . '</a:ln>' ) . '</c:spPr></c:dPt>';
            $sw = $v / $tot * $span;
            if ( $d['style'] === 'ring' || $sw < ( $half ? 11 : 16 ) ) { $dl .= '<c:dLbl><c:idx val="' . $i . '"/><c:delete val="1"/></c:dLbl>'; continue; }
            $R = $g['R']; $ri = $g['ri'] * $R; $fs = max(12, min(26, $R * ( $ri ? ($R-$ri)/$R*0.34 : 0.13 )));
            $dl .= '<c:dLbl><c:idx val="' . $i . '"/>' . ( $d['unit'] === '%' ? '<c:numFmt formatCode="' . ds_chart_x($fmt) . '" sourceLinked="0"/>' : '<c:numFmt formatCode="0%" sourceLinked="0"/>' )
                 . '<c:spPr><a:noFill/><a:ln><a:noFill/></a:ln></c:spPr>' . ds_chart_txpr($fs, ds_chart_on($c), true) . ( $dough ? '' : '<c:dLblPos val="ctr"/>' )
                 . '<c:showLegendKey val="0"/><c:showVal val="' . ( $d['unit'] === '%' ? 1 : 0 ) . '"/><c:showCatName val="0"/><c:showSerName val="0"/><c:showPercent val="' . ( $d['unit'] === '%' ? 0 : 1 ) . '"/><c:showBubbleSize val="0"/></c:dLbl>';
        }
        $dls = '<c:dLbls>' . $dl . '<c:spPr><a:noFill/><a:ln><a:noFill/></a:ln></c:spPr><c:showLegendKey val="0"/><c:showVal val="0"/><c:showCatName val="0"/><c:showSerName val="0"/><c:showPercent val="0"/><c:showBubbleSize val="0"/>' . ( $dough ? '' : '<c:showLeaderLines val="0"/>' ) . '</c:dLbls>';
        $ser = '<c:ser><c:idx val="0"/><c:order val="0"/>' . ds_chart_tx('$B$1', 'Value') . ( $d['style'] === 'exploded' ? '<c:explosion val="6"/>' : '' ) . $dpts . $dls . ds_chart_cat($L) . ds_chart_val($V, 'B', 2, $fmt) . '</c:ser>';
        $plot = ds_chart_manual(($g['cx'] - $g['R'])/$fw, ($g['cy'] - $g['R'])/$fh, 2*$g['R']/$fw, 2*$g['R']/$fh)
              . ( $dough ? '<c:doughnutChart><c:varyColors val="1"/>' . $ser . '<c:firstSliceAng val="' . ( $half ? 270 : $d['angle'] ) . '"/><c:holeSize val="' . (int) round($g['ri']*100) . '"/></c:doughnutChart>'
                         : '<c:pieChart><c:varyColors val="1"/>' . $ser . '<c:firstSliceAng val="' . $d['angle'] . '"/></c:pieChart>' );
        /* legend drawn as shapes (like the preview): a native legend cannot show the values */
        $leg = '';
        if ( $legend === 'right' ) {
            $rh = min(52, $h/max($n,1)); $lfs = max(14, min(22, $rh*0.46)); $y0 = ($h - $rh*$n)/2; $vw = 110;
            $lu = $d['unit'] === '%' ? '%' : $d['unit'];
            foreach ( $rows as $i => $r ) {
                $yy = $y + $y0 + $i*$rh;
                $out .= ds_chart_rect($x + $g['aw'] + 8, $yy + $rh/2 - $lfs*0.42, $lfs*0.84, $lfs*0.84, $cols[$i % count($cols)], null, $lfs*0.22);
                $out .= ds_chart_tb($x + $g['aw'] + 8 + $lfs*1.35, $yy, max(40, $g['LW'] - $lfs*1.35 - 8 - $vw), $rh, array(array(mb_strimwidth($r['l'], 0, 26, '…'), $lfs, $tc, false)));
                $out .= ds_chart_tb($x + $w - 4 - $vw, $yy, $vw, $rh, array(array(ds_chart_fmt($r['v'], $lu), $lfs, $tc, true)), array('align'=>'r'));
            }
        } elseif ( $legend === 'bottom' ) {
            $per = min($n, 3); $rows2 = (int) ceil($n/$per); $cw = $w/$per; $lh = min(36, $g['LH']/max($rows2,1)); $lfs = max(14, min(20, $lh*0.6));
            foreach ( $rows as $i => $r ) {
                $cx = $x + ($i % $per)*$cw; $yy = $y + $g['ah'] + ($g['LH'] - $lh*$rows2)/2 + floor($i/$per)*$lh;
                $out .= ds_chart_rect($cx + 14 - $lfs*0.38, $yy + $lh/2 - $lfs*0.38, $lfs*0.76, $lfs*0.76, $cols[$i % count($cols)], null, $lfs*0.38);
                $out .= ds_chart_tb($cx + 14 + $lfs*0.9, $yy, $cw - 14 - $lfs*0.9 - 6, $lh, array(array(mb_strimwidth($r['l'], 0, 20, '…'), $lfs, $tc, false)));
            }
        }
        $k = ds_chart_queue(ds_chart_space($plot, $leg, $O), ds_chart_xlsx($sheet));
        $out .= ds_chart_frame_xml($k, $x, $y, $fw, $fh);
        if ( $g['ri'] ) {   /* centre label: the biggest slice */
            $big = 0; foreach ( $rows as $i => $r ) if ( $r['v'] > $rows[$big]['v'] ) $big = $i;
            $ri = $g['ri'] * $g['R']; $cfs = max(16, $ri*0.42); $cyv = $half ? $g['cy'] - $ri*0.32 : $g['cy'] - $ri*0.1;
            $val = $d['unit'] === '%' ? ds_chart_fmt($rows[$big]['v'], '%') : round($rows[$big]['v']/$tot*100) . '%';
            $out .= ds_chart_tb($x + $g['cx'] - $ri*0.95, $y + $cyv - $cfs*0.62, $ri*1.9, $cfs*1.24, array(array($val, $cfs, $tc, true)), array('align'=>'ctr'));
            $out .= ds_chart_tb($x + $g['cx'] - $ri*0.95, $y + $cyv + $cfs*0.78 - max(11, $cfs*0.36)*0.7, $ri*1.9, max(11, $cfs*0.36)*1.4, array(array(mb_strimwidth($rows[$big]['l'], 0, 18, '…'), max(11, $cfs*0.36), $mc, true)), array('align'=>'ctr'));
        }
        return $out;
    }

    if ( $d['kind'] === 'bar' || $d['kind'] === 'line' ) {
        $mx = 0; foreach ( $vals as $v ) $mx = max($mx, $v); $top = ds_chart_nice_max($mx); $ac = $cols[0];
        $fs = max(15, min(21, $h*0.046));
        $sheet = array(array('', 'Value')); foreach ( $rows as $r ) $sheet[] = array($r['l'], $r['v']);
        $dls = function($pos, $bold = true) use ($fmt, $fs, $tc) { return '<c:dLbls><c:numFmt formatCode="' . ds_chart_x($fmt) . '" sourceLinked="0"/><c:spPr><a:noFill/><a:ln><a:noFill/></a:ln></c:spPr>' . ds_chart_txpr($fs, $tc, $bold) . '<c:dLblPos val="' . $pos . '"/><c:showLegendKey val="0"/><c:showVal val="1"/><c:showCatName val="0"/><c:showSerName val="0"/><c:showPercent val="0"/><c:showBubbleSize val="0"/></c:dLbls>'; };
        if ( $d['kind'] === 'bar' ) {
            $dir = $d['style'] === 'bar' ? 'bar' : 'col';
            $grad = '<a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:srgbClr val="' . ( $dir === 'bar' ? ds_chart_mix($ac, $bgc, 0.25) : $ac ) . '"/></a:gs><a:gs pos="100000"><a:srgbClr val="' . ( $dir === 'bar' ? $ac : ds_chart_mix($ac, $bgc, 0.25) ) . '"/></a:gs></a:gsLst><a:lin ang="' . ( $dir === 'bar' ? 0 : 5400000 ) . '" scaled="0"/></a:gradFill>';
            $ser = '<c:ser><c:idx val="0"/><c:order val="0"/>' . ds_chart_tx('$B$1', 'Value') . '<c:spPr>' . $grad . '<a:ln><a:noFill/></a:ln></c:spPr><c:invertIfNegative val="0"/>' . $dls('outEnd') . ds_chart_cat($labels) . ds_chart_val($vals, 'B', 2, $fmt) . '</c:ser>';
            $plot = '<c:layout/><c:barChart><c:barDir val="' . $dir . '"/><c:grouping val="clustered"/><c:varyColors val="0"/>' . $ser . '<c:gapWidth val="' . ( $dir === 'bar' ? 72 : 72 ) . '"/><c:axId val="5001"/><c:axId val="5002"/></c:barChart>' . ds_chart_axes($d, $O, $top, $dir);
        } else {
            $area = $d['style'] === 'area'; $lw = (int) round(max(3, $h*0.011) * 9525);
            $line = '<c:ser><c:idx val="0"/><c:order val="0"/>' . ds_chart_tx('$B$1', 'Value') . '<c:spPr><a:ln w="' . $lw . '" cap="rnd">' . ds_chart_fill($ac) . '<a:round/></a:ln></c:spPr>'
                  . '<c:marker><c:symbol val="circle"/><c:size val="' . (int) max(7, min(12, $h*0.028)) . '"/><c:spPr>' . ds_chart_fill($bgc) . '<a:ln w="' . (int) round(max(2.5, $h*0.008)*9525) . '">' . ds_chart_fill($ac) . '</a:ln></c:spPr></c:marker>'
                  . $dls('t') . ds_chart_cat($labels) . ds_chart_val($vals, 'B', 2, $fmt) . '<c:smooth val="' . ( $d['style'] === 'smooth' ? 1 : 0 ) . '"/></c:ser>';
            $plot = '<c:layout/>';
            if ( $area ) {
                $af = '<a:gradFill rotWithShape="1"><a:gsLst><a:gs pos="0"><a:srgbClr val="' . $ac . '"><a:alpha val="55000"/></a:srgbClr></a:gs><a:gs pos="100000"><a:srgbClr val="' . $ac . '"><a:alpha val="4000"/></a:srgbClr></a:gs></a:gsLst><a:lin ang="5400000" scaled="0"/></a:gradFill>';
                $plot .= '<c:areaChart><c:grouping val="standard"/><c:varyColors val="0"/><c:ser><c:idx val="1"/><c:order val="1"/>' . ds_chart_tx('$B$1', 'Value') . '<c:spPr>' . $af . '<a:ln><a:noFill/></a:ln></c:spPr>' . ds_chart_cat($labels) . ds_chart_val($vals, 'B', 2, $fmt) . '</c:ser><c:axId val="5001"/><c:axId val="5002"/></c:areaChart>';
            }
            $plot .= '<c:lineChart><c:grouping val="standard"/><c:varyColors val="0"/>' . $line . '<c:marker val="1"/><c:axId val="5001"/><c:axId val="5002"/></c:lineChart>' . ds_chart_axes($d, $O, $top, 'col');
        }
        $k = ds_chart_queue(ds_chart_space($plot, '', $O), ds_chart_xlsx($sheet));
        return ds_chart_frame_xml($k, $x, $y, $w, $h);
    }

    /* gauge family */
    $mxg = $d['max']; $track = function() use ($tc) { return ds_chart_fill($tc, 12000); };
    if ( $d['style'] === 'progress' ) {
        $rest = array(); $sheet = array(array('', 'Value', 'Remaining'));
        foreach ( $rows as $i => $r ) { $rv = max(0, $mxg - $r['v']); $rest[] = $rv; $sheet[] = array($r['l'], $r['v'], array('f' => max($mxg, 0) . '-B' . ($i + 2), 'v' => $rv)); }
        $rh = $h / $n; $fs = max(15, min(22, $rh*0.32)); $dp = '';
        foreach ( $rows as $i => $r ) $dp .= '<c:dPt><c:idx val="' . $i . '"/><c:invertIfNegative val="0"/><c:bubble3D val="0"/><c:spPr>' . ds_chart_fill($cols[$i % count($cols)]) . '<a:ln><a:noFill/></a:ln></c:spPr></c:dPt>';
        $s1 = '<c:ser><c:idx val="0"/><c:order val="0"/>' . ds_chart_tx('$B$1', 'Value') . '<c:spPr>' . ds_chart_fill($cols[0]) . '</c:spPr><c:invertIfNegative val="0"/>' . $dp
            . '<c:dLbls><c:numFmt formatCode="' . ds_chart_x($fmt) . '" sourceLinked="0"/><c:spPr><a:noFill/><a:ln><a:noFill/></a:ln></c:spPr>' . ds_chart_txpr($fs*0.9, 'FFFFFF', true) . '<c:dLblPos val="inEnd"/><c:showLegendKey val="0"/><c:showVal val="1"/><c:showCatName val="0"/><c:showSerName val="0"/><c:showPercent val="0"/><c:showBubbleSize val="0"/></c:dLbls>'
            . ds_chart_cat($labels) . ds_chart_val($vals, 'B', 2, $fmt) . '</c:ser>';
        $s2 = '<c:ser><c:idx val="1"/><c:order val="1"/>' . ds_chart_tx('$C$1', 'Remaining') . '<c:spPr>' . $track() . '<a:ln><a:noFill/></a:ln></c:spPr><c:invertIfNegative val="0"/>' . ds_chart_cat($labels) . ds_chart_val($rest, 'C', 2) . '</c:ser>';
        $cat = '<c:catAx><c:axId val="5001"/><c:scaling><c:orientation val="maxMin"/></c:scaling><c:delete val="0"/><c:axPos val="l"/><c:numFmt formatCode="General" sourceLinked="0"/><c:majorTickMark val="none"/><c:minorTickMark val="none"/><c:tickLblPos val="nextTo"/><c:spPr><a:noFill/><a:ln><a:noFill/></a:ln></c:spPr>' . ds_chart_txpr($fs, $tc, true) . '<c:crossAx val="5002"/><c:crosses val="autoZero"/><c:auto val="1"/><c:lblAlgn val="ctr"/><c:lblOffset val="100"/><c:noMultiLvlLbl val="0"/></c:catAx>';
        $val = '<c:valAx><c:axId val="5002"/><c:scaling><c:orientation val="minMax"/></c:scaling><c:delete val="1"/><c:axPos val="b"/><c:numFmt formatCode="0%" sourceLinked="1"/><c:majorTickMark val="none"/><c:minorTickMark val="none"/><c:tickLblPos val="nextTo"/><c:crossAx val="5001"/><c:crosses val="autoZero"/><c:crossBetween val="between"/></c:valAx>';
        $plot = '<c:layout/><c:barChart><c:barDir val="bar"/><c:grouping val="percentStacked"/><c:varyColors val="0"/>' . $s1 . $s2 . '<c:gapWidth val="' . (int) max(50, min(300, ($rh - min($rh*0.36, 26)) / max(1, min($rh*0.36, 26)) * 100)) . '"/><c:overlap val="100"/><c:axId val="5001"/><c:axId val="5002"/></c:barChart>' . $cat . $val;
        $k = ds_chart_queue(ds_chart_space($plot, '', $O), ds_chart_xlsx($sheet));
        return ds_chart_frame_xml($k, $x, $y, $w, $h);
    }
    if ( $d['style'] === 'rings' ) {
        $m = min($n, 6); $LW = min($w*0.44, 320); $aw = $w - $LW; $R = min($aw, $h)/2*0.94; $cx = $aw/2; $cy = $h/2;
        $band = $R*0.72/$m; $th = $band*0.74;
        $sheet = array(array(''), array('Value'), array('Remaining')); $sers = '';
        for ( $j = 0; $j < $m; $j++ ) {           /* innermost ring first in PowerPoint */
            $i = $m - 1 - $j; $r = $rows[$i]; $col = chr(66 + $j); $rv = max(0, $mxg - $r['v']);
            $sheet[0][] = $r['l']; $sheet[1][] = $r['v']; $sheet[2][] = array('f' => $mxg . '-' . $col . '2', 'v' => $rv);
            $sers .= '<c:ser><c:idx val="' . $j . '"/><c:order val="' . $j . '"/>' . ds_chart_tx('$' . $col . '$1', $r['l'])
                   . '<c:spPr><a:ln w="' . (int) round(($band - $th)*9525) . '">' . ds_chart_fill($bgc) . '</a:ln></c:spPr>'
                   . '<c:dPt><c:idx val="0"/><c:bubble3D val="0"/><c:spPr>' . ds_chart_fill($cols[$i % count($cols)]) . '<a:ln w="' . (int) round(($band - $th)*9525) . '">' . ds_chart_fill($bgc) . '</a:ln></c:spPr></c:dPt>'
                   . '<c:dPt><c:idx val="1"/><c:bubble3D val="0"/><c:spPr>' . $track() . '<a:ln w="' . (int) round(($band - $th)*9525) . '">' . ds_chart_fill($bgc) . '</a:ln></c:spPr></c:dPt>'
                   . ds_chart_cat(array('Value', 'Remaining'), 'A', 2) . ds_chart_val(array($r['v'], $rv), $col, 2, $fmt) . '</c:ser>';
        }
        $dl0 = '<c:dLbls><c:showLegendKey val="0"/><c:showVal val="0"/><c:showCatName val="0"/><c:showSerName val="0"/><c:showPercent val="0"/><c:showBubbleSize val="0"/><c:showLeaderLines val="0"/></c:dLbls>';
        $plot = ds_chart_manual(($cx - $R)/$w, ($cy - $R)/$h, 2*$R/$w, 2*$R/$h) . '<c:doughnutChart><c:varyColors val="0"/>' . $sers . $dl0 . '<c:firstSliceAng val="0"/><c:holeSize val="' . (int) max(10, min(90, round((1 - 0.72) * 100))) . '"/></c:doughnutChart>';
        $out .= ds_chart_frame_xml(ds_chart_queue(ds_chart_space($plot, '', $O), ds_chart_xlsx($sheet)), $x, $y, $aw, $h);
        /* legend with values (text, like the preview) */
        $rh = min(52, $h/$m); $fs = max(14, min(22, $rh*0.46)); $y0 = ($h - $rh*$m)/2;
        for ( $i = 0; $i < $m; $i++ ) {
            $yy = $y + $y0 + $i*$rh;
            $out .= ds_chart_rect($x + $aw + 8, $yy + $rh/2 - $fs*0.42, $fs*0.84, $fs*0.84, $cols[$i % count($cols)], null, $fs*0.22);
            $out .= ds_chart_tb($x + $aw + 8 + $fs*1.35, $yy, $LW - $fs*1.35 - 90, $rh, array(array($rows[$i]['l'], $fs, $tc, false)));
            $out .= ds_chart_tb($x + $w - 94, $yy, 90, $rh, array(array(ds_chart_fmt($rows[$i]['v'], $d['unit']), $fs, $tc, true)), array('align'=>'r'));
        }
        return $out;
    }
    /* gauges: one half-doughnut per item */
    $G = ds_chart_gauge_geo($w, $h, $n);
    for ( $i = 0; $i < $G['n']; $i++ ) {
        $r = $rows[$i]; $cx = $G['cw']*$i + $G['cw']/2; $R = $G['R']; $rv = max(0, $mxg - max(0, $r['v'])); $vv = max(0, min($mxg, $r['v']));
        $sheet = array(array('', $r['l']), array('Value', $r['v']), array('Remaining', array('f' => $mxg . '-B2', 'v' => $rv)), array(' ', $mxg));
        $ser = '<c:ser><c:idx val="0"/><c:order val="0"/>' . ds_chart_tx('$B$1', $r['l'])
             . '<c:dPt><c:idx val="0"/><c:bubble3D val="0"/><c:spPr>' . ds_chart_fill($cols[$i % count($cols)]) . '<a:ln><a:noFill/></a:ln></c:spPr></c:dPt>'
             . '<c:dPt><c:idx val="1"/><c:bubble3D val="0"/><c:spPr>' . $track() . '<a:ln><a:noFill/></a:ln></c:spPr></c:dPt>'
             . '<c:dPt><c:idx val="2"/><c:bubble3D val="0"/><c:spPr><a:noFill/><a:ln><a:noFill/></a:ln></c:spPr></c:dPt>'
             . ds_chart_cat(array('Value', 'Remaining', ' ')) . ds_chart_val(array($vv, $rv, $mxg), 'B', 2) . '</c:ser>';
        $plot = ds_chart_manual(0, 0, 1, 1) . '<c:doughnutChart><c:varyColors val="1"/>' . $ser . '<c:dLbls><c:showLegendKey val="0"/><c:showVal val="0"/><c:showCatName val="0"/><c:showSerName val="0"/><c:showPercent val="0"/><c:showBubbleSize val="0"/><c:showLeaderLines val="0"/></c:dLbls><c:firstSliceAng val="270"/><c:holeSize val="' . (int) round((1 - $G['th']/$R)*100) . '"/></c:doughnutChart>';
        $out .= ds_chart_frame_xml(ds_chart_queue(ds_chart_space($plot, '', $O), ds_chart_xlsx($sheet)), $x + $cx - $R, $y + $G['cy'] - $R, 2*$R, 2*$R);
        $vf = $R*0.34; $lf = max(15, min(24, $R*0.17));
        $out .= ds_chart_tb($x + $cx - $R, $y + $G['cy'] - $R*0.2 - $vf*0.62, 2*$R, $vf*1.24, array(array(ds_chart_fmt($r['v'], $d['unit']), $vf, $tc, true)), array('align'=>'ctr'));
        $out .= ds_chart_tb($x + $cx - $G['cw']/2 + 6, $y + $G['cy'] + $R*0.22 - $lf*0.7, $G['cw'] - 12, $lf*1.4, array(array($r['l'], $lf, $tc, true, 85000)), array('align'=>'ctr'));
    }
    return $out;
}

/* ============================================================
 * Classic deck layouts (snippet 16 calls ds_pptv2_chart_<variant>)
 * twin of DS_CHART.boxes / renderClassic
 * ============================================================ */
function ds_chart_points( $s ) {
    $o = array(); foreach ( (array) ($s['content'] ?? array()) as $x ) { $x = trim(wp_strip_all_tags((string) $x)); if ( $x !== '' ) $o[] = $x; if ( count($o) >= 4 ) break; }
    return $o;
}
function ds_chart_split( $x ) { $i = mb_strpos($x, ':'); return ( $i > 0 && $i < 80 ) ? array(trim(mb_substr($x, 0, $i)), trim(mb_substr($x, $i + 1))) : array(trim($x), ''); }
function ds_chart_classic_boxes( $v, $s ) {
    $d = ds_chart_norm($s['chart'] ?? array()); $P = ds_chart_points($s); $has = count($P) > 0; $pie = $d['kind'] === 'pie';
    $strip = trim((string) ($s['conclusion'] ?? ''));
    $b = array('title' => array('x'=>64, 'y'=>40, 'w'=>1152, 'align'=>'l'), 'panel' => null);
    $lg = $pie || $d['kind'] === 'gauge' ? 'legend' : 'kpi';
    if ( $v === 'n2' ) { $b['chart'] = array('x'=>560,'y'=>168,'w'=>680,'h'=>500,'legend'=> $pie ? ( $has ? 'bottom' : 'none' ) : 'none'); $b['panel'] = array('x'=>64,'y'=>182,'w'=>468,'h'=>476,'mode'=> $has ? 'points' : $lg); }
    elseif ( $v === 'n3' ) { $b['title']['align'] = 'c'; $b['chart'] = array('x'=>170,'y'=>162,'w'=>940,'h'=> $has ? 410 : 500,'legend'=> $pie ? 'right' : 'none'); if ( $has ) $b['panel'] = array('x'=>64,'y'=>596,'w'=>1152,'h'=>100,'mode'=>'cols'); }
    elseif ( $v === 'n4' ) { $b['panel'] = array('x'=>64,'y'=>176,'w'=>330,'h'=>490,'mode'=>'kpi'); $b['chart'] = array('x'=>430,'y'=>168,'w'=>800,'h'=>500,'legend'=> $pie ? 'bottom' : 'none'); }
    elseif ( $v === 'n5' ) { $st = $strip !== '' ? $strip : ( $has ? $P[0] : '' ); $b['chart'] = array('x'=>64,'y'=>160,'w'=>1152,'h'=> $st !== '' ? 456 : 520,'legend'=> $pie ? 'right' : 'none'); if ( $st !== '' ) $b['panel'] = array('x'=>64,'y'=>634,'w'=>1152,'h'=>58,'mode'=>'strip','text'=>$st); }
    else { $b['chart'] = array('x'=>40,'y'=>168,'w'=>680,'h'=>500,'legend'=> $pie ? ( $has ? 'bottom' : 'none' ) : 'none'); $b['panel'] = array('x'=>748,'y'=>182,'w'=>468,'h'=>476,'mode'=> $has ? 'points' : $lg); }
    if ( in_array($d['style'], array('rings','progress','gauge'), true) ) $b['chart']['legend'] = 'none';
    return $b;
}
function ds_chart_classic_body( $s, $v, $tc, $bc, $ac, $bg ) {
    $GLOBALS['ds_chart_sid'] = 7000;
    $tc = ds_chart_hex($tc, 'FFFFFF'); $bc = ds_chart_hex($bc, $tc); $ac = ds_chart_hex($ac); $bg = ds_chart_hex($bg, '1B2A4A');
    $d = ds_chart_norm($s['chart'] ?? array()); $b = ds_chart_classic_boxes($v, $s);
    $cols = ds_chart_palette($ac, ds_chart_lum($bg) < 0.5, max(count($d['rows']), 3)); $mc = ds_chart_mix($bc, $bg, 0.25);
    $x = ds_chart_rect(0, 0, 1280, 3, $ac);
    $al = $b['title']['align'] === 'c' ? 'ctr' : 'l';
    $paras = array(array(wp_strip_all_tags((string) ($s['title'] ?? '')), 36, $tc, true));
    if ( trim((string) ($s['subtitle'] ?? '')) !== '' ) $paras[] = array(wp_strip_all_tags((string) $s['subtitle']), 17, $ac, true, null, 500);
    $x .= ds_chart_tb($b['title']['x'], $b['title']['y'], $b['title']['w'], 116, $paras, array('align'=>$al, 'anchor'=>'t', 'ln'=>105000));
    $c = $b['chart'];
    $x .= ds_chart_frames($c['x'], $c['y'], $c['w'], $c['h'], $s['chart'] ?? array(), array('colors'=>$cols, 'tc'=>$tc, 'mc'=>$mc, 'bgc'=>$bg, 'legend'=>$c['legend']));
    $p = $b['panel'];
    if ( ! $p ) return $x;
    $P = ds_chart_points($s);
    if ( $p['mode'] === 'points' ) {
        $n = count($P); $gap = 14; $ch = ($p['h'] - $gap*($n-1)) / $n;
        foreach ( $P as $i => $pt ) {
            $yy = $p['y'] + $i*($ch + $gap); $sp = ds_chart_split($pt);
            $x .= ds_chart_rect($p['x'], $yy, $p['w'], $ch, $tc, 6000, 12);
            $x .= ds_chart_rect($p['x'], $yy + $ch*0.18, 6, $ch*0.64, $cols[$i % count($cols)], null, 3);
            $pp = array(array($sp[0], $n > 3 ? 19 : 22, $tc, true)); if ( $sp[1] !== '' ) $pp[] = array($sp[1], $n > 3 ? 15 : 16.5, $bc, false, null, 300);
            $x .= ds_chart_tb($p['x'] + 26, $yy + 8, $p['w'] - 44, $ch - 16, $pp);
        }
    } elseif ( $p['mode'] === 'legend' || $p['mode'] === 'kpi' ) {
        $rows = array(); foreach ( $d['rows'] as $i => $r ) $rows[] = array('r'=>$r, 'i'=>$i);
        if ( $p['mode'] === 'kpi' ) { usort($rows, function($a, $b){ return $b['r']['v'] <=> $a['r']['v']; }); $rows = array_slice($rows, 0, 3); } else $rows = array_slice($rows, 0, 7);
        $m = max(1, count($rows)); $one = in_array($d['kind'], array('bar','line'), true);
        $rh = $p['mode'] === 'kpi' ? min(150, $p['h']/$m) : min(64, $p['h']/$m); $y0 = $p['y'] + ($p['h'] - $rh*$m)/2;
        foreach ( $rows as $k => $o ) {
            $yy = $y0 + $k*$rh; $val = ds_chart_fmt($o['r']['v'], $d['unit']); $col = $cols[$one ? 0 : $o['i'] % count($cols)];
            if ( $k ) $x .= ds_chart_rect($p['x'], $yy, $p['w'], 1, $tc, 14000);
            if ( $p['mode'] === 'kpi' ) {
                $x .= ds_chart_tb($p['x'], $yy + 8, $p['w'], $rh - 16, array(array($val, $m > 2 ? 46 : 54, $col, true), array($o['r']['l'], 17, $bc, true, null, 300)), array('ln'=>100000));
            } else {
                $x .= ds_chart_rect($p['x'], $yy + $rh/2 - 8, 16, 16, $col, null, 5);
                $x .= ds_chart_tb($p['x'] + 30, $yy, $p['w'] - 150, $rh, array(array($o['r']['l'], 18, $tc, true)));
                $x .= ds_chart_tb($p['x'] + $p['w'] - 120, $yy, 120, $rh, array(array($val, $m > 5 ? 22 : 26, $tc, true)), array('align'=>'r'));
            }
        }
    } elseif ( $p['mode'] === 'cols' ) {
        $P3 = array_slice($P, 0, 3); $cw = ($p['w'] - 28*(count($P3)-1)) / max(1, count($P3));
        foreach ( $P3 as $i => $pt ) {
            $xx = $p['x'] + $i*($cw + 28); $sp = ds_chart_split($pt);
            $x .= ds_chart_rect($xx, $p['y'], $cw, 4, $cols[$i % count($cols)]);
            $x .= ds_chart_tb($xx, $p['y'] + 14, $cw, $p['h'] - 14, array(array($sp[0], 19, $tc, true), array($sp[1], 15.5, $bc, false, null, 200)), array('anchor'=>'t'));
        }
    } elseif ( $p['mode'] === 'strip' ) {
        $x .= ds_chart_rect($p['x'], $p['y'], $p['w'], $p['h'], $ac, 14000, 12);
        $x .= ds_chart_rect($p['x'], $p['y'] + $p['h']*0.2, 6, $p['h']*0.6, $ac, null, 3);
        $x .= ds_chart_tb($p['x'] + 28, $p['y'], $p['w'] - 50, $p['h'], array(array($p['text'], 18, $tc, true)));
    }
    return $x;
}
if ( ! function_exists('ds_pptv2_chart_v1') ) { function ds_pptv2_chart_v1( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'v1', $tc, $bc, $ac, $bg); } }
if ( ! function_exists('ds_pptv2_chart_n2') ) { function ds_pptv2_chart_n2( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'n2', $tc, $bc, $ac, $bg); } }
if ( ! function_exists('ds_pptv2_chart_n3') ) { function ds_pptv2_chart_n3( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'n3', $tc, $bc, $ac, $bg); } }
if ( ! function_exists('ds_pptv2_chart_n4') ) { function ds_pptv2_chart_n4( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'n4', $tc, $bc, $ac, $bg); } }
if ( ! function_exists('ds_pptv2_chart_n5') ) { function ds_pptv2_chart_n5( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'n5', $tc, $bc, $ac, $bg); } }
if ( ! function_exists('ds_pptv2_chart_v2') ) { function ds_pptv2_chart_v2( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'v1', $tc, $bc, $ac, $bg); } }
if ( ! function_exists('ds_pptv2_chart_v3') ) { function ds_pptv2_chart_v3( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'v1', $tc, $bc, $ac, $bg); } }
if ( ! function_exists('ds_pptv2_chart_v4') ) { function ds_pptv2_chart_v4( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'v1', $tc, $bc, $ac, $bg); } }
if ( ! function_exists('ds_pptv2_chart_v5') ) { function ds_pptv2_chart_v5( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'v1', $tc, $bc, $ac, $bg); } }
if ( ! function_exists('ds_pptv2_chart_v6') ) { function ds_pptv2_chart_v6( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'v1', $tc, $bc, $ac, $bg); } }
if ( ! function_exists('ds_pptv2_chart_v7') ) { function ds_pptv2_chart_v7( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'v1', $tc, $bc, $ac, $bg); } }
if ( ! function_exists('ds_pptv2_chart_v8') ) { function ds_pptv2_chart_v8( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'v1', $tc, $bc, $ac, $bg); } }
if ( ! function_exists('ds_pptv2_chart_v9') ) { function ds_pptv2_chart_v9( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'v1', $tc, $bc, $ac, $bg); } }
if ( ! function_exists('ds_pptv2_chart_v10') ) { function ds_pptv2_chart_v10( $slide, $tc, $bc, $ac, $image_rid = null, $icons = array(), $bg = '1B2A4A', $bg2 = null ) { return ds_chart_classic_body($slide, 'v1', $tc, $bc, $ac, $bg); } }

/* ============================================================
 * add the queued chart parts to the finished .pptx
 * ============================================================ */
function ds_chart_finalize( $fpath ) {
    $q = isset($GLOBALS['ds_chart_q']) ? $GLOBALS['ds_chart_q'] : array();
    if ( ! $q || ! file_exists($fpath) || ! class_exists('ZipArchive') ) return false;
    $z = new ZipArchive(); if ( $z->open($fpath) !== true ) return false;
    $ct = $z->getFromName('[Content_Types].xml'); $ov = '';
    foreach ( $q as $i => $c ) {
        $k = $i + 1;
        $z->addFromString('ppt/charts/chart' . $k . '.xml', $c['xml']);
        $rel = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        if ( $c['xlsx'] ) {
            $z->addFromString('ppt/embeddings/Microsoft_Excel_Worksheet' . $k . '.xlsx', $c['xlsx']);
            $rel .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/package" Target="../embeddings/Microsoft_Excel_Worksheet' . $k . '.xlsx"/>';
        }
        $z->addFromString('ppt/charts/_rels/chart' . $k . '.xml.rels', $rel . '</Relationships>');
        if ( ! $c['xlsx'] ) $z->addFromString('ppt/charts/chart' . $k . '.xml', str_replace('<c:externalData r:id="rId1"><c:autoUpdate val="0"/></c:externalData>', '', $c['xml']));
        $ov .= '<Override PartName="/ppt/charts/chart' . $k . '.xml" ContentType="application/vnd.openxmlformats-officedocument.drawingml.chart+xml"/>';
    }
    if ( $ct ) {
        if ( strpos($ct, 'Extension="xlsx"') === false ) $ov = '<Default Extension="xlsx" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"/>' . $ov;
        $z->addFromString('[Content_Types].xml', str_replace('</Types>', $ov . '</Types>', $ct));
    }
    for ( $i = 0; $i < $z->numFiles; $i++ ) {
        $nm = $z->getNameIndex($i);
        if ( ! preg_match('#^ppt/slides/slide(\d+)\.xml$#', $nm, $mm) ) continue;
        $sx = $z->getFromIndex($i);
        if ( ! preg_match_all('/r:id="rIdDsc(\d+)"/', $sx, $ms) ) continue;
        $rn = 'ppt/slides/_rels/slide' . $mm[1] . '.xml.rels';
        $rx = $z->getFromName($rn);
        if ( ! $rx ) $rx = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"></Relationships>';
        $add = '';
        foreach ( array_unique($ms[1]) as $k ) if ( strpos($rx, 'Id="rIdDsc' . $k . '"') === false ) $add .= '<Relationship Id="rIdDsc' . $k . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/chart" Target="../charts/chart' . $k . '.xml"/>';
        if ( $add ) $z->addFromString($rn, str_replace('</Relationships>', $add . '</Relationships>', $rx));
    }
    $z->close();
    $GLOBALS['ds_chart_q'] = array();
    return true;
}
function ds_chart_after_build( $res ) {
    if ( empty($GLOBALS['ds_chart_q']) || is_wp_error($res) ) return $res;
    $d = ( $res instanceof WP_REST_Response ) ? $res->get_data() : $res;
    if ( ! is_array($d) || empty($d['success']) || empty($d['url']) ) return $res;
    $up = wp_upload_dir();
    $path = str_replace($up['baseurl'], $up['basedir'], $d['url']);
    if ( substr($path, -5) === '.pptx' ) {
        $nq = count($GLOBALS['ds_chart_q']);
        if ( ds_chart_finalize($path) ) {                  /* report what went into the file */
            $z = new ZipArchive(); $linked = 0;
            if ( $z->open($path) === true ) { for ( $i = 0; $i < $z->numFiles; $i++ ) { $nm = $z->getNameIndex($i); if ( preg_match('#^ppt/slides/_rels/#', $nm) ) $linked += substr_count($z->getFromIndex($i), 'relationships/chart"'); } $z->close(); }
            $d['native_charts'] = array('parts' => $nq, 'linked' => $linked);
            if ( $res instanceof WP_REST_Response ) $res->set_data($d); else $res = $d;
        }
    }
    return $res;
}
/* Classic decks: runs before "DS PPT R2 Storage" (priority 10), which moves the file to R2 */
add_filter('rest_request_after_callbacks', function ( $res, $handlers, $req ) { return ds_chart_after_build($res); }, 5, 3);
/* Template decks are answered in rest_pre_dispatch, which skips the filter above */
add_filter('rest_post_dispatch', function ( $res ) { return ds_chart_after_build($res); }, 1, 1);

/* ============================================================
 * REST: POST /ds/v1/chart-slide
 * ============================================================ */
function ds_chart_cost() {
    if ( function_exists('ds_ppt_add_slide_cost') ) return ds_ppt_add_slide_cost();
    $opt = get_option('ds_ppt_credit_settings');
    $rate = ( is_array($opt) && isset($opt['extra_slide_rate']) && $opt['extra_slide_rate'] !== '' ) ? (int) $opt['extra_slide_rate'] : 1;
    return (int) apply_filters('ds_credits_per_slide', max(1, $rate), 'ai');
}
function ds_chart_credits( $uid ) { return function_exists('ds_get_credits') ? (int) ds_get_credits($uid) : (int) get_user_meta($uid, 'ds_ai_credits', true); }
function ds_chart_ai( $sys, $prompt ) {
    $keys = function_exists('ds_get_api_keys') ? ds_get_api_keys() : array();
    $key = isset($keys['groq']) ? $keys['groq'] : '';
    if ( ! $key ) return new WP_Error('nokey', 'AI key missing.');
    $resp = wp_remote_post('https://api.groq.com/openai/v1/chat/completions', array(
        'timeout' => 60,
        'headers' => array('Authorization' => 'Bearer ' . $key, 'Content-Type' => 'application/json'),
        'body' => wp_json_encode(array('model' => get_option('ds_groq_ppt_model', 'llama-3.3-70b-versatile'),
            'messages' => array(array('role'=>'system','content'=>$sys), array('role'=>'user','content'=>$prompt)),
            'temperature' => 0.7, 'max_tokens' => 1600, 'response_format' => array('type' => 'json_object'))),
    ));
    if ( is_wp_error($resp) ) return $resp;
    $data = json_decode(wp_remote_retrieve_body($resp), true);
    $c = trim((string) ($data['choices'][0]['message']['content'] ?? ''));
    $c = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $c);
    $j = json_decode($c, true);
    return is_array($j) ? $j : new WP_Error('parse', 'AI response could not be read.');
}
function ds_chart_rest( WP_REST_Request $req ) {
    $uid = get_current_user_id();
    if ( ! $uid ) return new WP_REST_Response(array('success'=>false,'message'=>'Please log in.'), 401);
    $cost = ds_chart_cost();
    if ( ds_chart_credits($uid) < $cost ) return array('success'=>false,'error'=>'Not enough credits','message'=>'You need ' . $cost . ' credits.');
    $k = 'ds_chart_rl_' . $uid; $cnt = (int) get_transient($k);
    if ( $cnt >= 8 ) return array('success'=>false,'message'=>'Too many chart slides in a minute. Please wait a moment.');
    set_transient($k, $cnt + 1, 60);

    $topic = mb_substr(trim(wp_strip_all_tags((string) $req->get_param('topic'))), 0, 400);
    $notes = mb_substr(trim(wp_strip_all_tags((string) $req->get_param('notes'))), 0, 1500);
    $deck  = mb_substr(trim(wp_strip_all_tags((string) $req->get_param('deck_title'))), 0, 200);
    $lang  = sanitize_text_field($req->get_param('language') ?: 'English');
    $chart = ds_chart_clean($req->get_param('chart'));
    $has = count($chart['values']) > 0;
    if ( ! $has && $topic === '' ) return array('success'=>false,'message'=>'Tell us what the chart is about, or add some data.');
    $fam = ds_chart_fam($chart['style']);
    $names = array('pie'=>'pie / donut chart (shares of a whole)', 'bar'=>'bar / column chart (comparison)', 'line'=>'line / area chart (trend over time)', 'gauge'=>'progress / gauge chart (each value out of ' . ( $chart['max'] ?: 100 ) . ')');

    $p  = "Write the text for ONE presentation slide built around a {$names[$fam]}.\n";
    $p .= "Language for all text: {$lang}.\n";
    if ( $deck !== '' ) $p .= "Presentation: {$deck}\n";
    if ( $topic !== '' ) $p .= "Chart topic: {$topic}\n";
    if ( $has ) {
        $p .= "Chart data (use exactly this, do not change numbers):\n";
        foreach ( $chart['labels'] as $i => $l ) $p .= "- {$l}: " . ds_chart_fmt($chart['values'][$i], $chart['unit']) . "\n";
    } else {
        $p .= "No data was given: create 3 to 6 realistic, specific data points that fit the topic. ";
        $p .= $fam === 'pie' ? "Values are percentages that add up to exactly 100.\n" : ( $fam === 'gauge' ? "Values are percentages between 5 and 100.\n" : ( $fam === 'line' ? "Labels are time periods in order (years, quarters or months).\n" : "Labels are the things being compared.\n" ) );
        if ( $fam !== 'pie' && $fam !== 'gauge' ) $p .= "Values are measured in: " . ( $chart['unit'] !== '' ? $chart['unit'] : 'plain numbers' ) . ". Give plain numbers only (for example 4000000, not 4M), showing the actual amounts, not growth rates.\n";
    }
    if ( $notes !== '' ) $p .= "Extra text from the user (use it, keep its facts):\n{$notes}\n";
    $p .= "\nReturn JSON only:\n{\"title\":\"slide title, max 8 words\",\"subtitle\":\"one line, max 14 words, states the main finding\",\"points\":[\"Heading of 2-4 words: explanation of 14-24 words that cites the numbers\",\"...\",\"...\"],\"conclusion\":\"one short takeaway sentence\"" . ( $has ? '' : ",\"labels\":[\"...\"],\"values\":[0]" ) . "}\n";
    $p .= "Exactly 3 points. No markdown, no emojis.";
    $ai = ds_chart_ai('You are a data storyteller who writes sharp, specific slide text for business presentations. Output only valid JSON.', $p);

    if ( is_wp_error($ai) ) {
        if ( ! $has ) return array('success'=>false,'message'=>'The AI could not create the chart right now. Please add your numbers or try again.');
        $ai = array();
    }
    if ( ! $has ) {
        $try = ds_chart_clean(array('style'=>$chart['style'], 'angle'=>$chart['angle'], 'unit'=> ( $fam === 'pie' || $fam === 'gauge' ) ? '%' : $chart['unit'], 'max'=>$chart['max'],
                                    'labels'=> $ai['labels'] ?? array(), 'values'=> $ai['values'] ?? array()));
        if ( count($try['values']) < 2 ) return array('success'=>false,'message'=>'The AI did not return usable data. Please add your numbers or try again.');
        $chart = $try;
    }
    $clean = function($s, $n){ return mb_substr(trim(preg_replace('/\s+/', ' ', str_replace(array('**','__','#'), '', wp_strip_all_tags((string) $s)))), 0, $n); };
    $pts = array();
    foreach ( (array) ($ai['points'] ?? array()) as $x ) { $x = $clean($x, 260); if ( $x !== '' ) $pts[] = $x; if ( count($pts) >= 3 ) break; }
    if ( ! $pts && $notes !== '' ) foreach ( preg_split('/\n+/', $notes) as $x ) { $x = $clean($x, 260); if ( $x !== '' ) $pts[] = $x; if ( count($pts) >= 3 ) break; }
    $slide = array(
        'type' => 'chart',
        'title' => $clean($ai['title'] ?? '', 120) ?: ( $topic !== '' ? $clean($topic, 120) : 'Key figures' ),
        'subtitle' => $clean($ai['subtitle'] ?? '', 200),
        'content' => $pts,
        'conclusion' => $clean($ai['conclusion'] ?? '', 220),
        'chart' => $chart,
        'image_query' => '',
    );
    if ( ! function_exists('ds_deduct_credits') || ! ds_deduct_credits($uid, $cost) ) return array('success'=>false,'error'=>'Not enough credits','message'=>'You need ' . $cost . ' credits.');
    $log = get_user_meta($uid, 'ds_ppt_generations', true); if ( ! is_array($log) ) $log = array();
    array_unshift($log, array('date'=>current_time('mysql'), 'prompt'=>'Chart slide: ' . ( $topic !== '' ? $topic : $slide['title'] ), 'credits'=>$cost, 'num_slides'=>1));
    update_user_meta($uid, 'ds_ppt_generations', array_slice($log, 0, 100));
    return array('success'=>true, 'slide'=>$slide, 'cost'=>$cost, 'credits_balance'=>ds_chart_credits($uid));
}
add_action('rest_api_init', function () {
    register_rest_route('ds/v1', '/chart-slide', array('methods'=>'POST', 'callback'=>'ds_chart_rest', 'permission_callback'=>function(){ return is_user_logged_in(); }));
});

/* ============================================================
 * front-end
 * ============================================================ */
function ds_chart_should_print() {
    if ( ! is_user_logged_in() ) return false;
    if ( function_exists('ds_tpl_should_print') ) return ds_tpl_should_print();
    global $post;
    return is_a($post, 'WP_Post') && ( has_shortcode($post->post_content, 'ds_ai_ppt') || has_shortcode($post->post_content, 'ds_ai_studio') );
}
add_action('wp_footer', function () {
    if ( ! ds_chart_should_print() ) return;
    $cfg = array('rest' => rest_url('ds/v1/chart-slide'), 'nonce' => wp_create_nonce('wp_rest'), 'cost' => ds_chart_cost(), 'auto' => (bool) apply_filters('ds_chart_auto_slides', true));
    echo "<script id=\"ds-charts\">window.DS_CHART_CFG=" . wp_json_encode($cfg) . ";\n" . ds_chart_js() . "\n</script>\n";
}, 6);
function ds_chart_js() {
    ob_start(); ?>
/* ============================================================
   DS Charts — core renderer (shared by Classic decks, template decks,
   the Add-Slide chart wizard and the chart editor).
   Geometry here is mirrored by the PHP PPTX writer (ds_chart_*), so a
   downloaded deck gets real, editable PowerPoint charts laid out the
   same way as the preview.
   ============================================================ */
(function(){
if (window.DS_CHART && window.DS_CHART.core) return;
var DC = window.DS_CHART = window.DS_CHART || {};
DC.core = 1; DC._n = 0;

/* ---------- colour helpers ---------- */
function hx(c){
  c = String(c || '').trim();
  var m = c.match(/#([0-9a-f]{6}|[0-9a-f]{3})\b/i);
  if (!m) return '#4A9EFF';
  var h = m[1]; if (h.length === 3) h = h[0]+h[0]+h[1]+h[1]+h[2]+h[2];
  return '#' + h.toUpperCase();
}
function rgb(c){ c = hx(c); return [parseInt(c.substr(1,2),16), parseInt(c.substr(3,2),16), parseInt(c.substr(5,2),16)]; }
function lum(c){ var r = rgb(c); return (0.299*r[0] + 0.587*r[1] + 0.114*r[2]) / 255; }
function toHsl(c){
  var r = rgb(c).map(function(v){ return v/255; }), mx = Math.max.apply(null, r), mn = Math.min.apply(null, r), h = 0, s = 0, l = (mx+mn)/2, d = mx-mn;
  if (d) { s = l > 0.5 ? d/(2-mx-mn) : d/(mx+mn);
    if (mx === r[0]) h = (r[1]-r[2])/d + (r[1] < r[2] ? 6 : 0); else if (mx === r[1]) h = (r[2]-r[0])/d + 2; else h = (r[0]-r[1])/d + 4; h *= 60; }
  return {h:h, s:s, l:l};
}
function fromHsl(h, s, l){
  h = ((h % 360) + 360) % 360 / 360;
  function f(p, q, t){ if (t < 0) t += 1; if (t > 1) t -= 1; if (t < 1/6) return p+(q-p)*6*t; if (t < 1/2) return q; if (t < 2/3) return p+(q-p)*(2/3-t)*6; return p; }
  var r, g, b;
  if (!s) r = g = b = l; else { var q = l < 0.5 ? l*(1+s) : l+s-l*s, p = 2*l-q; r = f(p,q,h+1/3); g = f(p,q,h); b = f(p,q,h-1/3); }
  return '#' + [r,g,b].map(function(v){ var x = Math.round(Math.max(0, Math.min(1, v))*255).toString(16); return x.length < 2 ? '0'+x : x; }).join('').toUpperCase();
}
function mix(a, b, t){ var A = rgb(a), B = rgb(b); return '#' + [0,1,2].map(function(i){ var x = Math.round(A[i] + (B[i]-A[i])*t).toString(16); return x.length < 2 ? '0'+x : x; }).join('').toUpperCase(); }
function firstHex(bg){ var m = String(bg || '').match(/#[0-9a-f]{6}|#[0-9a-f]{3}\b/i); return m ? hx(m[0]) : '#1B2A4A'; }
DC.u = {hx:hx, lum:lum, mix:mix, toHsl:toHsl, fromHsl:fromHsl, firstHex:firstHex};

/* palette: the accent first, then harmonious hues around it.
   PHP: ds_chart_palette() — keep in step. */
var OFFS = [[0,0],[180,0],[0,-0.16],[40,0.02],[180,-0.14],[-40,0.04],[0,0.13],[90,0],[-90,0],[140,-0.05],[-140,0.05],[20,-0.1]];
DC.palette = function(ac, dark, n){
  var A = toHsl(hx(ac)), out = [hx(ac)];
  var s = Math.max(0.5, Math.min(dark ? 0.85 : 0.72, A.s || 0.6));
  var l = dark ? Math.max(0.55, Math.min(0.66, A.l)) : Math.max(0.42, Math.min(0.52, A.l));
  for (var i = 1; i < Math.max(n, 1); i++) {
    var o = OFFS[i % OFFS.length], lap = Math.floor(i / OFFS.length), hh = A.h + o[0] + lap*12;
    var li = Math.max(0.28, Math.min(0.8, l + o[1])), c = fromHsl(hh, s, li);
    for (var g = 0; g < 8 && !dark && lum(c) > 0.6; g++) { li -= 0.04; c = fromHsl(hh, s, li); }
    for (var g2 = 0; g2 < 8 && dark && lum(c) < 0.36; g2++) { li += 0.04; c = fromHsl(hh, s, li); }
    out.push(c);
  }
  return out.slice(0, Math.max(n, 1));
};

/* ---------- data ---------- */
var FAM = {pie:'pie', donut:'pie', exploded:'pie', half:'pie', ring:'pie', column:'bar', bar:'bar', line:'line', smooth:'line', area:'line', gauge:'gauge', progress:'gauge', rings:'gauge'};
DC.FAM = FAM;
DC.STYLES = {
  pie:[['pie','Full pie'],['donut','Donut'],['exploded','Exploded'],['half','Half donut'],['ring','Thin ring']],
  bar:[['column','Column'],['bar','Horizontal bar']],
  line:[['line','Line'],['smooth','Smooth line'],['area','Area']],
  gauge:[['gauge','Gauges'],['progress','Progress bars'],['rings','Radial rings']]
};
DC.FAMILIES = [['pie','Pie & Donut'],['bar','Bar & Column'],['line','Line & Area'],['gauge','Progress & Gauge']];
function num(v){ var n = parseFloat(String(v == null ? '' : v).replace(/[^0-9.\-]/g, '')); return isFinite(n) ? n : NaN; }
DC.norm = function(ch){
  ch = ch || {};
  var style = FAM[ch.style] ? ch.style : (ch.kind && DC.STYLES[ch.kind] ? DC.STYLES[ch.kind][0][0] : 'donut');
  var rows = [];
  var L = Array.isArray(ch.labels) ? ch.labels : [], Vs = Array.isArray(ch.values) ? ch.values : [];
  for (var i = 0; i < Math.max(L.length, Vs.length); i++) {
    var v = num(Vs[i]); if (!isFinite(v)) continue;
    rows.push({l: String(L[i] == null ? '' : L[i]).trim().slice(0, 60) || ('Item ' + (i+1)), v: Math.max(FAM[style] === 'pie' ? 0 : -1e12, v)});
  }
  rows = rows.slice(0, 12);
  var unit = String(ch.unit == null ? '%' : ch.unit).trim().slice(0, 8);
  var mx = num(ch.max);
  if (!isFinite(mx) || mx <= 0) { mx = 100; rows.forEach(function(r){ if (r.v > mx) mx = r.v; }); if (unit !== '%') { var m2 = 0; rows.forEach(function(r){ if (r.v > m2) m2 = r.v; }); mx = m2 > 0 ? m2 : 100; } }
  return {kind: FAM[style], style: style, angle: ((parseInt(ch.angle, 10) || 0) % 360 + 360) % 360, unit: unit, max: mx, rows: rows};
};
var PRE = /^[$€£₹¥]$/;
DC.fmt = function(v, unit){
  var a = Math.abs(v), s = (Math.round(v*100)/100);
  var str = a >= 1e9 ? (Math.round(v/1e8)/10) + 'B' : a >= 1e6 ? (Math.round(v/1e5)/10) + 'M' : a >= 1e4 ? (Math.round(v/100)/10) + 'K' : String(s);
  if (!unit) return str;
  if (PRE.test(unit)) return (v < 0 ? '-' : '') + unit + str.replace('-', '');
  if (unit === '%') return str + '%';
  return str + ' ' + unit;
};
function esc(s){ return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function f1(n){ return Math.round(n*10)/10; }
function sumOf(rows){ var t = 0; rows.forEach(function(r){ t += Math.max(0, r.v); }); return t; }
function trunc(s, n){ s = String(s); return s.length > n ? s.slice(0, n-1) + '…' : s; }
/* nice axis max */
DC.niceMax = function(m){
  if (m <= 0) return 10;
  var p = Math.pow(10, Math.floor(Math.log10(m))), f = m / p, n = f <= 1 ? 1 : f <= 1.2 ? 1.2 : f <= 1.5 ? 1.5 : f <= 2 ? 2 : f <= 2.5 ? 2.5 : f <= 3 ? 3 : f <= 4 ? 4 : f <= 5 ? 5 : f <= 6 ? 6 : f <= 8 ? 8 : 10;
  return n * p;
};

/* ---------- geometry (mirrored in PHP) ---------- */
/* pie family: legend 'right' | 'bottom' | 'none' */
DC.pieGeo = function(w, h, style, legend, n){
  var LW = legend === 'right' ? Math.min(w*0.42, 340) : 0, LH = legend === 'bottom' ? Math.min(h*0.26, 20 + Math.ceil(n/3)*36) : 0;
  var aw = w - LW, ah = h - LH, g = {LW:LW, LH:LH, aw:aw, ah:ah};
  if (style === 'half') { g.R = Math.min(aw/2, ah*0.92) * 0.94; g.cx = aw/2; g.cy = ah/2 + g.R*0.45; if (g.cy + 0.02*g.R > ah) g.cy = ah - 2; }
  else { g.R = Math.min(aw, ah)/2 * 0.92; g.cx = aw/2; g.cy = ah/2; }
  g.ri = style === 'donut' ? 0.58 : style === 'ring' ? 0.8 : style === 'half' ? 0.56 : 0;
  return g;
};
DC.gaugeGeo = function(w, h, n){
  n = Math.max(1, Math.min(4, n)); var cw = w/n, R = Math.min(cw*0.45, h*0.56);
  return {n:n, cw:cw, R:R, th:R*0.24, cy:h*0.5 + R*0.28};
};

/* arc path helpers (angle 0 = 12 o'clock, clockwise, degrees) */
function pt(cx, cy, r, a){ var t = (a - 90) * Math.PI/180; return [cx + r*Math.cos(t), cy + r*Math.sin(t)]; }
function sector(cx, cy, R, ri, a0, a1){
  if (a1 - a0 >= 359.99) a1 = a0 + 359.99;
  var large = (a1 - a0) > 180 ? 1 : 0, p0 = pt(cx,cy,R,a0), p1 = pt(cx,cy,R,a1);
  if (!ri) return 'M'+f1(cx)+' '+f1(cy)+' L'+f1(p0[0])+' '+f1(p0[1])+' A'+f1(R)+' '+f1(R)+' 0 '+large+' 1 '+f1(p1[0])+' '+f1(p1[1])+' Z';
  var q1 = pt(cx,cy,ri,a1), q0 = pt(cx,cy,ri,a0);
  return 'M'+f1(p0[0])+' '+f1(p0[1])+' A'+f1(R)+' '+f1(R)+' 0 '+large+' 1 '+f1(p1[0])+' '+f1(p1[1])+' L'+f1(q1[0])+' '+f1(q1[1])+' A'+f1(ri)+' '+f1(ri)+' 0 '+large+' 0 '+f1(q0[0])+' '+f1(q0[1])+' Z';
}
function arcStroke(cx, cy, r, a0, a1){
  if (a1 - a0 >= 359.99) a1 = a0 + 359.99;
  var p0 = pt(cx,cy,r,a0), p1 = pt(cx,cy,r,a1);
  return 'M'+f1(p0[0])+' '+f1(p0[1])+' A'+f1(r)+' '+f1(r)+' 0 '+((a1-a0) > 180 ? 1 : 0)+' 1 '+f1(p1[0])+' '+f1(p1[1]);
}
function onColor(c){ return lum(c) > 0.62 ? '#111827' : '#FFFFFF'; }
function txt(x, y, s, o){
  o = o || {};
  return '<text x="'+f1(x)+'" y="'+f1(y)+'" font-size="'+f1(o.size||16)+'" font-weight="'+(o.wt||400)+'" fill="'+(o.fill||'#FFFFFF')+'"'
    + (o.op != null ? ' fill-opacity="'+o.op+'"' : '') + ' text-anchor="'+(o.anchor||'start')+'" dominant-baseline="'+(o.base||'central')+'">'+esc(s)+'</text>';
}

/* legend block */
function legendSvg(rows, cols, x, y, w, h, mode, o, showVal, unit){
  var s = '', n = rows.length;
  if (mode === 'right') {
    var rh = Math.min(52, h / Math.max(n, 1)), fs = Math.max(14, Math.min(22, rh*0.46)), y0 = y + (h - rh*n)/2 + rh/2;
    rows.forEach(function(r, i){
      var yy = y0 + i*rh;
      s += '<rect x="'+f1(x+8)+'" y="'+f1(yy-fs*0.42)+'" width="'+f1(fs*0.84)+'" height="'+f1(fs*0.84)+'" rx="'+f1(fs*0.22)+'" fill="'+cols[i % cols.length]+'"/>';
      s += txt(x+8+fs*1.35, yy, trunc(r.l, 26), {size:fs, fill:o.tc, wt:500});
      if (showVal) s += txt(x+w-4, yy, DC.fmt(r.v, unit), {size:fs, fill:o.tc, wt:800, anchor:'end'});
    });
  } else if (mode === 'bottom') {
    var per = Math.min(n, 3), rows2 = Math.ceil(n/per), cw = w/per, lh = Math.min(36, h/Math.max(rows2,1)), fs2 = Math.max(14, Math.min(20, lh*0.6));
    rows.forEach(function(r, i){
      var cx = x + (i % per)*cw, yy = y + (h - lh*rows2)/2 + Math.floor(i/per)*lh + lh/2;
      s += '<circle cx="'+f1(cx+14)+'" cy="'+f1(yy)+'" r="'+f1(fs2*0.38)+'" fill="'+cols[i % cols.length]+'"/>';
      s += txt(cx+14+fs2*0.9, yy, trunc(r.l, 20) + (showVal ? '  ' + DC.fmt(r.v, unit) : ''), {size:fs2, fill:o.tc, wt:500});
    });
  }
  return s;
}

/* ---------- SVG ----------
   o: {w, h, colors[], tc (labels), mc (muted), legend, bgc (solid under colour), uid} */
DC.svg = function(chart, o){
  o = o || {};
  var d = DC.norm(chart), w = o.w || 640, h = o.h || 400, rows = d.rows, n = rows.length;
  var tc = hx(o.tc || '#FFFFFF'), mc = hx(o.mc || mix(tc, o.bgc || '#000000', 0.35)), bgc = hx(o.bgc || '#1B2A4A');
  var cols = (o.colors && o.colors.length ? o.colors : DC.palette(o.ac || '#4A9EFF', lum(bgc) < 0.5, Math.max(n, 1))).map(hx);
  while (cols.length < n) cols = cols.concat(cols);
  var uid = (o.uid || 'c') + '_' + (++DC._n);
  var fam = o.font ? ' font-family="' + esc(o.font) + ',Arial,sans-serif"' : ' font-family="Arial,sans-serif"';
  var S = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '+f1(w)+' '+f1(h)+'" width="100%" height="100%" preserveAspectRatio="xMidYMid meet" style="display:block;overflow:visible;"'+fam+'>';
  if (!n) return S + txt(w/2, h/2, 'Add data to see the chart', {size:18, fill:mc, anchor:'middle'}) + '</svg>';
  var track = tc, trackOp = 0.12;

  if (d.kind === 'pie') {
    var legend = o.legend || 'right', g = DC.pieGeo(w, h, d.style, legend, n), tot = sumOf(rows) || 1;
    var span = d.style === 'half' ? 180 : 360, a = d.style === 'half' ? 270 : d.angle, R = g.R, ri = g.ri*R;
    var big = 0; rows.forEach(function(r, i){ if (r.v > rows[big].v) big = i; });
    var lbl = '';
    rows.forEach(function(r, i){
      var sw = Math.max(0, r.v)/tot*span, a0 = a, a1 = a + sw; a = a1;
      if (sw <= 0) return;
      var mid = (a0 + a1)/2, off = d.style === 'exploded' ? R*0.055 : 0, cx = g.cx, cy = g.cy;
      if (off) { var m = pt(0, 0, off, mid); cx += m[0]; cy += m[1]; }
      S += '<path d="'+sector(cx, cy, R, ri, a0, a1)+'" fill="'+cols[i]+'" stroke="'+bgc+'" stroke-width="'+(d.style === 'exploded' ? 0 : Math.max(1.5, R*0.012))+'" stroke-linejoin="round"/>';
      var share = r.v/tot*100, lab = d.unit === '%' ? DC.fmt(r.v, '%') : Math.round(share) + '%';
      if (d.style !== 'ring' && sw >= (d.style === 'half' ? 11 : 16)) {
        var lr = ri ? (R + ri)/2 : R*0.64, lp = pt(cx, cy, lr, mid), fs = Math.max(12, Math.min(26, R*(ri ? (R-ri)/R*0.34 : 0.13)));
        lbl += txt(lp[0], lp[1], lab, {size:fs, wt:800, fill:onColor(cols[i]), anchor:'middle'});
      }
    });
    S += lbl;
    if (ri) {
      var cr = rows[big], cfs = Math.max(16, ri*0.42);
      var cyv = d.style === 'half' ? g.cy - ri*0.32 : g.cy - ri*0.1;
      S += txt(g.cx, cyv, d.unit === '%' ? DC.fmt(cr.v, '%') : Math.round(cr.v/tot*100) + '%', {size:cfs, wt:900, fill:tc, anchor:'middle'});
      S += txt(g.cx, cyv + cfs*0.78, trunc(cr.l, 18), {size:Math.max(11, cfs*0.36), wt:600, fill:mc, anchor:'middle'});
    }
    if (legend === 'right') S += legendSvg(rows, cols, g.aw, 0, g.LW, h, 'right', {tc:tc}, true, d.unit === '%' ? '%' : d.unit);
    else if (legend === 'bottom') S += legendSvg(rows, cols, 0, g.ah, w, g.LH, 'bottom', {tc:tc}, false, d.unit);
    return S + '</svg>';
  }

  if (d.kind === 'bar') {
    var mxv = 0, mnv = 0; rows.forEach(function(r){ mxv = Math.max(mxv, r.v); mnv = Math.min(mnv, r.v); });
    var top = DC.niceMax(mxv), ac = cols[0], fs3 = Math.max(15, Math.min(21, h*0.046));
    var gid = uid + 'g';
    S += '<defs><linearGradient id="'+gid+'" x1="0" y1="0" x2="'+(d.style === 'bar' ? 1 : 0)+'" y2="'+(d.style === 'bar' ? 0 : 1)+'">'
       + (d.style === 'bar' ? '<stop offset="0" stop-color="'+mix(ac, bgc, 0.25)+'"/><stop offset="1" stop-color="'+ac+'"/>' : '<stop offset="0" stop-color="'+ac+'"/><stop offset="1" stop-color="'+mix(ac, bgc, 0.25)+'"/>')
       + '</linearGradient></defs>';
    if (d.style === 'bar') {
      var lw = Math.min(w*0.3, 230), px0 = lw + 10, pw = w - px0 - 70, rh = h/n, bh = Math.min(rh*0.58, 46);
      for (var gi = 0; gi <= 4; gi++) { var gx = px0 + pw*gi/4; S += '<line x1="'+f1(gx)+'" y1="0" x2="'+f1(gx)+'" y2="'+f1(h)+'" stroke="'+mc+'" stroke-opacity="0.22" stroke-dasharray="4 5"/>'; }
      rows.forEach(function(r, i){
        var yy = i*rh + rh/2, bw = Math.max(2, Math.max(0, r.v)/top*pw);
        S += txt(lw, yy, trunc(r.l, 24), {size:fs3, fill:tc, wt:600, anchor:'end'});
        S += '<rect x="'+f1(px0)+'" y="'+f1(yy-bh/2)+'" width="'+f1(bw)+'" height="'+f1(bh)+'" rx="'+f1(Math.min(8, bh/3))+'" fill="url(#'+gid+')"/>';
        S += txt(px0 + bw + 10, yy, DC.fmt(r.v, d.unit), {size:fs3, fill:tc, wt:800});
      });
    } else {
      var axw = 58, bot = 44, ph = h - bot - 26, pw2 = w - axw - 8, cw = pw2/n, bw2 = Math.min(cw*0.58, 90), y0 = 26;
      for (var gj = 0; gj <= 4; gj++) {
        var gy = y0 + ph - ph*gj/4;
        S += '<line x1="'+axw+'" y1="'+f1(gy)+'" x2="'+f1(w)+'" y2="'+f1(gy)+'" stroke="'+mc+'" stroke-opacity="'+(gj ? 0.22 : 0.5)+'"'+(gj ? ' stroke-dasharray="4 5"' : '')+'/>';
        S += txt(axw - 10, gy, DC.fmt(top*gj/4, d.unit), {size:fs3*0.85, fill:mc, anchor:'end'});
      }
      rows.forEach(function(r, i){
        var cx = axw + cw*i + cw/2, bh2 = Math.max(2, Math.max(0, r.v)/top*ph);
        S += '<path d="M'+f1(cx-bw2/2)+' '+f1(y0+ph)+' V'+f1(y0+ph-bh2+Math.min(8,bw2/4))+' Q'+f1(cx-bw2/2)+' '+f1(y0+ph-bh2)+' '+f1(cx-bw2/2+Math.min(8,bw2/4))+' '+f1(y0+ph-bh2)+' H'+f1(cx+bw2/2-Math.min(8,bw2/4))+' Q'+f1(cx+bw2/2)+' '+f1(y0+ph-bh2)+' '+f1(cx+bw2/2)+' '+f1(y0+ph-bh2+Math.min(8,bw2/4))+' V'+f1(y0+ph)+' Z" fill="url(#'+gid+')"/>';
        S += txt(cx, y0 + ph - bh2 - 13, DC.fmt(r.v, d.unit), {size:fs3, fill:tc, wt:800, anchor:'middle'});
        S += txt(cx, y0 + ph + 22, trunc(r.l, Math.max(6, Math.floor(cw/ (fs3*0.55)))), {size:fs3*0.9, fill:tc, wt:500, anchor:'middle', op:0.9});
      });
    }
    return S + '</svg>';
  }

  if (d.kind === 'line') {
    var mx3 = 0; rows.forEach(function(r){ mx3 = Math.max(mx3, r.v); });
    var top3 = DC.niceMax(mx3), axw3 = 58, bot3 = 44, y03 = 30, ph3 = h - bot3 - y03, pw3 = w - axw3 - 24, fs4 = Math.max(15, Math.min(21, h*0.046)), lc = cols[0];
    var step = n > 1 ? pw3/(n-1) : 0, P = rows.map(function(r, i){ return [axw3 + 12 + (n > 1 ? step*i : pw3/2), y03 + ph3 - Math.max(0, r.v)/top3*ph3]; });
    if (n > 1) { P.forEach(function(p){ p[0] = axw3 + 12 + (p[0] - axw3 - 12) * (pw3 - 12)/pw3; }); }
    for (var gk = 0; gk <= 4; gk++) {
      var gy3 = y03 + ph3 - ph3*gk/4;
      S += '<line x1="'+axw3+'" y1="'+f1(gy3)+'" x2="'+f1(w)+'" y2="'+f1(gy3)+'" stroke="'+mc+'" stroke-opacity="'+(gk ? 0.22 : 0.5)+'"'+(gk ? ' stroke-dasharray="4 5"' : '')+'/>';
      S += txt(axw3 - 10, gy3, DC.fmt(top3*gk/4, d.unit), {size:fs4*0.85, fill:mc, anchor:'end'});
    }
    var dl = '';
    if (d.style === 'smooth' && n > 2) {
      dl = 'M' + f1(P[0][0]) + ' ' + f1(P[0][1]);
      for (var k = 0; k < n-1; k++) {
        var p0 = P[Math.max(0,k-1)], p1 = P[k], p2 = P[k+1], p3 = P[Math.min(n-1,k+2)];
        dl += ' C' + f1(p1[0]+(p2[0]-p0[0])/6) + ' ' + f1(p1[1]+(p2[1]-p0[1])/6) + ' ' + f1(p2[0]-(p3[0]-p1[0])/6) + ' ' + f1(p2[1]-(p3[1]-p1[1])/6) + ' ' + f1(p2[0]) + ' ' + f1(p2[1]);
      }
    } else dl = P.map(function(p, i){ return (i ? 'L' : 'M') + f1(p[0]) + ' ' + f1(p[1]); }).join(' ');
    if (d.style === 'area') {
      var ag = uid + 'a';
      S += '<defs><linearGradient id="'+ag+'" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="'+lc+'" stop-opacity="0.55"/><stop offset="1" stop-color="'+lc+'" stop-opacity="0.04"/></linearGradient></defs>';
      S += '<path d="'+dl+' L'+f1(P[n-1][0])+' '+f1(y03+ph3)+' L'+f1(P[0][0])+' '+f1(y03+ph3)+' Z" fill="url(#'+ag+')"/>';
    }
    S += '<path d="'+dl+'" fill="none" stroke="'+lc+'" stroke-width="'+f1(Math.max(3, h*0.011))+'" stroke-linecap="round" stroke-linejoin="round"/>';
    rows.forEach(function(r, i){
      var p = P[i];
      S += '<circle cx="'+f1(p[0])+'" cy="'+f1(p[1])+'" r="'+f1(Math.max(5, h*0.016))+'" fill="'+bgc+'" stroke="'+lc+'" stroke-width="'+f1(Math.max(2.5, h*0.008))+'"/>';
      S += txt(p[0], p[1] - 20, DC.fmt(r.v, d.unit), {size:fs4, fill:tc, wt:800, anchor:'middle'});
      S += txt(p[0], y03 + ph3 + 22, trunc(r.l, Math.max(6, Math.floor((n > 1 ? step : pw3)/(fs4*0.55)))), {size:fs4*0.9, fill:tc, wt:500, anchor:'middle', op:0.9});
    });
    return S + '</svg>';
  }

  /* gauge family */
  var mxg = d.max;
  if (d.style === 'progress') {
    var lw4 = Math.min(w*0.3, 240), bx = lw4 + 18, bw4 = w - bx - 90, rh4 = h/n, bh4 = Math.min(rh4*0.36, 26), fs5 = Math.max(15, Math.min(22, rh4*0.32));
    rows.forEach(function(r, i){
      var yy = i*rh4 + rh4/2, fw = Math.max(0, Math.min(1, r.v/mxg))*bw4;
      S += txt(lw4, yy, trunc(r.l, 24), {size:fs5, fill:tc, wt:600, anchor:'end'});
      S += '<rect x="'+f1(bx)+'" y="'+f1(yy-bh4/2)+'" width="'+f1(bw4)+'" height="'+f1(bh4)+'" rx="'+f1(bh4/2)+'" fill="'+track+'" fill-opacity="'+trackOp+'"/>';
      if (fw > 0) S += '<rect x="'+f1(bx)+'" y="'+f1(yy-bh4/2)+'" width="'+f1(Math.max(fw, bh4))+'" height="'+f1(bh4)+'" rx="'+f1(bh4/2)+'" fill="'+cols[i]+'"/>';
      S += txt(bx + bw4 + 12, yy, DC.fmt(r.v, d.unit), {size:fs5, fill:tc, wt:800});
    });
    return S + '</svg>';
  }
  if (d.style === 'rings') {
    var LW5 = Math.min(w*0.44, 320), aw5 = w - LW5, R5 = Math.min(aw5, h)/2*0.94, cx5 = aw5/2, cy5 = h/2, m5 = Math.min(n, 6);
    var band = R5*0.72/m5, th5 = band*0.74;
    rows.slice(0, m5).forEach(function(r, i){
      var rr = R5 - th5/2 - i*band, fr = Math.max(0, Math.min(1, r.v/mxg));
      S += '<circle cx="'+f1(cx5)+'" cy="'+f1(cy5)+'" r="'+f1(rr)+'" fill="none" stroke="'+track+'" stroke-opacity="'+trackOp+'" stroke-width="'+f1(th5)+'"/>';
      if (fr > 0) S += '<path d="'+arcStroke(cx5, cy5, rr, 0, fr*360)+'" fill="none" stroke="'+cols[i]+'" stroke-width="'+f1(th5)+'" stroke-linecap="round"/>';
    });
    S += legendSvg(rows.slice(0, m5), cols, aw5, 0, LW5, h, 'right', {tc:tc}, true, d.unit);
    return S + '</svg>';
  }
  var G = DC.gaugeGeo(w, h, n);
  rows.slice(0, G.n).forEach(function(r, i){
    var cx = G.cw*i + G.cw/2, rr = G.R - G.th/2, fr = Math.max(0, Math.min(1, r.v/mxg));
    S += '<path d="'+arcStroke(cx, G.cy, rr, 270, 450)+'" fill="none" stroke="'+track+'" stroke-opacity="'+trackOp+'" stroke-width="'+f1(G.th)+'" stroke-linecap="butt"/>';
    if (fr > 0) S += '<path d="'+arcStroke(cx, G.cy, rr, 270, 270 + fr*180)+'" fill="none" stroke="'+cols[i]+'" stroke-width="'+f1(G.th)+'" stroke-linecap="butt"/>';
    S += txt(cx, G.cy - G.R*0.2, DC.fmt(r.v, d.unit), {size:G.R*0.34, wt:900, fill:tc, anchor:'middle'});
    S += txt(cx, G.cy + G.R*0.22, trunc(r.l, 22), {size:Math.max(15, Math.min(24, G.R*0.17)), wt:600, fill:tc, anchor:'middle', op:0.85});
  });
  return S + '</svg>';
};

/* small icon for the wizard */
DC.icon = function(style, c){
  c = c || '#8AB4F8';
  var demo = {pie:[40,25,20,15], bar:[30,55,40,70], line:[20,45,35,70], gauge:[72,48]}[FAM[style] || 'pie'];
  return DC.svg({style:style, unit:'%', labels:demo.map(function(_, i){ return 'A'+i; }), values:demo}, {w:220, h:140, colors:DC.palette(c, true, 5), tc:'#E5E7EB', mc:'#9CA3AF', bgc:'#1d1f27', legend:'none'})
    .replace(/<text[^>]*>[^<]*<\/text>/g, '');
};

/* ---------- percentage detection (auto chart slides) ---------- */
DC.detect = function(slide){
  if (!slide || !Array.isArray(slide.content)) return null;
  var t = String(slide.type || '');
  if (t === 'title' || t === 'closing' || t === 'chart' || t === 'agenda' || t === 'quote') return null;
  var labels = [], values = [], structured = 0;
  slide.content.forEach(function(it){
    var s = String(it || ''), m = s.match(/(-?\d{1,3}(?:[.,]\d+)?)\s?%/);
    if (!m) return;
    var v = parseFloat(m[1].replace(',', '.')); if (!isFinite(v) || v < 0 || v > 1000) return;
    var ci = s.indexOf(':'); if (ci > 0 && ci < 70) structured++;
    var lab = ci > 0 && ci < 60 ? s.slice(0, ci) : s.replace(m[0], '').replace(/[|:–—-].*$/, '');
    lab = lab.replace(/\*\*/g, '').replace(/^\s*[\d.,]+\s?%\s*/, '').trim();
    if (/^-?\d+([.,]\d+)?\s?%$/.test(lab) || !lab) { var rest = s.slice(ci + 1).split('|')[0].trim(); lab = rest || lab; }
    lab = lab.split(/\s+/).slice(0, 5).join(' ').slice(0, 40);
    labels.push(lab || ('Item ' + (labels.length + 1))); values.push(v);
  });
  if (values.length < 2) return null;
  var sum = values.reduce(function(a, b){ return a + b; }, 0);
  if (structured < 2 && !(sum >= 95 && sum <= 105)) return null;
  var all100 = values.every(function(v){ return v <= 100; });
  var style = (sum >= 95 && sum <= 105) ? 'donut' : (all100 && values.length <= 4) ? 'gauge' : (all100 && values.length <= 6) ? 'progress' : 'column';
  return {style:style, unit:'%', labels:labels.slice(0, 8), values:values.slice(0, 8), angle:0};
};
})();

/* ============================================================
   DS Charts — Classic deck layouts (snippet 17 calls DS_CHART.renderClassic)
   Boxes are in a 1280 x 720 design frame; the PHP writer uses the same
   numbers (ds_chart_classic_boxes) for the PowerPoint file.
   ============================================================ */
(function(){
var DC = window.DS_CHART; if (!DC || DC.classic) return;
DC.classic = 1;
var u = DC.u, W = 1280, H = 720, EM = 56.25 / W;

DC.VARIANTS = [
  {id:'v1',  lbl:'Chart + insights'},
  {id:'n2',  lbl:'Insights + chart right'},
  {id:'n3',  lbl:'Centered chart'},
  {id:'n4',  lbl:'Big numbers + chart'},
  {id:'n5',  lbl:'Full-width chart'}
];
DC.variants = function(){ return DC.VARIANTS; };

function pts(s){
  return (Array.isArray(s.content) ? s.content : []).map(function(x){ return String(x || '').trim(); }).filter(Boolean).slice(0, 4);
}
DC.points = pts;

/* PHP twin: ds_chart_classic_boxes() */
DC.boxes = function(v, s){
  var d = DC.norm(s.chart), P = pts(s), has = P.length > 0, pie = d.kind === 'pie';
  var stripTxt = s.conclusion || '';
  var b = {v:v, title:{x:64, y:40, w:1152, align:'l'}, panel:null};
  if (v === 'n2') {
    b.chart = {x:560, y:168, w:680, h:500, legend: pie ? (has ? 'bottom' : 'none') : 'none'};
    b.panel = {x:64, y:182, w:468, h:476, mode: has ? 'points' : (pie || d.kind === 'gauge' ? 'legend' : 'kpi')};
  } else if (v === 'n3') {
    b.title.align = 'c';
    b.chart = {x:170, y:162, w:940, h: has ? 410 : 500, legend: pie ? 'right' : 'none'};
    if (has) b.panel = {x:64, y:596, w:1152, h:100, mode:'cols'};
  } else if (v === 'n4') {
    b.panel = {x:64, y:176, w:330, h:490, mode:'kpi'};
    b.chart = {x:430, y:168, w:800, h:500, legend: pie ? 'bottom' : 'none'};
  } else if (v === 'n5') {
    var st = stripTxt || (has ? P[0] : '');
    b.chart = {x:64, y:160, w:1152, h: st ? 456 : 520, legend: pie ? 'right' : 'none'};
    if (st) b.panel = {x:64, y:634, w:1152, h:58, mode:'strip', text:st, conc: !!stripTxt};
  } else {
    b.chart = {x:40, y:168, w:680, h:500, legend: pie ? (has ? 'bottom' : 'none') : 'none'};
    b.panel = {x:748, y:182, w:468, h:476, mode: has ? 'points' : (pie || d.kind === 'gauge' ? 'legend' : 'kpi')};
  }
  if (d.style === 'rings' || d.style === 'progress' || d.style === 'gauge') { if (b.chart.legend !== 'none') b.chart.legend = 'none'; }
  return b;
};

function pc(v, tot){ return (Math.round(v / tot * 10000) / 100) + '%'; }
function box(r){ return 'position:absolute;left:'+pc(r.x,W)+';top:'+pc(r.y,H)+';width:'+pc(r.w,W)+';'+(r.h != null ? 'height:'+pc(r.h,H)+';' : ''); }
function em(px){ return (Math.round(px * EM * 1000) / 1000) + 'em'; }
function esc(s){ return String(s == null ? '' : s).replace(/\*\*/g, '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
function rgba(c, a){ c = u.hx(c); return 'rgba('+parseInt(c.substr(1,2),16)+','+parseInt(c.substr(3,2),16)+','+parseInt(c.substr(5,2),16)+','+a+')'; }
function split(x){ var s = String(x), i = s.indexOf(':'); return i > 0 && i < 80 ? [s.slice(0, i).trim(), s.slice(i+1).trim()] : [s.trim(), '']; }
function clamp(n){ return 'overflow:hidden;display:-webkit-box;-webkit-line-clamp:'+n+';-webkit-box-orient:vertical;'; }
function ed(idx, f, i){ return idx >= 0 ? ' contenteditable="true" spellcheck="false" data-ed-slide="'+idx+'" data-ed-field="'+f+'"'+(i != null ? ' data-ed-idx="'+i+'"' : '') : ''; }

DC.colorsFor = function(ac, bg, n){ return DC.palette(ac, u.lum(u.firstHex(bg)) < 0.5, Math.max(n, 1)); };

/* the chart itself, with the edit affordance (main canvas only) */
DC.chartHTML = function(s, idx, r, o){
  var svg = DC.svg(s.chart, {w:r.w, h:r.h, colors:o.colors, tc:o.tc, mc:o.mc, bgc:o.bgc, legend:r.legend, uid:'dsc'+idx+(o.tag||''), font:o.font});
  return '<div class="dsch-box" data-dsch-idx="'+idx+'" style="'+box(r)+'">'+svg
    + (idx >= 0 ? '<button type="button" class="dsch-edit" onclick="event.stopPropagation();window.DS_CHART&&DS_CHART.openEditor('+idx+')">&#9998; Edit chart</button>' : '')
    + '</div>';
};

DC.renderClassic = function(s, t, v, idx){
  var ff = '', fe = document.getElementById('dsp-font'); if (fe && fe.value) ff = "'" + fe.value + "',";
  var bg = t.bg || '#1B2A4A', bgc = u.firstHex(bg), tc = u.hx(t.tc || '#FFFFFF'), bc = u.hx(t.bc || tc), ac = u.hx(t.ac || '#4A9EFF');
  var d = DC.norm(s.chart), b = DC.boxes(v, s), cols = DC.colorsFor(ac, bg, Math.max(d.rows.length, 3));
  var mc = u.mix(bc, bgc, 0.25);
  var h = '<div class="dsch-slide" style="position:absolute;inset:0;background:'+bg+';overflow:hidden;font-family:'+ff+'Arial,sans-serif;">';
  h += '<div style="position:absolute;top:0;left:0;right:0;height:3px;background:'+ac+';pointer-events:none;"></div>';
  var al = b.title.align === 'c' ? 'center' : 'left';
  h += '<div style="'+box(b.title)+'text-align:'+al+';">'
     + '<div'+ed(idx,'title')+' style="outline:none;cursor:text;font-size:'+em(36)+';font-weight:800;color:'+tc+';line-height:1.12;'+clamp(2)+'">'+esc(s.title || '')+'</div>'
     + '<div'+ed(idx,'subtitle')+' style="outline:none;cursor:text;min-height:1em;margin-top:'+em(8)+';font-size:'+em(17)+';font-weight:600;color:'+ac+';line-height:1.3;'+clamp(2)+'">'+esc(s.subtitle || '')+'</div>'
     + '</div>';
  h += DC.chartHTML(s, idx, b.chart, {colors:cols, tc:tc, mc:mc, bgc:bgc, tag:v, font:fe && fe.value ? fe.value : ''});
  var p = b.panel;
  if (p) {
    var P = pts(s), card = rgba(tc, 0.06), line = rgba(tc, 0.14);
    if (p.mode === 'points') {
      var n = P.length, gap = 14, chh = (p.h - gap*(n-1)) / n;
      h += '<div style="'+box(p)+'display:flex;flex-direction:column;gap:'+pc(gap,H)+';">';
      P.forEach(function(x, i){
        var sp = split(x);
        h += '<div style="position:relative;flex:1;min-height:0;border-radius:'+em(12)+';background:'+card+';padding:'+em(14)+' '+em(18)+' '+em(14)+' '+em(26)+';display:flex;flex-direction:column;justify-content:center;overflow:hidden;">'
           + '<div style="position:absolute;left:0;top:18%;bottom:18%;width:'+em(6)+';border-radius:'+em(3)+';background:'+cols[i % cols.length]+';"></div>'
           + '<div'+ed(idx,'content-h',i)+' style="outline:none;cursor:text;font-size:'+em(n > 3 ? 19 : 22)+';font-weight:800;color:'+tc+';line-height:1.2;'+clamp(2)+'">'+esc(sp[0])+'</div>'
           + (sp[1] || idx >= 0 ? '<div'+ed(idx,'content-d',i)+' style="outline:none;cursor:text;min-height:1em;margin-top:'+em(5)+';font-size:'+em(n > 3 ? 15 : 16.5)+';color:'+bc+';line-height:1.38;'+clamp(n > 3 ? 2 : 3)+'">'+esc(sp[1])+'</div>' : '')
           + '</div>';
      });
      h += '</div>';
    } else if (p.mode === 'legend' || p.mode === 'kpi') {
      var rows = d.rows.map(function(r, i){ return {r:r, i:i}; });
      if (p.mode === 'kpi') rows = rows.slice().sort(function(a, c){ return c.r.v - a.r.v; }).slice(0, 3);
      else rows = rows.slice(0, 7);
      var m = rows.length || 1, tot = 0, one = d.kind === 'bar' || d.kind === 'line'; d.rows.forEach(function(r){ tot += Math.max(0, r.v); });
      h += '<div style="'+box(p)+'display:flex;flex-direction:column;justify-content:center;">';
      rows.forEach(function(o, k){
        var val = d.kind === 'pie' && d.unit !== '%' ? DC.fmt(o.r.v, d.unit) : DC.fmt(o.r.v, d.unit);
        if (p.mode === 'kpi') {
          h += '<div style="padding:'+em(14)+' 0;'+(k ? 'border-top:1px solid '+line+';' : '')+'">'
             + '<div style="font-size:'+em(m > 2 ? 46 : 54)+';font-weight:900;line-height:1;color:'+cols[one ? 0 : o.i % cols.length]+';">'+esc(val)+'</div>'
             + '<div style="margin-top:'+em(6)+';font-size:'+em(17)+';font-weight:600;color:'+bc+';line-height:1.3;'+clamp(2)+'">'+esc(o.r.l)+'</div></div>';
        } else {
          h += '<div style="display:flex;align-items:center;gap:'+em(14)+';padding:'+em(m > 5 ? 8 : 12)+' 0;'+(k ? 'border-top:1px solid '+line+';' : '')+'">'
             + '<span style="flex:0 0 auto;width:'+em(16)+';height:'+em(16)+';border-radius:'+em(5)+';background:'+cols[o.i % cols.length]+';"></span>'
             + '<span style="flex:1;min-width:0;font-size:'+em(18)+';font-weight:600;color:'+tc+';white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+esc(o.r.l)+'</span>'
             + '<span style="flex:0 0 auto;font-size:'+em(m > 5 ? 22 : 26)+';font-weight:900;color:'+tc+';">'+esc(val)+'</span></div>';
        }
      });
      h += '</div>';
    } else if (p.mode === 'cols') {
      var P3 = P.slice(0, 3);
      h += '<div style="'+box(p)+'display:flex;gap:'+pc(28,W)+';">';
      P3.forEach(function(x, i){
        var sp = split(x);
        h += '<div style="flex:1;min-width:0;border-top:'+em(4)+' solid '+cols[i % cols.length]+';padding-top:'+em(10)+';">'
           + '<div'+ed(idx,'content-h',i)+' style="outline:none;cursor:text;font-size:'+em(19)+';font-weight:800;color:'+tc+';line-height:1.2;'+clamp(1)+'">'+esc(sp[0])+'</div>'
           + '<div'+ed(idx,'content-d',i)+' style="outline:none;cursor:text;min-height:1em;margin-top:'+em(4)+';font-size:'+em(15.5)+';color:'+bc+';line-height:1.35;'+clamp(2)+'">'+esc(sp[1])+'</div></div>';
      });
      h += '</div>';
    } else if (p.mode === 'strip') {
      h += '<div style="'+box(p)+'border-radius:'+em(12)+';background:'+rgba(ac, 0.14)+';display:flex;align-items:center;padding:0 '+em(22)+' 0 '+em(28)+';overflow:hidden;">'
         + '<div style="position:absolute;left:0;top:20%;bottom:20%;width:'+em(6)+';border-radius:'+em(3)+';background:'+ac+';"></div>'
         + '<div'+(p.conc ? ed(idx,'dsch-conc') : ed(idx,'content-plain',0))+' style="outline:none;cursor:text;font-size:'+em(18)+';font-weight:600;color:'+tc+';line-height:1.3;'+clamp(2)+'">'+esc(p.text)+'</div></div>';
    }
  }
  return h + '</div>';
};
})();

/* ============================================================
   DS Charts — editor UI: Add Slide → Generate chart wizard, chart editor,
   automatic chart slides for percentage data.
   CFG (printed by PHP): window.DS_CHART_CFG = {rest, nonce, cost}
   ============================================================ */
(function(){
var DC = window.DS_CHART; if (!DC || DC.ui) return;
DC.ui = 1;
var CFG = window.DS_CHART_CFG || {}, u = DC.u;
function $(id){ return document.getElementById(id); }
function esc(s){ return String(s == null ? '' : s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function toast(m, k){ if (window.dspToast) window.dspToast(m, k || 'success'); }
function deckArrays(){
  var a = [];
  if (window.S && S.data && Array.isArray(S.data.slides)) a.push(S.data.slides);
  if (window.G && G.pptData && Array.isArray(G.pptData.slides) && a.indexOf(G.pptData.slides) < 0) a.push(G.pptData.slides);
  return a;
}
function tplActive(){ try { return window.DS_TPL_ACTIVE ? window.DS_TPL_ACTIVE() : null; } catch(e){ return null; } }
function theme(){
  var el = $('dsp-design'), k = el && el.value ? el.value : 'navy-pro', TH = window.TH || {};
  return TH[k] || TH['navy-pro'] || {bg:'#1B2A4A', tc:'#FFFFFF', bc:'#C8D8F0', ac:'#4A9EFF'};
}
function renderAny(s, v){
  var T = tplActive();
  if (T) { try { return T.render(s, v, -1); } catch(e){} }
  return DC.renderClassic(s, theme(), v, -1);
}
function cost(){
  var c = parseInt(CFG.cost, 10);
  if (!c) { var el = $('dsp-addslide-cost'); c = el ? parseInt(el.textContent, 10) : 0; }
  return c || 5;
}

/* ---------------- CSS ---------------- */
var CSS = ''
+ '#dsp-ppt-main-slide .dsch-box{cursor:pointer;}'
+ '.dsch-edit{position:absolute;top:6px;right:6px;z-index:5;display:none;padding:6px 12px;border-radius:999px;border:0;background:#1a73e8;color:#fff;font:600 12px/1 -apple-system,Segoe UI,Arial,sans-serif;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,.35);}'
+ '#dsp-ppt-main-slide .dsch-box:hover .dsch-edit{display:block;}'
+ '#dsp-ppt-main-slide .dsch-box:hover{outline:2px dashed rgba(138,180,248,.55);outline-offset:4px;border-radius:6px;}'
+ '#dsp-ppt-thumbs .dsch-edit,#vp-list .dsch-edit,.dsp-thumb-box .dsch-edit,#dsch-prev .dsch-edit{display:none!important;}'
+ '#dsch-addbtn{margin-top:14px;width:100%;display:flex;align-items:center;gap:12px;padding:12px 14px;border-radius:10px;border:1.5px dashed #3b4a6b;background:linear-gradient(135deg,rgba(26,115,232,.14),rgba(138,180,248,.06));color:#e8eaed;cursor:pointer;text-align:left;font-family:inherit;}'
+ '#dsch-addbtn:hover{border-color:#8ab4f8;background:linear-gradient(135deg,rgba(26,115,232,.24),rgba(138,180,248,.1));}'
+ '#dsch-addbtn b{display:block;font-size:13px;margin-bottom:2px;}#dsch-addbtn span{font-size:11px;color:#9ca3af;}'
+ '#dsch-addbtn i{font-style:normal;font-size:22px;line-height:1;}'
+ '#dsch-modal{position:fixed;inset:0;z-index:120000;background:rgba(0,0,0,.66);backdrop-filter:blur(4px);display:none;align-items:center;justify-content:center;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Arial,sans-serif;}'
+ '#dsch-modal.on{display:flex;}'
+ '#dsch-addbtn,#dsch-addbtn *,#dsch-modal button,#dsch-modal select,#dsch-modal input,#dsch-modal textarea{text-transform:none!important;letter-spacing:normal!important;}'
+ '#dsch-card{width:960px;max-width:96vw;max-height:94vh;display:flex;flex-direction:column;background:#1d1f27;border:1px solid #2a2a2a;border-radius:12px;overflow:hidden;box-shadow:0 12px 50px rgba(0,0,0,.6);color:#e8eaed;}'
+ '#dsch-head{display:flex;align-items:center;justify-content:space-between;padding:14px 20px;background:#11131b;border-bottom:1px solid #2a2a2a;}'
+ '#dsch-head h3{margin:0;font-size:15px;font-weight:700;color:#e8eaed;}#dsch-head small{color:#8b93a7;font-size:12px;margin-left:8px;font-weight:500;}'
+ '#dsch-x{background:none;border:0;color:#9ca3af;font-size:22px;cursor:pointer;line-height:1;}'
+ '#dsch-body{flex:1;min-height:0;overflow:auto;padding:18px 20px;}'
+ '#dsch-foot{display:flex;align-items:center;gap:10px;padding:12px 20px;background:#11131b;border-top:1px solid #2a2a2a;}'
+ '.dsch-b{padding:9px 16px;border-radius:7px;border:1px solid #2f3340;background:#15171f;color:#c3c8d4;font:600 13px/1 inherit;font-family:inherit;cursor:pointer;}'
+ '.dsch-b:hover{border-color:#4b5563;color:#fff;}'
+ '.dsch-p{background:#1a73e8;border-color:#1a73e8;color:#fff;}.dsch-p:hover{background:#1765cc;}.dsch-p[disabled]{opacity:.6;cursor:wait;}'
+ '.dsch-fams{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;}'
+ '.dsch-fam{background:#15171f;border:2px solid #2a2d38;border-radius:12px;padding:12px;cursor:pointer;text-align:center;transition:border-color .15s,transform .15s;}'
+ '.dsch-fam:hover{border-color:#8ab4f8;transform:translateY(-2px);}'
+ '.dsch-fam .ic{height:96px;display:flex;align-items:center;justify-content:center;}.dsch-fam .ic svg{height:96px;}'
+ '.dsch-fam b{display:block;margin-top:8px;font-size:13px;color:#e8eaed;}.dsch-fam span{display:block;margin-top:3px;font-size:11px;color:#8b93a7;}'
+ '.dsch-q{font-size:13px;color:#c3c8d4;margin:0 0 14px;}'
+ '.dsch-grid{display:grid;grid-template-columns:minmax(0,1fr) 440px;gap:20px;}'
+ '@media(max-width:860px){.dsch-grid{grid-template-columns:1fr;}.dsch-fams{grid-template-columns:repeat(2,1fr);}}'
+ '.dsch-l{font-size:10px;font-weight:700;letter-spacing:.7px;text-transform:uppercase;color:#8b93a7;margin:14px 0 7px;display:flex;align-items:center;justify-content:space-between;}'
+ '.dsch-l:first-child{margin-top:0;}'
+ '.dsch-tabs{display:flex;gap:6px;flex-wrap:wrap;}'
+ '.dsch-tab{padding:6px 11px;border-radius:999px;border:1px solid #2f3340;background:#15171f;color:#aeb4c2;font:600 12px/1 inherit;font-family:inherit;cursor:pointer;}'
+ '.dsch-tab.on{background:#1a73e8;border-color:#1a73e8;color:#fff;}'
+ '.dsch-styles{display:grid;grid-template-columns:repeat(auto-fill,minmax(92px,1fr));gap:8px;}'
+ '.dsch-st{background:#15171f;border:2px solid #2a2d38;border-radius:9px;padding:6px 6px 7px;cursor:pointer;text-align:center;}'
+ '.dsch-st svg{height:46px;width:100%;}.dsch-st div{font-size:11px;color:#c3c8d4;margin-top:3px;font-weight:600;}'
+ '.dsch-st.on{border-color:#8ab4f8;background:#1b2436;}'
+ '.dsch-in{width:100%;box-sizing:border-box;background:#0f1117;border:1.5px solid #2a2d38;border-radius:7px;padding:8px 10px;color:#e8eaed;font:13px/1.3 inherit;font-family:inherit;outline:none;}'
+ '.dsch-in:focus{border-color:#1a73e8;}'
+ 'textarea.dsch-in{min-height:64px;resize:vertical;}'
+ '.dsch-rows{display:flex;flex-direction:column;gap:6px;}'
+ '.dsch-row{display:grid;grid-template-columns:14px minmax(0,1fr) 110px 30px;gap:8px;align-items:center;}'
+ '.dsch-row i{width:12px;height:12px;border-radius:4px;display:block;}'
+ '.dsch-del{width:30px;height:32px;border-radius:7px;border:1px solid #2f3340;background:#15171f;color:#9ca3af;cursor:pointer;font-size:14px;}'
+ '.dsch-del:hover{color:#f87171;border-color:#7f1d1d;}'
+ '.dsch-add{margin-top:8px;padding:8px 12px;border-radius:7px;border:1.5px dashed #3b4a6b;background:transparent;color:#8ab4f8;font:600 12px/1 inherit;font-family:inherit;cursor:pointer;width:100%;}'
+ '.dsch-add:hover{border-color:#8ab4f8;}'
+ '.dsch-inline{display:flex;gap:10px;align-items:center;}'
+ '.dsch-inline>*{flex:1;}'
+ '.dsch-ang{display:flex;align-items:center;gap:10px;}.dsch-ang input[type=range]{flex:1;accent-color:#1a73e8;}.dsch-ang b{width:44px;text-align:right;font-size:12px;color:#e8eaed;}'
+ '#dsch-prevwrap{position:sticky;top:0;}'
+ '#dsch-prev{position:relative;width:440px;max-width:100%;aspect-ratio:16/9;font-size:7.82px;border-radius:8px;overflow:hidden;box-shadow:0 6px 24px rgba(0,0,0,.45);}'
+ '#dsch-prev *{pointer-events:none;}'
+ '.dsch-vars{display:grid;grid-template-columns:repeat(5,1fr);gap:6px;margin-top:10px;}'
+ '.dsch-var{position:relative;aspect-ratio:16/9;font-size:1.45px;border-radius:4px;overflow:hidden;border:2px solid #2a2d38;cursor:pointer;}'
+ '.dsch-var *{pointer-events:none;}.dsch-var.on{border-color:#8ab4f8;}'
+ '.dsch-hint{font-size:11px;color:#8b93a7;margin-top:6px;line-height:1.45;}'
+ '.dsch-cost{margin-left:auto;font-size:11px;color:#9ca3af;background:#0f1117;border:1px solid #2a2d38;border-radius:999px;padding:5px 10px;}'
+ '.dsch-sample{position:absolute;top:8px;left:8px;z-index:3;font:700 10px/1 Arial;color:#111;background:#fbbf24;border-radius:999px;padding:4px 8px;letter-spacing:.4px;}';

function injectCss(){ if ($('dsch-css')) return; var st = document.createElement('style'); st.id = 'dsch-css'; st.textContent = CSS; document.head.appendChild(st); }

/* ---------------- state ---------------- */
var ST = null;   /* {mode:'new'|'edit', idx, step, fam, chart, topic, notes, v} */
var SAMPLE = {
  pie:{labels:['Enterprise','SMB','Consumer','Partners'], values:[45,30,15,10], unit:'%'},
  bar:{labels:['2022','2023','2024','2025'], values:[1.2,1.9,2.6,3.8], unit:'$'},
  line:{labels:['Q1','Q2','Q3','Q4','Q5'], values:[12,19,27,34,46], unit:'%'},
  gauge:{labels:['Retention','Satisfaction','On-time delivery'], values:[94,87,72], unit:'%'}
};
function rowsOf(ch){ var L = ch.labels || [], V = ch.values || [], out = []; for (var i = 0; i < Math.max(L.length, V.length); i++) out.push({l: L[i] == null ? '' : String(L[i]), v: V[i] == null ? '' : String(V[i])}); return out; }
function filled(ch){ return rowsOf(ch).filter(function(r){ return r.v !== '' && isFinite(parseFloat(r.v)); }).length; }
function previewChart(){
  var c = ST.chart;
  if (filled(c) >= 1) return c;
  var s = SAMPLE[DC.FAM[c.style]] || SAMPLE.pie;
  return {style:c.style, angle:c.angle, max:c.max, unit:c.unit || s.unit, labels:s.labels, values:s.values, _sample:true};
}
function previewSlide(){
  var base = ST.mode === 'edit' ? currentSlide(ST.idx) : null, s = {};
  if (base) for (var k in base) s[k] = base[k];
  s.type = 'chart'; s.chart = previewChart();
  if (!base) {
    s.title = ST.topic ? ST.topic.charAt(0).toUpperCase() + ST.topic.slice(1) : 'Your chart title';
    s.subtitle = 'AI writes the title, subtitle and insights';
    var extra = (ST.notes || '').split(/\n+/).map(function(x){ return x.trim(); }).filter(Boolean).slice(0, 3);
    s.content = extra.length ? extra : ['Key insight: AI explains what the numbers mean for your audience.', 'Trend: The biggest movement in the data, in one line.', 'Action: What to do next based on this chart.'];
  }
  return s;
}
function currentSlide(i){ var a = deckArrays(); return a.length ? a[0][i] : null; }

/* ---------------- modal ---------------- */
function modal(){
  var m = $('dsch-modal'); if (m) return m;
  injectCss();
  m = document.createElement('div'); m.id = 'dsch-modal';
  m.innerHTML = '<div id="dsch-card"><div id="dsch-head"><h3 id="dsch-title"></h3><button type="button" id="dsch-x">&#10005;</button></div><div id="dsch-body"></div><div id="dsch-foot"></div></div>';
  document.body.appendChild(m);
  m.addEventListener('mousedown', function(e){ if (e.target === m) close(); });
  $('dsch-x').onclick = close;
  m.addEventListener('keydown', function(e){ if (e.key === 'Escape') close(); e.stopPropagation(); });
  return m;
}
function close(){ var m = $('dsch-modal'); if (m) m.classList.remove('on'); ST = null; }

function famCard(f){
  var st = DC.STYLES[f[0]][0][0], desc = {pie:'Shares of a whole — %', bar:'Compare values', line:'Trends over time', gauge:'Progress towards 100%'}[f[0]];
  return '<div class="dsch-fam" data-fam="'+f[0]+'"><div class="ic">'+DC.icon(f[0] === 'pie' ? 'donut' : f[0] === 'gauge' ? 'gauge' : st, '#8ab4f8')+'</div><b>'+f[1]+'</b><span>'+desc+'</span></div>';
}

function draw(){
  var m = modal(), body = $('dsch-body'), foot = $('dsch-foot'), c = ST.chart;
  $('dsch-title').innerHTML = ST.mode === 'edit' ? '&#128202; Edit chart <small>changes are free</small>' : '&#128202; Generate chart slide <small>'+(ST.step === 1 ? 'Step 1 of 2' : 'Step 2 of 2')+'</small>';
  if (ST.step === 1) {
    body.innerHTML = '<p class="dsch-q">Which type of chart do you want?</p><div class="dsch-fams">'+DC.FAMILIES.map(famCard).join('')+'</div>'
      + '<div class="dsch-hint" style="margin-top:14px;">Next you can type your numbers (percentages, amounts…), any text, and choose the shape and angle. You can edit the chart any time after it is added.</div>';
    Array.prototype.forEach.call(body.querySelectorAll('.dsch-fam'), function(el){ el.onclick = function(){ setFam(el.getAttribute('data-fam')); ST.step = 2; draw(); }; });
    foot.innerHTML = '<button type="button" class="dsch-b" id="dsch-cancel">Cancel</button><span class="dsch-cost">'+cost()+' credits</span>';
    $('dsch-cancel').onclick = close;
    m.classList.add('on');
    return;
  }
  var fam = DC.FAM[c.style], rows = rowsOf(c), cols = DC.palette(theme().ac || '#4A9EFF', true, Math.max(rows.length, 3));
  var h = '<div class="dsch-grid"><div>';
  h += '<div class="dsch-l">Chart type</div><div class="dsch-tabs">' + DC.FAMILIES.map(function(f){ return '<button type="button" class="dsch-tab'+(f[0] === fam ? ' on' : '')+'" data-fam="'+f[0]+'">'+f[1]+'</button>'; }).join('') + '</div>';
  h += '<div class="dsch-l">Shape</div><div class="dsch-styles">' + DC.STYLES[fam].map(function(s){ return '<div class="dsch-st'+(s[0] === c.style ? ' on' : '')+'" data-st="'+s[0]+'">'+DC.icon(s[0], '#8ab4f8')+'<div>'+s[1]+'</div></div>'; }).join('') + '</div>';
  if (fam === 'pie' && c.style !== 'half') h += '<div class="dsch-l">Start angle</div><div class="dsch-ang"><input type="range" min="0" max="355" step="5" id="dsch-angle" value="'+(c.angle || 0)+'"/><b id="dsch-angv">'+(c.angle || 0)+'&deg;</b></div>';
  h += '<div class="dsch-l"><span>Data'+(fam === 'pie' ? ' (percentages or amounts)' : '')+'</span><span style="text-transform:none;letter-spacing:0;font-weight:500;">'+rows.length+' / 12</span></div><div class="dsch-rows" id="dsch-rows">';
  rows.forEach(function(r, i){
    h += '<div class="dsch-row"><i style="background:'+cols[(fam === 'bar' || fam === 'line') ? 0 : i % cols.length]+'"></i>'
      + '<input class="dsch-in" data-k="l" data-i="'+i+'" value="'+esc(r.l)+'" placeholder="'+esc((SAMPLE[fam].labels[i] || 'Label') )+'"/>'
      + '<input class="dsch-in" data-k="v" data-i="'+i+'" value="'+esc(r.v)+'" inputmode="decimal" placeholder="'+esc(SAMPLE[fam].values[i] != null ? SAMPLE[fam].values[i] : '0')+'"/>'
      + '<button type="button" class="dsch-del" data-del="'+i+'" title="Delete row">&#10005;</button></div>';
  });
  h += '</div>' + (rows.length < 12 ? '<button type="button" class="dsch-add" id="dsch-addrow">+ Add row</button>' : '');
  h += '<div class="dsch-inline" style="margin-top:12px;"><div><div class="dsch-l" style="margin-top:0;">Unit</div><select class="dsch-in" id="dsch-unit">'
    + [['%','% Percent'],['$','$ Dollar'],['€','€ Euro'],['£','£ Pound'],['₹','₹ Rupee'],['','None'],['_c','Custom…']].map(function(o){ var on = (c.unit === o[0]) || (o[0] === '_c' && ['%','$','€','£','₹',''].indexOf(c.unit) < 0); return '<option value="'+o[0]+'"'+(on ? ' selected' : '')+'>'+o[1]+'</option>'; }).join('')
    + '</select></div>'
    + (['%','$','€','£','₹',''].indexOf(c.unit) < 0 ? '<div><div class="dsch-l" style="margin-top:0;">Custom unit</div><input class="dsch-in" id="dsch-cunit" maxlength="8" value="'+esc(c.unit)+'" placeholder="e.g. users"/></div>' : '')
    + (fam === 'gauge' ? '<div><div class="dsch-l" style="margin-top:0;">Full scale (100%)</div><input class="dsch-in" id="dsch-max" inputmode="decimal" value="'+esc(c.max || '')+'" placeholder="100"/></div>' : '')
    + '</div>';
  if (ST.mode === 'new') {
    h += '<div class="dsch-l">What is this chart about?</div><input class="dsch-in" id="dsch-topic" value="'+esc(ST.topic)+'" placeholder="e.g. Market share by customer segment in 2025"/>';
    h += '<div class="dsch-l">Extra text or points <span style="text-transform:none;letter-spacing:0;font-weight:500;">optional · one per line</span></div><textarea class="dsch-in" id="dsch-notes" placeholder="e.g. Enterprise grew from 31% to 45% after the new sales team&#10;Focus on onboarding in 2026">'+esc(ST.notes)+'</textarea>';
    h += '<div class="dsch-hint">AI writes a title, subtitle and 3 insights from your data and text. No numbers yet? Leave the rows empty and AI will create realistic data from your topic.</div>';
  }
  h += '</div><div><div id="dsch-prevwrap"><div class="dsch-l" style="margin-top:0;">Preview</div><div id="dsch-prev"></div><div class="dsch-l">Layout</div><div class="dsch-vars" id="dsch-vars"></div></div></div></div>';
  body.innerHTML = h;
  foot.innerHTML = (ST.mode === 'new' ? '<button type="button" class="dsch-b" id="dsch-back">&#8592; Back</button>' : '')
    + '<button type="button" class="dsch-b" id="dsch-cancel">Cancel</button>'
    + (ST.mode === 'new' ? '<span class="dsch-cost">'+cost()+' credits</span>' : '<span style="margin-left:auto"></span>')
    + '<button type="button" class="dsch-b dsch-p" id="dsch-go">'+(ST.mode === 'new' ? '&#10024; Generate chart slide' : '&#10003; Apply changes')+'</button>';
  bind();
  preview();
  m.classList.add('on');
}
function setFam(f){
  var c = ST.chart;
  if (DC.FAM[c.style] === f) return;
  c.style = DC.STYLES[f][0][0];
  if (f === 'pie') c.style = 'donut';
  if (f === 'gauge' && !c.unit) c.unit = '%';
}
function bind(){
  var body = $('dsch-body'), c = ST.chart;
  Array.prototype.forEach.call(body.querySelectorAll('.dsch-tab'), function(el){ el.onclick = function(){ setFam(el.getAttribute('data-fam')); draw(); }; });
  Array.prototype.forEach.call(body.querySelectorAll('.dsch-st'), function(el){ el.onclick = function(){ c.style = el.getAttribute('data-st'); draw(); }; });
  var ang = $('dsch-angle'); if (ang) ang.oninput = function(){ c.angle = parseInt(ang.value, 10) || 0; $('dsch-angv').innerHTML = c.angle + '&deg;'; preview(); };
  Array.prototype.forEach.call(body.querySelectorAll('.dsch-rows .dsch-in'), function(el){
    el.oninput = function(){
      var i = +el.getAttribute('data-i'), k = el.getAttribute('data-k');
      if (!c.labels) c.labels = []; if (!c.values) c.values = [];
      if (k === 'l') c.labels[i] = el.value; else c.values[i] = el.value.replace(/[^0-9.\-]/g, '') === '' ? '' : el.value.replace(/[^0-9.\-]/g, '');
      preview();
    };
    el.onkeydown = function(e){ if (e.key === 'Enter') { e.preventDefault(); var nx = el.parentNode.nextSibling; if (!nx && (c.labels || []).length < 12) { addRow(); return; } var f = nx && nx.querySelector('.dsch-in'); if (f) f.focus(); } };
  });
  Array.prototype.forEach.call(body.querySelectorAll('.dsch-del'), function(el){ el.onclick = function(){
    var i = +el.getAttribute('data-del'), r = rowsOf(c); r.splice(i, 1);
    if (!r.length) r.push({l:'', v:''});
    c.labels = r.map(function(x){ return x.l; }); c.values = r.map(function(x){ return x.v; }); draw();
  }; });
  var ad = $('dsch-addrow'); if (ad) ad.onclick = addRow;
  var un = $('dsch-unit'); if (un) un.onchange = function(){ c.unit = un.value === '_c' ? 'units' : un.value; draw(); };
  var cu = $('dsch-cunit'); if (cu) cu.oninput = function(){ c.unit = cu.value.trim(); preview(); };
  var mx = $('dsch-max'); if (mx) mx.oninput = function(){ c.max = mx.value.replace(/[^0-9.]/g, ''); preview(); };
  var tp = $('dsch-topic'); if (tp) tp.oninput = function(){ ST.topic = tp.value; preview(); };
  var nt = $('dsch-notes'); if (nt) nt.oninput = function(){ ST.notes = nt.value; preview(); };
  var bk = $('dsch-back'); if (bk) bk.onclick = function(){ ST.step = 1; draw(); };
  $('dsch-cancel').onclick = close;
  $('dsch-go').onclick = ST.mode === 'new' ? generate : apply;
}
function addRow(){
  var c = ST.chart, r = rowsOf(c); if (r.length >= 12) return;
  r.push({l:'', v:''}); c.labels = r.map(function(x){ return x.l; }); c.values = r.map(function(x){ return x.v; });
  draw();
  var ins = $('dsch-rows').querySelectorAll('.dsch-in[data-k="l"]'); if (ins.length) ins[ins.length - 1].focus();
}
var _pt = 0;
function preview(){
  clearTimeout(_pt);
  _pt = setTimeout(function(){
    if (!ST) return;
    var s = previewSlide(), pv = $('dsch-prev'); if (!pv) return;
    pv.innerHTML = renderAny(s, ST.v) + (s.chart._sample ? '<span class="dsch-sample">SAMPLE DATA</span>' : '');
    var vs = $('dsch-vars'); if (!vs) return;
    var T = tplActive(), list = T ? (T.variants('chart', s) || DC.VARIANTS) : DC.VARIANTS;
    vs.innerHTML = list.slice(0, 10).map(function(v){ return '<div class="dsch-var'+(v.id === ST.v ? ' on' : '')+'" data-v="'+v.id+'" title="'+esc(v.lbl)+'">'+renderAny(s, v.id)+'</div>'; }).join('');
    Array.prototype.forEach.call(vs.querySelectorAll('.dsch-var'), function(el){ el.onclick = function(){ ST.v = el.getAttribute('data-v'); preview(); }; });
  }, 60);
}
function cleanChart(c){
  var r = rowsOf(c).filter(function(x){ return x.v !== '' && isFinite(parseFloat(x.v)); });
  return {style:c.style, angle:c.angle || 0, unit:c.unit == null ? '%' : c.unit, max: c.max ? parseFloat(c.max) || '' : '',
          labels:r.map(function(x, i){ return x.l.trim() || ('Item ' + (i+1)); }), values:r.map(function(x){ return parseFloat(x.v); })};
}

/* ---------------- deck updates ---------------- */
function refresh(idx){
  if (window.renderEditList) try { window.renderEditList(); } catch(e){}
  if (window.dspRenderThumbs) window.dspRenderThumbs();
  if (window.dspSelectThumb) window.dspSelectThumb(idx);
  setTimeout(function(){ if (window.applyCanvas && window.S) { S.idx = idx; window.applyCanvas(idx, S.choices['slide_'+idx] || 'v1'); } }, 120);
}
function setChoice(idx, v){
  if (!window.S) return;
  S.choices = S.choices || {};
  S.choices['slide_'+idx] = v || 'v1';
  try { sessionStorage.setItem('dspVPC', JSON.stringify(S.choices)); } catch(e){}
}
function insertSlide(at, slide){
  var arrs = deckArrays();
  arrs.forEach(function(a){ a.splice(at, 0, at === arrs[0].length ? slide : slide); });
  if (window.S && S.choices) {
    var nc = {};
    Object.keys(S.choices).forEach(function(k){ var m = k.match(/^slide_(\d+)$/); if (!m) { nc[k] = S.choices[k]; return; } var n = +m[1]; nc['slide_' + (n >= at ? n + 1 : n)] = S.choices[k]; });
    S.choices = nc;
  }
}

function generate(){
  var c = cleanChart(ST.chart), btn = $('dsch-go');
  var topic = (ST.topic || '').trim();
  if (!topic && !c.values.length) { toast('Type what the chart is about, or add some data.', 'error'); var tp = $('dsch-topic'); if (tp) tp.focus(); return; }
  if (window.G && typeof G.credits === 'number' && G.credits < cost()) { toast('Not enough credits. Need ' + cost() + ' credits.', 'error'); return; }
  btn.disabled = true; btn.innerHTML = 'Generating…';
  var lang = $('dsp-lang'), ttl = $('dsp-etitle');
  fetch(CFG.rest || '/wp-json/ds/v1/chart-slide', {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json', 'X-WP-Nonce': window.NONCE || window.DSP_NONCE || CFG.nonce || ''},
    body: JSON.stringify({topic: topic, notes: ST.notes || '', chart: c, language: lang ? lang.value : 'English', deck_title: ttl ? ttl.textContent.trim() : ''})})
  .then(function(r){ return r.json(); })
  .then(function(d){
    if (d && d.credits_balance !== undefined && window.G) { G.credits = d.credits_balance; var ce = $('ds-cr-display'); if (ce) ce.textContent = Number(G.credits).toLocaleString(); }
    if (!d || !d.success || !d.slide) { btn.disabled = false; btn.innerHTML = '&#10024; Generate chart slide'; toast((d && (d.message || d.error)) || 'Could not generate the chart. Please try again.', 'error'); return; }
    var arrs = deckArrays(); if (!arrs.length) { close(); return; }
    var at = arrs[0].length, v = ST.v;
    insertSlide(at, d.slide);
    setChoice(at, v);
    close();
    if (window.dspAddSlideClose) window.dspAddSlideClose();
    refresh(at);
    toast('Chart slide added! (' + (d.cost || cost()) + ' credits used)');
  })
  .catch(function(){ btn.disabled = false; btn.innerHTML = '&#10024; Generate chart slide'; toast('Network error. Try again.', 'error'); });
}
function apply(){
  var c = cleanChart(ST.chart);
  if (!c.values.length) { toast('Add at least one value.', 'error'); return; }
  var idx = ST.idx;
  deckArrays().forEach(function(a){ if (a[idx]) { a[idx].chart = c; a[idx].type = 'chart'; } });
  setChoice(idx, ST.v);
  close();
  refresh(idx);
  toast('Chart updated');
}

/* ---------------- entry points ---------------- */
DC.openWizard = function(topic){
  var fam = 'pie';
  ST = {mode:'new', step:1, idx:-1, topic: topic || '', notes:'', v:'v1', chart:{style:'donut', unit:'%', angle:0, labels:['','','',''], values:['','','','']}};
  draw();
  void fam;
};
DC.openEditor = function(idx){
  var s = currentSlide(idx); if (!s || !s.chart) return;
  var c = JSON.parse(JSON.stringify(s.chart));
  c.labels = (c.labels || []).map(String); c.values = (c.values || []).map(function(v){ return v == null ? '' : String(v); });
  if (!DC.FAM[c.style]) c.style = 'donut';
  ST = {mode:'edit', step:2, idx:idx, topic:'', notes:'', v:(window.S && S.choices && S.choices['slide_'+idx]) || 'v1', chart:c};
  draw();
};

/* Add Slide modal: keep the normal screen, add a "Generate chart" option */
function hookAddSlide(){
  var ce = $('dsp-addslide-cost'); if (ce && CFG.cost && ce.textContent !== CFG.cost + ' credits') ce.textContent = CFG.cost + ' credits';
  var body = $('dsp-addslide-body');
  if (!body || $('dsch-addbtn')) return;
  injectCss();
  var b = document.createElement('button'); b.type = 'button'; b.id = 'dsch-addbtn';
  b.innerHTML = '<i>&#128202;</i><div><b>Generate a chart slide</b><span>Pie, donut, bar, line, area, gauges &amp; progress — type your numbers or let AI create them</span></div>';
  b.onclick = function(){ var inp = $('dsp-addslide-input'), t = inp ? inp.value.trim() : ''; if (window.dspAddSlideClose) window.dspAddSlideClose(); DC.openWizard(t); };
  body.appendChild(b);
}
/* clicking the chart on the main canvas opens the editor */
document.addEventListener('click', function(e){
  var bx = e.target && e.target.closest ? e.target.closest('#dsp-ppt-main-slide .dsch-box') : null;
  if (!bx) return;
  var i = parseInt(bx.getAttribute('data-dsch-idx'), 10);
  if (i >= 0) { e.preventDefault(); DC.openEditor(i); }
}, true);
/* conclusion strip edits (Classic chart layout) */
document.addEventListener('focusout', function(e){
  var el = e.target; if (!el || !el.getAttribute || el.getAttribute('data-ed-field') !== 'dsch-conc') return;
  var i = parseInt(el.getAttribute('data-ed-slide'), 10), t = (el.innerText || '').trim();
  deckArrays().forEach(function(a){ if (a[i]) a[i].conclusion = t; });
}, true);

/* ---------------- automatic chart slides ----------------
   After a deck is generated (AI or Preserve My Text), slides whose points
   carry 2+ percentages get a chart slide right after them (max 2 per deck).
   Reopened drafts are left as they were. */
var _loadTs = 0, _of = window.fetch;
window.fetch = function(url){
  try { var us = typeof url === 'string' ? url : (url && url.url) || ''; if (us.indexOf('/ds/v1/load-ppt') !== -1) _loadTs = Date.now(); } catch(e){}
  return _of.apply(this, arguments);
};
var _seen = typeof WeakSet === 'function' ? new WeakSet() : null, _seenArr = [];
function seen(o){ return _seen ? _seen.has(o) : _seenArr.indexOf(o) >= 0; }
function mark(o){ if (_seen) _seen.add(o); else _seenArr.push(o); }
/* "94%: Retention | up from 81%" -> "Retention: 94%, up from 81%" (stats items read as insight points) */
function pointText(x){
  var t = String(x || ''), m = t.match(/^\s*([-+]?[\d.,]+\s?[%xX$€£₹KMB]*)\s*:\s*(.+)$/);
  if (!m) return t;
  var parts = m[2].split('|'), lab = parts[0].trim(), rest = parts.slice(1).join(' ').trim();
  return lab + ': ' + m[1].trim() + (rest ? ', ' + rest.charAt(0).toLowerCase() + rest.slice(1) : '');
}
function autoScan(){
  var fs = $('dsp-editor-fullscreen');
  if (!fs || !fs.classList.contains('dsp-open') || !window.G || !G.pptData || !Array.isArray(G.pptData.slides)) return;
  var pd = G.pptData; if (seen(pd)) return;
  var th = $('dsp-ppt-thumbs'); if (!th || th.children.length !== pd.slides.length) return;
  if (!window.S || !S.data || !Array.isArray(S.data.slides) || S.data.slides.length !== pd.slides.length) return;
  mark(pd);
  if (Date.now() - _loadTs < 30000 || pd.__dschAuto || CFG.auto === false) return;
  pd.__dschAuto = 1;
  var sl = S.data.slides, found = [];
  if (sl.some(function(s){ return s && s.type === 'chart'; })) return;
  /* the slide count the user asked for is kept: a slide with percentage data
     becomes a chart slide itself (its text stays as the insight points) */
  /* a planned deck (the AI chose its own charts): only those slides become charts; older decks keep the percentage scan */
  var planned = sl.some(function(s){ return s && (s.planner === 2 || s.chart_ai); });
  for (var i = 1; i < sl.length - 1 && found.length < 2; i++) {
    var src = sl[i]; if (!src) continue;
    if (planned) {
      var ca = src.chart_ai; if (!ca || !Array.isArray(ca.values) || ca.values.length < 2) continue;
      var cst = String(ca.style || 'column'); if (!DC.FAM || !DC.FAM[cst]) cst = 'column';
      found.push({i: i, chart: {style: cst, unit: ca.unit == null ? '' : String(ca.unit), labels: (ca.labels || []).slice(0, 8), values: ca.values.slice(0, 8), angle: 0}, content: (src.content || []).map(pointText)});
      continue;
    }
    if ((src.content || []).length > 4) continue;
    var ch = DC.detect(src); if (!ch) continue;
    found.push({i: i, chart: ch, content: (src.content || []).map(pointText)});
  }
  if (!found.length) return;
  found.forEach(function(f){
    deckArrays().forEach(function(a){ var s = a[f.i]; if (!s) return; s.__origType = s.type; s.type = 'chart'; s.chart = JSON.parse(JSON.stringify(f.chart)); s.content = f.content.slice(); s.auto = 1; });
    setChoice(f.i, 'v1');
  });
  var cur = (window.G && G.activeSlideIdx) || 0;
  refresh(cur);
  toast('📊 ' + found.length + ' slide' + (found.length > 1 ? 's' : '') + ' with percentages now show' + (found.length > 1 ? '' : 's') + ' a chart — click a chart to edit it.');
}
/* keep chart slides on a chart layout (other tools may pick an image layout id for every slide) */
function fixChoices(){
  if (!window.S || !S.data || !Array.isArray(S.data.slides) || !S.choices) return;
  var T = tplActive(), ok = {}; (T ? (T.variants('chart', {type:'chart'}) || DC.VARIANTS) : DC.VARIANTS).forEach(function(v){ ok[v.id] = 1; });
  var ch = false;
  S.data.slides.forEach(function(s, i){ if (s && s.type === 'chart' && S.choices['slide_'+i] && !ok[S.choices['slide_'+i]]) { S.choices['slide_'+i] = 'v1'; ch = true; } });
  if (ch) try { sessionStorage.setItem('dspVPC', JSON.stringify(S.choices)); } catch(e){}
}
var _t = setInterval(function(){ hookAddSlide(); try { autoScan(); fixChoices(); } catch(e){ if (window.console) console.warn('[DS Charts]', e); } }, 700);
void _t;
})();

<?php return ob_get_clean();
}
} // DS_CHARTS_V1
