.. include:: /Includes.rst.txt

.. _changelog:

=========
Changelog
=========

All notable changes to this project will be documented in this file.

1.1.1
=====

Feature
-------

- f494765 [FEATURE] add icons to sys_category and sys_file_collection (thanks to @t3brightside)


1.1.0
=====

Feature
-------

- 4750398 Added substitution of path segments with icon names in YAML-configuration files

1.0.1
=====

- Updated docuentation

1.0.0
=====

First stable version...

Feature
-------

.. rst-class:: compact-list

- Compatibility with CKEditor 5 and TYPO3 version 12
- Icons can now be selected and edited directly in the RTE by double-clicking
- Added title and description to SVG elements (title will be converted to
  `<title>` and alt to `<desc>`)
- The JavaScript has been almost completely rewritten in TypeScript
- Many other changes and optimizations under the hood...

Removed
-------

.. rst-class:: compact-list

- Removed "enablePlugin" from the extension configuration

Breaking
--------

.. rst-class:: compact-list

- Changed the icon tag from `<icon>` to `<span>` for better compatibility! Due
  to a lack of funding for the project, there is unfortunately no upgrade wizard
  for this, but there is at least downward compatibility in the frontend. To
  update your content and switch to the new tag, simply open the data record for
  editing and save it again.

0.3.3
=====

.. rst-class:: compact-list

- d37a6d7 [BUGFIX] Opening modalbox in IRRE content elements (#10)
- 17b1d17 [FEATURE] Make the CKEditor plugin optional in the extension
  configuration

0.3.2
=====

.. rst-class:: compact-list

- 9632c4a [FEATURE] Option to remove default CSS (#11)

0.3.1
=====

.. rst-class:: compact-list

- f6e7419 [BUGFIX] Undefined global variable $LANG
- 106a562 Restructured queryIconpackIcons()
- 2ca27b9 Updated cache configuration

0.3.0
=====

.. rst-class:: compact-list

- e21fa7b Removed the templates for "bootstrap_package". Please use
  "EXT:bootstrap_package_iconpack" instead!

0.2.1
=====

.. rst-class:: compact-list

- 99f9ccf Remove title attribute from svg elements as it is forbidden in HTML

0.2.0
=====

.. rst-class:: compact-list

- daf35b7 Added support for custom CSS class
- 7d2f743 SVG Sanitizer Improvement
- c9154b2 Improved CSS for backend/frontend
- d7d8f34 Added translatable labels
- 73d9d7c Code cleanup, PSR12 compatibility
