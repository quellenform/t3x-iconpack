.. include:: /Includes.rst.txt

.. _configuration:

=============
Configuration
=============

The Extension works out of the box and configures all relevant parameters
automatically.

Only the TypoScript needs to be inserted in order to obtain a display in the
frontend:

TypoScript Configuration
========================

To enable the icons to be output in the frontend, a few things need to be
defined in the TypoScript:

- RTE content is parsed by `lib.parseFunc_RTE.nonTypoTagUserFunc`
- Allowed content is defined for the RTE to enable the output of SVG elements
- Templates are added so that the icon field contained in the extension can be
  rendered for the title of a record. Keep this in mind if you use your own
  templates for the header.
- A small CSS is added to the frontend output to achieve a consistent display of
  different icons.

All these values can of course be overwritten individually.

.. tip::

   Take a closer look at the content of
   `iconpack/Configuration/TypoScript/setup.typoscript` to make individual
   adjustments.

If you want to keep the default settings, simply add the TypoScript provided at
the end of your setup:

.. image:: /Images/TypoScript.png

.. note::
   If you use `EXT:bootstrap_package_iconpack
   <https://github.com/quellenform/t3x-bootstrap-package-iconpack>`_, make sure
   you include the templates at the end, otherwise `lib.parseFunc_RTE` will be
   overwritten by `EXT:bootstrap_package
   <https://github.com/benjaminkott/bootstrap_package/>`_ and the icons cannot
   be displayed by the RTE.



Overriding Settings
-------------------

The basic representation of icons is defined in the respective iconpack via the
"renderTypes" key, and can be subsequently overwritten at any time using
TypoScript for the frontend. The defined values are keywords separated by
commas, which represent a sequence. If an iconpack does not contain the
requested *renderType*, the next defined *renderType* is used.

To override the default settings for rendering iconpacks, the key
`plugin.tx_iconpack.settings.renderTypes` is created in the template or in the
respective page. The settings can apply to all iconpacks with the key
`_default`, or can refer to a specific iconpack.

The following examples show such a configuration for the frontend.



Override Settings in the Frontend
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The settings for the frontend are made in the *TypoScript Setup*. Rendering in
the frontend is done via `dataProcessing`, which can be assigned to any custom
fields.

.. code-block:: typoscript

   # Typoscript setup
   plugin.tx_iconpack {
      settings {
         # If you change the CSS class, please also note that you have to change it in your CSS files accordingly!
         #   You can also remove this completely if you don't need it.
         cssClass = {$plugin.tx_iconpack.cssClass}
         # Define the order of the renderTypes here.
         #   If an Iconpack provides one of the types defined here (separated by commas), it will be used (the order is crucial!).
         #   This can be specified differently for icons from a field or the RTE with the keys "native" and "rte".
         #   Available values: svgInline, svgSprite, webfont, svg
         renderTypes {
            # Override default values for all iconpacks
            _default {
               native = svgSprite, webfont
               rte = webfont
            }
            # Override values only for specific iconpacks by using their key
            fa6 {
               native = svgInline
            }
            glyphicons = webfont
         }
      }
   }



Extension Configuration
=======================

You can control whether the required assets should be included automatically and
whether the CKEditor should be configured automatically.

Some iconpacks also offer the option of choosing between different icon set
versions and other options. These settings are made in the extension
configuration of the respective iconpack.

The following settings are currently available:

.. confval:: autoConfigRte

   :type: bool
   :default: 1

   If enabled, the CKEditor will be configured automatically (no PageTS needed),
   so that the icons inserted in the RTE and their values are preserved when
   saving. If this option is disabled, these parameters must be inserted
   manually in the custom YAML configuration for the CKEditor.

.. confval:: autoAddAssets

   :type: bool
   :default: 1

   If enabled, all CSS files required by the installed iconpacks are
   automatically included in the frontend.

.. confval:: defaultConfig

   :type: string
   :default: EXT:iconpack/Configuration/Iconpack/Default.yaml

   Path to the YAML configuration file containing the default options for all
   installed icon packs. Leave this field blank to disable it, or use your own
   configuration file. If you specify your own file here, it will overwrite
   the default options for all icon packs, unless they contain their own options.
   Please also note that you must specify a corresponding CSS file for rendering
   the respective options in the YAML file with the key *optionsCss* (see example below).

.. important::
   Changing the extension configuration requires an emptying of the TYPO3 cache
   for the changes to take effect!

If you deactivate *autoConfigRte* or *autoAddAssets*, however, you will have to make the required
configuration yourself and integrate it into your own setup. Use the following two examples...



Example of manual CKEditor 4 configuration (TYPO3 v10/11)
---------------------------------------------------------

