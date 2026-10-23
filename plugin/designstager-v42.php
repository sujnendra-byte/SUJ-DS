<?php
/**
 * Plugin Name: Designstager-V42-AI Document (designed Word documents, 784 designs) + Word add-in
 * Description: All active DesignStager Code Snippets packaged as a single plugin. V42 = V41 (41.5.0) + AI Document: write with AI or use your own text, review, then a designed document in the AI Presentation-style editor; 784 designs in 8 categories (business, education, career, letters, meetings, finance, marketing, events) incl. 2-sided flyers, tri-fold brochures, posters, social posts, infographics, business cards, invitations, menus, certificates, tickets and folded cards; page sizes per type; Word, PDF, PNG, JPG, TIFF (96-1200 dpi) and EPS+SVG downloads + DesignStager Word add-in. Deactivate V41 before activating V42.
 * Version: 42.0.3
 * Author: DesignStager
 * Requires PHP: 7.4
 * License: GPL-2.0-or-later
 *
 * CHANGELOG
 * ---------
 * 42.0.3 - 2026-10-23 Picture fill, sizes and box-select. 5 includes updated (151, 265, 371, 372, 373).
 *   371-373  AI Document: Fill > Picture for shapes, design pieces, text boxes and texts (generate with AI,
 *        browse the store, upload). With several shapes selected, one picture shows through all of them.
 *        Card texts, the logo, the QR code and the photo can be made any size from their corners.
 *        Box-select can start outside the page. Shapes and texts move together on cards.
 *        A selected logo shows an Upload logo / Change logo button.
 *   151, 265  AI Presentation and AI Document: a GROUP of shapes with Fill > Picture shows one picture across
 *        all of them (also in the PowerPoint / Word download). Several shapes selected without a group each get
 *        their own copy. Ungroup removes the group's picture. Grouping shapes that all have a picture shares the first one.
 * 42.0.2 - 2026-10-23 AI Document business cards. 2 includes updated (372, 373; 371 in the previous commit).
 *   372  55 corporate business card designs (front/back, details-first and one-side layouts), QR codes and photos.
 *        FIXED: an uploaded logo did not show on the back; it now shows on both sides in different places.
 *        Logo size can be set for the front and the back. Card pages no longer spill onto a blank page in Word.
 *   373  Cards: texts, the logo, the QR code and the photo can be dragged anywhere (also in the Word file).
 *        Clicking or double-clicking the logo opens the logo dialog; the upload box is now easy to see.
 * 42.0.1 - 2026-10-23 Bug fixes found by loading every page, admin screen and REST route on a test site. 4 includes updated.
 *   058  Creator portal - FIXED: visitors who are not signed in got a fatal error (it called users_can_register(),
 *        which is not a WordPress function); they now see the sign-in screen with Sign up when registration is open.
 *   093  AI Images - FIXED: on a page with only [ds_ai_images] (no AI Studio tabs) it threw "dsSTab is not defined".
 *   108  Marketplace - FIXED: a fatal error when the download categories cannot be read (for example while
 *        Download Monitor is off or updating); the page now shows without categories instead.
 *   124  My Creations - FIXED: PHP warning "Undefined variable \$cashback_pct" when the user has no images yet.
 * 42.0.0 - 2026-10-23 AI Document and the Word add-in. 1 include updated (353), 4 added (371-374).
 *   353  DS Vector Export (staging id 1191) - UPDATED: EPS + SVG download also loads on the AI Document page.
 *   371  DS AI Document - Backend (staging id 1256) - NEW: AI writing (no invented facts, [placeholders]), credits,
 *        save / load My documents, document types incl. flyer, brochure, poster, social post, infographic, business card.
 *   372  DS AI Document - Engine (staging id 1257) - NEW: 784 document designs, page sizes per type (A3, A5, business card,
 *        social sizes, landscape), multi-page / multi-panel designs, readable text colours, Word (.docx) export.
 *   373  DS AI Document - Page & Editor (staging id 1258) - NEW: [ds_ai_document] page (creates the AI Document page on
 *        first admin visit), AI Presentation-style editor, downloads Word / PDF / PNG / JPG / TIFF with dpi choice / EPS+SVG.
 *   374  DS Word Add-in (staging id 1259) - NEW: DesignStager task pane for Microsoft Word (?ds_word=taskpane),
 *        sign-in tokens, Settings > DesignStager Word > Download manifest.xml.
 *   Not included: staging 1263 / 1265 (older copies of the AI Document engine and backend) - 372 and 371 replace them.
 *   The add-in bridge (148) stays as in 41.5.0: it is newer than staging 862.
 * 41.5.0 - 2026-10-17 Privacy Policy page: names the PowerPoint add-in sign-in storage, AI partners and no AI training. 1 include updated (261).
 * 41.4.0 - 2026-10-17 PowerPoint add-in on Safari: AI Suites tile pictures fill the whole tile. 1 include updated (148).
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED: in Safari the AI Suites tile pictures were sized from
 *        their height and left a blank strip on the right of each tile; they now take the full tile width.
 * 41.3.0 - 2026-10-17 PowerPoint add-in on Safari: sign-in window and pop-ups. 1 include updated (148), 1 added (370).
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED: on PowerPoint for the web, when the large window has to
 *        open without a click (for example right after signing in), PowerPoint shows its own "Allow" prompt first, so
 *        Safari no longer blocks the window as a pop-up. Clearer message if the window still does not open.
 *   370  DS Office - sign-in window fixes (staging id 1250) - NEW: someone already signed in on the website who clicks
 *        Sign in / Sign up in the add-in goes straight to the add-in start page (it showed the login form without the
 *        Google / Microsoft buttons); "Skip to content" no longer shows when the PowerPoint window opens.
 * 41.2.0 - 2026-10-16 Free presentation designs picked from a list. 1 include updated (196).
 *   196  DS PPT Free Plan (staging id 968) - UPDATED: Settings > Free Presentations lists every design template with a
 *        tick box (search, Select all, Clear, Show free only, "N of M designs free") instead of a text box of ids; ticked
 *        designs are free, the rest locked. New or renamed templates show in the list by themselves. Classic stays free.
 *        The locked-design message no longer names only Classic / Glassline / Cobalt Bold.
 * 41.1.0 - 2026-10-16 Crop for pictures inside shapes. 1 include updated (265).
 *   265  DS Template Element Editor (staging id 1078) - UPDATED: a picture inside a shape (template photo shapes,
 *        Fill > Picture) gets a Crop button (on the picture under Change image, and in the toolbar). Drag the picture
 *        to move it inside the shape, scroll to zoom (up to 400%); the whole picture shows faintly around the shape
 *        while cropping; Crop picture panel with Zoom / Left-right / Top-bottom sliders, Reset and Done; Esc closes.
 * 41.0.0 - 2026-10-16 V41 = V40 (40.0.10) + updates made on staging on 2026-10-15 / 16. 6 includes updated, 16 added.
 *   035  DS Unified Dashboard (staging id 721) - UPDATED: the DS User Activity page shows the new all-users report
 *        (when no single user is looked up) and its download counts come from the new daily log.
 *   067  DS Daily Download Limit API (staging id 753) - UPDATED: every download is logged per day for every plan
 *        (free downloads by Business / unlimited users were never counted before).
 *   151  DS Template Engine (T00) (staging id 952) - UPDATED: the AI plans each deck like an expert presenter for its
 *        real type (investor pitch, sales, company profile, report, training, lesson, research ...) instead of one
 *        fixed slide plan; slide types are chosen by content; no invented statistics; charts only from numbers the
 *        user gave (the AI's own chart, or one built from the topic's figures); recolour keeps marked parts.
 *   165  DS Charts (staging id 898) - UPDATED: a planned deck becomes chart slides only where the AI placed a chart
 *        (older decks keep the percentage scan). The plugin keeps its single combined <script> block.
 *   265  DS Template Element Editor (staging id 1078) - UPDATED: selection handles shrink for small objects, Draw and
 *        Edit points, Change image on picture-filled shapes, "Loading your image..." spinner, photo shapes keyed safely.
 *   343  DS Deck Structure (staging id 1178) - UPDATED: stats / timeline / quote only when the content calls for them;
 *        agenda only in longer decks.
 *   354-365  DS Template 130-141 (staging ids 1216-1227) - NEW: Aurora Deck, Blob Collage, Arc Profile, Chapter Blaze,
 *        Leaf Lens, Spark Ideas, Corner Shards, Offset Frame, Nav Rail, Sage Tiles, Plum Ledger, Cloud Nine.
 *   366  DS Editor Loading Screen (staging id 1232) - NEW: "Your presentation is loading..." until pictures and layouts
 *        are ready; editing is blocked until then.
 *   367  DS Template Photo Shapes (staging id 1237) - NEW: template photos become real pictures cut into the shape
 *        (no more full-width photo with covering shapes), for every template; editable like a picture fill.
 *   368  DS User Activity Report (staging id 1244) - NEW: all users by period (Today / Yesterday / This week /
 *        This month / All time), sort by new signups / name / active users, Free / Paid filter; credits, free and
 *        pro downloads, AI images, videos, audio, PPTs generated and PPTs downloaded.
 *   369  DS Downloads - Free / Pro in one click (staging id 1246, admin only) - NEW: Free / Pro badge column (click to
 *        switch), Make Free / Make Pro bulk actions, select all items on every page, Free / Pro filter.
 *   New and changed scripts are not in the script cache yet: they stay inline (the same as before V34), nothing breaks.
 * 40.0.10 - 2026-10-14 Sign-in from the PowerPoint add-in panel returns to the add-in start page. 1 include updated (009).
 *   009  DS Login Redirect (staging id 695) - UPDATED: a login started from the add-in's side panel returns to
 *        the add-in start page (not the marketplace page, which does not fit the panel).
 * 40.0.10 - 2026-10-14 Add-in sign-in: back to the add-in page after signing in, and the drag tip shows again. 2 includes updated (023, 148).
 *   023  Login & sign-up fixes - UPDATED: signing in from the PowerPoint side panel returns to the add-in start page (?ds_office=1).
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED: the "Drag this edge to extend" tip also shows after the panel
 *        moves to another page (for example after sign-in); its saved on/off setting uses a new key, so it shows again once.
 * 40.0.9 - 2026-10-14 9:16 chart slides: no large empty areas. 1 include updated (151).
 *   151  DS Template Engine (T00) (staging id 952) - UPDATED: on 9:16 (and other tall) chart slides the chart
 *        takes only the height it needs (gauges, pies, bars, lines) and the insight cards stack under it, the
 *        group centred; the chart box used to stretch over the whole slide and leave big blank space.
 * 40.0.8 - 2026-10-14 PowerPoint add-in: Review Your Slides usable in the narrow side panel. 1 include updated (148).
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED: in a narrow side panel the Review Your Slides
 *        card (AI, Preserve My Text and document import) scrolls as a whole and Generate / Back to Edit stay
 *        visible at the bottom (they were cut off and the list could not scroll).
 * 40.0.7 - 2026-10-14 PowerPoint add-in: closing My account no longer lands on the add-in panel page. 2 includes updated (114, 148).
 *   114  DS Account Dropdown - UPDATED: closing the My account card never goes back to the add-in's panel page
 *        (/ds-office/...); inside PowerPoint it goes to the start page instead.
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED: if the add-in panel page is ever open outside
 *        PowerPoint's side panel, its buttons open the tools in that same window (they did nothing before).
 * 40.0.6 - 2026-10-14 PowerPoint add-in on the web: tools open in the large window. 1 include updated (148).
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED.
 *        - PowerPoint for the web keeps the website's sign-in out of the side panel, so there every tool
 *          opens straight in the large PowerPoint window (desktop PowerPoint keeps the side panel view).
 * 40.0.5 - 2026-10-14 PowerPoint add-in: editor buttons sized properly, menus and pop-ups never cut off. 1 include updated (148).
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED.
 *        - Side panel editor buttons are made smaller with real sizes instead of CSS zoom (zoom confused the
 *          editor's own menu placement): element toolbar AND the fixed-button dock (Exit, Positions, Layers,
 *          Add element, Icons, Edit Slide, Logo, Master Slide, Add Slide, Reset), Shortcuts, Themes / Pick a
 *          design / Layouts bar, page controls and the top bar.
 *        - Inside PowerPoint the Download / Insert menu is never wider than the window (it was cut off on the
 *          left in the side panel) and shows no "Upgrade" label (policy 1100.3).
 *        - Free-plan pop-ups taller than the window scroll, so their buttons are never cut off.
 * 40.0.4 - 2026-10-14 Update with AI / Preserve My Text cards no longer squeezed. 2 includes updated (080, 148).
 *   080  Snippet 30 Template Gallery (staging id 763) - UPDATED. The Browse Templates card copied its neighbour's
 *        flex once when the page loaded; a page first opened narrow kept "full width" and, once wider, squeezed
 *        Update with AI and Preserve My Text into thin vertical strips. It now always takes an equal share.
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED. Inside PowerPoint (side panel AND large window)
 *        the three cards sit one under another, with the "Paid plans" label under the card text.
 * 40.0.3 - 2026-10-14 PowerPoint add-in: smaller editor buttons in the side panel. 1 include updated (148).
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED. In the side panel the editor's element toolbar
 *        (Edit screen / Exit, Positions, Layers, Add element, Icons, Edit Slide, Logo, Master Slide, Add Slide,
 *        undo / redo, Reset), the Shortcuts button, the bottom bar (Themes, Pick a design, Layouts, page / zoom)
 *        and the top-bar buttons (Back, Insert, Free chip) are half size, so the slide gets more room.
 * 40.0.2 - 2026-10-14 PowerPoint add-in: editor room in the side panel, slide size check on Insert. 1 include updated (148).
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED.
 *        - Editor in the side panel: the Layouts strip is hidden; a LAYOUTS button (next to THEMES) opens it
 *          and it closes again after a layout is picked. Slide thumbnails are kept about 60px tall whatever
 *          the slide shape (9:16 decks made them very tall), so the slide being edited has room.
 *        - Insert checks the slide size (PowerPointApi 1.10): when the deck (e.g. 9:16) differs from the open
 *          presentation (e.g. 16:9), the page asks "Change to 9:16" / "Keep 16:9" before inserting; an empty
 *          presentation is changed without asking. Older PowerPoint inserts as before.
 * 40.0.1 - 2026-10-14 PowerPoint add-in side panel fixes. 1 include updated (148).
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED.
 *        - "Continue with Google / Microsoft" in the side panel opens the sign-in in the large PowerPoint
 *          window (Google and Microsoft refuse to load inside the panel: 403 / refused). After sign-in the
 *          window closes and the panel shows the page the sign-in was for. Any other outside page that
 *          cannot load in the panel shows an "Open window" notice.
 *        - Wider side panel: Update with AI / Preserve My Text / Browse Templates stack as full-width cards at
 *          every width (they were squeezed into thin vertical strips between 901 and 1400 px), and the
 *          "Paid plans" label sits under the card text instead of covering the title.
 *        - Animated "Drag this edge to extend" tip at the panel's left edge while the panel is narrow; it goes
 *          away for good once the panel is widened or the tip is closed (x), and fades out after 9 seconds.
 *        - Zoom - / 100% / + buttons removed from the panel bar (Back, large window, browser remain);
 *          pages always show at 100 %.
 * 40.0.0 - 2026-10-14 PowerPoint add-in tools open inside the side panel; EPS + SVG download. 1 include added, 1 updated.
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED (keeps the 38.1.3 renews / ends date wording).
 *        - AI Presentation, Template Matcher, Marketplace, AI Image, Video, Audio, My Creations and the
 *          library open INSIDE the PowerPoint side panel first (the website's own page, phone layout),
 *          not straight in the popup window.
 *        - Side panel shows only the tool: no site header, AI Images / Presentation / Video / Audio tabs,
 *          credits row, breadcrumbs, footer or chat bubble. The large PowerPoint window still shows the
 *          whole page. Template Matcher stacks (content box over the template grid) in the narrow panel.
 *        - Bar on top of the panel page: Back, zoom - / 100% / +, open in the large PowerPoint window, open in
 *          the browser - each only on a click (AppSource 1100.4 / 1100.5).
 *        - Insert works the same from the side panel (same messages as the large window).
 *        - PowerPoint on the web: sign in / sign up / my account open in the large window; a page that lost
 *          the sign-in shows an "Open window" notice instead of signing the user out.
 *        - Website busy (503 / 502 / 504): the panel says so with a "Try again" button (no automatic retries).
 *        - Pricing / upgrade guard of the large window applies inside the panel too.
 *   353  DS Vector Export - EPS + SVG download (staging id 1191) - NEW.
 *        "EPS + SVG (vector)" in the editor's Download menu: one .zip with an SVG and an EPS per slide
 *        (named layers, live text in the template's fonts) and "Fonts used.txt". Free presentations follow
 *        the free plan (DSFP.canExport('vector')). Pictures on other storage hosts go through
 *        GET /wp-json/ds/v1/vx-img (signed-in users, pictures only, own site / R2 / Iconify).
 *        Not offered inside the PowerPoint add-in (side panel or PowerPoint window): there the menu stays
 *        PowerPoint, PDF, PNG, JPG.
 *        assets/sc: cached copy of its main script (the small settings line stays inline).
 *   MAIN Constants/functions DSV39_* -> DSV41_*; all older names stay defined and every old
 *        *_DISABLE / *_NO_SCRIPT_CACHE switch still works. Deactivate V39 before activating V40 (never both).
 * 39.0.0 - 2026-10-13 Ratios for every template, 30 cover designs, PNG image holder, preview photos, AI directives. 4 includes added (staging, 2026-10-13). Nothing changed in the 38.1.5 files.
 *   349  DS Template 65R - 4:3 and 9:16 for every design template (staging id 1186) - NEW.
 *        - Aspect ratio 4:3 and 9:16 work for all 129 design templates (was 16:9 only): the editor,
 *          thumbnails, downloads (PowerPoint slide size 4:3 / 9:16) and the home previews follow the
 *          ratio picked; every slide type gets layouts made for that shape.
 *        - 30 cover designs (ticket stub, colour blocks, corner circle, polaroid stack, poster type,
 *          sidebar index, stair blocks, Bauhaus shapes, Swiss grid, badge seal, overlap card,
 *          gradient blob, wave flyer, speaker arch, hex tech, circuit lines ...), in the template's own
 *          colours and fonts, also mirrored and with rounded / arch photos. Every template opens with
 *          its own cover (neighbours never match) and can switch to any other in Layouts; in 16:9 the
 *          template's own covers stay first and the 30 designs are added after them.
 *   350  DS Image Holder - transparent PNG stays transparent (staging id 1187) - NEW.
 *        A PNG / WebP / GIF with see-through parts uploaded into an image holder (Upload from Device,
 *        drag and drop, second picture box) stays PNG with nothing behind it (large ones used to be
 *        flattened onto white JPEG).
 *   351  DS Template Preview Photos (staging id 1188) - NEW.
 *        Template cards, the 3-slide sample and the hover preview show real tech photos from the
 *        marketplace (no watermark) instead of grey "Click to add image" boxes: a different photo
 *        for every template and for every picture box. Real decks are not changed.
 *        REST: GET /wp-json/ds/v1/tpl-preview-photos (list cached 12 h).
 *   352  DS AI Instruction Guard (staging id 1190) - NEW.
 *        With Specific AI Directives (or hard rules in the prompt: do not invent, exact titles, word
 *        limits) the AI preview treats them as binding: exact slide count, titles, subtitles,
 *        phrases, points per slide and word limit are checked, repaired once by the AI and then
 *        applied; "do not invent" keeps made-up funding, names and websites out. Prompts without
 *        directives use the standard generator unchanged. Covers /ds/v1/ai-preview and
 *        /ds/v1/doc-ai-preview.
 *        assets/sc: cached copy of the 1 new fixed script (350); 349 and 351 print site data and stay inline.
 *   MAIN Constants/functions DSV38_* -> DSV39_*; all older names stay defined and every old
 *        *_DISABLE switch still works. Deactivate V38 before activating V39 (never both).
 * 38.1.5 - 2026-10-12 Build from My Content needs enough credits. 1 include updated.
 *   348  Clicking "Build from My Content" (Preserve My Text) with fewer credits than the cheapest
 *        presentation shows "Not enough credits" with a Buy credits button, before any AI call.
 *        The server refuses the Preserve preview requests too (not_enough_credits). The free plan
 *        (free presentations without credits) is not affected.
 * 38.1.4 - 2026-10-12 Credits keep paid access after a plan ends. 10 includes updated.
 *   001  New ds_access_plan(): the live paid plan, or - with no live plan - the last paid plan
 *        (Premium if never subscribed) while the user holds enough unexpired credits for one
 *        presentation. ds_valid_credits() leaves out expired pools before the nightly job runs.
 *        Billing (renewal, expiry, refunds, referrals) still uses the stored plan, unchanged.
 *   067  Marketplace download limits (also add-in, templates, PPT builder) follow the access plan.
 *   108  Marketplace page tier follows the access plan.     068  Saved-items cap too.
 *   076/093/121/124  PPT builder, AI Images (sizes), AI Video, My Creations follow it.
 *   128  Free-tier image guard exempts anyone with paid access.
 *   196  Free presentations: only valid (unexpired) credits lift the free-plan limits.
 *   348  Use AI switch: free = no paid access (plan or credits).
 * 38.1.3 - 2026-10-12 Use AI switch, blinking free counter, billing fixes. 1 include added, 3 updated.
 *   348  DS PPT Preserve My Text - Use AI switch (staging id 1185) - NEW. A "Use AI" switch next
 *        to "Use Sample" in Preserve My Text. Paid plans: ON by default, OFF splits the text by
 *        its headings without AI (same price). Free plan (no live paid plan, whatever the
 *        credits): always OFF, opens "Available on paid plans"; the server refuses the
 *        Preserve AI requests for free accounts.
 *   347  Free Plan Switch: on "No limit" the "Free - N of N left" line no longer blinks.
 *   148  PowerPoint add-in: the date says renews / renewal due since / ends / expired.
 *   022  Daily plan-expiry job checks every paid account (was the first 500 users only).
 * 38.1.2 - 2026-10-11 Free Plan Switch. 1 include added.
 *   347  DS Free Plan Switch (staging id 1182) - NEW. Settings > Free Plan Switch: for free
 *        presentations and for free AI images choose Limited (as today), No limit or
 *        Stopped. Works on top of DS PPT Free Plan, DS AI Image REST and DS Free Tier Guard
 *        without editing them. The wording follows the choice on the pricing page (Free
 *        card, compare table, FAQ), home page, AI Studio, the free-plan banner and the
 *        support chat. With both on Limited the only visible change is one line on the
 *        pricing Free card that states the free presentations.
 * 38.1.1 - 2026-10-10 Shared links look like the editor. 1 include added.
 *   346  DS Share Pictures (staging id 1181) - NEW. A shared link (/?ds_view=...) used to
 *        draw slides with its own simple code (a photo with plain text on top), so a
 *        design-template presentation looked completely different when shared. On Share,
 *        every slide is now photographed the way the JPG download does it and stored with
 *        the link (uploads/ds-share-pics); the viewer shows those pictures, and the
 *        WhatsApp / Facebook / LinkedIn / X preview uses the first slide and the title.
 *        Old links keep the old view until the presentation is shared again.
 *        assets/sc: cached copy of the 1 new script.
 * 38.1.0 - 2026-10-10 40 new design templates (T90-T129) with Template Kit 5, picker popup, deck structures, card sync, Edit + Add Points button. 45 includes added (staging, 2026-10-10). Nothing changed in the 38.0.4 files.
 *   301  DS Template 90 — Index Tabs (staging id 1132) - NEW.
 *   302  DS Template 91 — Stair Step (staging id 1133) - NEW.
 *   303  DS Template 92 — Sunburst (staging id 1134) - NEW.
 *   304  DS Template 93 — Barcode (staging id 1135) - NEW.
 *   305  DS Template 94 — Weave (staging id 1136) - NEW.
 *   306  DS Template 95 — Meridian (staging id 1137) - NEW.
 *   307  DS Template 96 — Tartan (staging id 1138) - NEW.
 *   308  DS Template 97 — Jigsaw (staging id 1139) - NEW.
 *   309  DS Template 98 — Sector (staging id 1140) - NEW.
 *   310  DS Template 99 — Terrazzo (staging id 1141) - NEW.
 *   311  DS Template 101 — String Art (staging id 1143) - NEW.
 *   312  DS Template 102 — Sawtooth (staging id 1144) - NEW.
 *   313  DS Template 103 — Scallop (staging id 1145) - NEW.
 *   314  DS Template 104 — Velocity (staging id 1146) - NEW.
 *   315  DS Template 105 — Venn (staging id 1147) - NEW.
 *   316  DS Template 106 — Strata (staging id 1148) - NEW.
 *   317  DS Template 107 — Guilloche (staging id 1149) - NEW.
 *   318  DS Template 108 — Constellation (staging id 1150) - NEW.
 *   319  DS Template 109 — Equalizer (staging id 1151) - NEW.
 *   320  DS Template 110 — Helix (staging id 1152) - NEW.
 *   321  DS Template 111 — Low-Poly (staging id 1153) - NEW.
 *   322  DS Template 112 — Golden Section (staging id 1154) - NEW.
 *   323  DS Template 113 — Library Spine (staging id 1155) - NEW.
 *   324  DS Template 114 — Perforation (staging id 1156) - NEW.
 *   325  DS Template 115 — Labyrinth (staging id 1157) - NEW.
 *   326  DS Template 116 — Elbow (staging id 1158) - NEW.
 *   327  DS Template 117 — Kanban (staging id 1159) - NEW.
 *   328  DS Template 118 — Planner (staging id 1160) - NEW.
 *   329  DS Template 119 — Cargo (staging id 1161) - NEW.
 *   330  DS Template 120 — Radar (staging id 1162) - NEW.
 *   331  DS Template 121 — Abacus (staging id 1163) - NEW.
 *   332  DS Template 122 — Tangram (staging id 1164) - NEW.
 *   333  DS Template 123 — Crest (staging id 1165) - NEW.
 *   334  DS Template 124 — Dash Code (staging id 1166) - NEW.
 *   335  DS Template 125 — Coin Stack (staging id 1167) - NEW.
 *   336  DS Template 126 — Herringbone (staging id 1168) - NEW.
 *   337  DS Template 127 — Link Chain (staging id 1169) - NEW.
 *   338  DS Template 128 — Flight Path (staging id 1170) - NEW.
 *   339  DS Template 129 — Truss (staging id 1171) - NEW.
 *   340  DS Template 100 — Masonry (staging id 1172) - NEW.
 *   342  DS Picker Popup - Explore Themes / View All open in a popup (click, then Select) (staging id 1176) - NEW.
 *        "Explore Themes" and "View All" on the AI Presentation home screen open a
 *        popup instead of stretching the page; click a theme or template, then Select.
 *   343  DS Deck Structure - a storyline for every presentation type (pitch, company overview, sales, report ...) (staging id 1178) - NEW.
 *        The AI gives each kind of presentation its own sections (45 kinds: pitch deck,
 *        company overview, sales, report, proposal ...) instead of one fixed slide order.
 *        Admin test endpoint: POST /wp-json/ds/v1/deck-type. Off switch: option ds_dp_off.
 *   344  DS Card Sync - card fill, outline and shadow act as one (round corners, shape, move, resize) (staging id 1179) - NEW.
 *        A card drawn as two boxes (fill + outline) acts as one in the editor, so the
 *        outline and the shadow follow round corners, shape, move, resize and rotate.
 *   345  DS Edit + Add Points button - above the slide in the editor (staging id 1180) - NEW.
 *        Button above the top-left corner of the slide; opens the Edit Slide window
 *        (reads "Edit Slide" on title, quote and closing slides).
 *        assets/sc: cached copies of the 44 new scripts (own files; map.php extended).
 * 38.0.4 - 2026-10-10 No more flashing; browser boxes become popups; phone footer. 2 includes added.
 *   298  DS Editor Extras (id 1128) - UPDATED. A locked window no longer refreshes
 *        itself over and over (the editor was flashing and other popups needed
 *        several clicks to open), and the slide list / Change Layout list no
 *        longer redraw every second.
 *   299  DS Editor Popup Dialogs (id 1129) - NEW. Browser question boxes such as
 *        "Delete slide 5?" open as a movable popup inside the editor (OK / Cancel,
 *        Enter / Esc).
 *   300  DS Mobile Footer Fix (id 1130) - NEW. On phones the pages no longer have an
 *        empty strip on the right (footer widget 3 had a 150 px left margin).
 * 38.0.3 - 2026-10-10 Locked popups become their own windows.
 *   298  DS Editor Extras (id 1128) - UPDATED. Locking an editor popup (Position /
 *        Layers, Add element, Fill, Transparency ...) makes it a separate window that
 *        stays open next to the others, can be moved and resized, and passes every
 *        click / slider / box to the editor. Locked windows are remembered.
 * 38.0.2 - 2026-10-10 Editor extras. 1 include added.
 *   298  DS Editor Extras (staging id 1128) - NEW.
 *        - Add element: the small Icons section is gone (use the Icons button).
 *        - "Change image" button on pictures under another layer (full-background).
 *        - Editor popups (Position / Layers, Add element, element settings, Theme,
 *          Icons) can be resized and locked; a locked popup stays open (only x
 *          closes it) and keeps its place and size.
 *        - Slide thumbnails: Copy, Delete and Hide. Hidden slides stay in the
 *          editor but are left out of PowerPoint, PDF, PNG and JPG downloads.
 * 38.0.1 - 2026-10-09 Downloads fixed and sharper; tool bar wraps.
 *   297  DS Export Fix - PDF / PNG / JPG downloads (staging id 1127) - NEW.
 *        The PDF / PNG / JPG download no longer stops with "createPattern ...
 *        width or height of 0" on slides with an empty decorative background,
 *        and photos are drawn from the full stored picture (sharper).
 *   296  DS Editor Icons Library (id 1126) - UPDATED. With a mouse, the bottom
 *        tool bar wraps onto a second row instead of hiding the last buttons.
 * 38.0.0 - 2026-10-09 Icons library, Edit screen side panels, 24 new design templates. 30 includes added (staging, 2026-10-09). Nothing changed in the V37 files.
 *   267  DS Fix - Sample format button fit (responsive) (staging id 1097) - NEW.
 *   268  DS Template Kit 3 (shared for T66-T73) (staging id 1098) - NEW.
 *   269  DS Template 66 — Hex Clinic (staging id 1099) - NEW.
 *   270  DS Template 67 — Crimson Tile (staging id 1100) - NEW.
 *   271  DS Template 68 — Rhombus Proposal (staging id 1101) - NEW.
 *   272  DS Template 69 — Finance Prism (staging id 1102) - NEW.
 *   273  DS Template 70 — Night Diamond (staging id 1103) - NEW.
 *   274  DS Template 71 — Profile Block (staging id 1104) - NEW.
 *   275  DS Template 72 — Capsule Corporate (staging id 1105) - NEW.
 *   276  DS Template 73 — Tide Report (staging id 1106) - NEW.
 *   277  DS Template Kit 4 (shared for T74-T89) (staging id 1107) - NEW.
 *   278  DS Template 74 — Metro Line (staging id 1108) - NEW.
 *   279  DS Template 75 — Bento Board (staging id 1109) - NEW.
 *   280  DS Template 76 — Bauhaus Studio (staging id 1110) - NEW.
 *   281  DS Template 77 — Contour (staging id 1111) - NEW.
 *   282  DS Template 78 — Pinstripe (staging id 1112) - NEW.
 *   283  DS Template 79 — Ticker (staging id 1113) - NEW.
 *   284  DS Template 80 — Viewfinder (staging id 1114) - NEW.
 *   285  DS Template 81 — Duotone Pop (staging id 1115) - NEW.
 *   286  DS Template 82 — Caliper (staging id 1116) - NEW.
 *   287  DS Template 83 — Colonnade (staging id 1117) - NEW.
 *   288  DS Template 84 — Pixel Mosaic (staging id 1118) - NEW.
 *   289  DS Template 85 — Botanical Line (staging id 1119) - NEW.
 *   290  DS Template 86 — Halftone Signal (staging id 1120) - NEW.
 *   291  DS Template 87 — Vault Frames (staging id 1121) - NEW.
 *   292  DS Template 88 — Swiss Index (staging id 1122) - NEW.
 *   293  DS Template 89 — Compass Point (staging id 1123) - NEW.
 *   294  DS Template Row Fit (point explanations, T66-T89) (staging id 1124) - NEW.
 *   295  DS Edit Screen Side Panels (large screens) (staging id 1125) - NEW.
 *   296  DS Editor Icons Library (Iconify search) (staging id 1126) - NEW.
 *   assets/sc: cached copies added for the 28 new scripts.
 *   MAIN Constants/functions DSV37_* -> DSV38_*; all older names stay defined
 *        and every old *_DISABLE switch still works.
 *        Deactivate V37 before activating V38 (never both).
 * 37.0.0 - 2026-10-08 Editable PPT layouts. 6 includes updated, 3 added (staging, 2026-10-08).
 *   265  DS Template Element Editor (staging id 1078) - NEW.
 *        - Every element of a design-template slide can be selected, moved,
 *          resized, rotated, restyled, grouped, layered and reordered; own
 *          text boxes, shapes, lines and icons can be added. Changes are kept
 *          per slide and written into the PowerPoint, PDF, PNG and JPG files.
 *        - Switched on for all 65 design templates (ds_el_editor_templates).
 *        - Text alignment (left / centre / right / justify) separate from Align.
 *        - Picture fill for boxes and shapes (generate with AI, marketplace,
 *          upload) with transparency, fit, zoom and position.
 *        - Round corners on every shape and picture (drag the small circle
 *          inside the corner, or the Corners number), sides 3-12 for polygons.
 *        - Icon colour as a gradient, icon outline in a colour or a gradient.
 *        - Object settings in the top bar, fixed buttons (Positions, Layers,
 *          Add element, Edit Slide, Logo, Master Slide, Add Slide, Undo / Redo,
 *          Reset) in a bar at the bottom; "Edit screen" hides the slides and
 *          layout panels (Exit brings them back). Phone and tablet too.
 *        - Settings panels can be dragged anywhere (double-click the title to
 *          put one back); font size has a slider under the size box.
 *   266  DS Template 65 - Classic (staging id 1094) - NEW. The Classic look
 *        rebuilt as a design template so every editor tool works on it; the
 *        "Classic" card in the template picker now makes these decks (old
 *        Classic decks still open the old way). Colours follow the colour
 *        theme; 2-16 points; Classic-style layouts for every slide type.
 *   264  DS PPT Layout Choices - keep them after save and re-open (staging id
 *        1080) - NEW. Layouts picked after the first save are kept.
 *   151  DS Template Engine T00 (id 952) - UPDATED. Element keys and drawing for
 *        the editor (rotation, shadows, gradients, picture fill, custom shapes
 *        with round corners, icon gradient / outline) in the editor and the
 *        PowerPoint build; the Classic card picks the editable Classic.
 *   077  Snippet 17 variant picker (id 953) - UPDATED. Edit Slide fixes.
 *   132  DS PPT Background Autosave (id 833) - UPDATED. Element edits are saved.
 *   196  DS PPT Free Plan (id 968) - UPDATED. The editable Classic is always
 *        allowed for free presentations, like Classic.
 *   199  DS PPT Editor Mobile & Tablet (id 975) - UPDATED. The slide fits
 *        between the editor's top and bottom bars.
 *   251  DS Template speed (id 1048) - UPDATED. Chart and diagram slides show
 *        each layout option correctly in Change Layout.
 *   assets/sc: cached copies added for the new and changed scripts.
 *   NOT included, as before: 840, 862 and 890 (862 is include 148).
 *   MAIN Constants/functions DSV36_* -> DSV38_*; all older names stay defined
 *        and every old *_DISABLE switch still works.
 *        Deactivate V36 before activating V37 (never both).
 * 36.0.16 - 2026-10-07 AI Presentation: automatic marketplace pictures are free. 2 includes updated.
 *   138  DS PPT Card Layout Fix + Auto Marketplace Image (id 820) - UPDATED.
 *        - The pictures the maker places by itself when a deck opens were
 *          taken through the picker, so each one used up a marketplace
 *          download (a free account's 5 per day were gone after one deck).
 *          They now come from the not-counted route (/ds/v1/mkt-auto-image,
 *          1920px copy). A picture the person picks by hand from the
 *          marketplace is still counted, per day or per month by plan.
 *        - assets/sc: script file replaced to match.
 *   081  Snippet 19 marketplace images (id 764) - UPDATED.
 *        - A hand-picked picture is counted only once its file is in hand.
 *          A missing file used to cost a download.
 * 36.0.15 - 2026-10-07 PowerPoint add-in: clear message when a download limit is reached. 1 include updated.
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED.
 *        - When the plan's download limit is reached, the panel now shows a
 *          message box "Daily download limit reached" (or Monthly), saying
 *          how many downloads the plan includes, that it is a plan limit and
 *          not an error, and when it resets. The button reads "Limit
 *          reached" instead of "Try again".
 *        - Templates: the limit is checked before the file is requested.
 *          Before, the reached limit came back as a redirect to the pricing
 *          page and the add-in reported "Please sign in".
 *        - Items outside the plan get "Not included in your plan".
 *        - Same message in the full-window (website inside PowerPoint) view.
 * 36.0.14 - 2026-10-07 PowerPoint add-in: inserts now count as downloads. 1 include updated.
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED.
 *        - Marketplace images, videos, audio and icons are inserted from
 *          their own file, not through a Download link, so the insert was
 *          never counted against the plan's download limit. The file proxy
 *          (/ds-office/.../fetch) now counts each of them with the same
 *          counter the website uses (ds_dl_check_core), and refuses with
 *          "download limit reached" or "not included in your plan".
 *        - Templates were already counted (Download Monitor gate) and are
 *          still counted once.
 *        - A retry of the same item within 30 seconds is one insert.
 *        - Pro or Free is read from the database, because the proxy answers
 *          before Download Monitor registers its tags.
 * 36.0.13 - 2026-10-07 Home page top section rearranged. 1 include updated.
 *   012  DS AI Hero Section (id 698) - UPDATED.
 *        - The video player, its title and "Watch other related videos" now
 *          sit beside the headline, where the laptop picture was. The laptop
 *          is only shown when no hero videos are set.
 *        - New banner space under the top section: Hero Videos > Home Banner
 *          (image upload, optional link, alt text). Hidden until an image is
 *          chosen and "Show it on the home page" is ticked. Best 2560 x 400.
 *        - The six feature cards follow on their own row, three across.
 *        - "8 languages" under the buttons now reads "46 languages" (the
 *          number offered in the AI Presentation language list).
 *        - assets/sc: hero script file replaced to match.
 * 36.0.12 - 2026-10-07 Free presentations: PowerPoint/PNG/JPG download, three designs, chart type lock. 2 includes updated.
 *   196  DS PPT Free Plan (id 968) - UPDATED.
 *        - Download: a free presentation can now be downloaded as PowerPoint,
 *          PNG, JPG or PDF (tick boxes in Settings > Free Presentations).
 *        - Designs: only Classic, Glassline and Cobalt Bold. Every other
 *          design template shows a lock on the home screen, and the server
 *          refuses a PowerPoint build with a locked design.
 *        - Charts: the numbers, labels and unit can be edited; the chart type
 *          and shape cannot be changed (pie stays pie).
 *        - No new slides in the editor: Add Slide, the chart slide button and
 *          the diagram slide button all show the upgrade message; the server
 *          refuses a PowerPoint build with more slides than the free limit.
 *        - Still 2 a day, 5 slides, Preserve My Text only, not saved.
 *   195  DS PPT Export (id 967) - UPDATED. The Download menu asks the free
 *        plan which formats are unlocked instead of allowing PDF only.
 * 36.0.11 - 2026-10-07 Delete my account: administrators and cancelling. 1 include updated.
 *   263  DS Delete My Account (id 1069, now 1.1.0) - UPDATED.
 *        - An administrator now sees the button and the box, but the tick
 *          box cannot be ticked and says "You are an administrator.
 *          Administrator accounts cannot be deleted here." (the old
 *          ?ds_del_preview=1 is gone; the server still refuses as well).
 *        - Cancelling inside the 24 hours is clearer: "Cancel deletion" on
 *          the confirmation screen and "Cancel deletion - keep my account"
 *          in the My Account popup; the email and Terms use the same words.
 * 36.0.10 - 2026-10-07 Delete my account (24-hour delay). 1 include added.
 *   263  DS Delete My Account (staging id 1069) - NEW.
 *        - "Delete my account" under "Sign Out" in the My Account popup. It
 *          opens a confirmation box above the popup: what is lost, a tick
 *          box that must be ticked, and the 24-hour notice.
 *        - Nothing is deleted at once. The account is deleted 24 hours after
 *          confirming (hourly check, or the moment the owner next opens a
 *          page). Until then "Keep my account" in the popup stops it.
 *        - Confirming cancels an active subscription (it will not renew);
 *          at deletion the subscription is stopped completely.
 *        - Published Marketplace items are handed to the site administrator
 *          first, so they stay available. Everything else is removed by the
 *          existing account clean-up (generated files, voice samples).
 *        - An email address that had a deleted account does not get the
 *          sign-up bonus again (one-way hash only).
 *        - Users > Deleted accounts: who is waiting ("Delete now" button)
 *          and a record of deleted accounts (name, email, date).
 *        - Terms page: new section "Deleting your account" (added through
 *          the ds_terms_sections filter, before "Contact").
 *        - Administrators have no such button (?ds_del_preview=1 shows it
 *          to an administrator for a look; confirming is refused).
 * 36.0.9 - 2026-10-07 Google / Microsoft sign-in: stuck blank page fixed. 1 include updated.
 *   262  DS Social Login (id 1067, now 1.3.3) - UPDATED.
 *        - Cause of the blank / reloading page after choosing an account:
 *          with no "return to" address the redirect was sent with an empty
 *          destination. No destination now means the home page.
 *        - Sign-in runs in two steps: /ds-sso/callback/ finds or creates the
 *          account, /ds-sso/finish/ (one-time ticket, 2 minutes) sets the
 *          login cookie and sends the visitor on.
 *        - Settings > DS Social Login: "Upload or choose image" for each
 *          button logo, and a list of the last sign-in requests.
 * 36.0.8 - 2026-10-07 Google / Microsoft sign-in on the Login page only; repeat-callback fix. 1 include updated.
 *   262  DS Social Login (id 1067, now 1.2.0) - UPDATED.
 *        - The Google / Microsoft panel is on the Login page only (it also
 *          creates the account on a first visit). Sign Up is as before.
 *        - Signed in but back on /login/ with "took too long or was opened
 *          twice": the return address was being requested twice. The second
 *          arrival now just waits for the first and sends the visitor on,
 *          and a signed-in visitor is never shown a sign-in message.
 * 36.0.7 - 2026-10-07 Google / Microsoft sign-in: blank page fixed, new layout. 1 include updated.
 *   262  DS Social Login (id 1067, now 1.1.2) - UPDATED.
 *        - Layout: Login and Sign Up now show two panels side by side,
 *          "Google / Microsoft" first and the email form second (stacked on
 *          a phone, Google / Microsoft on top). Sign Up buttons read "Sign up
 *          with ...". Optional logo image per provider (Settings > DS Social
 *          Login > Logo image address); empty = text only.
 *        - The panel no longer waits for the first mouse move or tap: its
 *          script is excluded from LiteSpeed "delay JS".
 *        - Blank page after choosing an account: output printed by another
 *          plugin during sign-in is now held back so the login cookie and
 *          the redirect are always sent, a fault in an after-login step no
 *          longer stops the sign-in, and if a redirect still cannot be sent
 *          the page forwards the visitor itself.
 *        - Settings page shows why the last unfinished sign-in stopped.
 * 36.0.6 - 2026-10-07 Sign in with Google or Microsoft. 1 include added, 1 updated.
 *   262  DS Social Login (staging id 1067) - NEW. "Continue with Google" and
 *        "Continue with Microsoft" on the Login and Sign Up forms. A first
 *        visit creates the account and signs the visitor in; later visits
 *        sign them in. An account with the same email is reused only when
 *        the provider confirms the email is verified. A verified email
 *        skips the OTP code email; sign-up bonus, referral and the other
 *        user_register hooks run as usual. Administrators always use their
 *        password. Nothing is shown until a provider is switched on and has
 *        a client ID and secret in Settings > DS Social Login. Addresses:
 *        /ds-sso/start/<provider>/ and /ds-sso/callback/<provider>/.
 *   261  DS Privacy Policy page (id 1061) - UPDATED. One sentence about
 *        signing in with Google or Microsoft.
 * 36.0.5 - 2026-10-06 Template Matcher address; sign-in card for signed-out
 *        visitors. 4 includes updated from staging, none added.
 *   147  Browse Templates URL (staging id 857) - UPDATED.
 *        /ai-presentation/browse-templates/ became "page not found" after
 *        the move from V35 to V36: its rewrite rule lives in WordPress's
 *        saved rule list, and that list was rebuilt while neither version
 *        was loaded. The address is now also recognised directly (request
 *        filter), so it no longer depends on the saved list. This also
 *        removes the "ADD-IN ERROR" bar the PowerPoint window showed for it.
 *   121  DS AI Video (id 802), 106 DS AI Audio (id 787), 124 My Creations
 *        (id 805) - UPDATED. Signed-out visitors see the same sign-in card
 *        as the AI Studio page (ds_guest_login_card) instead of the bare
 *        line "Please log in.". Sign-in returns them to the page they opened.
 * 36.0.4 - 2026-10-06 AI Video: a video made a moment ago can be downloaded and
 *        inserted without reloading the page. 2 includes updated from staging.
 *        36.0.3 fixed only part of this (a card that holds a storage link).
 *   The real cause: the card of a new video holds the AI provider's link.
 *        Seconds later the video is copied to our own storage and the saved
 *        link is replaced, so the card's link no longer matched the owner
 *        check ("Not your video", shown as HTTP 500) until a reload.
 *   121  DS AI Video (staging id 802) - UPDATED. The new card's Download
 *        button also sends the generation id (prediction_id).
 *   123  DS AI Video REST (staging id 804) - UPDATED. download-video looks the
 *        video up by that id in the signed-in user's own list and serves
 *        whatever is stored for it now (storage file, or the provider link
 *        while it is still being copied). Requests without the id work as
 *        before; other users' files are still refused.
 * 36.0.3 - 2026-10-06 AI Video: a new video can be downloaded / inserted at once.
 *        2 includes updated from staging, none added.
 *   123  DS AI Video REST (staging id 804) - UPDATED. Download of a video that
 *        has just been generated failed with HTTP 500 until the page was
 *        reloaded: the new card sends the video's storage link (the ds-hub
 *        file route) without a file id, and the server tried to fetch that
 *        link itself, without the visitor's login. The file id is now read
 *        from the link, so the same owner check and download as after a
 *        reload are used. This is what stopped "Insert" of a new AI video in
 *        the PowerPoint add-in. Website download of a new video is fixed too.
 *   121  DS AI Video (staging id 802) - UPDATED. The status tag of a finished
 *        video says "Done" (it stayed on "Queued").
 * 36.0.2 - 2026-10-06 PowerPoint add-in: small logo in the task pane header.
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED. The header
 *        shows the small DS logo (transparent, embedded in the page). A
 *        narrow pane (under 276px) shows the logo alone, without the
 *        "Designstager" text; a wider pane shows the logo and the text.
 *        The ribbon and manifest icons are not changed.
 * 36.0.1 - 2026-10-06 PowerPoint add-in task pane. 1 include updated, none added.
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED.
 *        - Signed out: the plan and credits line is hidden. It used to stay
 *          on screen (the row is display:flex, which overrode the hidden
 *          attribute), so a guest saw the last account's plan.
 *        - Light-mode PCs: the pane now has one (dark) look everywhere. The
 *          old light variant changed only five colours while the rest of
 *          the pane is fixed dark, so the credits number, the AI Suites
 *          tile names and "Insert audio" were invisible on a light-mode PC.
 *        - Narrow pane (under 276px): the logo text is no longer cut to
 *          "Design..." (36.0.0), and the account name gets its own line
 *          instead of shrinking to "N...". 276px and wider is unchanged.
 * 36.0.0 - 2026-10-06 Privacy Policy page and PowerPoint add-in review fixes.
 *        V36 is V35.0.6 with 1 include updated and 1 added; every other
 *        include is byte-for-byte that of V35.0.6. Both come from staging.
 *   261  DS Privacy Policy page (staging id 1061) - NEW. Shortcode
 *        [ds_privacy]: a Privacy Policy page in the style of [ds_terms].
 *        Put it on a page of its own (for example /privacy/); the
 *        /privacy-policy/ address already shows the Terms. The contact
 *        address is support@<site domain> (filter ds_privacy_email); the
 *        text can be replaced with the ds_privacy_sections filter.
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED. Staging
 *        snippet 862 is now this include, so both are the same code.
 *        - Large pictures (a 28 MB PNG) no longer fail with "Failed to
 *          fetch": the file is read as bytes instead of response.blob().
 *        - Task pane header: the Browser button is no longer cut off by
 *          PowerPoint's own pane control.
 *        - Manifest: declares PowerPointApi 1.2 (needed to insert slides).
 *          Download the manifest again before submitting or sideloading.
 *        - "Clear search" also empties the search box of the open section.
 *        - Editor button reads "Insert" (was "Insert - Insert PPTX"); its
 *          menu is headed "Insert as" and no longer names other products.
 *        - Audio rows show two lines of title, full title on hover.
 *        - Marketplace mode also hides links to the Customize (paid design
 *          service) page inside the PowerPoint window.
 *        Asset VERSION stays 1.10.0; clear the Office cache once.
 *   MAIN Constants/functions DSV35_* -> DSV38_*; the DSV35 names and every
 *        older *_DISABLE switch still work. Plugin folder is now
 *        designstager-v36: deactivate V35 before activating V36 (never both).
 * 35.0.6 - 2026-10-06 Offer slide contact details; PowerPoint add-in messages.
 *        2 includes updated from staging, none added.
 *   076  12b ds ppt builder (staging id 760) - UPDATED. The "Don't Love This
 *        Presentation?" slide added to every downloaded deck (all templates)
 *        showed customize@stagingtest.designstager.com and
 *        stagingtest.designstager.com. It now shows support@designstager.com
 *        and designstager.com. Only those two texts changed; the include is
 *        byte-for-byte the staging snippet.
 *   148  DS PowerPoint Add-in Bridge (staging id 862) - UPDATED. Messages
 *        inside PowerPoint: site messages are shown on the page instead of
 *        alert() (PowerPoint on the web blocks alert). Plan / credit messages
 *        get an "Open in browser" button. With Marketplace mode on, the
 *        site's upgrade pop-ups and pricing / checkout links show the same
 *        message instead of opening. Only this part of staging snippet 862
 *        was merged in: the rest of include 148 is newer than the staging
 *        snippet and is kept as it was. Asset VERSION stays 1.10.0, so clear
 *        the Office cache once after updating.
 * 35.0.5 - 2026-10-06 AI Audio: voice preview. 1 include added, none changed.
 *   260  DS AI Audio - voice preview (staging id 1058) - NEW. On the AI Audio
 *        page (Text to Speech) the Voice box is a list with a play button in
 *        front of every voice, and one on the closed box for the selected
 *        voice. Play = hear the voice, click the name = select it. The
 *        preview reads a fixed sample sentence (never the user's text) and
 *        costs the user no credits. Each voice's sample is generated once
 *        with the same speech model (first press of its play button, about
 *        13 s), saved in uploads/ds-voice-previews/ and reused for everybody:
 *        at most 17 short generations on the site's WaveSpeed account. The
 *        original <select id="dsaaVoice"> stays in the page (hidden), so
 *        Generate Speech is unchanged. REST: POST ds/v1/voice-preview.
 * 35.0.4 - 2026-10-05 Pick a Design: the full-deck download works for design
 *        templates, charts and diagrams. 1 include added, none changed.
 *   259  DS Pick a Design - full deck download (staging id 1057) - NEW.
 *        "Download Combined PPTX" built every slide without a picked
 *        marketplace design with the classic builder, called directly. A deck
 *        made with a design template therefore came out in the classic look
 *        (near-empty slides), and chart / diagram slides of a classic deck
 *        came out without their chart. The request now goes through the
 *        normal download route (template engine, charts, diagrams, logo, deck
 *        style all take part as in a normal download); the picked marketplace
 *        designs are then put in the place of the slides they replace with
 *        the merge engine of include 082, and the same credits are charged.
 *        Includes 082 and 085 are not edited. If the new path gives no
 *        result the browser falls back to the old request.
 * 35.0.0 - 2026-10-05 Local image generation and a daily free-presentation
 *        allowance. Nothing from V34.0.7 was removed or changed: the 247
 *        includes are byte-for-byte those of V34.0.7; 2 includes were added.
 *   257  DS Local PC Image (free tier) + Cloudflare fallback (staging id
 *        1054) - NEW. A free-tier request for the "Cloudflare FLUX-1-Schnell"
 *        image model is made on the owner's own PC first (Stable Diffusion
 *        Forge, SDXL, about 1280x720). The PC is never exposed to the
 *        internet: a helper program on the PC asks the site for waiting jobs
 *        (REST ds-localpc/v1/poll) and posts the picture back
 *        (ds-localpc/v1/result). PC off, busy, slow or failed -> the normal
 *        Cloudflare model answers, the user sees no error. Paid users and
 *        presentation pictures keep Cloudflare.
 *        35.0.3: every request answers within 40 seconds (the web server
 *        repeats an unanswered request after 60s and fails it at 120s);
 *        orphaned waiting images no longer block the PC; "Clear waiting
 *        images" button in the settings.
 *        35.0.2: administrator "My PC" images that take longer than one
 *        request (FHD, 4K, or waiting behind another image) no longer fail:
 *        the page shows "Processing" and the picture is added to the gallery
 *        when the PC sends it. Up to 6 administrator images wait in line. A
 *        busy PC is reported as busy, not offline. The helper keeps a
 *        full-quality copy of every administrator image (my-pc-images).
 *        35.0.1: "My PC (admin only)" in the AI Studio model list, shown to
 *        administrators only (setting "Administrators"). It always uses the
 *        PC - no Cloudflare fallback, an error if the PC is off or busy -
 *        costs no credits, one image per request, and offers HD 1280x720,
 *        FHD 1920x1080 (refined 1.5x) and 4K 3840x2160 (FHD upscaled 2x).
 *        An administrator who picks a Cloudflare model gets Cloudflare.
 *        The PC helper (ds_worker.py) must be the version of 5 Oct 2026 or
 *        later for FHD / 4K.
 *        Settings > DS Local PC Image: on / off (OFF by default), helper key,
 *        steps, time limit, PC status, test button, activity log.
 *        HOW IT HOOKS IN: 257 is listed just BEFORE include 089 and defines
 *        ds_call_cloudflare() itself (try the PC, else
 *        ds_call_cloudflare_cloud(), an unchanged copy of 089's function).
 *        089 skips its own copy through its function_exists() guard. If the
 *        Cloudflare code in 089 is changed later, change the copy in 257 too.
 *   258  DS PPT Free Plan - daily reset (staging id 1055) - NEW. The free
 *        presentations of include 196 are counted per day: the counter reads
 *        as 0 on any day other than the day it was last written (site
 *        timezone, midnight). The number per day is still the setting in
 *        Settings > Free Presentations (default 2). Limit message and banner
 *        say "today". Runs last.
 *   NOTE The staging snippet ids 1054 / 1055 are the ids on the staging site;
 *        id 1054 is also the id written for include 256 in V34 (different
 *        site). The 'id' value is informational only.
 *   MAIN Constants/functions DSV34_* -> DSV35_*; DSV34/33/32/... names stay
 *        defined and every old *_DISABLE switch and DSV34_NO_SCRIPT_CACHE
 *        still work. Deactivate V34 before activating V35 (never both).
 * 34.0.0 - 2026-10-04 Script cache (speed). No include was changed, added or
 *        removed: the 246 includes are byte-for-byte those of V33.0.4.
 *   NEW  script-cache.php + assets/sc/. Measured on V33 (local test site):
 *        a logged-in user downloaded ~350 KB of inline JavaScript on EVERY
 *        page and ~2.3 MB on the AI Presentation page, again on every view,
 *        because inline script cannot be cached. Just before a page is sent,
 *        each inline script that is identical to a file shipped in assets/sc/
 *        is replaced by a <script src> tag to that file - same place, same
 *        order, same code. The browser / CDN keeps the files. Scripts that
 *        carry anything per user or per request never match and stay inline.
 *        The design templates are served as one file.
 *        34.0.1: a script that begins with settings (site address) and
 *        continues with a constant rest - diagrams, charts, document import -
 *        is split there; the rest is cached too (about 390 KB more).
 *        Off switch: define( 'DSV34_NO_SCRIPT_CACHE', true ); in wp-config.php.
 *        One page the old way: add ?dsv_inline=1 to its address.
 *   151  DS Template Engine T00 (id 952) - UPDATED in 34.0.2. PowerPoint
 *        download: a glow or glass shine (radial fill on an ellipse) showed
 *        the rim of its ellipse as a hard edge. PowerPoint runs a radial fill
 *        out to the corners of the shape's box, the editor to its sides; the
 *        stops are now pulled in so the fade ends inside the rim. All
 *        templates and layouts.
 *   237  DS Template Frosted Glass (id 1032) - UPDATED in 34.0.3. The shine on
 *        a glass box is no longer an oval (its rim showed in PowerPoint). 34.0.4:
 *        it is a wide diagonal band of light. The band is painted by a shape
 *        of exactly the size and corners of the box (so the shape has no edge
 *        of its own) with a gradient that is fully clear beside the band.
 *        34.0.5: each template has its own light - ten kinds that differ in
 *        angle, width, position, single or double band, or a glow from the top.
 *   256  DS Template chart background (id 1054) - NEW in 34.0.6. A panel behind
 *        every chart and diagram of a design template, in the deck's accent
 *        colour (follows the theme; glass templates use their own glass).
 *        34.0.7: the settings are inside Edit chart / Edit diagram: on / off
 *        and a slider for the corner roundness (0 = sharp ... 48), per slide;
 *        "Use on all chart slides" copies them. Cancel puts them back.
 *        The panel is a normal shape, so all downloads show it.
 *   MAIN Constants/functions DSV33_* -> DSV34_*; DSV33/32/31/... names stay
 *        defined and every old *_DISABLE switch still works.
 *        Deactivate V33 before activating V34 (never both).
 * 33.0.0 - 2026-10-04 Text / photo position layouts for every template.
 *        Nothing from V32.0.3 was removed; 1 include was added, checked
 *        character-for-character against the staging snippet of 4 Oct 2026:
 *   252  DS Template Scenario Layouts (id 1049) - NEW. The 55 templates that
 *        did not have them (all except Frost Orbit and the eight Glass
 *        templates) get the same set of layouts: one text panel left, right,
 *        centre, top, bottom or in a corner, on the page, over a full photo or
 *        beside a half photo. The panel uses the template's own card style,
 *        colours and fonts. Each template's own layouts stay first in the list.
 *        Slides with 9-16 points only get the wide panels.
 *        33.0.1: no side of a layout stays blank - every area the panel
 *        leaves free gets a tinted field with a graphic made from the slide's
 *        topic (the slide's icon and the icons of its points, joined by lines).
 *   253  DS Template Cycle layout (id 1050) - NEW in 33.0.2. In the "Cycle"
 *        layout (Pastel template) each text sits at the height of its own
 *        icon; with 3 points the third text no longer stands top-left while
 *        its icon is bottom-left.
 *   254  DS Template ghost numerals (id 1051) - NEW in 33.0.3. Layouts that
 *        print a huge faint number behind the heading (Mono Portfolio "Index
 *        rows", "Ghost columns" and similar): with a colour theme the number
 *        took the full heading colour and read as overlapping text. It now
 *        stays faint in every theme.
 *   255  DS PowerPoint add-in - no PDF (id 1052) - NEW in 33.0.4. Inside the
 *        PowerPoint add-in the Download menu no longer offers PDF, because
 *        PowerPoint cannot insert a PDF. The website in a browser is unchanged.
 *   MAIN Constants/functions DSV32_* -> DSV33_*; DSV32/31/30/... names stay
 *        defined and every old *_DISABLE switch still works.
 *        Deactivate V32 before activating V33 (never both).
 * 32.0.3 - 2026-10-04 No freeze after picking a theme or template.
 *   251  DS Template speed (id 1048) - NEW. Picking a theme on the AI
 *        Presentation page redrew all 65 template cards, and every card's
 *        font request redrew them again (64 times). Now: fonts are requested
 *        once per font set and cause at most one redraw; a template card is
 *        drawn when it is on screen. In the editor a slide is worked out once
 *        per action instead of 3-4 times, and a layout card of the "Change
 *        layout" panel is drawn when it scrolls into view.
 * 32.0.2 - 2026-10-04 Sharper slide pictures.
 *   249  DS PPT image clarity (id 1046) - NEW. Save and Download no longer
 *        compress slide pictures a second time (was 1600px, JPEG 0.82). A
 *        picture that is already slide-size is left as it is; an oversized
 *        one is resized once to 1920px at JPEG 0.92.
 *   250  DS PPT auto pictures (id 1047) - NEW. The automatic marketplace
 *        picture on a slide was the 400px preview. It is now the original
 *        resized to 1920px (JPEG 90, cached in uploads/ds-ppt-auto-img),
 *        not counted in the marketplace download limit; ceiling of 120 per
 *        user per hour; a Pro picture stays a preview for a plan that cannot
 *        use Pro pictures. New REST route ds/v1/mkt-auto-image.
 * 32.0.1 - 2026-10-04 PowerPoint could not open decks with hexagons.
 *   151  DS Template Engine T00 (id 952) - UPDATED. Hexagon shapes are written
 *        with both PowerPoint settings (adj + vf). Before, a deck with a
 *        non-square hexagon (Forest Campaign, Hex Forge, Spectrum Infographic,
 *        diagrams) made PowerPoint show "found a problem with content" and
 *        the repaired slides came out blank.
 *   248  DS Template 21 Forest - road fix (id 1045) - NEW. The second road on
 *        the Forest Campaign cover / closing was the colour of the panel and
 *        ran past the slide edge; it is now a lighter tone of the panel and
 *        stays inside it.
 * 32.0.0 - 2026-10-04 16 new design templates, frosted glass and PNG objects.
 *        Nothing from V31.0.1 was removed; 19 includes were added and 1 updated,
 *        checked character-for-character against the staging snippets of
 *        4 Oct 2026:
 *   229-235  DS Template 49-55 (Code Snippets ids 1022-1028) - NEW. Chevron Teal
 *        (the photo fills only the inside of the chevron), Copper Diamond
 *        (photos cut to a diamond, one or two), Saffron Study, Care Blue,
 *        Silk Burgundy, Ribbon Pine and Pulse Aqua.
 *   236  DS Template PNG Objects (id 1030) - NEW. "+ Add PNG" on a template
 *        slide: a transparent PNG (person, product, object) placed on top of
 *        the slide; drag to move, blue corner to resize, red x to remove; up to
 *        6 per slide; shown in the editor, thumbnails and downloads.
 *   237  DS Template Frosted Glass (id 1032) - NEW. Whatever lies under a glass
 *        box (background shapes, the slide photo) is blurred behind it; a soft
 *        shine in one corner; arrow shapes of the glass templates are glass
 *        too; charts of the glass templates use one colour family.
 *   238  DS Template Glass Kit (id 1034) - NEW. The shared layouts of templates
 *        57-64: several glass boxes, or one glass panel on any side or corner,
 *        over the scene or over a full / half photo (17+ layouts per slide kind).
 *   239  DS Template 56 - Frost Orbit (id 1033) - NEW. Dark, orange and violet
 *        spheres with gold rings behind frosted glass.
 *   240-247  DS Template 57-64 (ids 1035-1042) - NEW. Dark corporate glass:
 *        Glass Tower, Glass Beam, Glass Arc, Glass Mesh, Glass Wave, Glass Slat,
 *        Glass Flow and Glass Flute. Quiet backgrounds, text first.
 *   151  DS Template Engine T00 (id 952) - UPDATED. PowerPoint download writes
 *        the PNG objects, the blurred glass backdrops and arrow-shaped glass.
 *   Load order: the 19 new includes run last (229-247), after the engine and
 *        Template Kit 2 they need; the Glass Kit (238) loads before 240-247.
 *   NOT included, as in V31 (active on staging, never part of the plugin):
 *        840, 862 and 890; staging snippet 1014 (switched off) and the backup
 *        copies of the engine.
 *   MAIN Constants/functions DSV31_* -> DSV32_*; DSV31/30/29/28/27/26/25/24/23/22/20
 *        names stay defined and every old *_DISABLE switch still works.
 *        Deactivate V31 before activating V32 (never both).
 * 31.0.1 - 2026-10-03 Uploaded files written with "Slide 1:", "Slide 2:" markers.
 *   228  DS Doc Import - Slide Markers (Code Snippets id 1021) - NEW. An uploaded
 *        TXT / MD / DOCX / PDF with two or more "Slide N:" markers is read like
 *        pasted text: every marker starts its own slide, the next line is the
 *        subtitle, "Point:" + the next line become one point, and the deck title
 *        is the title of Slide 1 (it used to be the file name, with the text cut
 *        into slides of four points). Free plan and paid uploads. Runs last.
 * 31.0.0 - 2026-10-03 19 new design templates and the saved-items changes. Nothing
 *        from V30.0.1 was removed; 26 includes were added and 4 updated,
 *        byte-for-byte from the staging snippets exported on 3 Oct 2026:
 *   202-220  DS Template 30-48 (Code Snippets ids 986-1004) - NEW. Azure Prism,
 *        Opal Glass, Obsidian Glass, Circuit Tide, Halo Type, Sunset Glass,
 *        Frost Slate, Ember Noir, Linen Glass, Polar Night, Garnet Glass,
 *        Daybreak Glass, Citrus Pop, Mono Ink, Rosewater Glass, Slate Brief,
 *        Tangerine Split, Hex Forge (3D gears) and Stack Glass (3D servers).
 *        Each has its own fonts, colours and layouts for cover, agenda,
 *        points, stats, timeline, quote and closing.
 *   221  DS Template Hover Preview (id 1005) - NEW. Moving the mouse over a
 *        template card shows its 3-slide sample under the card.
 *   222  DS Template Point Overflow (id 1006) - NEW. Agenda, Timeline and Stats
 *        slides with more points than their own layout can show are drawn with
 *        the many-point layouts, so added points are no longer dropped.
 *   223  DS Template Second Picture (id 1008) - NEW. The second picture box of a
 *        two-picture layout (Midnight Neon closing) takes its own picture.
 *   224  DS Template Closing Contacts Fix (id 1009) - NEW. Contact pills move
 *        down when the takeaway line reaches them (Azure Corporate closing).
 *   225  DS PPT Save Limit Popup (id 1012) - NEW. Saving at the saved-items
 *        limit opens a popup (OK / Cancel); OK lists the saved items inside
 *        the popup, the user deletes some and the deck is then saved. Also
 *        removes not-kept download files from the server after an hour.
 *   226  DS PPT Real Cover Thumbnails (id 1013) - NEW. My Presentations cards
 *        show the real cover slide of each deck.
 *   227  DS My Creations - hide Downloaded Presentations (id 1019) - NEW.
 *   151  DS Template Engine T00 (id 952) - UPDATED. Second picture in the
 *        template .pptx; a download is built without the saved-items check
 *        and is no longer added to the saved list.
 *   076  12b ds ppt builder (id 760) - UPDATED. Same download rule for Classic
 *        decks.
 *   069  DS Storage & API Settings (id 755) - UPDATED. Downloaded PowerPoint
 *        files are not counted as saved items.
 *   124  DS My Creations Hub - my-cap fix (id 805) - UPDATED. Same count rule
 *        for the "saved items used" figure.
 *   Load order: the 26 new includes run last (202-227), after the engine and
 *        Template Kit 2 they need.
 *   NOT included, as in V30 (active on staging, never part of the plugin):
 *        840, 862 and 890. Also not included: staging snippet 1014 (Saved
 *        Items "Downloaded PPT" tab), which is switched off.
 *   MAIN Constants/functions DSV30_* -> DSV31_*; DSV30/29/28/27/26/25/24/23/22/20
 *        names stay defined and every old *_DISABLE switch still works.
 *        Deactivate V30 before activating V31 (never both).
 * 30.0.1 - 2026-10-03 Pictures missing in the PowerPoint download of design-template
 *        decks (all 29 templates; the picture area came out empty):
 *   151  DS Template Engine T00 (id 952) - FIXED. (1) WebP, GIF and other
 *        picture formats are converted to JPEG / PNG for the .pptx - before,
 *        only JPEG and PNG pictures were kept. (2) Pictures that the save /
 *        download off-loader (include 070) sends as "dsimg:" references are
 *        turned back into pictures before a template is built; the template
 *        builder runs earlier than the filter that did this for Classic decks.
 * 30.0.0 - 2026-10-03 Deck style, picture resize, breathing space, logo options - on
 *        Classic decks and on design templates. Nothing from V29 was removed;
 *        5 includes were added and 8 updated, byte-for-byte from the staging
 *        snippets exported on 3 Oct 2026:
 *   201  DS PPT Deck Style (Code Snippets id 979) - NEW. "Deck style" card above
 *        slide 1: one font, and weight / case (as typed, UPPERCASE, Title Case)
 *        / size / colour for Title, Subtitle, Point title, Point explanation
 *        and Key takeaway; slide background and accent colour; Reset to
 *        default. Click any text or icon for its own colour and size (icon and
 *        icon-background colour). Side pictures can be made wider or narrower
 *        ("- Image size +" or drag); on Classic decks the cards re-flow. Text
 *        and icons keep a margin inside every box; text that does not fit is
 *        made smaller. Works on Classic decks and on the 29 design templates;
 *        used by the editor, thumbnails, PDF / PNG / JPG and the .pptx.
 *   197  DS Template - Text Fit Guard (id 972) - NEW.
 *   198  DS PPT Image Limit - Upgrade Popup (id 973, front-end) - NEW.
 *   199  DS PPT Editor - Mobile & Tablet Layout (id 975, front-end) - NEW.
 *   200  DS PPT Editor - Marketplace Picker and Popup Fixes (id 976, front-end)
 *        - NEW.
 *   073  Snippet 16 PPTX renderers (id 957) - UPDATED. Deck style, single-object
 *        colours and the resized picture in the Classic .pptx; logo glow and
 *        second logo.
 *   077  Snippet 17 variant picker UI (id 953) - UPDATED. Deck style hooks; Logo
 *        settings: Title slide first, Glow sliders, second (short) logo for
 *        the slides after the cover.
 *   151  DS Template Engine T00 (id 952) - UPDATED. Second logo in the template
 *        .pptx.
 *   166  DS Diagrams (id 904) - UPDATED. Phases / chevrons roadmap alignment.
 *   046  Snippet 34 Canva-style image crop (id 732) - UPDATED. Crop works on
 *        slides whose picture was resized.
 *   132  DS PPT Background Autosave (id 833) - UPDATED. Deck style and
 *        single-object changes count as changes to save.
 *   181  DS Template 19 Mint & Lilac (id 918) - UPDATED.
 *   195  DS PPT Export (id 967, front-end) - UPDATED.
 *   Load order: the five new includes run last (197-201).
 *   NOT included, as in V29 (active on staging, never part of the plugin):
 *        840, 862 (the plugin's include 148 is already add-in bridge 1.10.0)
 *        and 890.
 *   MAIN Constants/functions DSV29_* -> DSV30_*; DSV29/28/27/26/25/24/23/22/20
 *        names stay defined and every old *_DISABLE switch still works.
 *        Deactivate V29 before activating V30 (never both).
 * 29.0.0 - 2026-10-02 30 designs, Classic Editor, export menu, free plan. Nothing from
 *        V28 was removed; 23 includes were added and 5 updated from the staging
 *        snippets exported on 1-2 Oct 2026 (byte-for-byte unless noted):
 *   174  DS Template Kit 2 (Code Snippets id 955) - NEW. Shared drawing helpers
 *        and layouts for templates 13-29; step pills take 8 points.
 *   175-191 DS Templates 13-29 (ids 912-920, 956, 922-928) - NEW. Graphite Loop,
 *        Grid Paper Classroom, Spectrum Infographic, Navy & Coral Flow,
 *        Isometric Slate, Lumen Gradient, Mint & Lilac, Horizon Launch, Forest
 *        Campaign, Terracotta & Teal (serpentine flow without overlapping
 *        text), Mono Portfolio, Navy Proposal, Retro Grid Pop, Brutal Orange,
 *        Emerald Corporate, Violet Pitch, Neon Lime Glass.
 *   192  DS Template - Preserve Mode Fix (id 930) - NEW. "Preserve My Text"
 *        decks use the design template picked on the home screen.
 *   193  DS PPT Image Upload (id 943, front-end) - NEW. "Upload from Device" in
 *        Add Image, upload link in Generate Image, drag & drop onto a slide.
 *   194  DS AI Presentation - Studio Home Layout (id 960, front-end) - NEW.
 *        Two-column home screen, template sample, My Presentations grid with
 *        search, sort and delete.
 *   195  DS PPT Export (id 967, front-end) - NEW. Download menu: PPTX, PDF, PNG,
 *        JPG (PDF and pictures are made in the browser).
 *   196  DS PPT Free Plan (id 968) - NEW. Free users get 2 presentations without
 *        AI (Preserve My Text only, 5 slides, PDF only, not saved). WP Admin >
 *        Settings > Free Presentations. Test as admin with ?ds_free_preview=1.
 *   073  Snippet 16 PPTX renderers (id 957, replaces 759) - UPDATED. Classic
 *        decks: up to 16 points per slide, Key takeaway and company logo in the
 *        .pptx, larger stat numbers.
 *   077  Snippet 17 variant picker UI (id 953, replaces 761) - UPDATED. Edit
 *        Slide panel, 9-16 point layouts, Key takeaway and logo, also on
 *        template decks; switch a stats slide to a chart and back.
 *   151  DS Template Engine T00 (id 952, replaces 873) - UPDATED. Template
 *        editing with 9-16 points and the company logo (also in the .pptx).
 *   172  DS Doc Import (id 903) - UPDATED. Uploaded-document decks carry the
 *        picked design template.
 *   084  26 DS PPT AI review before generate (id 933, replaces 766) - UPDATED,
 *        MERGED. The AI writes a "takeaway" line for point slides. Staging 933
 *        is a clone of staging 766, which does not have this plugin's 17.3
 *        timeout / retry / Groq-error handling, so only the three takeaway
 *        changes were applied to the V28 file; the V28 handling is kept.
 *   NOT included (older copies in the export folder): 911 (Kit 2 before the
 *        8-point fix), 921 (Template 22 before the serpentine fix), 931 and 932
 *        (Snippet 16 / 17 before the later clones). 905 is unchanged from V28.
 *   MAIN Constants/functions DSV28_* -> DSV29_*; DSV28/27/26/25/24/23/22/20 names
 *        stay defined and every old *_DISABLE switch still works.
 *        Deactivate V28 before activating V29 (never both).
 * 28.0.0 - 2026-09-30 Referral "Returning customers" setting. Nothing from V27 was
 *        removed or changed except the one include below, copied byte-for-byte
 *        from the staging snippet as of 30 Sep 2026 21:10 IST:
 *   167  DS Referral System - Core + Admin (id 905) - UPDATED. People who had an
 *        account before (deleted and re-joined) or were already referred are
 *        now rewarded like any new customer and only flagged "re-joined" in
 *        WP Admin > Referrals. New setting Referrals > Settings > Friend &
 *        safety > "Returning customers" (off by default) brings back the old
 *        rule of not rewarding them. Self-referrals stay blocked.
 *   MAIN Constants/functions DSV27_* -> DSV28_*; DSV27/26/25/24/23/22/20 names
 *        stay defined and every old *_DISABLE switch still works.
 *        Deactivate V27 before activating V28 (never both).
 * 27.0.0 - 2026-09-30 Upload reference file, Add-slide tile, diagram updates. Nothing
 *        from V26 was removed; two includes were added and two replaced, copied
 *        byte-for-byte from the staging snippets as of 30 Sep 2026 19:30 IST:
 *   172  DS Doc Import (Code Snippets id 903) - NEW. "Upload PDF, Word or PPT"
 *        in the prompt box attaches the file (no popup); "Instructions for AI"
 *        box under the prompt. On Build: Preserve My Text keeps the author's
 *        words (AI only arranges them into slides), Update with AI rewrites
 *        them; the selected language translates everything. Review Your
 *        Slides -> Generate Presentation as before. REST: /ds/v1/doc-preserve-
 *        preview, /doc-preserve-confirm, /doc-ai-preview, /doc-ocr.
 *   173  DS Add-Slide Tile (id 910) - NEW. "+" tile after the last slide and a
 *        white "Add slide" screen that opens the normal Add Slide popup.
 *   166  DS Diagrams (id 904) - UPDATED. Edit-chart hover box, 10 Gantt designs,
 *        layout cards keep their order, points kept / AI-filled when switching
 *        layouts, summary strip under gauge layouts, text box beside Target vs
 *        actual bars, optional prompt boxes + "Insert & edit myself".
 *   154  DS Template 03 - Chrome Noir (id 876) - UPDATED. Closing slide: text
 *        lifted on "Rings bottom right/left + image".
 *   MAIN Constants/functions DSV26_* -> DSV27_*; DSV26/25/24/23/22/20 names stay
 *        defined and every old *_DISABLE switch still works.
 *        Deactivate V26 before activating V27 (never both).
 * 26.0.0 - 2026-09-30 Referral program, share previews and diagrams. Nothing from
 *        V25 was removed or changed; six includes were added, all copied
 *        byte-for-byte from the staging snippets updated on 30 Sep 2026:
 *   166  DS Diagrams (Code Snippets id 904): business diagram gallery, 10
 *        categories, editable diagram slides, native charts in the PPTX.
 *   167  DS Referral System - Core + Admin (id 905): one referral code/link per
 *        user (?ref=CODE, 30-day cookie), sign-ups and subscriptions tracked in
 *        the wp_ds_referrals table, reward granted automatically once the friend
 *        completes the waiting period (default 30 days) without a refund:
 *        fixed credits per plan, % of the plan credits, or % of the payment as
 *        cash. Blocks self-referrals, same-IP sign-ups (optional), and anyone
 *        who had an account before or was already referred (deleted accounts
 *        are remembered by a one-way e-mail hash; Gmail dots/+tags ignored).
 *        WP Admin > Referrals: all referrals, top referrers, settings, manual
 *        grant / reject / restore, link a referral manually.
 *   168  My Referrals tab (id 906, front-end): in the account popup next to
 *        Credit History, replaces the Plans tab (setting). Link, code, share
 *        buttons, e-mail invites, results and each referral's status.
 *   169  Home Page Banner (id 907): referral banner below the home hero; design
 *        uploaded in WP Admin > Referrals > Home Banner; [ds_referral_banner].
 *   170  DS Share Preview (id 908): og:image / title / description for WhatsApp,
 *        Facebook, LinkedIn, X on every page; WP Admin > Settings > Share
 *        Preview. Choose the share image there after installing.
 *   171  Wallet & Withdrawals (id 909): cash rewards go to a wallet; UPI or
 *        bank payout details; Withdraw button from the minimum (default
 *        Rs 1,000 / $12); WP Admin > Referrals > Withdrawals to mark paid (UTR)
 *        or reject. Tables wp_ds_ref_withdrawals.
 *   MAIN Constants/functions DSV25_* -> DSV26_*; DSV25/24/23/22/20 names stay
 *        defined and every old *_DISABLE switch still works.
 * 25.0.0 - 2026-09-29 AI Presentation charts. New include 165 (Code Snippets id 898):
 *        "Generate a chart slide" in Add Slide (chart type, shape, start angle,
 *        rows of labels/values, extra text; AI writes title, subtitle and
 *        insights), a chart editor (click a chart), automatic charts on slides
 *        with percentage data (slide count unchanged), and real editable
 *        PowerPoint charts in downloads for Classic and template decks.
 *        073 (Snippet 16, id 759): adds the chart parts to the built .pptx.
 *        076 (12b, id 760), 151 (template engine, id 873): Add Slide charges the
 *        new "Credits per Add Slide" price. 151 also: chart layouts for
 *        template decks, 1024M memory for template downloads.
 *        077 (Snippet 17, id 761): chart slides render and pick chart layouts.
 *        088 (DS Credit Settings, id 769): new "Credits per Add Slide" setting
 *        (default 5) under AI Presentation Slides.
 *        Plugin renamed; constants/functions DSV24_* -> DSV25_*, the DSV24/23/22/20
 *        names stay defined and every old *_DISABLE switch still works.
 * 24.0.2 - 2026-09-29 Email verification (041, Code Snippets id 727): the "Resend code"
 *        button shows a countdown (Resend code in 0:59) and stays off until a new
 *        code may be sent; a too-early resend says how many seconds are left.
 * 24.0.1 - 2026-09-29 Sign-up bonus credits. New include 164 (Code Snippets id 894):
 *        every new account gets free AI credits once (default 5). On/off and the
 *        amount are in WP Admin > DS Credits > Sign-up Bonus. Off until switched on.
 * 24.0.0 - 2026-09-29 AI Presentation design templates. New includes 151-163: the
 *        template engine (Code Snippets id 873) and 12 design templates (874-885:
 *        Glassline, Cobalt Bold, Chrome Noir, Aqua Wave, Paper Proposal, Noir Gold,
 *        Violet, Blueprint, Neon, Bronze, Azure, Executive). Each template has its
 *        own AI prompt, layouts and PPTX build; themes recolour template decks.
 *        077 (Snippet 17, id 761): hooks for template decks; Milestone cards and
 *        Circular timeline layouts respaced so text fits. 138 (id 820): timeline
 *        descriptions kept small instead of the 1.05em card size.
 * 23.0.17 - 2026-09-27 PowerPoint add-in: My Generations images, videos and audio
 *        insert into the presentation instead of downloading. Cloud-storage links
 *        (/wp-json/ds-hub/v1/file/ID) are recognised, fetched on the server for
 *        their owner, and nameless files are identified from their contents.
 *        Video/audio file links (.mp4, .mp3 ...) are caught as well.
 * 23.0.16 - 2026-09-27 PowerPoint add-in: Sign out asks "Sign out of DesignStager?"
 *        with Cancel / Sign out before signing out.
 * 23.0.15 - 2026-09-27 PowerPoint add-in: Sign out works in the panel itself. The
 *        log-out link was HTML-escaped (&amp;), so WordPress showed "Do you really
 *        want to log out?" in a dialog and PowerPoint reported an add-in error.
 * 23.0.14 - 2026-09-27 PowerPoint add-in: pictures are kept at or under 3 MB before
 *        inserting; PowerPoint cuts off pictures over ~3.6 MB (only the top part
 *        shows). Library paging ordered by date + ID so no item repeats or is
 *        skipped. Files in Download Monitor's protected dlm_uploads folder
 *        (HTTP 403 when read directly) insert through their Download link, once.
 * 23.0.13 - 2026-09-27 PowerPoint add-in: Images insert the item's real download
 *        file. Some items' featured image is only a 400 px thumb-*.webp, which was
 *        inserted small and partly drawn. Any .webp/.avif picture is converted to
 *        PNG before inserting.
 * 23.0.12 - 2026-09-27 PowerPoint add-in: a search bar in every Marketplace section
 *        (Templates, Images, Videos, Icons, Audios) that searches only that
 *        section, using the website marketplace search (titles, descriptions
 *        and the Groq AI tags, with a word-by-word fallback). Videos, Audios and
 *        Icons previously ignored searches. Switching section clears the search.
 * 23.0.11 - 2026-09-27 /my-account/ opens the account popup directly (signed-out
 *        visitors go to /login/ and come back); also ?ds_account=1 or #account on
 *        any page. Add-in AI Suites: tile pictures can be chosen in Settings >
 *        DesignStager Office; icons removed from tiles; AI Studio tile removed;
 *        AI Image opens /ai-studio/. The account popup's Plans tab is hidden
 *        inside PowerPoint.
 * 23.0.10 - 2026-09-27 PowerPoint add-in: status bar shows up to 3 lines instead of
 *        cutting messages off; a missing file (404) says so instead of "not
 *        available for your account"; the download-limit message no longer
 *        says "for today" (paid plans have monthly limits).
 * 23.0.9 - 2026-09-27  PowerPoint add-in: Icons tab lists the icons category
 *        (/marketplace/?type=visual&cat=icons). It was always hidden because the
 *        check ran before Download Monitor registered its categories. Items that
 *        are .pptx say "Insert slides". Icon cards use small thumbnails. Images
 *        big-window link goes to ?type=visual&cat=images.
 * 23.0.8 - 2026-09-27  PowerPoint add-in: audio rows (and all cards) always fit the
 *        panel width; a long title no longer pushes the row and its Insert
 *        button off the right edge. Player shrinks on narrow panels.
 * 23.0.7 - 2026-09-27  PowerPoint add-in: picking a category (or searching) opens
 *        the results full-panel with a back arrow and the category name; Back
 *        (or Esc) returns to the front page exactly as it was.
 * 23.0.6 - 2026-09-27  AppSource readiness. Images tab uses small cached 480 px
 *        thumbnails (was the 30-60 MB originals). /support/ redirects to the
 *        contact page and /terms/ shows the [ds_terms] Terms of Use when no real
 *        page exists. Buy / Upgrade hidden in the add-in (policy 1100.3). Icons
 *        tab hidden while there is no icons category. All leftover
 *        stagingtest.designstager.com links, emails and watermark text now say
 *        designstager.com.
 * 23.0.5 - 2026-09-27  PowerPoint add-in: an inserted video shows a real frame from
 *        the clip (about 1 s in) as its picture on the slide, falling back to the
 *        marketplace poster, instead of the generic navy play placeholder.
 * 23.0.4 - 2026-09-27  PowerPoint add-in: video cards use the website's poster
 *        images; clips that still need a live preview retry once if the server
 *        is busy (it answered some of 24 parallel previews with HTTP 500).
 * 23.0.3 - 2026-09-27  PowerPoint add-in: pictures are inserted up to 3840x2160
 *        instead of 1920x1080, so they stay sharp full-slide and when presenting.
 * 23.0.2 - 2026-09-27  PowerPoint add-in: Images tab now lists the "images"
 *        category (same as /marketplace/?type=visual&cat=images). It used to take
 *        "everything not a template", so thumbnail-less videos filled page 1 and
 *        were all dropped, showing "Nothing found".
 * 23.0.1 - 2026-09-27  PowerPoint add-in: video preview and "Insert video" fixed.
 *        Marketplace videos store a server disk path in Download Monitor, which
 *        the add-in turned into a URL that 404'd. Paths now map to the uploads URL.
 * 23.0.0 - 2026-09-27  Snippet 148 rebuilt. Asset VERSION stays 1.10.0, but the
 *        add-in files changed, so clear the Office cache once after updating
 *        (close PowerPoint, empty %LOCALAPPDATA%\Microsoft\Office\16.0\Wef).
 *   148  - Marketplace tabs for Videos, Icons and Audios alongside Templates
 *          and Images, served by a second library handler that runs ahead of
 *          the built-in one and answers only for those kinds.
 *        - Audio and video now insert into the deck. PowerPoint's JavaScript
 *          API cannot place media on a slide, so the pane builds a one-slide
 *          .pptx around the file in the browser and inserts that.
 *        - Images are scaled to fit 1920x1080 before insertion. Marketplace
 *          originals run to 5888px and 57 MB, which PowerPoint refuses.
 *        - New /ds-office/<ver>/preview route streams audio and video to the
 *          pane with byte ranges, for thumbnails and the track player. It
 *          never touches Download Monitor, so listening is not a download.
 *        - Audio is listed as track rows matching the marketplace audio
 *          section; video tiles show the clip's own first frame.
 *        - Marketplace pages opened inside PowerPoint collapse the category
 *          rail behind a Categories button. The website is unchanged.
 *        - Add-in files are served no-store, so updates reach PowerPoint.
 *
 * 22.1.0 - 2026-09-21  Snippet 148 reworked for the AppSource submission.
 *        Asset VERSION 1.9.2 -> 1.10.0, so re-download the manifest and
 *        re-add the add-in in PowerPoint after updating.
 *   148  TASK PANE REWRITTEN. It now searches the library and inserts from
 *        the pane itself (new /ds-office/<ver>/library JSON endpoint) instead
 *        of only opening the website in a dialog - the "web wrapper" shape
 *        Marketplace policy 1120.1 rejects. The dialog is still there as
 *        "Open full library".
 *        - First paint is plain HTML with a skeleton and a live status line,
 *          so a slow office.js no longer leaves a blank white pane
 *          (policy 1120.3), and failures show a message with Retry.
 *        - Layout fits a 320px task pane: auto-fill grid, no horizontal
 *          scroll, single column below 330px, light and dark themes.
 *        - Guests see real thumbnails and titles before signing in
 *          (policy 1120.1: no sign-in wall on first run); only inserting
 *          needs an account.
 *        - Inserts run through the existing proxy, so Download Monitor
 *          permissions, plan checks and daily limits are unchanged.
 *        - New "Marketplace mode" setting (on by default) hides pricing,
 *          upgrade and checkout links inside the PowerPoint window, as
 *          policy 1100.3 requires.
 *        - Ribbon and manifest icons are now the DesignStager logo, embedded
 *          in the snippet at 16/20/24/32/40/48/64/80/96/128, so they no
 *          longer depend on the Site Icon setting or on GD (policy 1100.7).
 *
 * 22.0.3 - 2026-09-21  Removed the "Connected to PowerPoint" toast. bridge.js
 *        re-activates on every page load inside the PowerPoint window, so it
 *        reappeared on every navigation. The relabelled Insert buttons already
 *        signal that the add-in is live. Insert progress, success and error
 *        toasts are unchanged. Asset VERSION stays 1.9.2, so any manifest
 *        already loaded into PowerPoint keeps working.
 *
 * 22.0.2 - 2026-09-21  Snippet 148 finally actually loads.
 *   148  Replaced the class_exists( 'DS_Office_Bridge' ) re-entry guard with a
 *        constant guard, and refreshed the snippet from the hardened 1.9.2
 *        bridge (task-pane sign in / sign up / sign out, whoami and health
 *        endpoints, configurable support page for the manifest's SupportUrl).
 *
 *        ROOT CAUSE, for the record: PHP binds an unconditional top-level
 *        class declaration at COMPILE time, before any statement in the file
 *        runs. class_exists( 'DS_Office_Bridge' ) at the top of the file was
 *        therefore ALWAYS true on the very first load, the file returned
 *        there every time, DS_Office_Bridge::init() was never reached, no
 *        hooks were registered, and /ds-office/... returned the theme's 404 -
 *        with no exception, no log line and a file that parsed perfectly. It
 *        only ever worked under Code Snippets, whose eval()'d bodies are not
 *        compile-time bound, which is why staging worked and live never did.
 *
 * 22.0.1 - 2026-09-21  Fixes snippet 148 never loading on designstager.com.
 *   148  The class_exists( 'DS_Office_Bridge' ) guard used PHP's default
 *        autoload=true, so it ran every registered autoloader on the site for
 *        that class name. An autoloader that throws on an unknown class made
 *        the whole require fail; the loader's per-snippet try/catch swallowed
 *        it, so the PowerPoint bridge silently never registered while every
 *        other snippet kept working. Now class_exists( ..., false ), which
 *        only asks whether the class is already declared. No behaviour change
 *        when no autoloader misbehaves.
 *   MAIN Snippet load failures are no longer log-only. They are collected and
 *        printed as an admin notice to users who can activate plugins, naming
 *        the file, the message and the file:line. A silently skipped snippet
 *        was impossible to diagnose without server log access.
 *
 * 22.0.0 - 2026-09-21  Renamed release of the 20.1.0 package. Nothing was
 *        dropped: all 140 active snippets, index.php guards, readme and build
 *        manifest carry over unchanged except where listed here.
 *   MAIN Plugin file renamed designstager-v20.php -> designstager-v22.php,
 *        plugin name "DesignStager V22", version 22.0.0. Constants and
 *        functions renamed DSV20_* -> DSV25_* and dsv20_* -> dsv25_*. The old
 *        DSV20_VERSION / DSV20_PATH / DSV20_URL are still defined as aliases,
 *        and a DSV20_DISABLE already sitting in wp-config.php still works
 *        alongside the new DSV25_DISABLE, so no existing recovery switch or
 *        third-party reference breaks.
 *   148  DS PowerPoint Add-in Bridge (Office) refreshed from the standalone
 *        designstager-office-bridge.php export (DS_Office_Bridge internal
 *        VERSION 1.8.0). The class body is byte-identical to that file; only
 *        its "Plugin Name:" header is stripped so WordPress does not list the
 *        bridge as a second plugin, an ABSPATH guard is restored for direct
 *        file access, and the class_exists( 'DS_Office_Bridge' ) guard is kept
 *        so a Code Snippets or standalone copy wins instead of fataling on a
 *        duplicate class.
 *   149  DS Google Ads Conversion - Subscription Purchase carried over from
 *        20.1.0 unchanged.
 *
 * 20.1.0 - 2026-09-21  Two snippets added from the 20/21 Sep 2026 exports:
 *   148  NEW DS PowerPoint Add-in Bridge (Office). Lets the DesignStager
 *        PowerPoint add-in open the site in a full-size PowerPoint window and
 *        insert downloads (slides, images, SVG) straight into the deck; normal
 *        visitors see nothing. Exported as a standalone plugin, so its
 *        "Plugin Name:" header was stripped (WordPress would otherwise list it
 *        twice) and a class_exists( 'DS_Office_Bridge' ) guard was added so a
 *        Code Snippets or standalone copy wins instead of fataling on a
 *        duplicate class. Settings live under its own admin screen.
 *   149  NEW DS Google Ads Conversion - Subscription Purchase. Fires the
 *        AW-18302135400 conversion on /thank-you only after snippet 117
 *        (DS Thank You Page) has shown #ds-ty-success, i.e. after Razorpay is
 *        verified. Value = plan INR price, transaction_id = Razorpay payment
 *        id, de-duplicated in localStorage so a refresh cannot double count.
 *        Scope front-end. Loads after 117 for that reason.
 *
 * 20.0.0 - 2026-09-18  V19 plus these snippets, exported from staging:
 *   012  DS AI Hero Section - updated from staging.
 *   080  Snippet 30 template gallery [CLONE2] - updated from staging.
 *   108  ds-marketplace-shortcode 04/28 [CLONE] - updated from staging; grid
 *        card videos now retry playback (autoplay attribute + loadeddata retry
 *        + a 1.5s watchdog), so thumbnails no longer sit frozen.
 *   146  NEW DS User Location.
 *   147  NEW DS AI Presentation - Browse Templates URL (rewrite).
 *
 * 19.0.0 - 2026-09-16  V17.1 plus these fixes (ported from staging):
 *   080  Snippet 30 template gallery: finds template files stored on R2 / in
 *        DLM's "_files" JSON meta (fixes "Template file not found"), raises
 *        the PCRE backtrack limit so 1-3 MB slide XML is readable (templates
 *        24260, 24259, 24166 were downloading unfilled), and returns a clear
 *        error instead of silently downloading an unfilled template.
 *   073  Snippet 16 variant renderers: every point gets an SVG icon - when the
 *        AI icon name is missing/invalid it searches Iconify by the point
 *        title, then falls back to a varied default set.
 *   043  Snippet 28 theme picker: theme panel no longer auto-opens (editor or
 *        template preview); it opens only from the Theme button.
 *   085  Snippet 21: button renamed to "Pick a Design".
 *   145  NEW Snippet 32T: template/marketplace-pick icon injection, separate
 *        from Snippet 32 (which is unchanged). Skips number-only labels,
 *        pairs each icon with its own point title, cached + retried Iconify
 *        downloads, no star fallback, handles large slide XML. It takes over
 *        the ds30/ds_tpl icon filters from Snippet 32 and Snippet 33.
 *   Not ported: the 12c blank-strip CSS fix - the live 12c markup already has
 *   correct ids, so the strip does not occur there.
 *
 * 17.1.0 - 2026-09-13  Rebuilt from live source. Fixes carried in this build:
 *   022  Razorpay webhook: every delivery is now recorded before signature
 *        checking (ds_rzp_last_time / ds_rzp_last_raw), rejections log a
 *        reason instead of failing silently, ds_resolve_plan_for_subscription()
 *        maps a Razorpay plan_id back to our plan key so a renewal can no
 *        longer land without changing the plan, the renewal grace window went
 *        from 2 to 5 days, and an admin plan change keeps the real sub_id
 *        instead of overwriting it with 'manual'.
 *   003  ds_activate_plan() now records amount, currency and plan_credits in
 *        ds_plan_history, so a purchase can be audited against what the plan
 *        was supposed to grant.
 *   089  Removed a duplicated empty "elseif ($is_4k) {}". Every 4K image
 *        request fell into that empty branch, $final was never assigned, and
 *        the caller was charged for an empty data URI. 4K now returns
 *        4096x2304.
 *   092  Repaired a botched find-and-replace that had swapped the model KEY
 *        'cloudflare-klein' for its display name in six places, breaking the
 *        free-model list, the cost map and the synchronous-model check.
 *   071  ds_get_video_cost() normalises resolution keys and falls back to the
 *        highest configured tier, so an unconfigured '4k' is no longer billed
 *        at the 1.0 multiplier.
 *   084  PPT outline generation: token budget scales with slide count
 *        (was floored at 9000, which swallowed scaling below 12 slides),
 *        Groq HTTP/API errors are surfaced and retried with back-off, and a
 *        truncated response retries once at a smaller budget.
 *   093  Credit amounts removed from the AI Studio model dropdown labels
 *        (data-cost is still emitted for the live cost readout).
 *   116  Pricing page rebuilt to read live values: per-model 4K resolution
 *        overrides from ds_image_credit_settings, and an AI Video section
 *        that calls ds_get_video_cost() instead of quoting stale numbers.
 *   140  DS_Daily_Report::instance() was never called when the class already
 *        existed, which silently removed the Tools > DS Daily Report screen.
 *        Added a subscription_purchases() section reporting, per purchase,
 *        both the credits the plan defines and the credits actually granted.
 *
 * 17.0.0 - 2026-09-11  First packaged build from the Code Snippets export.
 *        Iconify icons on marketplace-template downloads, SVG (recolourable)
 *        icons in AI-generated decks, no re-save / background autosave on
 *        saved presentations, Pick-a-Design sidebar fixes.
 *
 * HOW THIS RELATES TO CODE SNIPPETS
 * ---------------------------------
 * Each file in includes/ is one snippet, named <load order>-<snippet name>.php.
 * They are required in exactly the order Code Snippets ran them: by priority
 * first, then by the ds-order-NNN tag, then by snippet id. The order matters —
 * several snippets check function_exists() for helpers defined by earlier ones.
 *
 * IMPORTANT: activate this plugin OR the Code Snippets copies, never both.
 * Running both at once declares the same functions twice and fatals the site.
 * Deactivate every DesignStager snippet in Code Snippets before activating.
 *
 * Inactive snippets were deliberately left out. They are in the export folder
 * under snippets/inactive/ if you ever need them.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DSV42_VERSION', '42.0.3' );
define( 'DSV42_PATH', plugin_dir_path( __FILE__ ) );
define( 'DSV42_URL', plugin_dir_url( __FILE__ ) );

/*
 * Back-compat aliases. Nothing inside includes/ uses these, but anything
 * outside the bundle that learned the V20 or V22 names keeps working.
 */
