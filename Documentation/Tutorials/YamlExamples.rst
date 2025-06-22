.. include:: /Includes.rst.txt

.. _yamlExamples:

===========================
YAML-Configuration Examples
===========================

Detailed information on the use of YAML can be found `here
<(https://www.w3schools.io/file/yaml-arrays/>`_.



Example 1 (Simple)
==================

.. code-block:: yaml

   iconpack:
      title: "Custom Icons"
      key: "ci"
      version: "1.0"

      renderTypes:
         webfont:
            css: "EXT:iconpack_ci/Resources/Public/Css/CustomWebfont.css"
            prefix: "ci-"
            attributes:
               class: "ci"

      icons:
         - cloud
         - at
         - plus
         - minus
         - arrow_up
         - arrow_down
         - arrow_right
         - arrow_left



Example 2 (Extended)
====================

.. code-block:: yaml

   iconpack:
      title: "Bootstrap Icons"
      key: "bi1"
      version: 1.10.2
      url: "https://icons.getbootstrap.com/"

      renderTypes:
         webfont:
            css: "EXT:iconpack_bootstrap/Resources/Public/Vendor/icons-1.10.2/font/bootstrap-icons.css"
            prefix: "bi-"

         image:
            source: "EXT:iconpack_bootstrap/Resources/Public/Vendor/icons-1.10.2/icons/"
            attributes:
               class: "bi"

         svgSprite:
            source: "EXT:iconpack_bootstrap/Resources/Public/Vendor/icons-1.10.2/bootstrap-icons.svg"
            attributes:
               class: "bi"
               fill: "currentColor"

         svgInline:
            source: "EXT:iconpack_bootstrap/Resources/Public/Vendor/icons-1.10.2/icons/"
            attributes:
               class: "bi"
               fill: "currentColor"

      icons: "EXT:iconpack_bootstrap/Resources/Public/Vendor/icons-1.10.2/font/bootstrap-icons.json"



Example 3 (Complex)
===================

