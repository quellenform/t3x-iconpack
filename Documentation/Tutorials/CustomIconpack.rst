.. include:: /Includes.rst.txt

.. _customIconpack:

Creating your own Iconpack Provider
===================================

Any custom iconsets can be registered and provided via custom extensions.

Iconsets that meet at least one of the following criteria can be used:
   - Available as webfont
   - Available as SVG sprite
   - Available as single SVG icons

An individual Iconpack extension consists of the necessary assets (SVG files,
StyleSheets, etc.) and a configuration file, which is described below.

.. tip::
   Have a look at the existing extensions!

.. note::
   If you are so kind and want to make your iconpack extension available to the
   community, it would be great if you use the name `iconpack_*` as extension
   key, e.g. `iconpack_supericons`.



Iconpack Kickstarter
--------------------

If you want to take the easy route and create a ready-to-use iconpack from SVG files,
you can use the `Iconpack Kickstarter <https://github.com/quellenform/t3x-iconpack-kickstarter/>`_,
which allows you to create a complete iconpack and includes all the necessary
`renderTypes` (svgInline, svgSprites, svg, webfont).

Simply clone the repository and follow the instructions in the README.md file.



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
      hidden: false

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

      # Optional categories for the backend modal.
      # All icons not listed here can be displayed using ViewHelper,
      # but cannot be selected in the backend modal wizard!!!
      categories:
         cat1:
            icons:
               - icon1
               - icon2
               - icon3
               - ...
            label: "Category 1"
         cat2:
            icons:
               - icon4
               - icon5
               - icon6
               - ...
            label: "Category 2"

      # Define here which icons are provided by this iconpack
      # In this case, the values here correspond to the file names (without file name extension)
      icons:
         - icon1
         - icon2
         - icon3
         - ...
         - ...
         - iconZ

      # Aliases for obsolete icons.
      # Here you can list those icons that have been replaced by this particular version of the icon pack.
      aliases:
         icon1:
            - old-icon1
            - old-icon2

The keys "categories", "icons" and "aliases" can be used directly in the main
configuration with arrays, but it is also possible to refer to a YAML or JSON
file instead. External configuration of these values is particularly recommended
if the number of icons is very high or if better maintainability is required.

.. code-block:: yaml

      # Icon categories for listing in the modal window (BE only)
      categories: "EXT:iconpack_customicons/Resources/Public/Metadata/categories.yml"

      # The icons in this iconpack
      icons: "EXT:iconpack_customicons/Resources/Public/Metadata/icons.yml"
      # Alternative: Use a JSON-file
      #icons: "EXT:iconpack_customicons/Resources/Public/Metadata/icons.json"

      # Aliases for specific icons
      aliases: "EXT:iconpack_customicons/Resources/Public/Metadata/aliases.yaml"



Aliases
~~~~~~~

It may happen that some icons are removed from newer versions of certain iconpacks.
Aliases are used to register these icons and provide replacements for them.
Essentially, this simply involves replacing icon identifiers, with an alias
specifying which icon should be replaced by which other icon.

Aliases can be used in external configuration files in two ways:
- Either directly in the "icons" key/array
- Or in a separate file in the "aliases" key/array

If you use the "aliases" key or your own file for storing aliases, any aliases
stored directly in the "icons" key will be ignored!

If the configuration is done in icons.yml, use the following structure:

.. code-block:: yaml

   icon1:
      aliases:
         - old-icon1
         - very-old-icon1
   icon2:
      aliases: old-icon2

If you want to configure aliases.yml (which replaces the previous method),
use the following:

.. code-block:: yaml

   icon1:
      - old-icon1
      - very-old-icon1
   icon2: old-icon2



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