if ( ! defined( 'DSV41_VERSION' ) ) {
	define( 'DSV41_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV41_PATH' ) ) {
	define( 'DSV41_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV41_URL' ) ) {
	define( 'DSV41_URL', DSV42_URL );
}
if ( ! defined( 'DSV40_VERSION' ) ) {
	define( 'DSV40_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV40_PATH' ) ) {
	define( 'DSV40_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV40_URL' ) ) {
	define( 'DSV40_URL', DSV42_URL );
}
if ( ! defined( 'DSV39_VERSION' ) ) {
	define( 'DSV39_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV39_PATH' ) ) {
	define( 'DSV39_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV39_URL' ) ) {
	define( 'DSV39_URL', DSV42_URL );
}
if ( ! defined( 'DSV38_VERSION' ) ) {
	define( 'DSV38_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV38_PATH' ) ) {
	define( 'DSV38_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV38_URL' ) ) {
	define( 'DSV38_URL', DSV42_URL );
}
if ( ! defined( 'DSV37_VERSION' ) ) {
	define( 'DSV37_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV37_PATH' ) ) {
	define( 'DSV37_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV37_URL' ) ) {
	define( 'DSV37_URL', DSV42_URL );
}
if ( ! defined( 'DSV36_VERSION' ) ) {
	define( 'DSV36_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV36_PATH' ) ) {
	define( 'DSV36_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV36_URL' ) ) {
	define( 'DSV36_URL', DSV42_URL );
}
if ( ! defined( 'DSV35_VERSION' ) ) {
	define( 'DSV35_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV35_PATH' ) ) {
	define( 'DSV35_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV35_URL' ) ) {
	define( 'DSV35_URL', DSV42_URL );
}
if ( ! defined( 'DSV34_VERSION' ) ) {
	define( 'DSV34_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV34_PATH' ) ) {
	define( 'DSV34_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV34_URL' ) ) {
	define( 'DSV34_URL', DSV42_URL );
}
if ( ! defined( 'DSV33_VERSION' ) ) {
	define( 'DSV33_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV33_PATH' ) ) {
	define( 'DSV33_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV33_URL' ) ) {
	define( 'DSV33_URL', DSV42_URL );
}
if ( ! defined( 'DSV32_VERSION' ) ) {
	define( 'DSV32_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV32_PATH' ) ) {
	define( 'DSV32_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV32_URL' ) ) {
	define( 'DSV32_URL', DSV42_URL );
}
if ( ! defined( 'DSV31_VERSION' ) ) {
	define( 'DSV31_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV31_PATH' ) ) {
	define( 'DSV31_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV31_URL' ) ) {
	define( 'DSV31_URL', DSV42_URL );
}
if ( ! defined( 'DSV30_VERSION' ) ) {
	define( 'DSV30_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV30_PATH' ) ) {
	define( 'DSV30_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV30_URL' ) ) {
	define( 'DSV30_URL', DSV42_URL );
}
if ( ! defined( 'DSV29_VERSION' ) ) {
	define( 'DSV29_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV29_PATH' ) ) {
	define( 'DSV29_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV29_URL' ) ) {
	define( 'DSV29_URL', DSV42_URL );
}
if ( ! defined( 'DSV28_VERSION' ) ) {
	define( 'DSV28_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV28_PATH' ) ) {
	define( 'DSV28_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV28_URL' ) ) {
	define( 'DSV28_URL', DSV42_URL );
}
if ( ! defined( 'DSV27_VERSION' ) ) {
	define( 'DSV27_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV27_PATH' ) ) {
	define( 'DSV27_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV27_URL' ) ) {
	define( 'DSV27_URL', DSV42_URL );
}
if ( ! defined( 'DSV26_VERSION' ) ) {
	define( 'DSV26_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV26_PATH' ) ) {
	define( 'DSV26_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV26_URL' ) ) {
	define( 'DSV26_URL', DSV42_URL );
}
if ( ! defined( 'DSV25_VERSION' ) ) {
	define( 'DSV25_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV25_PATH' ) ) {
	define( 'DSV25_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV25_URL' ) ) {
	define( 'DSV25_URL', DSV42_URL );
}
if ( ! defined( 'DSV24_VERSION' ) ) {
	define( 'DSV24_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV24_PATH' ) ) {
	define( 'DSV24_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV24_URL' ) ) {
	define( 'DSV24_URL', DSV42_URL );
}
if ( ! defined( 'DSV23_VERSION' ) ) {
	define( 'DSV23_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV23_PATH' ) ) {
	define( 'DSV23_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV23_URL' ) ) {
	define( 'DSV23_URL', DSV42_URL );
}
if ( ! defined( 'DSV22_VERSION' ) ) {
	define( 'DSV22_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV22_PATH' ) ) {
	define( 'DSV22_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV22_URL' ) ) {
	define( 'DSV22_URL', DSV42_URL );
}
if ( ! defined( 'DSV20_VERSION' ) ) {
	define( 'DSV20_VERSION', DSV42_VERSION );
}
if ( ! defined( 'DSV20_PATH' ) ) {
	define( 'DSV20_PATH', DSV42_PATH );
}
if ( ! defined( 'DSV20_URL' ) ) {
	define( 'DSV20_URL', DSV42_URL );
}

