=== Designstager-V42-AI Document (designed Word documents, 784 designs) + Word add-in ===
Requires PHP: 7.4
Stable tag: 42.0.2

All active DesignStager Code Snippets packaged as a single plugin.

V42 is V41 (41.5.0) with 1 include updated (353) and 4 added (371-374).
Deactivate V41 before activating V42 (never both).

NEW IN 42.0.2
 - includes/022 (fixed): a payment can only be counted once, and the credits or
   plan it gives come from Razorpay's record of the order, not from the browser.
 - includes/093 (fixed): two leftover debug addresses could be used by anyone to change\nthe database; they now need an admin login.
 - includes/001 (fixed): two requests at the same moment can no longer both spend the\nsame credits.
 - includes/072 080 082 085 092 104 123 139 143 259 (fixed): AI images, video, audio, voice clone, extend audio and\n   template / design downloads no longer hand over the result when the credits could\n   not be taken (several requests started at once used to get it free).

NEW IN 42.0.1
 - includes/058 (fixed): the creator portal no longer gives a fatal error to
   visitors who are not signed in.
 - includes/093 (fixed): no "dsSTab is not defined" error on AI Images.
 - includes/108 (fixed): the marketplace page no longer crashes if the download
   categories cannot be read.
 - includes/124 (fixed): no PHP warning on My Creations for users with no images.

NEW IN 42.0.0
 - includes/371-373 (new): AI Document. Write with AI or use your own text, check
   the text in a review popup, then get a designed document and edit it in an
   editor laid out like the AI Presentation editor (layers, shapes, icons, logo,
   pictures, crop, zoom and pan, copy / clone, lockable panels). 784 designs in 8
   categories, including 2-sided flyers, tri-fold brochures, posters, social media
   posts, infographics, business cards, invitations, menus, certificates, tickets
   and folded A5 cards. Page sizes follow the document type (A4, Letter, A3, A5,
   Tabloid, business card, 1080 x 1080 / 1080 x 1350 / story). Downloads: Word,
   PDF, PNG, JPG, TIFF (choose 96-1200 dpi) and EPS + SVG. Text colours are
   checked for contrast, so text stays readable on every design. The AI uses
   [placeholders] instead of inventing names, dates or numbers.
   The AI Document page (/ai-document/, shortcode [ds_ai_document]) is created
   the first time an admin opens wp-admin after activation.
 - includes/374 (new): DesignStager Word add-in (task pane ?ds_word=taskpane,
   Settings > DesignStager Word > Download manifest.xml).
 - includes/353 (updated): EPS + SVG download also works on the AI Document page.

NEW IN 41.5.0
- Privacy Policy page ([ds_privacy]): the PowerPoint add-in section says how sign-in is kept and that nothing is sold in the add-in; the AI section names the AI partners and says creations are not used for AI training. Last updated 17 October 2026.

NEW IN 41.4.0
 - includes/148 (updated): Safari - the AI Suites tile pictures fill the whole
   tile (they left a blank strip on the right).

NEW IN 41.3.0
 - includes/148 (updated): Safari no longer blocks the large PowerPoint window as a
   pop-up when it opens without a click (PowerPoint asks "Allow" first).
 - includes/370 (new): signed-in users skip the login form in the add-in; no
   "Skip to content" box when the PowerPoint window opens.

NEW IN 41.2.0
 - includes/196 (updated): Settings > Free Presentations - tick the designs that
   are free from a list of every template (search, select all, clear, show free
   only); new templates appear in the list by themselves.

NEW IN 41.1.0
 - includes/265 (updated): Crop for pictures inside shapes - drag the picture to
   move it, scroll to zoom, the whole picture shows faintly while cropping;
   Crop picture panel with sliders, Reset and Done.

NEW IN 41.0.0
 - AI presentations are planned for their real type: investor pitch, sales pitch,
   company profile, report, training, lesson, marketing, strategy, research or a
   general topic. Slide types are picked by content (no fixed stats / timeline /
   quote slides), no invented statistics, and charts only from numbers the user
   gave (includes/151, 165, 343).
 - 12 new design templates T130-T141: Aurora Deck, Blob Collage, Arc Profile,
   Chapter Blaze, Leaf Lens, Spark Ideas, Corner Shards, Offset Frame, Nav Rail,
   Sage Tiles, Plum Ledger, Cloud Nine (includes/354-365).
 - Template photos are real pictures cut into the shape, in every template
   (includes/367); Change image on picture shapes with a "Loading your image..."
   spinner, Draw and Edit points, smaller selection handles on small objects
   (includes/265).
 - "Your presentation is loading..." screen; editing waits until it is ready
   (includes/366).
 - DS User Activity: all-users report by Today / Yesterday / This week / This
   month / All time with credits, free / pro downloads, AI images, videos, audio,
   PPTs generated and downloaded; sort by new signups, name or active users;
   free / paid filter (includes/368, 035). Free downloads by unlimited plans are
   now counted (includes/067).
 - Downloads list: Free / Pro badge column (click to switch), Make Free / Make
   Pro bulk actions, select all items on every page, Free / Pro filter
   (includes/369, admin only).
 - New and changed scripts stay inline until they are added to the script cache;
   nothing else changes.

NEW IN 40.0.10
 - includes/009 (updated): signing in from the PowerPoint side panel returns to the add-in start page.

NEW IN 40.0.10
 - includes/023 (updated): signing in from the PowerPoint side panel returns to the add-in start page.
 - includes/148 (updated): the drag tip shows again after the panel changes page (for example after sign-in).

NEW IN 40.0.9
 - includes/151 (updated): 9:16 chart slides - the chart takes only the
   height it needs and the insights stack under it (no big blank space).

NEW IN 40.0.8
 - includes/148 (updated): Review Your Slides scrolls in the narrow side
   panel and its Generate / Back buttons always show.

