# DesignStager plugin

The DesignStager WordPress plugin for designstager.com: every site feature
(AI Presentation, marketplace, credits, AI image / video / audio, AI Document,
PowerPoint and Word add-ins) packaged as one plugin.

- `plugin/` — the plugin as it is installed (`designstager-v42.php` is the main file,
  `includes/` holds one file per feature in load order, `assets/sc/` the cached scripts).
- `plugin/readme.txt` — what changed in each version.
- `plugin/BUILD-MANIFEST.txt` — every file with its size and hash.
- `build.sh` — makes the installable zip: `./build.sh 42` → `dist/designstager-v42.zip`.

Install: deactivate the previous version, upload the zip in Plugins → Add New, activate.
Never have two versions active at the same time.

Test changes on stagingtest.designstager.com before the live site.