/**
 * The snippets, in load order. 'scope' mirrors the Code Snippets scope:
 *   global    — runs everywhere
 *   front-end — runs only on the front end
 *   admin     — runs only in wp-admin
 */
$dsv42_snippets = array(
	array( 'file' => '001-ds-credit-core-override.php', 'scope' => 'global', 'id' => 687 ),
	array( 'file' => '002-ds-api-keys-settings-snippet-clone-clone.php', 'scope' => 'global', 'id' => 688 ),
	array( 'file' => '003-snippet-1a-ds-credit-core-admin-credit-settings-tiered-ppt.php', 'scope' => 'global', 'id' => 689 ),
	array( 'file' => '004-ds-ppt-preserve-mode-clone-fix-title-fields-short-desc-pairing-journey.php', 'scope' => 'global', 'id' => 690 ),
	array( 'file' => '005-ds-security-guard-claude-2026-09-06.php', 'scope' => 'global', 'id' => 691 ),
	array( 'file' => '006-download-with-log-in.php', 'scope' => 'global', 'id' => 692 ),
	array( 'file' => '007-ds-premium-role-refresh.php', 'scope' => 'global', 'id' => 693 ),
	array( 'file' => '008-ds-pricing-plan-status.php', 'scope' => 'global', 'id' => 694 ),
	array( 'file' => '009-ds-login-redirect.php', 'scope' => 'global', 'id' => 695 ),
	array( 'file' => '010-6-ds-ppt-download-engine.php', 'scope' => 'global', 'id' => 696 ),
	array( 'file' => '011-ds-homepage-split-section.php', 'scope' => 'global', 'id' => 697 ),
	array( 'file' => '012-ds-ai-hero-section.php', 'scope' => 'global', 'id' => 698 ),
	array( 'file' => '013-ds-services-page-style.php', 'scope' => 'global', 'id' => 699 ),
	array( 'file' => '014-11-ds-ai-thumbnails.php', 'scope' => 'global', 'id' => 700 ),
	array( 'file' => '015-snippet-hide-ai-cards.php', 'scope' => 'global', 'id' => 701 ),
	array( 'file' => '016-14-ds-profile-popup.php', 'scope' => 'global', 'id' => 702 ),
	array( 'file' => '017-snippet-marketplace-css.php', 'scope' => 'global', 'id' => 703 ),
	array( 'file' => '018-ds-disable-big-image-scaling.php', 'scope' => 'global', 'id' => 704 ),
	array( 'file' => '019-12a-ds-ai-ppt-renderers.php', 'scope' => 'global', 'id' => 705 ),
	array( 'file' => '020-ds-marketplace-admin-approve.php', 'scope' => 'global', 'id' => 706 ),
	array( 'file' => '021-ds-credit-pools.php', 'scope' => 'global', 'id' => 707 ),
	array( 'file' => '022-snippet-1b-ds-credit-webhook.php', 'scope' => 'global', 'id' => 708 ),
	array( 'file' => '023-ds-login-signup-fixes-v3-updated.php', 'scope' => 'global', 'id' => 709 ),
	array( 'file' => '024-12c-ds-ppt-ui-new.php', 'scope' => 'global', 'id' => 710 ),
	array( 'file' => '025-snippet-3-admin.php', 'scope' => 'global', 'id' => 711 ),
	array( 'file' => '026-snippet-4-dlm-new.php', 'scope' => 'global', 'id' => 712 ),
	array( 'file' => '027-snippet-5-earnings.php', 'scope' => 'global', 'id' => 713 ),
	array( 'file' => '028-ds-ai-image-persistence.php', 'scope' => 'global', 'id' => 714 ),
	array( 'file' => '029-snippet-18-image-per-slide.php', 'scope' => 'global', 'id' => 715 ),
	array( 'file' => '030-ds-ai-auto-fill-creator-uploads.php', 'scope' => 'global', 'id' => 716 ),
	array( 'file' => '031-snippet-5-dlm-product-ai-fill.php', 'scope' => 'global', 'id' => 717 ),
	array( 'file' => '032-ds-marketplace-media-pipeline.php', 'scope' => 'global', 'id' => 718 ),
	array( 'file' => '033-20-ds-ppt-public-share.php', 'scope' => 'global', 'id' => 719 ),
	array( 'file' => '034-22-ds-ppt-save-load-variant-fix.php', 'scope' => 'global', 'id' => 720 ),
	array( 'file' => '035-ds-unified-dashboard.php', 'scope' => 'global', 'id' => 721 ),
	array( 'file' => '036-ds-fix-broken-local-font-fallback-code-snippets.php', 'scope' => 'global', 'id' => 722 ),
	array( 'file' => '037-ds-ai-settings.php', 'scope' => 'global', 'id' => 723 ),
	array( 'file' => '038-block-generation-until-email-verified.php', 'scope' => 'global', 'id' => 724 ),
	array( 'file' => '039-set-razorpay-webhook-secret.php', 'scope' => 'global', 'id' => 725 ),
	array( 'file' => '040-new-standalone-snippet.php', 'scope' => 'global', 'id' => 726 ),
	array( 'file' => '041-fix-email-verification-snippet.php', 'scope' => 'global', 'id' => 727 ),
	array( 'file' => '042-27-ds-ppt-structure-auto-detect.php', 'scope' => 'global', 'id' => 728 ),
	array( 'file' => '043-snippet-28-theme-font-picker.php', 'scope' => 'global', 'id' => 729 ),
	array( 'file' => '044-snippet-29-template-gallery.php', 'scope' => 'global', 'id' => 730 ),
	array( 'file' => '045-snippet-33-iconify-marketplace-bridge.php', 'scope' => 'global', 'id' => 731 ),
	array( 'file' => '046-snippet-34-canva-style-image-crop.php', 'scope' => 'global', 'id' => 732 ),
	array( 'file' => '047-snippet-35b-ds-blog-page.php', 'scope' => 'global', 'id' => 733 ),
	array( 'file' => '048-snippet-36-ds-copyright-claim.php', 'scope' => 'global', 'id' => 734 ),
	array( 'file' => '049-snippet-37-ds-terms-and-conditions.php', 'scope' => 'global', 'id' => 735 ),
	array( 'file' => '050-snippet-32-ds-blog-page.php', 'scope' => 'global', 'id' => 736 ),
	array( 'file' => '051-snippet-35-ds-ai-blog-writer.php', 'scope' => 'global', 'id' => 737 ),
	array( 'file' => '052-snippet-37-ds-blog-share-bar.php', 'scope' => 'global', 'id' => 738 ),
	array( 'file' => '053-snippet-37b-ds-terms-conditions-admin-editor.php', 'scope' => 'global', 'id' => 739 ),
	array( 'file' => '054-snippet-36b-ds-copyright-admin.php', 'scope' => 'global', 'id' => 740 ),
	array( 'file' => '055-snippet-38-ds-blog-social-auto-post.php', 'scope' => 'global', 'id' => 741 ),
	array( 'file' => '056-ds-ai-video-tab-injector.php', 'scope' => 'global', 'id' => 742 ),
	array( 'file' => '057-ds-ai-video-settings.php', 'scope' => 'global', 'id' => 743 ),
	array( 'file' => '058-2-designstager-native-portal-ui.php', 'scope' => 'global', 'id' => 744 ),
	array( 'file' => '059-1-designstager-landing-snippet.php', 'scope' => 'global', 'id' => 745 ),
	array( 'file' => '060-ds-pricing-settings.php', 'scope' => 'global', 'id' => 746 ),
	array( 'file' => '061-ds-ppt-r2-storage-post-process.php', 'scope' => 'global', 'id' => 747 ),
	array( 'file' => '062-snippet-1-backend-r2-marketplace-fix.php', 'scope' => 'global', 'id' => 748 ),
	array( 'file' => '063-ds-ai-video-marketplace-submission-r2-fix.php', 'scope' => 'global', 'id' => 749 ),
	array( 'file' => '064-ds-storage-retention-cron-dashboard.php', 'scope' => 'global', 'id' => 750 ),
	array( 'file' => '065-ds-ai-studio-auto-load-draft-from-my-creations-hub.php', 'scope' => 'global', 'id' => 751 ),
	array( 'file' => '066-ds-turnstile-bot-protection-signup.php', 'scope' => 'global', 'id' => 752 ),
	array( 'file' => '067-ds-marketplace-daily-download-limit-api-free-ip-cap-5-day.php', 'scope' => 'global', 'id' => 753 ),
	array( 'file' => '068-ds-saved-items-cap-settings.php', 'scope' => 'global', 'id' => 754 ),
	array( 'file' => '069-ds-storage-api-settings-r2-retention-v3-clone-clone-clone-tier-based-s.php', 'scope' => 'global', 'id' => 755 ),
	array( 'file' => '070-12e-ds-ppt-save-drafts-my-ppts-load-more-pagination-tier-based-save-ca.php', 'scope' => 'global', 'id' => 756 ),
	array( 'file' => '071-ds-ai-video-api-callers-r2-storage-admin-credit-settings.php', 'scope' => 'global', 'id' => 757 ),
	array( 'file' => '072-snippet-31-slide-preview-live-admin-template-credit-cost.php', 'scope' => 'global', 'id' => 758 ),
	array( 'file' => '073-snippet-16-variant-pptx-renderers-clone2-fix-multipoint-grid-5-8-card.php', 'scope' => 'global', 'id' => 957 ),
	array( 'file' => '076-12b-ds-ppt-builder-my-built-ppts-load-more-pagination-clone-add-slide.php', 'scope' => 'global', 'id' => 760 ),
	array( 'file' => '077-snippet-17-variant-picker-ui-clone5-add-slide-sends-append-slide-flag.php', 'scope' => 'global', 'id' => 953 ),
	array( 'file' => '078-snippet-32-ds-ppt-iconify-icon-injector-v2-geometry-based-pairing-clon.php', 'scope' => 'global', 'id' => 762 ),
	array( 'file' => '080-snippet-30-template-gallery-clone2-real-credit-deduction-fix.php', 'scope' => 'global', 'id' => 763 ),
	array( 'file' => '081-snippet-19-marketplace-images-clone2-load-more-race-guard-fix.php', 'scope' => 'global', 'id' => 764 ),
	array( 'file' => '082-23-ds-ppt-multi-template-combine-clone-credit-history-log-fix.php', 'scope' => 'global', 'id' => 765 ),
	array( 'file' => '084-26-ds-ppt-ai-review-before-generate-clone3-cap-slides-to-actual-count.php', 'scope' => 'global', 'id' => 933 ),
	array( 'file' => '085-21-ds-ppt-smart-template-matcher-live-admin-template-credit-cost-cost.php', 'scope' => 'global', 'id' => 767 ),
	array( 'file' => '087-9-ds-ai-studio-quick-icons-inline-popups-fix-dmcfmtts-modal-css-fix-sa.php', 'scope' => 'global', 'id' => 768 ),
	array( 'file' => '088-ds-credit-settings-template-cost-live-slide-bar-clone.php', 'scope' => 'global', 'id' => 769 ),
	/* V35: 257 must run before 089 - it defines ds_call_cloudflare() first (PC, then Cloudflare). */
	array( 'file' => '257-ds-local-pc-image-free-tier-cloudflare-fallback.php', 'scope' => 'global', 'id' => 1054 ),
	array( 'file' => '089-ds-ai-image-api-part-a-r2-storage-clone-clone-clone-admin-credit-setti.php', 'scope' => 'global', 'id' => 770 ),
	array( 'file' => '090-ds-mark-submitted-clone.php', 'scope' => 'global', 'id' => 771 ),
	array( 'file' => '091-ds-fix-link-100-ppt-templates-featured-image-download-file.php', 'scope' => 'global', 'id' => 772 ),
	array( 'file' => '092-ds-ai-image-rest-r2-storage-free-ip-cap-2-day-clone-clone-clone-clone.php', 'scope' => 'global', 'id' => 773 ),
	array( 'file' => '093-ds-ai-studio-images-save-to-my-creations-hide-expired-no-blank-slot-te.php', 'scope' => 'global', 'id' => 774 ),
	array( 'file' => '094-ds-marketplace-ai-auto-tagger-groq-vision.php', 'scope' => 'global', 'id' => 775 ),
	array( 'file' => '095-ds-site-header-dark-background.php', 'scope' => 'global', 'id' => 776 ),
	array( 'file' => '096-ds-pricing-page-dark-background.php', 'scope' => 'global', 'id' => 777 ),
	array( 'file' => '097-ds-home-page-dark-background.php', 'scope' => 'global', 'id' => 778 ),
	array( 'file' => '098-ds-header-greeting-text-fix-dark-pages.php', 'scope' => 'global', 'id' => 779 ),
	array( 'file' => '099-ds-customize-page-dark-theme.php', 'scope' => 'global', 'id' => 780 ),
	array( 'file' => '100-ds-blog-single-post-dark-theme-navigation.php', 'scope' => 'global', 'id' => 781 ),
	array( 'file' => '101-ds-contact-page-dark-theme.php', 'scope' => 'global', 'id' => 782 ),
	array( 'file' => '102-ds-global-typography-poppins.php', 'scope' => 'global', 'id' => 783 ),
	array( 'file' => '103-ds-ai-audio-api-part-a.php', 'scope' => 'global', 'id' => 784 ),
	array( 'file' => '104-ds-ai-audio-rest.php', 'scope' => 'global', 'id' => 785 ),
	array( 'file' => '105-ds-ai-audio-tab-injector.php', 'scope' => 'global', 'id' => 786 ),
	array( 'file' => '106-ds-ai-audio.php', 'scope' => 'global', 'id' => 787 ),
	array( 'file' => '107-ds-ai-audio-marketplace-submission.php', 'scope' => 'global', 'id' => 788 ),
	array( 'file' => '108-ds-marketplace-shortcode-04-28-clone.php', 'scope' => 'global', 'id' => 789 ),
	array( 'file' => '109-ds-bulk-storage-cleanup-admin-tool.php', 'scope' => 'global', 'id' => 790 ),
	array( 'file' => '110-ds-orphaned-r2-files-cleanup-admin-tool.php', 'scope' => 'global', 'id' => 791 ),
	array( 'file' => '111-ds-video-marketplace-watermark-upload-endpoint.php', 'scope' => 'global', 'id' => 792 ),
	array( 'file' => '112-ds-notifications-system-bell-icon-admin-composer.php', 'scope' => 'global', 'id' => 793 ),
	array( 'file' => '113-ds-footer-fix-remove-border-lines-unify-color-fix-mobile-alignment.php', 'scope' => 'global', 'id' => 794 ),
	array( 'file' => '114-ds-account-dropdown-clone-refund-cancel-buttons.php', 'scope' => 'global', 'id' => 795 ),
	array( 'file' => '115-ds-refund-system-24h-window.php', 'scope' => 'global', 'id' => 796 ),
	array( 'file' => '116-snippet-13-ds-ai-pricing-clone.php', 'scope' => 'global', 'id' => 797 ),
	array( 'file' => '117-ds-thank-you-page-dynamic.php', 'scope' => 'global', 'id' => 798 ),
	array( 'file' => '118-link.php', 'scope' => 'global', 'id' => 799 ),
	array( 'file' => '119-import-marketplace-images.php', 'scope' => 'global', 'id' => 800 ),
	array( 'file' => '120-ds-ai-video-api-part-b-kling-3-0-4k-ltx-2-3.php', 'scope' => 'global', 'id' => 801 ),
	array( 'file' => '121-ds-ai-video-r2-retention-clone-clone-hide-expired-no-blank-slot-temp-i.php', 'scope' => 'global', 'id' => 802 ),
	array( 'file' => '122-ds-ai-video-custom-pricing-kling-3-0-4k-ltx-2-3.php', 'scope' => 'global', 'id' => 803 ),
	array( 'file' => '123-ds-ai-video-rest-r2-storage-my-videos-load-more-pagination-expired-fil.php', 'scope' => 'global', 'id' => 804 ),
	array( 'file' => '124-ds-my-creations-hub-my-cap-fix-clone.php', 'scope' => 'global', 'id' => 805 ),
	array( 'file' => '126-ds-audio-resume-pending-after-refresh.php', 'scope' => 'global', 'id' => 806 ),
	array( 'file' => '127-ds-perf-wp-json-fetch-concurrency-limiter-503-mitigation.php', 'scope' => 'global', 'id' => 807 ),
	array( 'file' => '128-ds-free-tier-guard-daily-budget-rate-limit-clone-image-only.php', 'scope' => 'global', 'id' => 808 ),
	array( 'file' => '129-ds-free-image-limit-upgrade-popup-clone-light-theme-colors.php', 'scope' => 'global', 'id' => 809 ),
	array( 'file' => '130-review-rating-popup-with-improve-feedback-clone-moved-to-left-side.php', 'scope' => 'global', 'id' => 810 ),
	array( 'file' => '131-ds-suji-support-chat-clone-button-flush-in-corner.php', 'scope' => 'global', 'id' => 811 ),
	array( 'file' => '132-ds-ppt-background-autosave-no-re-save-when-unchanged.php', 'scope' => 'global', 'id' => 833 ),
	array( 'file' => '135-39-ds-ppt-browse-templates-smart-sort.php', 'scope' => 'global', 'id' => 812 ),
	array( 'file' => '136-25-ds-ppt-preserve-review-before-generate.php', 'scope' => 'global', 'id' => 813 ),
	array( 'file' => '137-24-ds-ppt-preserve-ai-rearrange-clone-credit-gated-instead-of-plan-gat.php', 'scope' => 'global', 'id' => 814 ),
	array( 'file' => '138-ds-ppt-card-layout-fix-auto-marketplace-image-editor-preview.php', 'scope' => 'global', 'id' => 820 ),
	array( 'file' => '139-ds-ai-voice-clone.php', 'scope' => 'global', 'id' => 815 ),
	array( 'file' => '140-ds-daily-report.php', 'scope' => 'global', 'id' => 816 ),
	array( 'file' => '141-ds-saved-items-card-actions.php', 'scope' => 'front-end', 'id' => 817 ),
	array( 'file' => '143-ds-ai-audio-outpaint-extend-audio.php', 'scope' => 'global', 'id' => 818 ),
	array( 'file' => '144-ds-ai-audio-40-60-studio-layout.php', 'scope' => 'global', 'id' => 819 ),
	array( 'file' => '145-snippet-32t-template-icon-injection.php', 'scope' => 'global', 'id' => 844 ),
	array( 'file' => '146-ds-user-location.php', 'scope' => 'global', 'id' => 855 ),
	array( 'file' => '147-ds-ai-presentation-browse-templates-url-rewrite.php', 'scope' => 'global', 'id' => 857 ),
	array( 'file' => '148-ds-powerpoint-add-in-bridge-office.php', 'scope' => 'global', 'id' => 862 ),
	array( 'file' => '149-ds-google-ads-conversion-subscription-purchase.php', 'scope' => 'front-end', 'id' => 860 ),
	array( 'file' => '150-ds-branded-account-emails.php', 'scope' => 'global', 'id' => 861 ),
	array( 'file' => '151-ds-template-engine-t00.php', 'scope' => 'global', 'id' => 952 ),
	array( 'file' => '152-ds-template-01-glassline.php', 'scope' => 'global', 'id' => 874 ),
	array( 'file' => '153-ds-template-02-cobalt-bold.php', 'scope' => 'global', 'id' => 875 ),
	array( 'file' => '154-ds-template-03-chrome-noir.php', 'scope' => 'global', 'id' => 876 ),
	array( 'file' => '155-ds-template-04-aqua-wave.php', 'scope' => 'global', 'id' => 877 ),
	array( 'file' => '156-ds-template-05-paper-proposal.php', 'scope' => 'global', 'id' => 878 ),
	array( 'file' => '157-ds-template-06-noir-gold.php', 'scope' => 'global', 'id' => 879 ),
	array( 'file' => '158-ds-template-07-violet.php', 'scope' => 'global', 'id' => 880 ),
	array( 'file' => '159-ds-template-08-blueprint.php', 'scope' => 'global', 'id' => 881 ),
	array( 'file' => '160-ds-template-09-neon.php', 'scope' => 'global', 'id' => 882 ),
	array( 'file' => '161-ds-template-10-bronze.php', 'scope' => 'global', 'id' => 883 ),
	array( 'file' => '162-ds-template-11-azure.php', 'scope' => 'global', 'id' => 884 ),
	array( 'file' => '163-ds-template-12-executive.php', 'scope' => 'global', 'id' => 885 ),
	array( 'file' => '164-ds-signup-bonus-credits.php', 'scope' => 'global', 'id' => 894 ),
	array( 'file' => '165-ds-charts.php', 'scope' => 'global', 'id' => 898 ),
	array( 'file' => '166-ds-diagrams.php', 'scope' => 'global', 'id' => 904 ),
	array( 'file' => '167-ds-referral-system-core-admin.php', 'scope' => 'global', 'id' => 905 ),
	array( 'file' => '168-ds-referral-system-my-referrals-tab-account-popup.php', 'scope' => 'front-end', 'id' => 906 ),
	array( 'file' => '169-ds-referral-system-home-page-banner.php', 'scope' => 'global', 'id' => 907 ),
	array( 'file' => '170-ds-share-preview-whatsapp-social-link-image.php', 'scope' => 'global', 'id' => 908 ),
	array( 'file' => '171-ds-referral-system-wallet-withdrawals.php', 'scope' => 'global', 'id' => 909 ),
	array( 'file' => '172-ds-doc-import.php', 'scope' => 'global', 'id' => 903 ),
	array( 'file' => '173-ds-add-slide-tile.php', 'scope' => 'global', 'id' => 910 ),
	array( 'file' => '174-ds-template-kit-2.php', 'scope' => 'global', 'id' => 955 ),
	array( 'file' => '175-ds-template-13-graphite-loop.php', 'scope' => 'global', 'id' => 912 ),
	array( 'file' => '176-ds-template-14-grid-paper-classroom.php', 'scope' => 'global', 'id' => 913 ),
	array( 'file' => '177-ds-template-15-spectrum-infographic.php', 'scope' => 'global', 'id' => 914 ),
	array( 'file' => '178-ds-template-16-navy-coral-flow.php', 'scope' => 'global', 'id' => 915 ),
	array( 'file' => '179-ds-template-17-isometric-slate.php', 'scope' => 'global', 'id' => 916 ),
	array( 'file' => '180-ds-template-18-lumen-gradient.php', 'scope' => 'global', 'id' => 917 ),
	array( 'file' => '181-ds-template-19-mint-lilac.php', 'scope' => 'global', 'id' => 918 ),
	array( 'file' => '182-ds-template-20-horizon-launch.php', 'scope' => 'global', 'id' => 919 ),
	array( 'file' => '183-ds-template-21-forest-campaign.php', 'scope' => 'global', 'id' => 920 ),
	array( 'file' => '184-ds-template-22-terracotta-teal.php', 'scope' => 'global', 'id' => 956 ),
	array( 'file' => '185-ds-template-23-mono-portfolio.php', 'scope' => 'global', 'id' => 922 ),
	array( 'file' => '186-ds-template-24-navy-proposal.php', 'scope' => 'global', 'id' => 923 ),
	array( 'file' => '187-ds-template-25-retro-grid-pop.php', 'scope' => 'global', 'id' => 924 ),
	array( 'file' => '188-ds-template-26-brutal-orange.php', 'scope' => 'global', 'id' => 925 ),
	array( 'file' => '189-ds-template-27-emerald-corporate.php', 'scope' => 'global', 'id' => 926 ),
	array( 'file' => '190-ds-template-28-violet-pitch.php', 'scope' => 'global', 'id' => 927 ),
	array( 'file' => '191-ds-template-29-neon-lime-glass.php', 'scope' => 'global', 'id' => 928 ),
	array( 'file' => '192-ds-template-preserve-mode-fix.php', 'scope' => 'global', 'id' => 930 ),
	array( 'file' => '193-ds-ppt-image-upload-from-device.php', 'scope' => 'front-end', 'id' => 943 ),
	array( 'file' => '194-ds-ai-presentation-studio-home-layout.php', 'scope' => 'front-end', 'id' => 960 ),
	array( 'file' => '195-ds-ppt-export-pptx-pdf-png-jpg.php', 'scope' => 'front-end', 'id' => 967 ),
	array( 'file' => '196-ds-ppt-free-plan.php', 'scope' => 'global', 'id' => 968 ),
	array( 'file' => '197-ds-template-text-fit-guard.php', 'scope' => 'global', 'id' => 972 ),
	array( 'file' => '198-ds-ppt-image-limit-upgrade-popup.php', 'scope' => 'front-end', 'id' => 973 ),
	array( 'file' => '199-ds-ppt-editor-mobile-tablet-layout.php', 'scope' => 'front-end', 'id' => 975 ),
	array( 'file' => '200-ds-ppt-editor-marketplace-picker-and-popup-fixes.php', 'scope' => 'front-end', 'id' => 976 ),
	array( 'file' => '201-ds-ppt-deck-style.php', 'scope' => 'global', 'id' => 979 ),
	array( 'file' => '202-ds-template-30-azure-prism.php', 'scope' => 'global', 'id' => 986 ),
	array( 'file' => '203-ds-template-31-opal-glass.php', 'scope' => 'global', 'id' => 987 ),
	array( 'file' => '204-ds-template-32-obsidian-glass.php', 'scope' => 'global', 'id' => 988 ),
	array( 'file' => '205-ds-template-33-circuit-tide.php', 'scope' => 'global', 'id' => 989 ),
	array( 'file' => '206-ds-template-34-halo-type.php', 'scope' => 'global', 'id' => 990 ),
	array( 'file' => '207-ds-template-35-sunset-glass.php', 'scope' => 'global', 'id' => 991 ),
	array( 'file' => '208-ds-template-36-frost-slate.php', 'scope' => 'global', 'id' => 992 ),
	array( 'file' => '209-ds-template-37-ember-noir.php', 'scope' => 'global', 'id' => 993 ),
	array( 'file' => '210-ds-template-38-linen-glass.php', 'scope' => 'global', 'id' => 994 ),
	array( 'file' => '211-ds-template-39-polar-night.php', 'scope' => 'global', 'id' => 995 ),
	array( 'file' => '212-ds-template-40-garnet-glass.php', 'scope' => 'global', 'id' => 996 ),
	array( 'file' => '213-ds-template-41-daybreak-glass.php', 'scope' => 'global', 'id' => 997 ),
	array( 'file' => '214-ds-template-42-citrus-pop.php', 'scope' => 'global', 'id' => 998 ),
	array( 'file' => '215-ds-template-43-mono-ink.php', 'scope' => 'global', 'id' => 999 ),
	array( 'file' => '216-ds-template-44-rosewater-glass.php', 'scope' => 'global', 'id' => 1000 ),
	array( 'file' => '217-ds-template-45-slate-brief.php', 'scope' => 'global', 'id' => 1001 ),
	array( 'file' => '218-ds-template-46-tangerine-split.php', 'scope' => 'global', 'id' => 1002 ),
	array( 'file' => '219-ds-template-47-hex-forge.php', 'scope' => 'global', 'id' => 1003 ),
	array( 'file' => '220-ds-template-48-stack-glass.php', 'scope' => 'global', 'id' => 1004 ),
	array( 'file' => '221-ds-template-hover-preview.php', 'scope' => 'global', 'id' => 1005 ),
	array( 'file' => '222-ds-template-point-overflow.php', 'scope' => 'global', 'id' => 1006 ),
	array( 'file' => '223-ds-template-second-picture.php', 'scope' => 'global', 'id' => 1008 ),
	array( 'file' => '224-ds-template-closing-contacts-fix.php', 'scope' => 'global', 'id' => 1009 ),
	array( 'file' => '225-ds-ppt-save-limit-popup.php', 'scope' => 'global', 'id' => 1012 ),
	array( 'file' => '226-ds-ppt-real-cover-thumbnails.php', 'scope' => 'global', 'id' => 1013 ),
	array( 'file' => '227-ds-my-creations-hide-downloaded-presentations.php', 'scope' => 'global', 'id' => 1019 ),
	array( 'file' => '228-ds-doc-import-slide-markers.php', 'scope' => 'global', 'id' => 1021 ),
	array( 'file' => '229-ds-template-49-chevron-teal.php', 'scope' => 'global', 'id' => 1022 ),
	array( 'file' => '230-ds-template-50-copper-diamond.php', 'scope' => 'global', 'id' => 1023 ),
	array( 'file' => '231-ds-template-51-saffron-study.php', 'scope' => 'global', 'id' => 1024 ),
	array( 'file' => '232-ds-template-52-care-blue.php', 'scope' => 'global', 'id' => 1025 ),
	array( 'file' => '233-ds-template-53-silk-burgundy.php', 'scope' => 'global', 'id' => 1026 ),
	array( 'file' => '234-ds-template-54-ribbon-pine.php', 'scope' => 'global', 'id' => 1027 ),
	array( 'file' => '235-ds-template-55-pulse-aqua.php', 'scope' => 'global', 'id' => 1028 ),
	array( 'file' => '236-ds-template-png-objects.php', 'scope' => 'global', 'id' => 1030 ),
	array( 'file' => '237-ds-template-frosted-glass.php', 'scope' => 'global', 'id' => 1032 ),
	array( 'file' => '238-ds-template-glass-kit.php', 'scope' => 'global', 'id' => 1034 ),
	array( 'file' => '239-ds-template-56-frost-orbit.php', 'scope' => 'global', 'id' => 1033 ),
	array( 'file' => '240-ds-template-57-glass-tower.php', 'scope' => 'global', 'id' => 1035 ),
	array( 'file' => '241-ds-template-58-glass-beam.php', 'scope' => 'global', 'id' => 1036 ),
	array( 'file' => '242-ds-template-59-glass-arc.php', 'scope' => 'global', 'id' => 1037 ),
	array( 'file' => '243-ds-template-60-glass-mesh.php', 'scope' => 'global', 'id' => 1038 ),
	array( 'file' => '244-ds-template-61-glass-wave.php', 'scope' => 'global', 'id' => 1039 ),
	array( 'file' => '245-ds-template-62-glass-slat.php', 'scope' => 'global', 'id' => 1040 ),
	array( 'file' => '246-ds-template-63-glass-flow.php', 'scope' => 'global', 'id' => 1041 ),
	array( 'file' => '247-ds-template-64-glass-flute.php', 'scope' => 'global', 'id' => 1042 ),
	array( 'file' => '248-ds-template-21-forest-road-fix-cover-closing.php', 'scope' => 'global', 'id' => 1045 ),
	array( 'file' => '249-ds-ppt-image-clarity-no-second-compression-on-save-download.php', 'scope' => 'global', 'id' => 1046 ),
	array( 'file' => '250-ds-ppt-auto-pictures-sharp-1920px-marketplace-image-not-counted.php', 'scope' => 'global', 'id' => 1047 ),
	array( 'file' => '251-ds-template-speed-no-freeze-after-a-theme-template-pick-home-editor.php', 'scope' => 'global', 'id' => 1048 ),
	array( 'file' => '252-ds-template-scenario-layouts-text-photo-positions-for-every-template.php', 'scope' => 'global', 'id' => 1049 ),
	array( 'file' => '253-ds-template-cycle-layout-each-text-beside-its-own-icon.php', 'scope' => 'global', 'id' => 1050 ),
	array( 'file' => '254-ds-template-ghost-numerals-big-numbers-behind-headings-stay-faint.php', 'scope' => 'global', 'id' => 1051 ),
	array( 'file' => '255-ds-powerpoint-add-in-no-pdf-in-the-download-menu.php', 'scope' => 'global', 'id' => 1052 ),
	array( 'file' => '256-ds-template-chart-background-theme-colour-round-sharp-off.php', 'scope' => 'global', 'id' => 1054 ),
	array( 'file' => '259-ds-pick-a-design-full-deck-download-for-design-templates-charts-diagrams.php', 'scope' => 'global', 'id' => 1057 ),
	array( 'file' => '260-ds-ai-audio-voice-preview-play-button-for-every-text-to-speech-voice.php', 'scope' => 'global', 'id' => 1058 ),
	array( 'file' => '258-ds-ppt-free-plan-daily-reset.php', 'scope' => 'global', 'id' => 1055 ),
	array( 'file' => '261-ds-privacy-policy-page-ds-privacy.php', 'scope' => 'global', 'id' => 1061 ),
	array( 'file' => '262-ds-social-login-continue-with-google-microsoft.php', 'scope' => 'global', 'id' => 1067 ),
	array( 'file' => '263-ds-delete-my-account-24-hour-delay.php', 'scope' => 'global', 'id' => 1069 ),
	array( 'file' => '264-ds-ppt-layout-choices-keep-them-after-save-and-re-open.php', 'scope' => 'global', 'id' => 1080 ),
	array( 'file' => '265-ds-template-element-editor-layers-positions-element-toolbar.php', 'scope' => 'global', 'id' => 1078 ),
	array( 'file' => '266-ds-template-65-classic-editable-replaces-the-classic-card.php', 'scope' => 'global', 'id' => 1094 ),
	array( 'file' => '267-ds-fix-sample-format-button-fit-responsive.php', 'scope' => 'front-end', 'id' => 1097 ),
	array( 'file' => '268-ds-template-kit-3-shared-for-t66-t73.php', 'scope' => 'global', 'id' => 1098 ),
	array( 'file' => '269-ds-template-66-hex-clinic.php', 'scope' => 'global', 'id' => 1099 ),
	array( 'file' => '270-ds-template-67-crimson-tile.php', 'scope' => 'global', 'id' => 1100 ),
	array( 'file' => '271-ds-template-68-rhombus-proposal.php', 'scope' => 'global', 'id' => 1101 ),
	array( 'file' => '272-ds-template-69-finance-prism.php', 'scope' => 'global', 'id' => 1102 ),
	array( 'file' => '273-ds-template-70-night-diamond.php', 'scope' => 'global', 'id' => 1103 ),
	array( 'file' => '274-ds-template-71-profile-block.php', 'scope' => 'global', 'id' => 1104 ),
	array( 'file' => '275-ds-template-72-capsule-corporate.php', 'scope' => 'global', 'id' => 1105 ),
	array( 'file' => '276-ds-template-73-tide-report.php', 'scope' => 'global', 'id' => 1106 ),
	array( 'file' => '277-ds-template-kit-4-shared-for-t74-t89.php', 'scope' => 'global', 'id' => 1107 ),
	array( 'file' => '278-ds-template-74-metro-line.php', 'scope' => 'global', 'id' => 1108 ),
	array( 'file' => '279-ds-template-75-bento-board.php', 'scope' => 'global', 'id' => 1109 ),
	array( 'file' => '280-ds-template-76-bauhaus-studio.php', 'scope' => 'global', 'id' => 1110 ),
	array( 'file' => '281-ds-template-77-contour.php', 'scope' => 'global', 'id' => 1111 ),
	array( 'file' => '282-ds-template-78-pinstripe.php', 'scope' => 'global', 'id' => 1112 ),
	array( 'file' => '283-ds-template-79-ticker.php', 'scope' => 'global', 'id' => 1113 ),
	array( 'file' => '284-ds-template-80-viewfinder.php', 'scope' => 'global', 'id' => 1114 ),
	array( 'file' => '285-ds-template-81-duotone-pop.php', 'scope' => 'global', 'id' => 1115 ),
	array( 'file' => '286-ds-template-82-caliper.php', 'scope' => 'global', 'id' => 1116 ),
	array( 'file' => '287-ds-template-83-colonnade.php', 'scope' => 'global', 'id' => 1117 ),
	array( 'file' => '288-ds-template-84-pixel-mosaic.php', 'scope' => 'global', 'id' => 1118 ),
	array( 'file' => '289-ds-template-85-botanical-line.php', 'scope' => 'global', 'id' => 1119 ),
	array( 'file' => '290-ds-template-86-halftone-signal.php', 'scope' => 'global', 'id' => 1120 ),
	array( 'file' => '291-ds-template-87-vault-frames.php', 'scope' => 'global', 'id' => 1121 ),
	array( 'file' => '292-ds-template-88-swiss-index.php', 'scope' => 'global', 'id' => 1122 ),
	array( 'file' => '293-ds-template-89-compass-point.php', 'scope' => 'global', 'id' => 1123 ),
	array( 'file' => '294-ds-template-row-fit-point-explanations-t66-t89.php', 'scope' => 'global', 'id' => 1124 ),
	array( 'file' => '295-ds-edit-screen-side-panels-large-screens.php', 'scope' => 'global', 'id' => 1125 ),
	array( 'file' => '296-ds-editor-icons-library-iconify-search.php', 'scope' => 'global', 'id' => 1126 ),
	array( 'file' => '297-ds-export-fix-pdf-png-jpg-downloads.php', 'scope' => 'front-end', 'id' => 1127 ),
	array( 'file' => '298-ds-editor-extras-popups-lock-resize-slide-copy-delete-hide.php', 'scope' => 'global', 'id' => 1128 ),
	array( 'file' => '299-ds-editor-popup-dialogs-instead-of-browser-boxes.php', 'scope' => 'front-end', 'id' => 1129 ),
	array( 'file' => '300-ds-mobile-footer-fix-no-blank-strip-on-phones.php', 'scope' => 'front-end', 'id' => 1130 ),
	array( 'file' => '301-ds-template-90-index-tabs.php', 'scope' => 'global', 'id' => 1132 ),
	array( 'file' => '302-ds-template-91-stair-step.php', 'scope' => 'global', 'id' => 1133 ),
	array( 'file' => '303-ds-template-92-sunburst.php', 'scope' => 'global', 'id' => 1134 ),
	array( 'file' => '304-ds-template-93-barcode.php', 'scope' => 'global', 'id' => 1135 ),
	array( 'file' => '305-ds-template-94-weave.php', 'scope' => 'global', 'id' => 1136 ),
	array( 'file' => '306-ds-template-95-meridian.php', 'scope' => 'global', 'id' => 1137 ),
	array( 'file' => '307-ds-template-96-tartan.php', 'scope' => 'global', 'id' => 1138 ),
	array( 'file' => '308-ds-template-97-jigsaw.php', 'scope' => 'global', 'id' => 1139 ),
	array( 'file' => '309-ds-template-98-sector.php', 'scope' => 'global', 'id' => 1140 ),
	array( 'file' => '310-ds-template-99-terrazzo.php', 'scope' => 'global', 'id' => 1141 ),
	array( 'file' => '311-ds-template-101-string-art.php', 'scope' => 'global', 'id' => 1143 ),
	array( 'file' => '312-ds-template-102-sawtooth.php', 'scope' => 'global', 'id' => 1144 ),
	array( 'file' => '313-ds-template-103-scallop.php', 'scope' => 'global', 'id' => 1145 ),
	array( 'file' => '314-ds-template-104-velocity.php', 'scope' => 'global', 'id' => 1146 ),
	array( 'file' => '315-ds-template-105-venn.php', 'scope' => 'global', 'id' => 1147 ),
	array( 'file' => '316-ds-template-106-strata.php', 'scope' => 'global', 'id' => 1148 ),
	array( 'file' => '317-ds-template-107-guilloche.php', 'scope' => 'global', 'id' => 1149 ),
	array( 'file' => '318-ds-template-108-constellation.php', 'scope' => 'global', 'id' => 1150 ),
	array( 'file' => '319-ds-template-109-equalizer.php', 'scope' => 'global', 'id' => 1151 ),
	array( 'file' => '320-ds-template-110-helix.php', 'scope' => 'global', 'id' => 1152 ),
	array( 'file' => '321-ds-template-111-low-poly.php', 'scope' => 'global', 'id' => 1153 ),
	array( 'file' => '322-ds-template-112-golden-section.php', 'scope' => 'global', 'id' => 1154 ),
	array( 'file' => '323-ds-template-113-library-spine.php', 'scope' => 'global', 'id' => 1155 ),
	array( 'file' => '324-ds-template-114-perforation.php', 'scope' => 'global', 'id' => 1156 ),
	array( 'file' => '325-ds-template-115-labyrinth.php', 'scope' => 'global', 'id' => 1157 ),
	array( 'file' => '326-ds-template-116-elbow.php', 'scope' => 'global', 'id' => 1158 ),
	array( 'file' => '327-ds-template-117-kanban.php', 'scope' => 'global', 'id' => 1159 ),
	array( 'file' => '328-ds-template-118-planner.php', 'scope' => 'global', 'id' => 1160 ),
	array( 'file' => '329-ds-template-119-cargo.php', 'scope' => 'global', 'id' => 1161 ),
	array( 'file' => '330-ds-template-120-radar.php', 'scope' => 'global', 'id' => 1162 ),
	array( 'file' => '331-ds-template-121-abacus.php', 'scope' => 'global', 'id' => 1163 ),
	array( 'file' => '332-ds-template-122-tangram.php', 'scope' => 'global', 'id' => 1164 ),
	array( 'file' => '333-ds-template-123-crest.php', 'scope' => 'global', 'id' => 1165 ),
	array( 'file' => '334-ds-template-124-dash-code.php', 'scope' => 'global', 'id' => 1166 ),
	array( 'file' => '335-ds-template-125-coin-stack.php', 'scope' => 'global', 'id' => 1167 ),
	array( 'file' => '336-ds-template-126-herringbone.php', 'scope' => 'global', 'id' => 1168 ),
	array( 'file' => '337-ds-template-127-link-chain.php', 'scope' => 'global', 'id' => 1169 ),
	array( 'file' => '338-ds-template-128-flight-path.php', 'scope' => 'global', 'id' => 1170 ),
	array( 'file' => '339-ds-template-129-truss.php', 'scope' => 'global', 'id' => 1171 ),
	array( 'file' => '340-ds-template-100-masonry.php', 'scope' => 'global', 'id' => 1172 ),
	array( 'file' => '341-ds-template-kit-5-shared-for-t90-t129.php', 'scope' => 'global', 'id' => 1173 ),
	array( 'file' => '342-ds-picker-popup-explore-themes-view-all-open-in-a-popup-click-then-sel.php', 'scope' => 'front-end', 'id' => 1176 ),
	array( 'file' => '343-ds-deck-structure-a-storyline-for-every-presentation-type-pitch-compan.php', 'scope' => 'global', 'id' => 1178 ),
	array( 'file' => '344-ds-card-sync-card-fill-outline-and-shadow-act-as-one-round-corners-sha.php', 'scope' => 'front-end', 'id' => 1179 ),
	array( 'file' => '345-ds-edit-add-points-button-above-the-slide-in-the-editor.php', 'scope' => 'front-end', 'id' => 1180 ),
	array( 'file' => '346-ds-share-pictures-a-shared-link-shows-the-slides-exactly-as-the-editor.php', 'scope' => 'global', 'id' => 1181 ),
	array( 'file' => '347-ds-free-plan-switch-free-presentations-and-free-images-limited-no-limi.php', 'scope' => 'global', 'id' => 1182 ),
	array( 'file' => '348-ds-ppt-preserve-my-text-use-ai-switch.php', 'scope' => 'global', 'id' => 1185 ),	array( 'file' => '349-ds-template-65r-4-3-and-9-16-for-every-design-template-ratio-layouts.php', 'scope' => 'global', 'id' => 1186 ),
	array( 'file' => '350-ds-image-holder-transparent-png-stays-transparent.php', 'scope' => 'global', 'id' => 1187 ),
	array( 'file' => '351-ds-template-preview-photos-real-marketplace-tech-photos.php', 'scope' => 'global', 'id' => 1188 ),
	array( 'file' => '352-ds-ai-instruction-guard-ai-follows-prompt-and-directives.php', 'scope' => 'global', 'id' => 1190 ),
	array( 'file' => '353-ds-vector-export-eps-svg-download-editable-vector-live-text.php', 'scope' => 'global', 'id' => 1191 ),
	/* V41: 12 new design templates (T130-T141), editor loading screen, template photo shapes, user activity report, Free / Pro in one click */
	array( 'file' => '354-ds-template-130-aurora-deck.php', 'scope' => 'global', 'id' => 1216 ),
	array( 'file' => '355-ds-template-131-blob-collage.php', 'scope' => 'global', 'id' => 1217 ),
	array( 'file' => '356-ds-template-132-arc-profile.php', 'scope' => 'global', 'id' => 1218 ),
	array( 'file' => '357-ds-template-133-chapter-blaze.php', 'scope' => 'global', 'id' => 1219 ),
	array( 'file' => '358-ds-template-134-leaf-lens.php', 'scope' => 'global', 'id' => 1220 ),
	array( 'file' => '359-ds-template-135-spark-ideas.php', 'scope' => 'global', 'id' => 1221 ),
	array( 'file' => '360-ds-template-136-corner-shards.php', 'scope' => 'global', 'id' => 1222 ),
	array( 'file' => '361-ds-template-137-offset-frame.php', 'scope' => 'global', 'id' => 1223 ),
	array( 'file' => '362-ds-template-138-nav-rail.php', 'scope' => 'global', 'id' => 1224 ),
	array( 'file' => '363-ds-template-139-sage-tiles.php', 'scope' => 'global', 'id' => 1225 ),
	array( 'file' => '364-ds-template-140-plum-ledger.php', 'scope' => 'global', 'id' => 1226 ),
	array( 'file' => '365-ds-template-141-cloud-nine.php', 'scope' => 'global', 'id' => 1227 ),
	array( 'file' => '366-ds-editor-loading-screen-your-presentation-is-loading-until-pictures-a.php', 'scope' => 'global', 'id' => 1232 ),
	array( 'file' => '367-ds-template-photo-shapes-template-photos-become-real-pictures-cut-into.php', 'scope' => 'global', 'id' => 1237 ),
	array( 'file' => '368-ds-user-activity-report-all-users-by-period-downloads-ai-images-videos.php', 'scope' => 'global', 'id' => 1244 ),
	array( 'file' => '369-ds-downloads-free-pro-in-one-click-column-bulk-make-free-make-pro-sele.php', 'scope' => 'admin', 'id' => 1246 ),
	array( 'file' => '370-ds-office-sign-in-window-fixes.php', 'scope' => 'global', 'id' => 1250 ),
	/* V42: AI Document (engine and backend before the page; the Word add-in needs both) */
	array( 'file' => '371-ds-ai-document-backend.php', 'scope' => 'global', 'id' => 1256 ),
	array( 'file' => '372-ds-ai-document-engine.php', 'scope' => 'global', 'id' => 1257 ),
	array( 'file' => '373-ds-ai-document-page-editor.php', 'scope' => 'global', 'id' => 1258 ),
	array( 'file' => '374-ds-word-add-in.php', 'scope' => 'global', 'id' => 1259 ),
);