NEW IN 40.0.7
 - includes/114 (updated): closing My account inside PowerPoint goes to the
   start page, never to the add-in panel page.
 - includes/148 (updated): the add-in panel page opened outside PowerPoint
   opens tools in the same window.

NEW IN 40.0.6
 - includes/148 (updated): on PowerPoint for the web, tools open in the
   large PowerPoint window (the web keeps the sign-in out of the side panel).

NEW IN 40.0.5
 - includes/148 (updated): side panel editor buttons sized properly (no
   zoom), Insert menu never cut off and no "Upgrade" label in PowerPoint,
   free-plan pop-ups scroll so their buttons always show.

NEW IN 40.0.4
 - includes/080, 148 (updated): Update with AI / Preserve My Text / Browse
   Templates cards no longer squeezed into thin strips (website and
   PowerPoint); inside PowerPoint they stack, "Paid plans" under the text.

NEW IN 40.0.3
 - includes/148 (updated): editor buttons in the side panel are smaller.

NEW IN 40.0.2
 - includes/148 (updated): side panel editor - Layouts open from a LAYOUTS
   button, smaller slide thumbnails. Insert asks to change the
   presentation's slide size (e.g. 16:9 to 9:16) when the deck differs.

NEW IN 40.0.1
 - includes/148 (updated): side panel - "Continue with Google / Microsoft"
   signs in through the large window; Update with AI / Preserve My Text /
   Browse Templates stack properly in a wide panel; animated "Drag this
   edge to extend" tip; zoom buttons removed from the panel bar.

NEW IN 40.0.0
 - includes/148 (updated): PowerPoint add-in. AI Presentation, Template
   Matcher, Marketplace, AI Image / Video / Audio and My Creations open
   inside the side panel first, showing only the tool (no site header,
   tool tabs or footer). Bar on top: Back, open in the
   large PowerPoint window, open in the browser. Insert works the same.
   "Website busy" message with a Try again button. "Continue with Google /
   Microsoft" runs the sign-in in the large window, then the panel shows
   the signed-in page. Mode cards stack properly in a wide panel, and an
   animated "Drag this edge to extend" tip shows while the panel is
   narrow. The large window still shows the whole page.
 - includes/353 (new): EPS + SVG (vector) download in the editor's
   Download menu on the website. Not offered inside the PowerPoint add-in.
 - assets/sc: cached copy of the vector export script.

V39 is V38.1.5 with 4 includes added (349-352). No V38 file was changed.
Deactivate V38 before activating V39 (never both).

NEW IN 39.0.0
 - includes/349 (new): 4:3 and 9:16 for every design template (editor,
   thumbnails, downloads, home previews), and 30 cover designs in the
   template's own colours - every template opens with its own cover.
 - includes/350 (new): a transparent PNG uploaded into an image holder
   stays transparent (no white or coloured background).
 - includes/351 (new): template cards, the 3-slide sample and the hover
   preview show real marketplace tech photos, a different one per template
   and per picture box (no watermark).
 - includes/352 (new): the AI follows the prompt and Specific AI Directives
   (exact slide count, titles, phrases, points, word limit, do not invent).
 - assets/sc: cached copy of 1 new script.

V38 is V37.0.0 with 81 includes added (267-347). No V37 file was changed.
Deactivate V37 before activating V38 (never both).

NEW IN 38.1.2
 - includes/347 (new): Settings > Free Plan Switch. Free presentations and
   free AI images can each be Limited (as today), No limit or Stopped. The
   pricing page, home page, AI Studio and support chat wording follows.

NEW IN 38.1.1
 - includes/346 (new): a shared link shows the slides exactly as the editor
   shows them (design template, layout, edits, charts). On Share the slides
   are photographed and stored with the link; the WhatsApp / social preview
   uses the first slide. Share a presentation again to refresh an old link.

NEW IN 38.1.0 - 40 new templates, picker popup, deck structures
 - includes/301-341 (new): templates 90-129 and Template Kit 5
   (Index Tabs, Stair Step, Sunburst, Barcode, Weave, Meridian, Tartan,
   Jigsaw, Sector, Terrazzo, Masonry, String Art, Sawtooth, Scallop,
   Velocity, Venn, Strata, Guilloche, Constellation, Equalizer, Helix,
   Low-Poly, Golden Section, Library Spine, Perforation, Labyrinth, Elbow,
   Kanban, Planner, Cargo, Radar, Abacus, Tangram, Crest, Dash Code, Coin
   Stack, Herringbone, Link Chain, Flight Path, Truss). All editable like
   the other design templates.
 - includes/342 (new): "Explore Themes" and "View All" open in a popup;
   click a theme or template, then Select.
 - includes/343 (new): each kind of presentation gets its own storyline
   (45 kinds: pitch deck, company overview, sales, report, proposal ...).
 - includes/344 (new): a card's outline and shadow follow its shape (round
   corners, shape change, move, resize, rotate) in the editor.
 - includes/345 (new): "Edit + Add Points" button above the slide.
 - assets/sc: cached copies of the 44 new scripts.
 - No 38.0.4 include was changed.

NEW IN 38.0.4
 - includes/298 (updated): no more flashing (locked windows, slide list and
   Change Layout list); locked windows no longer block other popups.
 - includes/299 (new): browser question boxes (e.g. "Delete slide 5?") open as a
   movable popup inside the editor.
 - includes/300 (new): no empty strip on the right side of pages on phones.

NEW IN 38.0.3
 - includes/298 (updated): a locked editor popup becomes its own window that
   stays open beside the others; move it by its title bar, resize from the corner.

NEW IN 38.0.2
 - includes/298 (new): editor extras - no Icons section in Add element;
   "Change image" on full-background pictures; editor popups can be resized
   and locked; slide thumbnails have Copy, Delete and Hide (hidden slides are
   left out of PowerPoint, PDF, PNG and JPG downloads).