.. code-block:: yaml

   editor:

      externalPlugins:
         iconpack: { resource: 'EXT:iconpack/Resources/Public/JavaScript/v11/CKEditor/plugin.js' }

      config:
         # Note: CSS files required by specific iconpacks is loaded automatically!
         contentsCss:
            - 'EXT:iconpack/Resources/Public/Css/Backend/CKEditor.min.css'

         # This configuration is necessary so that certain contents can be inserted in CKEditor4 in the first place.
         # All values defined here finally end up in the RTE and can be edited there.
         #
         # Note, however, that these values are additionally filtered again with PHP when saving, and ultimately only the attributes
         # defined here are actually stored in the database. In addition, for the output in the frontend on the one hand the
         # RteHtmlParser is used and on the other hand the Sanitizer, which finally decides which output ends up in the FE.
         #
         # More information about the RTE content filter can be found here:
         #   https://ckeditor.com/docs/ckeditor4/latest/examples/acfcustom.html
         #   https://ckeditor.com/docs/ckeditor4/latest/guide/dev_advanced_content_filter.html
         #
         extraAllowedContent:
            # webfont: Allow <span> tags
            - span(*)[!data-iconfig,id,name,class,style,alt,title]{color,background*,margin,padding,align,vertical-align}
            # image: Allow svg images
            - img[!data-iconfig,id,name,class,style,alt,title]{margin,padding,align,vertical-align}

         extraPlugins:
            - iconpack



Example of manual CKEditor 5 configuration (TYPO3 v12+)
-------------------------------------------------------

.. code-block:: yaml

   editor:
      config:
         # Note: CSS files required by specific iconpacks is loaded automatically!
         contentsCss:
            - 'EXT:iconpack/Resources/Public/Css/Backend/CKEditor.min.css'

         # Load modules for plugins when CKEditor is initialized
         # See CKEditor plugin API for details
         importModules:
            - { module: '@quellenform/iconpack-ckeditor.js', exports: [ 'Iconpack' ] }

         toolbar:
            items:
               - '|'
               - Iconpack

(Please take at look at the examples, located in *Configuration/RTE/*)



Example of a YAML file for custom options
-----------------------------------------

.. code-block:: yaml

   iconpack:
      optionsCss:
         transforms: "EXT:iconpack/Resources/Public/Css/IconpackTransforms.min.css"

      options:
         size:
            type: "select"
            label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:label.size"
            values:
            xs:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.xs"
               attributes:
                  class: "iconpack-xs"
            sm:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.sm"
               attributes:
                  class: "iconpack-sm"
            md:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.md"
               attributes:
                  class: "iconpack-md"
            lg:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.lg"
               attributes:
                  class: "iconpack-lg"
            1x:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.1x"
               attributes:
                  class: "iconpack-1x"
            2x:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.2x"
               attributes:
                  class: "iconpack-2x"
            3x:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.3x"
               attributes:
                  class: "iconpack-3x"
            4x:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.4x"
               attributes:
                  class: "iconpack-4x"
            5x:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.5x"
               attributes:
                  class: "iconpack-5x"
            6x:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.6x"
               attributes:
                  class: "iconpack-6x"
            7x:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.7x"
               attributes:
                  class: "iconpack-7x"
            8x:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.8x"
               attributes:
                  class: "iconpack-8x"
            9x:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.9x"
               attributes:
                  class: "iconpack-9x"
            10x:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:size.10x"
               attributes:
                  class: "iconpack-10x"

         decoration:
            type: "select"
            label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:label.decoration"
            values:
            border:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:decoration.border"
               attributes:
                  class: "iconpack-border"

         transform:
            type: "select"
            label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:label.transform"
            values:
            r90:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.90"
               attributes:
                  class: "iconpack-rotate-90"
            r180:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.180"
               attributes:
                  class: "iconpack-rotate-180"
            r270:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.270"
               attributes:
                  class: "iconpack-rotate-270"
            fx:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.flipHorizontal"
               attributes:
                  class: "iconpack-flip-horizontal"
            fy:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.flipVertical"
               attributes:
                  class: "iconpack-flip-vertical"
            fxy:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.flipBoth"
               attributes:
                  class: "iconpack-flip-both"
            beat:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.beat"
               attributes:
                  class: "iconpack-beat"
            bounce:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.bounce"
               attributes:
                  class: "iconpack-bounce"
            fade:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.fade"
               attributes:
                  class: "iconpack-fade"
            beat-fade:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.beat-fade"
               attributes:
                  class: "iconpack-beat-fade"
            flip:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.flip"
               attributes:
                  class: "iconpack-flip"
            shake:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.shake"
               attributes:
                  class: "iconpack-shake"
            spin:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.spin"
               attributes:
                  class: "iconpack-spin"
            spin-pulse:
               label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:transform.spin-pulse"
               attributes:
                  class: "iconpack-spin-pulse"

         fixed:
            type: "checkbox"
            label: "LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:label.fixed"
            attributes:
            class: "iconpack-fw"