/*
 * WHY THIS IS DEFERRED (fixes the "critical error" on activation)
 * --------------------------------------------------------------
 * Code Snippets does not evaluate snippet bodies while plugins are loading —
 * it runs them on the `plugins_loaded` hook. That difference is fatal here.
 * Pluggable functions live in wp-includes/pluggable.php, which WordPress loads
 * AFTER the plugin loop, so during plugin load wp_get_current_user() does not
 * exist yet — and neither does current_user_can(), which calls it.
 *
 * includes/041-fix-email-verification-snippet.php calls current_user_can() at
 * its top level (line 114). Requiring that file at plugin-load time therefore
 * threw "Call to undefined function wp_get_current_user()" on every request,
 * taking down the front end and wp-admin together with no way back in.
 *
 * The requires now run on `plugins_loaded` priority 1 — the same point in the
 * request Code Snippets used, so every snippet sees exactly the environment it
 * was written against. Snippet bodies execute in function scope, which is also
 * what Code Snippets' eval() gave them.
 */
function dsv42_load_snippets() {

	// Recovery switch. If this plugin ever takes the site down again, add
	//     define( 'DSV42_DISABLE', true );
	// to wp-config.php to get back in without touching the filesystem.
	// Older DSV40_DISABLE / DSV39_DISABLE / DSV38_DISABLE / DSV37_DISABLE / DSV36_DISABLE / DSV35_DISABLE / DSV34_DISABLE / DSV33_DISABLE / DSV32_DISABLE / DSV31_DISABLE / DSV30_DISABLE / DSV29_DISABLE / DSV28_DISABLE / DSV27_DISABLE / DSV26_DISABLE / DSV25_DISABLE / DSV24_DISABLE / DSV23_DISABLE / DSV20_DISABLE switches are still honoured.
	if ( defined( 'DSV42_DISABLE' ) && DSV42_DISABLE ) {
		return;
	}
	if ( defined( 'DSV41_DISABLE' ) && DSV41_DISABLE ) {
		return;
	}
	if ( defined( 'DSV40_DISABLE' ) && DSV40_DISABLE ) {
		return;
	}
	if ( defined( 'DSV39_DISABLE' ) && DSV39_DISABLE ) {
		return;
	}
	if ( defined( 'DSV38_DISABLE' ) && DSV38_DISABLE ) {
		return;
	}
	if ( defined( 'DSV37_DISABLE' ) && DSV37_DISABLE ) {
		return;
	}
	if ( defined( 'DSV36_DISABLE' ) && DSV36_DISABLE ) {
		return;
	}
	if ( defined( 'DSV35_DISABLE' ) && DSV35_DISABLE ) {
		return;
	}
	if ( defined( 'DSV34_DISABLE' ) && DSV34_DISABLE ) {
		return;
	}
	if ( defined( 'DSV33_DISABLE' ) && DSV33_DISABLE ) {
		return;
	}
	if ( defined( 'DSV32_DISABLE' ) && DSV32_DISABLE ) {
		return;
	}
	if ( defined( 'DSV31_DISABLE' ) && DSV31_DISABLE ) {
		return;
	}
	if ( defined( 'DSV30_DISABLE' ) && DSV30_DISABLE ) {
		return;
	}
	if ( defined( 'DSV29_DISABLE' ) && DSV29_DISABLE ) {
		return;
	}
	if ( defined( 'DSV28_DISABLE' ) && DSV28_DISABLE ) {
		return;
	}
	if ( defined( 'DSV27_DISABLE' ) && DSV27_DISABLE ) {
		return;
	}
	if ( defined( 'DSV26_DISABLE' ) && DSV26_DISABLE ) {
		return;
	}
	if ( defined( 'DSV25_DISABLE' ) && DSV25_DISABLE ) {
		return;
	}
	if ( defined( 'DSV24_DISABLE' ) && DSV24_DISABLE ) {
		return;
	}
	if ( defined( 'DSV23_DISABLE' ) && DSV23_DISABLE ) {
		return;
	}
	if ( defined( 'DSV20_DISABLE' ) && DSV20_DISABLE ) {
		return;
	}

	global $dsv42_snippets;

	if ( ! isset( $GLOBALS['dsv42_failures'] ) ) {
		$GLOBALS['dsv42_failures'] = array();
	}

	foreach ( (array) $dsv42_snippets as $dsv42_snippet ) {

		if ( 'front-end' === $dsv42_snippet['scope'] && is_admin() ) {
			continue;
		}
		if ( 'admin' === $dsv42_snippet['scope'] && ! is_admin() ) {
			continue;
		}

		$dsv42_file = DSV42_PATH . 'includes/' . $dsv42_snippet['file'];

		if ( ! is_readable( $dsv42_file ) ) {
			error_log( 'DSV39: missing snippet file ' . $dsv42_snippet['file'] );
			$GLOBALS['dsv42_failures'][] = array(
				'file'  => $dsv42_snippet['file'],
				'msg'   => 'file missing or not readable',
				'where' => $dsv42_file,
			);
			continue;
		}

		/*
		 * One broken snippet should cost one feature, not the whole site. In
		 * PHP 7+ "Call to undefined function" throws Error, so this catches it,
		 * logs which file failed, and carries on with the rest.
		 */
		try {
			require_once $dsv42_file;
		} catch ( Throwable $dsv42_e ) {
			error_log( sprintf(
				'DSV39: snippet %s failed to load - %s in %s:%d',
				$dsv42_snippet['file'],
				$dsv42_e->getMessage(),
				$dsv42_e->getFile(),
				$dsv42_e->getLine()
			) );
			$GLOBALS['dsv42_failures'][] = array(
				'file'  => $dsv42_snippet['file'],
				'msg'   => $dsv42_e->getMessage(),
				'where' => $dsv42_e->getFile() . ':' . $dsv42_e->getLine(),
			);
		}
	}
}
add_action( 'plugins_loaded', 'dsv42_load_snippets', 1 );