NEW IN 38.0.1
 - includes/297 (new): PDF / PNG / JPG downloads no longer stop on slides with
   an empty decorative background ("createPattern" error), and photos come
   out sharper (drawn from the full stored picture).
 - includes/296 (updated): with a mouse, the bottom tool bar wraps onto a
   second row instead of hiding the last buttons.

NEW IN 38.0.0 - Icons library, side panels, 24 new templates
 - includes/267 (new): Sample format button stays inside its box on small windows.
 - includes/268-276 (new): Template Kit 3 + templates 66-73 (Hex Clinic,
   Crimson Tile, Rhombus Proposal, Finance Prism, Night Diamond, Profile
   Block, Capsule Corporate, Tide Report).
 - includes/277-293 (new): Template Kit 4 + templates 74-89 (Metro Line,
   Bento Board, Bauhaus Studio, Contour, Pinstripe, Ticker, Viewfinder,
   Duotone Pop, Caliper, Colonnade, Pixel Mosaic, Botanical Line, Halftone
   Signal, Vault Frames, Swiss Index, Compass Point).
 - includes/294 (new): point explanations stay visible on row layouts
   (templates 66-89 use two columns when there are many points).
 - includes/295 (new): Edit screen on large screens - tools in a left panel
   (Theme under Exit), element settings in a right panel, small Prev / Next
   under the slide, no bottom bar; compact movable Theme popup (also from the
   bottom-bar Theme button on large screens).
 - includes/296 (new): Icons library - Icons button in the editor tool bar;
   search 100,000+ free icons (commercial-use licences only), All / Line /
   Solid / Colour, 30 categories, movable panel.
 - assets/sc: cached copies of the 28 new scripts.

NEW IN 37.0.0 - editable PPT layouts
 - includes/265 (new): element editor for every design template - select,
   move, resize, rotate, restyle, group, layer and add elements; text
   alignment; picture fill; round corners (corner handle) and polygon sides;
   icon gradient and outline; object settings on top, fixed buttons at the
   bottom, "Edit screen" (hides slides and layout panels); movable settings
   panels; font size slider. Edits go into PowerPoint / PDF / PNG / JPG.
 - includes/266 (new): Classic rebuilt as an editable design template; the
   Classic card now makes these decks. Old Classic decks open as before.
 - includes/264 (new): layout choices are kept after save and re-open.
 - includes/151, 077, 132, 196, 199, 251 (updated): engine support for the
   editor, Edit Slide fixes, autosave of element edits, free plan allows the
   editable Classic, mobile editor fit, chart / diagram layout previews.
 - assets/sc: cached copies of the new and changed scripts.

NEW IN 36.0.16
 - includes/138 (updated): AI Presentation - pictures placed automatically
   no longer use up marketplace downloads. Pictures picked by hand from the
   marketplace are counted as before.
 - includes/081 (updated): a hand-picked picture is counted only when its
   file was delivered.

NEW IN 36.0.15
 - includes/148 (updated): PowerPoint add-in - a reached download limit now
   shows a message box that explains the limit and when it resets, and the
   button reads "Limit reached" instead of "Try again".

NEW IN 36.0.14
 - includes/148 (updated): PowerPoint add-in - inserting a marketplace image,
   video, audio or icon now counts against the plan's download limit, the
   same as a download on the website. Templates were already counted.

NEW IN 36.0.13
 - includes/012 (updated): home page - the video now sits beside the headline
   in place of the laptop picture; a banner space follows (upload the image
   under Hero Videos > Home Banner, best 2560 x 400); the six feature cards
   follow on their own row.

NEW IN 36.0.12
 - includes/196 + 195 (updated): free presentations can be downloaded as
   PowerPoint, PNG, JPG or PDF; only the Classic, Glassline and Cobalt Bold
   designs are free; a chart's type cannot be changed (its numbers can); no
   new slides in the editor. Settings > Free Presentations has the formats
   and the list of free designs.

NEW IN 36.0.11
 - includes/263 (updated): administrators see "Delete my account" but cannot
   tick the box; users get a clear "Cancel deletion" option for 24 hours.

NEW IN 36.0.10
 - includes/263 (new): "Delete my account" in the My Account popup. The
   account is deleted 24 hours after the user confirms; "Keep my account"
   stops it. Confirming cancels an active subscription. Published Marketplace
   items stay available. Users > Deleted accounts lists pending and deleted
   accounts. The Terms page gets a "Deleting your account" section.

NEW IN 36.0.9
 - includes/262 (updated): fixed the blank or endlessly reloading page after
   choosing a Google or Microsoft account (the visitor is now sent to the
   home page when no other destination was asked for).
 - Settings > DS Social Login: upload button for each provider logo, and a
   list of the last sign-in requests for troubleshooting.

NEW IN 36.0.8
 - includes/262 (updated): the Google / Microsoft panel is on the Login page
   only; it signs a new visitor up by itself. Sign Up page is as before.
 - Fixed: after a Google or Microsoft sign-in the visitor could land back on
   the Login page with an error although already signed in.

NEW IN 36.0.7
 - includes/262 (updated): Login and Sign Up show "Google / Microsoft" and
   the email form as two panels side by side (Google / Microsoft first).
   Optional logo image per provider in Settings > DS Social Login.
 - Fixed: blank page after choosing a Google or Microsoft account.
 - Fixed: the buttons appeared only after the first mouse move or tap.
 - Settings > DS Social Login shows why the last unfinished sign-in stopped.

NEW IN 36.0.6
 - includes/262 (new): "Continue with Google" and "Continue with Microsoft"
   on the Login and Sign Up forms. Set it up in Settings > DS Social Login:
   each provider needs a client ID, a client secret and the redirect address
   shown there. Nothing appears on the site until a provider is switched on.
 - includes/261 (updated): the Privacy Policy mentions these sign-in methods.

