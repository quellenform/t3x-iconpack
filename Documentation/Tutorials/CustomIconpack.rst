.. include:: /Includes.rst.txt

.. _customIconpack:

Creating your own Iconpack Provider
===================================

Any custom iconsets can be registered and provided via custom extensions.

Iconsets that meet at least one of the following criteria can be used:
   - Available as webfont
   - Available as SVG sprite (preferred)
   - Available as single SVG icons

An individual Iconpack extension consists of the necessary assets (SVG files,
StyleSheets, etc.) and a configuration file, which is described below.

.. tip::
   Have a look at the existing extensions!

.. important::
   Currently only webfonts and SVG images are possible in the RTE, although SVG
   sprites are already implemented in CKEditor 4. The implementation in CKEditor
   5 for TYPO3 v12 will follow. However, the output in the frontend is independent
   of this and always takes place in the selected format.

.. note::
   If you are so kind and want to make your iconpack extension available to the
   community, it would be great if you use the name `iconpack_*` as extension
   key, e.g. `iconpack_supericons`.



The Structure of an Iconpack Extension
--------------------------------------

The structure for your own iconpack extension, which consists of SVG icons and
a webfont, for example, looks something like this:

.. code-block:: none

   iconpack_customicons
   ├── composer.json
   ├── Configuration
   │   └── MyCustomIconpack.yaml
   ├── ext_emconf.php
   ├── ext_localconf.php
   └── Resources
      └── Public
         ├── Css
         │   └── webfont.css
         ├── Icons
         │   ├── Extension.svg
         │   ├── icon1.svg
         │   ├── icon2.svg
         │   ├── icon3.svg
         │   ├── ...
         │   ├── ...
         │   └── iconZ.svg
         └── Fonts
            ├── my-custom-iconpack.eot
            ├── my-custom-iconpack.svg
            ├── my-custom-iconpack.ttf
            └── my-custom-iconpack.woff



Create a Configuration for your Iconpack
-----------------------------------------

The iconpack itself is configured via the YAML file. In this file, the basic
information about the iconpack is recorded and the definitions for the available
`renderTypes` are specified:

.. caution::
   The `key` of an iconpack should be chosen wisely and should not be changed, as
   it is used to identify the iconpack and this value is also saved in the database!

.. code-block:: yaml

   iconpack:
      title: "My Custom Icons"
      # The key of the iconpack (!)
      key: "mci"
      version: 1.0.0
      # Set this value to "true" if you want to hide your iconpack in dropdown menus in the BE.
      # This is useful if you only want to use it in FE/ViewHelpers
      #   Default value: false
      hidden: true

      renderTypes:
         webfont:
            # CSS file that is used for web fonts:
            css: "EXT:iconpack_customicons/Resources/Public/Css/webfont.css"
            # CSS prefix for the icons:
            prefix: "mci-"
            # Attributes to be used by default:
            attributes:
               aria-hidden: "true"
               role: "img"

         svg:
            # Source folder of the SVG files, which are rendered as <img> tag:
            source: "EXT:iconpack_customicons/Resources/Public/Icons/"
            attributes:
               class: "mci"

         svgInline:
            # Source folder of the SVG files that are rendered as <svg> tag (inline SVG):
            source: "EXT:iconpack_customicons/Resources/Public/Icons/"
            attributes:
               class: "mci"
               fill: "currentColor"

      # Define here which icons are provided by this iconpack
      # In this case, the values here correspond to the file names (without file name extension)
      icons:
         - icon1
         - icon2
         - icon3
         - ...
         - ...
         - iconZ



Register your Iconpack
----------------------

The iconpack is then registered in the `IconpackRegistry` via `ext_localconf.php`

.. code-block:: php

   <?php

   defined('TYPO3') || die();

   if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('iconpack')) {
      \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
         \Quellenform\Iconpack\IconpackRegistry::class
      )->registerIconpack(
         'EXT:iconpack_customicons/Configuration/MyCustomIconpack.yaml'
      );
   }



Rendering in the Frontend
-------------------------

Depending on which `renderTypes` your iconpack provides (in this simple example
we have webfonts and SVG icons), these are selected by default in the following
order (with a fallback from top to bottom):

.. rst-class:: compact-list

   - svgInline
   - svgSprite
   - webfont
   - svg

If you want to overwrite or customize these settings later, you can do this
via TypoScript:

.. code-block:: typoscript

   plugin.tx_iconpack.settings.renderTypes.mci = svg



Rendering in the Backend
------------------------

In the backend, the order of the `renderTypes` for native fields and the RTE is
predefined in the source code.

Unless you explicitly change this order in your YAML file or disable the `rteSvg`
option in the extension settings, it will also be:

.. rst-class:: compact-list

   - svgInline
   - svgSprite
   - webfont
   - svg



Customizing the Rendering Sequence
----------------------------------

If you want to change the order of rendering fallbacks or specify only a specific
renderType (which in both cases would only make sense in exceptional cases), you
can add the following setting to the YAML configuration:

.. code-block:: yaml

   iconpack:
      preferredRenderTypes:
         backend:
            native: "svg"
            rte: "svg"
         frontend:
            native: "svg"
            rte: "svg"
