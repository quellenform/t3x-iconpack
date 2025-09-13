.. include:: /Includes.rst.txt

.. _configuration:

=============
Configuration
=============

The Extension works out of the box and configures all relevant parameters
automatically.

Only the TypoScript needs to be inserted in order to obtain a display in the
frontend. Here you can choose between site sets, add classic TypoScript records,
or write your own TypoScript.



.. _configuration-site-set:

Site Sets
=========

..  versionadded:: TYPO3 v13.1 / iconpack v1.2.6
    If you are working with TYPO3 v12.4, use :ref:`configuration-typoscript-record`.

The extension ships some TypoScript code which can be included in the site
configuration via :ref:`Site sets <t3coreapi/13:site-sets>`:

#.  Got to backend module :guilabel:`Site Management` > :guilabel:`Sites`.

#.  Edit the configuration of your site.

#.  On the first tab go to :guilabel:`Sets for this Site`.

#.  Add the template :guilabel:`Iconpack` from the available items.



.. _configuration-typoscript-record:

TypoScript Records
==================

The extension ships some TypoScript code which needs to be included.

You can edit the corresponding template directly, or you can choose the following
method (TYPO3 v12+):

#. Switch to :guilabel:`Site Management` > :guilabel:`TypoScript`.

#. Choose :guilabel:`Edit TypoScript record` from the dropdown menu.

#. Select the corresponding template in :guilabel:`Selected record`.

#. Press the link :guilabel:`Edit the whole TypoScript record` and switch to the
   tab :guilabel:`Advanced Options`.

#. Select :guilabel:`Iconpack (iconpack)` at the field :guilabel:`Include TypoScript sets`:

   .. image:: /Images/TypoScript.png



.. _configuration-edit-typoscript-constants:

Edit TypoScript Constants
=========================

If you choose one of the two methods above, the following TypoScript constants are
available for editing:

.. confval:: cssClass

   :type: string
   :default: iconpack

   Default CSS class: This classname will be the added in the frontend to all icons.

.. confval:: cssFile

   :type: string
   :default: EXT:iconpack/Resources/Public/Css/Iconpack.min.css

   Default CSS file: Use this CSS file as default in the frontend.

.. confval:: renderTypesNative

   :type: string
   :default: svgInline,svgSprite,webfont,svg

   Render types order (Native fields): If an Iconpack provides one of the types defined here (separated by commas), it will be used in the frontend (the order is crucial!).

.. confval:: renderTypesRte

   :type: string
   :default: svgInline,svgSprite,webfont,svg

   Render types order (RTE): If an Iconpack provides one of the types defined here (separated by commas), it will be used in the frontend (the order is crucial!).



.. _configuration-custom-typoscript-configuration:

Custom TypoScript Configuration
===============================

If you do not choose either of the methods above or want to make individual
configurations, you will need to define a few things in TypoScript so that the icons
can be displayed in the frontend:

- RTE content needs to be parsed by `lib.parseFunc_RTE.nonTypoTagUserFunc`
- For the RTE, allowed content must be defined to enable the output of SVG elements
- Templates must be added so that the icon field contained in the extension can be
  rendered for the title of a record. Keep this in mind if you use your own
  templates for the header.
- A small CSS is required for the frontend output to achieve a consistent display of
  different icons.

.. tip::

   Take a closer look at the content of
   `iconpack/Configuration/TypoScript/setup.typoscript` to make individual
   adjustments.

.. note::
   If you use `EXT:bootstrap_package_iconpack
   <https://github.com/quellenform/t3x-bootstrap-package-iconpack>`_, make sure
   you include the templates at the end, otherwise `lib.parseFunc_RTE` will be
   overwritten by `EXT:bootstrap_package
   <https://github.com/benjaminkott/bootstrap_package/>`_ and the icons cannot
   be displayed by the RTE.



.. _configuration-override-settings:

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



.. _configuration-override-settings-in-the-frontend:

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



.. _configuration-extension-configuration:

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

.. confval:: rteSvg

   :type: bool
   :default: 1

   If this option is enabled, icons are loaded in the RTE as SVG (inline, sprites) if they
   are available in the respective icon pack. Otherwise, only webfonts and IMG tags will
   be used instead, which results in a simpler format but may differ from the frontend appearance.
   Note: Clear the TYPO3 cache after changing this option!

   This setting only affects the presentation of the icons used in the RTE, not the content
   stored in the database! When loading the content, an icon is generated as SVG via the
   iconfig string and displayed in the RTE. When saving, it is converted back and no real
   SVG code ever ends up in the database!

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
            # Allow <span> tags (webfont)
            - span(*)[!data-iconfig,id,name,class,style,alt,title]{color,background*,margin,padding,align,vertical-align}
            # Allow SVG images (svg)
            - img[!data-iconfig,id,name,class,style,alt,title]{margin,padding,align,vertical-align}

         extraPlugins:
            - iconpack

If you have also enabled the *rteSvg* option via the extension configuration, you will
need to allow various SVG elements in CKEditor4 as well (in CKEditor5, this is done via
the model schema of the CKEditor plugin):

.. code-block:: yaml

   editor:
      config:
         extraAllowedContent:
            # Allow SVG (svgInline, svgSprite)
            #   svgGlobalAttributes = class,id,title
            #   svgPresentationAttributes = clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility
            - svg(*)[!data-iconfig,name,style,alt,width,height,fill,viewBox,xmlns,xmlns:xlink,class,id,title,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]{color,background*,margin,padding,align,vertical-align}
            - use[href,xlink:href,x,y,width,height,class,id,title,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]
            - title[id]
            - desc[id]
            - defs[class,id,title,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]
            - linearGradient[gradientUnits,gradientTransform,spreadMethod,x1,x2,y1,y2,class,id,title,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]
            - radialGradient[gradientUnits,gradientTransform,spreadMethod,cx,cy,fr,fx,fy,r,class,id,title,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]
            - g(*)[class,id,title,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]
            - stop[stop-color,stop-opacity,offset,class,id,title,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]
            - line[x1,y1,x2,y2,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]
            - path[!d,style,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]
            - polyline[!points,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]
            - polygon[!points,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]
            - rect[width,height,x,y,rx,ry,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]
            - circle[cx,cy,r,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]
            - ellipse[cx,cy,rx,ry,clip-path,clip-rule,color,display,fill,fill-opacity,fill-rule,filter,mask,opacity,shape-rendering,stroke,stroke-dasharray,stroke-dashoffset,stroke-linecap,stroke-linejoin,stroke-miterlimit,stroke-opacity,stroke-width,transform,visibility]



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

The *defaultConfig* option defines a YAML file that controls the default options for all
installed icon packs. You can edit this file as you wish so that you can use your own
transformations and animations. Please note, however, that the values used here will also
be stored in the database, so you should use these settings with caution.

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