FIXED IN 36.0.5
 - includes/147 (updated): /ai-presentation/browse-templates/ (Template
   Matcher) opens again; it no longer needs permalinks to be re-saved after
   the plugin is replaced.
 - includes/121, 106, 124 (updated): signed-out visitors see a sign-in card
   on AI Video, AI Audio and My Creations instead of "Please log in.".

FIXED IN 36.0.4
 - includes/121 and 123 (updated): a video made a moment ago can be
   downloaded, and inserted from the PowerPoint add-in, without reloading the
   page. (36.0.3 fixed only part of this.)

FIXED IN 36.0.3
 - includes/123 (updated): a video that has just been generated can be
   downloaded, and inserted from the PowerPoint add-in, straight away (it
   failed with "Download failed (HTTP 500)" until the page was reloaded).
 - includes/121 (updated): a finished video shows "Done" instead of "Queued".

CHANGED IN 36.0.2
 - includes/148 (updated): the task pane header uses the small DS logo. A
   narrow pane shows the logo alone; a wider pane shows the logo and name.

FIXED IN 36.0.1
 - includes/148 (updated): PowerPoint add-in task pane. Signed out, the plan
   and credits line is hidden (it showed the last account's plan). On a
   light-mode PC all text is readable (credits, AI Suites tile names and
   "Insert audio" were invisible). In a narrow pane the logo text and the
   account name are no longer cut off.

NEW IN V36 (36.0.0)
 - includes/261 (new): Privacy Policy page. Put the shortcode [ds_privacy] on
   a page of its own (for example /privacy/). Read the text before you
   publish it; it is a draft written from what the site does.
 - includes/148 (updated): PowerPoint add-in fixes from the AppSource review.
   Large pictures insert, the Browser button is no longer cut off, the
   manifest declares PowerPointApi 1.2 (download the manifest again), "Clear
   search" empties the box, the editor button says "Insert", audio titles
   show two lines, and the Customize page is not linked inside PowerPoint
   while Marketplace mode is on. Clear the Office cache once after updating.
 - Off switches of V35 still work (DSV35_DISABLE, DSV35_NO_SCRIPT_CACHE); the
   new names are DSV36_DISABLE and DSV36_NO_SCRIPT_CACHE.

----------------------------------------------------------------------
V35 notes (unchanged)
----------------------------------------------------------------------
V35 is V34.0.7 with every include unchanged, plus 2 new includes.

FIXED IN 35.0.6
 - includes/076 (updated): the "Don't Love This Presentation?" slide at the
   end of every downloaded deck now shows support@designstager.com and
   designstager.com instead of the staging address (all templates).
 - includes/148 (updated): inside the PowerPoint add-in, site messages are
   shown on the page instead of a browser alert; plan and credit messages get
   an "Open in browser" button. Clear the Office cache once after updating.

NEW IN 35.0.5
 - includes/260 (new): AI Audio > Text to Speech - a play button in front of
   every voice. The preview is a fixed sample sentence, free for the user;
   each voice sample is generated once and then reused.

FIXED IN 35.0.4
 - includes/259 (new): Pick a Design > "Download Combined PPTX" now works for
   decks made with a design template (they keep the template look instead of
   near-empty classic slides) and for chart / diagram slides. Picked marketplace
   designs replace their slides as before; same credits as before.

NEW IN V35 (35.0.0 - 35.0.3)
 - includes/257 (new): free-tier pictures of the "Cloudflare FLUX-1-Schnell"
   model are made on your own PC first (Stable Diffusion Forge). If the PC is
   off, busy, slow or fails, the normal Cloudflare model is used and the user
   sees no error. Paid users keep Cloudflare.
 - 35.0.3: every request now answers within 40 seconds (the web server
   repeats a request it has no answer to after 60 seconds and shows an error
   page at 120). Images whose page stopped waiting no longer block the PC.
   Settings > DS Local PC Image has a "Clear waiting images" button.
 - 35.0.2: administrator "My PC" images that take longer than one request
   (FHD, 4K, or waiting behind another image) are no longer lost: the page
   shows "Processing" and the picture is added to the gallery when the PC
   sends it. Up to 6 administrator images wait in line. The PC helper keeps
   a full-quality copy of each one in its my-pc-images folder.
 - 35.0.1: administrators get one more model in AI Studio, "My PC (admin
   only)". It always uses the PC (no Cloudflare fallback), costs no credits
   and offers HD 1280x720, FHD 1920x1080 and 4K 3840x2160. Nobody else sees
   it. Switch: Settings > DS Local PC Image > Administrators.
   OFF by default. Turn it on in Settings > DS Local PC Image and paste the
   helper key there. On the PC, run the helper (ds-helper folder,
   START-DESIGNSTAGER.bat) with "site_url" in ds_worker_config.json set to
   this site. The PC is not reachable from the internet; it only asks the
   site for jobs.
 - includes/258 (new): free presentations (includes/196) are counted per day
   instead of per account. Default 2 a day; the number is the setting in
   Settings > Free Presentations. Resets at midnight, site timezone.
 - Off switches of V34 still work (DSV34_DISABLE, DSV34_NO_SCRIPT_CACHE); the
   new names are DSV35_DISABLE and DSV35_NO_SCRIPT_CACHE.

----------------------------------------------------------------------
V34 notes (unchanged)
----------------------------------------------------------------------
V34 is V33.0.4 with every include unchanged, plus the script cache.

NEW IN 34.0.6
 - includes/256 (new): a background panel behind every chart and diagram on a
   design-template slide. It takes the deck's accent colour, so it changes with
   the theme. Shown in the PowerPoint / PDF / PNG / JPG downloads too.
 - 34.0.7: the settings are inside the Edit chart / Edit diagram window
   ("Chart background"): an on / off switch and a "Corner roundness" slider
   from sharp to fully round, per slide. "Use on all chart slides" copies the
   setting to every chart slide. Cancel puts the old setting back.

