# Changelog

All notable changes to this project will be documented in this file.

## 1.2.0

- a9d0cfe Fixed PSR-2 coding style
- 7df6e8b Code cleanup/refactoring
- 01c6e69 Unify YAML file loader
- 70e4c0e Added default options for iconpacks
- e133121 Optimized CSS for CKEditor4
- 5a0ca24 Fixed icon height in backend field

## 1.1.11
- 9cc6073 That thing with localization has been rolled back and implemented correctly
- 8679068 Fixed false positive in extension scanner (v14)

## 1.1.10

- 8b6b4a8 Cleanup & correct various typos
- cb079b4 Link icons in CKEditor5!
- a357b9a Added v14 compatibility and branch alias to composer.json
- c7b82a8 Removed resolveBackPath() to ensure compatibility to v14
- b9d4ddc Register extension icon for TYPO3 14
- 36f8126 Render modal with ViewFactory (v13+)
- f128aa8 Added plugin translation for CKEditor4
- 6f05ca2 Optimized CSS for dark mode (v13+)
- 7c90777 Added IconSize if icon could not be found (v13+)
- 5d46a14 Update documentation
- 32b7f62 Optimized translation, added IconpackLocalization.php
- 4b6c390 Added aria-hidden to all icons (...if there is no title/alt)
- 54f54c4 Changed order of preferred renderTypes in the Backend (svgInline loads faster)
- ea3ae24 Added constants description
- 83adead Query correct assets for backend/frontend
- ce55e69 Return empty array if there are no styles available
- 8a02b82 Added exception if configuration file could not be found
- 6ba4600 Added .cache to .gitignore
- ac0fe28 Select the icon by double-clicking on it
- c1e69a4 Tooltip with icon name added to modal window
- 6c8c60b Fixed typos/formatting
- 982928b Display icons in the correct color when dark mode is selected
- ac18d6e Render Label for TYPO3 >=v13.3 (thanks to @bigbozo)

## 1.1.9

- 0930730 [BUGFIX] Fixed typos & added variable in header template (#30)
- 5e30902 Drop deprecated renderStatic
- 300c230 Added compatibility to 13.*.*@dev
- 50c1da1 Moved PageTSConfig to file page.tsconfig for v12+

## 1.1.8

- 36ac5b0 [BUGFIX] Error on installation with TYPO3 13.3 (#24)
- 36ac5b0 [BUGFIX] Missing argument when calling YamlFileLoader in version 13.3+ (#23)
- 2a2bebc Added event listener for v13.3 (XCLASS is no longer necessary due to new events in RteHtmlParser)

## 1.1.7

- b628dc5 Switched from DataProcessor to lib.parseFunc_RTE for automatic conversion of icons in all RTE fields

## 1.1.6

- e98e5d8 Switched from PageRenderer to AssetCollector (this changes the way CSS files are included in BE/FE)
- 77aec61 Suppress false positive messages in Install Tool

## 1.1.5

- 214c9b7 Wrap content of ext_localconf.php with call_user_func() (thanks to @froemken)
- 69aa787 Remove autoloader from ext_emconf.php (thanks to @froemken)

## 1.1.4

- 50bc2db [BUGFIX] iconpack Configuration is null (#17)

## 1.1.3

- d13ac36 [BUGFIX] HiRes Icon for RTE is missing (thanks to @christianhaendel)

## 1.1.2

- 37556e3 [FEATURE] Added compatibility to TYPO3 v13

## 1.1.1

### Feature

- f494765 [FEATURE] add icons to sys_category and sys_file_collection (thanks to @t3brightside)

## 1.1.0

### Feature

- 4750398 Added substitution of path segments with icon names in YAML-configuration files

## 1.0.1

- Updated documentation

## 1.0.0

First stable version...

### Feature

- 64a41d5 [FEATURE ] Compatibility with CKEditor 5 and TYPO3 version 12
- Icons can now be selected and edited directly in the RTE by double-clicking
- Added title and description to SVG elements (title will be converted to
  `<title>` and alt to `<desc>`)
- The JavaScript has been almost completely rewritten in TypeScript
- 452e655 Added documentation
- Many other changes and optimizations under the hood...

### Removed

- Removed "enablePlugin" from the extension configuration

### Breaking

- Changed the icon tag from `<icon>` to `<span>` for better compatibility! Due
  to a lack of funding for the project, there is unfortunately no upgrade wizard
  for this, but there is at least downward compatibility in the frontend. To
  update your content and switch to the new tag, simply open the data record for
  editing and save it again.

## 0.3.3

- d37a6d7 [BUGFIX] Opening modalbox in IRRE content elements (#10)
- 17b1d17 [FEATURE] Make the CKEditor plugin optional in the extension
  configuration

## 0.3.2

- 9632c4a [FEATURE] Option to remove default CSS (#11)

## 0.3.1

- f6e7419 [BUGFIX] Undefined global variable $LANG
- 106a562 Restructured queryIconpackIcons()
- 2ca27b9 Updated cache configuration

## 0.3.0

- e21fa7b Removed the templates for "bootstrap_package". Please use
  "EXT:bootstrap_package_iconpack" instead!

## 0.2.1

- 99f9ccf Remove title attribute from svg elements as it is forbidden in HTML

## 0.2.0

- daf35b7 Added support for custom CSS class
- 7d2f743 SVG Sanitizer Improvement
- c9154b2 Improved CSS for backend/frontend
- d7d8f34 Added translatable labels
- 73d9d7c Code cleanup, PSR12 compatibility
