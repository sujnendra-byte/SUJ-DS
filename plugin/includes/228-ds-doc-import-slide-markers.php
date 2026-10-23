<?php
/**
 * Code Snippets export — DS Doc Import — Slide Markers
 *
 * Snippet ID : 1021
 * Active     : yes
 * Priority   : 12
 * Scope      : global
 * Tags       : ds-ppt
 * Modified   : 2026-10-03 16:32:09
 * Load order : 219 of 219
 */
/**
 * DS Doc Import - Slide Markers
 * Run: Everywhere
 *
 * AI Presentation - uploaded file (TXT / MD / DOCX / PDF) written in the suggested format:
 *     Slide 1: Title
 *     subtitle
 *
 *     Slide 2: Heading
 *     Point:
 *     explanation ...
 * Before, an uploaded file ignored the "Slide N:" markers: the file name became the title,
 * "Slide 1", "Slide 2" ... were treated as ordinary points and the text was cut into slides of
 * four points. Pasting the same text already worked. With this snippet a file with two or more
 * "Slide N:" markers is read exactly like pasted text: every marker starts its own slide, the
 * line after it is the subtitle, "Point:" + next line become one point, and the deck title is
 * the title of Slide 1. Files without markers are read as before.
 * Needs the "DS Doc Import" snippet and the Preserve-mode parser (dspPreserveParse).
 * NEW SNIPPET (nothing existing is changed). Undo: deactivate this snippet.
 */
if ( ! defined('ABSPATH') ) exit;

add_action('wp_footer', function () {
    if ( is_admin() ) return;
    if ( function_exists('ds_tpl_should_print') && ! ds_tpl_should_print() ) return;
    ?>
<script id="ds-docimport-slide-markers">
(function(){
(function(){
  var tries = 0;
  function install(){
    var DI = window.DS_DOCIMPORT;
    if (!DI || !DI.read || !DI.heuristic || !DI.toDeck) { if (++tries < 80) setTimeout(install, 250); return; }
    if (DI.__slideMarks) return; DI.__slideMarks = 1;
    var read = DI.read, heur = DI.heuristic, toDeck = DI.toDeck, EXACT = null;
    var MARK = /(?:^|\n)[ \t]*Slide\s+\d+\s*[:\-\u2013\u2014.]/gi;
    function lineOf(b){ return b && b.t ? (b.k === 'li' ? '- ' + b.t : b.t) : ''; }
    /* the author wrote "Slide 1: ...", "Slide 2: ..." - every marker starts its own slide */
    function exact(text){
      if (typeof window.dspPreserveParse !== 'function') return null;
      var t = String(text || '').replace(/\r/g, '');
      if ((t.match(MARK) || []).length < 2) return null;
      t = t.replace(/(^|\n)([ \t]*Slide\s+\d+)\s*[\-\u2013\u2014.]\s*/gi, '$1$2: ').replace(/\n[ \t]*(Slide\s+\d+\s*:)/gi, '\n\n$1');
      var s = null; try { s = window.dspPreserveParse(t); } catch (e) { s = null; }
      return s && s.length >= 2 ? s : null;
    }
    DI.read = async function(file){
      var got = await read.apply(this, arguments);
      try {
        if (got && !got.slides) {
          var ext = String((file && file.name) || '').split('.').pop().toLowerCase(), text = '';
          if (ext === 'txt' || ext === 'md' || ext === 'markdown') text = await file.text();
          else text = (got.blocks || []).map(lineOf).filter(Boolean).join('\n');
          var s = exact(text);
          if (s) {
            EXACT = {file: file, slides: s};
            return {info: got.info, slides: s.map(function(x){ return {title: x.title || '', sub: x.subtitle || '', lines: (x.content || []).slice(), img: null}; })};
          }
        }
        if (EXACT && EXACT.file === file) EXACT = null;
      } catch (e) {}
      return got;
    };
    DI.heuristic = function(secs){
      var r = heur.apply(this, arguments);
      try { var a = DI.attached && DI.attached(); if (EXACT && a && a.file === EXACT.file && secs === a.secs && r) r.__dsExact = EXACT.slides; } catch (e) {}
      return r;
    };
    DI.toDeck = function(slides){
      if (slides && slides.__dsExact) { try { return JSON.parse(JSON.stringify(slides.__dsExact)); } catch (e) {} }
      return toDeck.apply(this, arguments);
    };
  }
  install();
})();
})();
</script>
<?php
}, 1106);