FIXED IN 34.0.3
 - includes/237 (frosted glass): the glass reflection is a wide diagonal band
   of light instead of an oval, with no visible edge (all glass templates).

FIXED IN 34.0.2
 - includes/151 (engine): in the PowerPoint download a glow or glass shine no
   longer shows the edge of its ellipse (all templates, all layouts).

NEW IN V34 - SCRIPT CACHE (SPEED)
 - script-cache.php + assets/sc/: the JavaScript that was printed inside every
   page (about 350 KB on any page for a logged-in user, about 2.3 MB on the AI
   Presentation page) is now served as cached files. Same code, same order;
   the browser simply stops downloading it again on every page view.
 - Scripts that contain anything per user or per request stay inside the page.
 - Off switch: define( 'DSV34_NO_SCRIPT_CACHE', true ); in wp-config.php.
 - To see one page the old way, add ?dsv_inline=1 to its address.

----------------------------------------------------------------------
V33 notes (unchanged)
----------------------------------------------------------------------
NEW IN V33 (33.0.0 - 33.0.4)
 - includes/252 DS Template Scenario Layouts (Code Snippets id 1049): every
   design template gets the text / photo position layouts - text panel left,
   right, centre, top, bottom or in a corner, on the page, over a full photo
   or beside a half photo - in the template's own card style, colours and
   fonts. 17+ layouts per slide type (9-16 point slides: 5+).
   No side stays blank: the free area carries a graphic built from the
   icons of the slide's own points.

 - includes/253 DS Template Cycle layout (id 1050): in the "Cycle" layout
   each text sits beside its own icon.

 - includes/254 DS Template ghost numerals (id 1051): the big number behind
   a heading stays faint when a colour theme is used (no more overlapping
   look).

 - includes/255 DS PowerPoint add-in - no PDF (id 1052): the Download menu
   inside the PowerPoint add-in has no PDF choice.

----------------------------------------------------------------------
V32 notes (unchanged)
----------------------------------------------------------------------
V32 is V31.0.1 (nothing removed) plus the staging snippets of 4 Oct 2026.
23 includes are new and 1 is updated.
Deactivate V31 before activating V32 (never both).

NEW IN V32
 - includes/229-235 DS Template 49-55 (Code Snippets ids 1022-1028): Chevron
   Teal, Copper Diamond, Saffron Study, Care Blue, Silk Burgundy, Ribbon Pine,
   Pulse Aqua.
 - includes/236 DS Template PNG Objects (id 1030): "+ Add PNG" on a template
   slide - a transparent PNG placed on top of the slide (move, resize, remove),
   up to 6 per slide, also in the downloads.
 - includes/237 DS Template Frosted Glass (id 1032): glass boxes blur what lies
   under them (background shapes and the slide photo), with a corner shine.
 - includes/238 DS Template Glass Kit (id 1034): the shared layouts of the
   glass templates - glass panel on any side or corner, over the scene or over
   a full / half photo.
 - includes/239 DS Template 56 Frost Orbit (id 1033).
 - includes/240-247 DS Template 57-64 (ids 1035-1042): Glass Tower, Glass Beam,
   Glass Arc, Glass Mesh, Glass Wave, Glass Slat, Glass Flow, Glass Flute.
   With Classic the picker now has 65 entries.

NEW IN 32.0.3 - no freeze after picking a theme or template
 - includes/251 DS Template speed (id 1048): the AI Presentation page and the
   editor no longer redraw every template card / layout card at once.

NEW IN 32.0.2 - sharper slide pictures
 - includes/249 DS PPT image clarity (id 1046): Save and Download no longer
   compress slide pictures a second time.
 - includes/250 DS PPT auto pictures (id 1047): the automatic marketplace
   picture on a slide is the original resized to 1920px instead of the 400px
   preview; not counted in the marketplace download limit.

FIXED IN 32.0.1
 - PowerPoint could not open decks that use hexagons that are not square
   (Forest Campaign, Hex Forge, Spectrum Infographic, diagrams): it showed
   "found a problem with content" and the repaired slides were blank. Fixed
   in includes/151.
 - includes/248 DS Template 21 Forest - road fix (id 1045): the second road on
   the Forest Campaign cover / closing is visible and stays inside the panel.

UPDATED IN V32
 - includes/151 DS Template Engine T00 (id 952): the PowerPoint download writes
   PNG objects, the blurred glass backdrops and arrow-shaped glass.

----------------------------------------------------------------------
V31 notes (unchanged)
----------------------------------------------------------------------
V31 is V30.0.1 (nothing removed) plus the staging snippets exported on
3 Oct 2026. 26 includes are new and 4 are updated.
Deactivate V30 before activating V31 (never both).

FIXED IN 31.0.1
 - includes/228 DS Doc Import - Slide Markers (id 1021): an uploaded file in
   the suggested "Slide 1: ... / Slide 2: ..." format now makes one slide per
   marker, with the title of Slide 1 as the deck title (not the file name).