/* V34+: serve the big inline scripts as cached files (see script-cache.php). Not loaded when the plugin is switched off. */
if ( ! ( defined( 'DSV42_DISABLE' ) && DSV42_DISABLE ) && ! ( defined( 'DSV39_DISABLE' ) && DSV39_DISABLE ) && ! ( defined( 'DSV38_DISABLE' ) && DSV38_DISABLE ) && ! ( defined( 'DSV37_DISABLE' ) && DSV37_DISABLE ) && ! ( defined( 'DSV36_DISABLE' ) && DSV36_DISABLE ) && ! ( defined( 'DSV35_DISABLE' ) && DSV35_DISABLE ) && ! ( defined( 'DSV34_DISABLE' ) && DSV34_DISABLE ) ) {
	require_once DSV42_PATH . 'script-cache.php';
}

/**
 * Admin notice naming any snippet that failed to load this request.
 *
 * The loader deliberately swallows a broken snippet so one feature dies instead
 * of the site. Without this notice that failure is invisible unless you can read
 * the PHP error log, which on managed hosting you often cannot.
 */
add_action( 'admin_notices', function () {

	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	$dsv42_failed = isset( $GLOBALS['dsv42_failures'] ) ? (array) $GLOBALS['dsv42_failures'] : array();

	if ( ! $dsv42_failed ) {
		return;
	}

	echo '<div class="notice notice-error"><p><strong>DesignStager V41:</strong> '
		. esc_html( sprintf( '%d snippet(s) failed to load and were skipped.', count( $dsv42_failed ) ) )
		. ' The rest of the plugin is running normally.</p><ul style="margin-left:1.5em;list-style:disc">';

	foreach ( $dsv42_failed as $dsv42_f ) {
		echo '<li><code>' . esc_html( $dsv42_f['file'] ) . '</code> &mdash; '
			. esc_html( $dsv42_f['msg'] )
			. ' <em>(' . esc_html( $dsv42_f['where'] ) . ')</em></li>';
	}

	echo '</ul></div>';
} );

/**
 * Admin notice if Code Snippets is still running DesignStager snippets, since
 * having both active is the one way this plugin can take a site down.
 */
add_action( 'admin_notices', function () {

	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	if ( ! function_exists( 'code_snippets' ) ) {
		return;
	}

	echo '<div class="notice notice-warning"><p><strong>DesignStager V41</strong> is active and the Code Snippets plugin is also installed. '
		. 'If any DesignStager snippet is still enabled there, the same functions are declared twice and the site will fatal-error. '
		. 'Deactivate the DesignStager snippets in Code Snippets.</p></div>';
} );