.. code-block:: yaml

   iconpack:
      # The title which is used in the backend [mandatory]
      title: "Fontawesome Icons 5 (mod)"
      # The main key for this iconpack [mandatory]
      key: "fa5"
      version: "5.15.4"
      url: "https://fontawesome.com/"

      # Override predefined preferred renderTypes (The first available type is selected) [optional]
      preferredRenderTypes:
         backend:
            native: "svgSprite,svgInline,webfont,svg"
            rte: "webfont,svg"
         frontend:
            native: "svgInline,svgSprite,webfont,svg"
            rte: "svgInline,svgSprite,webfont,svg"

      # Use only the following styles, even if others would be available in principle
      stylesEnabled: "regular,solid,brands"

      renderTypes:
         # Default values (Always included in all renderTypes!)
         _default:
            # Default values (Always included in all styles!)
            _default:
               prefix: "fa-"
               #attributes:
                  # Note: Individual classes can be added here.
                  #class: "XXX XXX2 XXX3"
                  # Note: Individual styles can be added here.
                  #style: "color:white;background-color:red"
               css:
                  # Included in all styles but only in CKEditor
                  ckeditor: "EXT:iconpack_fontawesome/Resources/Public/Css/CustomStylesForCKEditor.css"
            regular:
               # Note: Language specific labels possible:
               #label: "LLL:EXT:iconpack_xxx/Resources/Private/Language/locallang_be.xlf:label"
               label: "FontAwesome (Regular)"
               attributes:
                  class: "far"
                  #style: "background-color:green"
            solid:
               label: "FontAwesome (Solid)"
               attributes:
                  class: "fas"
            brands:
               label: "FontAwesome (Brands)"
               attributes:
                  class: "fab"

         webfont:
            _default:
               css:
                  # Include in all webfonts-styles in all scopes (backend, CKEeditor, frontend)
                  shared: "EXT:iconpack_fontawesome/Resources/Public/Css/StylesForAllWebfonts.css"
            regular:
               css: "EXT:iconpack_fontawesome/Resources/Public/Css/regular.css"
            solid:
               css: "EXT:iconpack_fontawesome/Resources/Public/Css/solid.css"
            brands:
               css: "EXT:iconpack_fontawesome/Resources/Public/Css/brands.css"

         svgSprite:
            _default:
               css:
                  backend: "EXT:iconpack_fontawesome/Resources/Public/Css/SvgBackend.css"
                  ckeditor: "EXT:iconpack_fontawesome/Resources/Public/Css/SvgBackend.css"
                  frontend: "EXT:iconpack_fontawesome/Resources/Public/Css/SvgFrontend.css"
               attributes:
                  fill: "currentColor"
            regular:
               source: "EXT:iconpack_fontawesome/Resources/Public/Sprites/regular.svg"
            solid:
               source: "EXT:iconpack_fontawesome/Resources/Public/Sprites/solid.svg"
            brands:
               source: "EXT:iconpack_fontawesome/Resources/Public/Sprites/brands.svg"

         svgInline:
            _default:
               css:
                  backend: "EXT:iconpack_fontawesome/Resources/Public/Css/SvgBackend.css"
                  ckeditor: "EXT:iconpack_fontawesome/Resources/Public/Css/SvgBackend.css"
                  frontend: "EXT:iconpack_fontawesome/Resources/Public/Css/SvgFrontend.css"
               attributes:
                  fill: "currentColor"
            regular:
               source: "EXT:iconpack_fontawesome/Resources/Public/Svg/regular/"
            solid:
               source: "EXT:iconpack_fontawesome/Resources/Public/Svg/solid/"
            brands:
               source: "EXT:iconpack_fontawesome/Resources/Public/Svg/brands/"

      # If you specify your own options here, they will be used instead of the default options.
      # Please note that you must also use the corresponding CSS in the renderTypes above.
      options:
         customCheckbox:
            type: "checkbox"
            label: "My Special Effect"
            attributes:
               class: "fa-special"

         customStyles:
            type: "select"
            label: "Custom CSS-Style"
            values:
               red:
                  label: "Red"
                  attributes:
                     style: "color: red"
               green:
                  label: "Green"
                  attributes:
                     style: "color: green"

      categories: "EXT:iconpack_fontawesome/Resources/Public/Vendor/fontawesome-free-5.15.4-web/metadata/categories.yml"

      # The icons in this iconpack (mandatory)
      icons: "EXT:iconpack_fontawesome/Resources/Public/Vendor/fontawesome-free-5.15.4-web/metadata/icons.yml"

      # Alternative 1: Use a JSON-file
      #icons: "EXT:iconpack_fontawesome/Resources/Public/Vendor/fontawesome-free-5.15.4-web/metadata/icons.json"

      # Alternative 2: Define the icons as array
      #icons:
      #  - icon1
      #  - icon2
      #  - icon3


Example 4 (Path substitution)
=============================

The following example shows the setting in which parts of a path are replaced
with parts of an icon identifier.

For example, the identifier is `actions-archive`, which in the case of an SVG
sprite leads to the path
`EXT:iconpack_typo3/Resources/Public/Vendor/TYPO3.Icons-4.1.0/sprites/actions.svg#actions-archive`,
or in the case of an SVG to
`EXT:iconpack_typo3/Resources/Public/Vendor/TYPO3.Icons-4.1.0/svgs/actions-archive.svg`.

.. code-block:: yaml

   iconpack:
      title: "TYPO3 Icons"
      key: "t3"
      version: 4.1.0
      url: "https://typo3.github.io/TYPO3.Icons/"

      renderTypes:

         _default:
            css: "EXT:iconpack_typo3/Resources/Public/Css/t3-icons.min.css"
            prefix: "t3-"

         svg:
            source: "EXT:iconpack_typo3/Resources/Public/Vendor/TYPO3.Icons-4.1.0/svgs/%1$s/"

         svgSprite:
            source: "EXT:iconpack_typo3/Resources/Public/Vendor/TYPO3.Icons-4.1.0/sprites/%1$s.svg"

         svgInline:
            source: "EXT:iconpack_typo3/Resources/Public/Vendor/TYPO3.Icons-4.1.0/svgs/%1$s/"