NEW IN V31
 - includes/202-220 DS Template 30-48 (Code Snippets ids 986-1004): 19 new
   design templates - Azure Prism, Opal Glass, Obsidian Glass, Circuit Tide,
   Halo Type, Sunset Glass, Frost Slate, Ember Noir, Linen Glass, Polar Night,
   Garnet Glass, Daybreak Glass, Citrus Pop, Mono Ink, Rosewater Glass,
   Slate Brief, Tangerine Split, Hex Forge, Stack Glass. With Classic the
   picker now has 49 entries.
 - includes/221 DS Template Hover Preview (id 1005): 3-slide sample on mouse
   over a template card.
 - includes/222 DS Template Point Overflow (id 1006): Agenda / Timeline / Stats
   slides show every added point.
 - includes/223 DS Template Second Picture (id 1008): own picture for the
   second picture box (editor and PowerPoint).
 - includes/224 DS Template Closing Contacts Fix (id 1009)
 - includes/225 DS PPT Save Limit Popup (id 1012): popup at the saved-items
   limit with the saved items listed for deleting, then the deck is saved.
 - includes/226 DS PPT Real Cover Thumbnails (id 1013): real cover slide on the
   My Presentations cards.
 - includes/227 DS My Creations - hide Downloaded Presentations (id 1019)

UPDATED IN V31
 - includes/151 DS Template Engine T00 (id 952): second picture in the .pptx;
   downloads are never blocked by the saved-items limit and are not kept.
 - includes/076 12b ds ppt builder (id 760): the same for Classic decks.
 - includes/069 DS Storage & API Settings (id 755) and includes/124 DS My
   Creations Hub my-cap (id 805): downloaded PowerPoint files do not count
   as saved items.

SAVED ITEMS RULE FROM V31
 Only what the user saves counts (saved presentations, images, videos, audio).
 A PowerPoint download is built, handed to the user and removed from the
 server after an hour; it is never added to the saved list.

----------------------------------------------------------------------
V30 notes (unchanged)
----------------------------------------------------------------------
V30 is V29.0.0 (nothing removed) plus the staging snippets exported on
3 Oct 2026. 5 includes are new and 8 are updated.
Deactivate V29 before activating V30 (never both).

FIXED IN 30.0.1
 - includes/151 DS Template Engine T00 (id 952): pictures were missing in the
   PowerPoint download of design-template decks. WebP / GIF pictures are now
   converted, and pictures stored on the server are loaded before the build.

NEW IN V30
 - includes/201 DS PPT Deck Style (Code Snippets id 979)
   "Deck style" card above slide 1: font family; weight, case (as typed /
   UPPERCASE / Title Case), size and colour for Title, Subtitle, Point title,
   Point explanation and Key takeaway; background and accent colour; Reset to
   default. Click any text or icon on a slide to change its own colour and
   size (icons: drawing colour and background colour). Side pictures can be
   made wider or narrower; on Classic decks the cards re-flow, on design
   templates the text is re-fitted. Breathing space: text and icons keep a
   margin inside every box, text that does not fit is made smaller.
   Classic decks and all 29 design templates; editor, thumbnails, PDF / PNG /
   JPG and PowerPoint download.
 - includes/197 DS Template - Text Fit Guard (id 972)
 - includes/198 DS PPT Image Limit - Upgrade Popup (id 973, front-end)
 - includes/199 DS PPT Editor - Mobile & Tablet Layout (id 975, front-end)
 - includes/200 DS PPT Editor - Marketplace Picker and Popup Fixes (id 976,
   front-end)

UPDATED IN V30
 - includes/073 Snippet 16 PPTX renderers (id 957): deck style, single-object
   colours, resized picture, logo glow and second logo in the Classic .pptx.
 - includes/077 Snippet 17 variant picker UI (id 953): deck style hooks; Logo
   settings with Title slide first, Glow sliders and a second logo for the
   slides after the cover.
 - includes/151 DS Template Engine T00 (id 952): second logo in the template
   .pptx.
 - includes/166 DS Diagrams (id 904): roadmap chevrons alignment.
 - includes/046 Snippet 34 image crop (id 732): crop on resized pictures.
 - includes/132 DS PPT Background Autosave (id 833): style changes are saved.
 - includes/181 DS Template 19 Mint & Lilac (id 918)
 - includes/195 DS PPT Export (id 967, front-end)

----------------------------------------------------------------------
V29 notes (unchanged)
----------------------------------------------------------------------
V29 is V28.0.0 (nothing removed) plus the staging snippets exported on
1-2 Oct 2026. 23 includes are new and 5 are updated.

NEW
 - includes/174 DS Template Kit 2 (Code Snippets id 955)
   Shared helpers and layouts for templates 13-29.
 - includes/175-191 DS Templates 13-29 (ids 912-920, 956, 922-928)
   Graphite Loop, Grid Paper Classroom, Spectrum Infographic, Navy & Coral
   Flow, Isometric Slate, Lumen Gradient, Mint & Lilac, Horizon Launch, Forest
   Campaign, Terracotta & Teal, Mono Portfolio, Navy Proposal, Retro Grid Pop,
   Brutal Orange, Emerald Corporate, Violet Pitch, Neon Lime Glass.
   With templates 01-12 and Classic that is 30 designs.
 - includes/192 DS Template - Preserve Mode Fix (id 930)
   "Preserve My Text" decks use the design template picked on the home screen.
 - includes/193 DS PPT Image Upload (id 943, front-end)
   "Upload from Device" in the Add Image popup, an upload link in Generate
   Image, and drag & drop of a picture onto a slide.
 - includes/194 DS AI Presentation - Studio Home Layout (id 960, front-end)
   Two-column home screen, sample of the picked template, My Presentations
   grid with search, sort, slide count and delete.
 - includes/195 DS PPT Export (id 967, front-end)
   The Download button becomes a menu: PowerPoint, PDF, PNG, JPG.
 - includes/196 DS PPT Free Plan (id 968)
   Free users get 2 presentations without AI (Preserve My Text only, 5 slides,
   PDF only, not saved). WP Admin > Settings > Free Presentations.
   Admin test: open the AI Presentation page with ?ds_free_preview=1.

