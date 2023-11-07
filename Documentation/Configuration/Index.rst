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

- RTE content (bodytext) is transferred to a DataProcessor
- Allowed content is defined for the RTE to enable the output of SVG elements
- Templates are added so that the icon field contained in the extension can be
  rendered for the title of a record.
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
`plugin.tx_iconpack.overrides.renderTypes` is created in the template or in the
respective page. The settings can apply to all iconpacks with the key
`_default`, or can refer to a specific iconpack.

The following examples show such a configuration for the frontend.



Override Settings in the Frontend
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

The settings for the frontend are made in the *TypoScript Setup*. Rendering in
the frontend is done via `dataProcessing`, which can be assigned to any custom
fields.

.. code-block:: typoscript

   plugin.tx_iconpack {
      settings {
         # This classname will be the added in the frontend to all icons
         cssClass = iconpack
         # This can be used to override the rendering of the icons in the frontend.
         overrides {
            renderTypes {
               _default {
                  native = svgSprite, webfont
                  rte = webfont
               }
               fa5 {
                  native = svgInline
               }
               glyphicons = webfont
            }
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

.. important::
   Changing the extension configuration requires an emptying of the TYPO3 cache
   for the changes to take effect!

If you deactivate these settings, however, you will have to make the required
configuration yourself and integrate it into your own setup. Use the following
two examples...



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



Example of manual CKEditor 5 configuration (TYPO3 v12)
------------------------------------------------------

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