UPDATED
 - includes/073 Snippet 16 PPTX renderers (id 957, was 759)
   Classic decks: up to 16 points, Key takeaway and company logo in the .pptx.
 - includes/077 Snippet 17 variant picker UI (id 953, was 761)
   Edit Slide panel, 9-16 point layouts, Key takeaway, logo, also on template
   decks.
 - includes/151 DS Template Engine T00 (id 952, was 873)
   Template editing with 9-16 points and the company logo.
 - includes/172 DS Doc Import (id 903)
   Uploaded-document decks carry the picked design template.
 - includes/084 26 DS PPT AI review before generate (id 933, was 766) - MERGED
   The AI writes a Key takeaway for point slides. Only these three changes
   were applied to the V28 file. Staging 933 does not contain this plugin's
   timeout / retry / Groq-error handling (17.3), so a byte-for-byte copy would
   have removed it. All other files are byte-for-byte copies of the exports.

NOT INCLUDED (older copies that were in the export folder)
 - id 911 Template Kit 2 (955 is the newer copy)
 - id 921 Template 22 (956 is the newer copy)
 - ids 931 and 932 Snippet 16 / 17 (957 and 953 are the newer copies)
 - id 905 Referral Core is identical to V28, so include 167 is unchanged.

== Installation (upgrading from V28) ==

1. Deactivate Designstager-V28 first (folder name changed: designstager-v28 ->
   designstager-v29; running both declares every function twice).
2. Plugins > Add New > Upload Plugin > designstager-v29.zip > Activate.
3. Keep every DesignStager snippet deactivated in Code Snippets, including the
   new ones (911-933, 943, 952-968) and their originals (759, 761, 766, 873).
4. Delete V28 once the site looks right.
Recovery: add define( 'DSV29_DISABLE', true ); to wp-config.php.

== 28.0.0 ==
Requires PHP: 7.4
Stable tag: 28.0.0

All active DesignStager Code Snippets packaged as a single plugin.

V28 is V27.0.0 with one include updated, copied byte-for-byte from staging:
 - includes/167 DS Referral System - Core + Admin (Code Snippets id 905) - UPDATED
   Returning customers (people who deleted their account and joined again, or
   were already referred once) are now rewarded like new customers when they
   subscribe; they are only flagged "re-joined" in WP Admin > Referrals.
   New setting: Referrals > Settings > Friend & safety > "Returning customers"
   (off by default) - tick it to stop rewarding them again.
   Self-referrals stay blocked.

== Installation (upgrading from V27) ==

1. Deactivate Designstager-V27 first (folder name changed: designstager-v27 ->
   designstager-v28; running both declares every function twice).
2. Plugins > Add New > Upload Plugin > designstager-v28.zip > Activate.
3. Keep every DesignStager snippet deactivated in Code Snippets (incl. 905).
4. Delete V27 once the site looks right.

== 27.0.0 ==
Requires PHP: 7.4
Stable tag: 27.0.0

All active DesignStager Code Snippets packaged as a single plugin.

V27 is V26.0.0 (nothing removed) plus the snippets added/updated on 30 Sep 2026,
copied byte-for-byte from staging:
 - includes/172 DS Doc Import (Code Snippets id 903) - NEW
   Upload a PDF, Word, PowerPoint or text file in the AI Presentation prompt box
   (it is attached, no popup) and use the new "Instructions for AI" box. On
   Build: Preserve My Text keeps your words, Update with AI rewrites them; the
   chosen language translates everything. Review -> Generate as before.
 - includes/173 DS Add-Slide Tile (id 910) - NEW: "+" tile after the last slide.
 - includes/166 DS Diagrams (id 904) - UPDATED (gauges strip, bars text box,
   10 Gantt designs, layout order, edit box, optional AI prompt).
 - includes/154 DS Template 03 Chrome Noir (id 876) - UPDATED (closing slide).
Install: deactivate Designstager-V26, then upload and activate V27. Never run
V26 and V27 (or the Code Snippets copies) at the same time.

V26 is V25.0.0 (nothing removed or changed) plus the snippets updated on
30 Sep 2026, copied byte-for-byte from staging:
 - includes/166 DS Diagrams (Code Snippets id 904)
 - includes/167 DS Referral System - Core + Admin (id 905)
   Unique referral link per user (?ref=CODE). Sign-ups and subscriptions are
   tracked; the reward is granted automatically after the friend completes the
   waiting period (default 30 days) without a refund. Reward = fixed credits
   per plan, % of the plan credits, or % of the payment as cash. Re-joined /
   deleted accounts and already-referred e-mails are not eligible.
   WP Admin > Referrals (All referrals, Top referrers, Settings).
 - includes/168 My Referrals tab in the account popup (id 906, front-end)
   Replaces the Plans tab (can be switched off in Referrals > Settings).
 - includes/169 Referral banner on the home page (id 907)
   Upload the design in WP Admin > Referrals > Home Banner.
   Shortcode: [ds_referral_banner]
 - includes/170 DS Share Preview (id 908)
   Real website image + title/description for WhatsApp, Facebook, LinkedIn, X.
   WP Admin > Settings > Share Preview.
 - includes/171 Wallet & Withdrawals (id 909)
   Cash rewards go to a wallet; users add UPI or bank details and withdraw from
   Rs 1,000 ($12 outside India). WP Admin > Referrals > Withdrawals: Mark paid
   (UTR) or Reject (amount returns to the balance).

New database tables (created automatically): wp_ds_referrals,
wp_ds_ref_withdrawals.

== Installation (upgrading from V25) ==

1. Deactivate Designstager-V25 first (folder name changed: designstager-v25 ->
   designstager-v26; running both declares every function twice).
2. Plugins > Add New > Upload Plugin > designstager-v26.zip > Activate.
3. If Code Snippets is used on the site, keep every DesignStager snippet
   deactivated there, including the new ones: DS Diagrams (904) and
   DS Referral System / DS Share Preview (905-909).
4. After activating on a new site:
   - Referrals > Settings: reward type, percentage / credits, waiting days.
   - Referrals > Home Banner: upload the banner design (optional).
   - Referrals > Withdrawals > Settings: minimum withdrawal, UPI / bank.
   - Settings > Share Preview: upload designstager-share-image-1200x630.jpg
     (in the V26 folder) for the home page and other pages, then click Save.
5. Delete V25 once the site looks right.

== 25.0.0 ==

Requires PHP: 7.4
Stable tag: 25.0.0

All active DesignStager Code Snippets packaged as a single plugin.

V25 is V24.0.2 plus AI Presentation charts:
 - includes/165 DS Charts (Code Snippets id 898) - NEW
   "Generate a chart slide" in Add Slide: pie/donut (full, donut, exploded,
   half donut, thin ring, start angle), bar/column, line/smooth/area, gauges,
   progress bars, radial rings; add/delete rows; AI writes the slide text.
   Click a chart to edit it. Slides with percentage data become chart slides
   (the slide count stays as chosen). Downloads contain real PowerPoint charts
   (right-click > Edit Data) for Classic and template decks.
 - includes/073 Snippet 16 (id 759): adds the chart parts to the .pptx
 - includes/076 12b builder (id 760), includes/151 template engine (id 873):
   Add Slide charges "Credits per Add Slide" (default 5)
 - includes/077 Snippet 17 (id 761): chart slides in the editor
 - includes/088 DS Credit Settings (id 769): new "Credits per Add Slide" field
   (WP Admin > DS Credits > Credit Settings > AI Presentation Slides)

== Installation (upgrading from V24) ==

1. Deactivate DesignStager V24 first (folder name changed: designstager-v24 ->
   designstager-v25; running both declares every function twice).
2. Plugins > Add New > Upload Plugin > designstager-v25.zip > Activate.
3. If Code Snippets is used on the site, keep "DS Charts" (id 898) and every
   other DesignStager snippet deactivated there.
4. Delete V24 once the site looks right.

== 24.0.2 ==

Requires PHP: 7.4
Stable tag: 24.0.2

All active DesignStager Code Snippets packaged as a single plugin.

V24 is V23.0.17 plus the AI Presentation design templates:
 - includes/151 DS Template Engine (T00)            (Code Snippets id 873)
 - includes/152-163 the 12 design templates          (ids 874-885)
   Glassline, Cobalt Bold, Chrome Noir, Aqua Wave, Paper Proposal, Noir Gold,
   Violet, Blueprint, Neon, Bronze, Azure, Executive
 - includes/077 Snippet 17 (id 761): template-deck hooks, Milestone cards and
   Circular timeline respaced
 - includes/138 Card layout fix (id 820): timeline description size
 - includes/164 Sign-up bonus credits (id 894), 24.0.1: WP Admin > DS Credits >
   Sign-up Bonus to switch on/off and set the amount (off until switched on)
 - includes/041 Email verification (id 727), 24.0.2: countdown timer on Resend code

== Installation (upgrading from V23) ==

1. Deactivate DesignStager V23 first (folder name changed: designstager-v23 ->
   designstager-v24; running both declares every function twice).
2. Plugins > Add New > Upload Plugin > designstager-v24.zip > Activate.
3. Deactivate the matching snippets in Code Snippets, including the new ones:
   "DS Template Engine" and "DS Template 01-12" (ids 873-885).
4. Delete V23 once the site looks right.

== 22.0.3 ==

Removed the "Connected to PowerPoint - Insert buttons now add items to your
slides" toast. bridge.js re-activates on every page load inside the PowerPoint
window, so it reappeared on every navigation. Insert progress, success and
error toasts are unchanged. The add-in's asset VERSION stays 1.9.2, so a
manifest already loaded into PowerPoint keeps working - no need to reload it.

== 22.0.2 ==

Snippet 148 (the PowerPoint bridge) now actually loads. Its re-entry guard
tested class_exists( 'DS_Office_Bridge' ), but PHP binds an unconditional
top-level class declaration at COMPILE time - so that check was already true
the first time the file was required. The file returned there every time,
init() was never called, no hooks were registered, and every /ds-office/ URL
returned the theme's 404 with no error logged anywhere. A constant guard
replaces it. The snippet is also refreshed from the hardened 1.9.2 bridge:
sign in / sign up / sign out in the task pane, whoami and health endpoints,
and a configurable support page for the manifest's SupportUrl.

The standalone DesignStager Office Bridge plugin is no longer needed. Deactivate
and delete it - this bundle now carries the fixed bridge.

== 22.0.1 ==

Fixes the PowerPoint bridge (snippet 148) never loading on some sites. Its
class_exists() guard used PHP's default autoload=true, which runs every
autoloader registered on the site; an autoloader that throws on an unknown
class killed the whole require. The loader's per-snippet try/catch then
skipped the snippet silently, so the add-in's /ds-office/ URLs returned 404
while the rest of the plugin worked fine. The guard now passes false.

Snippet load failures are also shown as an admin notice now, naming the file,
the error and the file:line, instead of only going to the PHP error log.

== Recovery ==

If the plugin ever takes the site down, add this to wp-config.php to get back
in without touching the filesystem:

    define( 'DSV28_DISABLE', true );

Older DSV27_DISABLE / DSV26_DISABLE / DSV25_DISABLE / DSV24_DISABLE / DSV23_DISABLE switches still work.

A DSV20_DISABLE left over from the V20 package is still honoured, so an
existing recovery switch keeps working.

== Notes ==

Constants and functions were renamed DSV20_* -> DSV22_* and dsv20_* ->
dsv22_*. DSV20_VERSION, DSV20_PATH and DSV20_URL are still defined as aliases
for anything outside the bundle that learned the old names. Nothing inside
includes/ refers to either set.

== Changelog ==

The full changelog is in the header of designstager-v22.php.
