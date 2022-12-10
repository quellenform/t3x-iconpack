[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg?style=for-the-badge)](https://www.paypal.me/quellenform)
[![Latest Stable Version](https://img.shields.io/packagist/v/quellenform/t3x-iconpack?style=for-the-badge)](https://packagist.org/packages/quellenform/t3x-iconpack)
[![TYPO3 10](https://img.shields.io/badge/TYPO3-10-%23f49700.svg?style=for-the-badge)](https://get.typo3.org/version/11)
[![TYPO3 11](https://img.shields.io/badge/TYPO3-11-%23f49700.svg?style=for-the-badge)](https://get.typo3.org/version/10)
[![License](https://img.shields.io/packagist/l/quellenform/t3x-iconpack?style=for-the-badge)](https://packagist.org/packages/quellenform/t3x-iconpack)

# Iconpack

TYPO3 CMS Extension `iconpack`


## What does it do?

This extension provides an iconpack registry for custom iconpacks which can be used in backend and frontend and are rendered according to the configuration of the installed iconpacks.

Instead of storing the entire HTML markup for an icon in the database fields, a short configuration string is stored instead (also in the RTE). This string is called "iconfig" and looks something like `fa6:solid,star,transform:spin`. This example will render the icon *Star* from the iconpack *Font Awesome 6* (solid style) with the additional property of a spinng rotation.

Only when rendering in the backend or frontend, this string is transformed into the corresponding HTML, giving the greatest possible flexibility.

Furthermore, this extension adds additional icon fields for pages and text headers, but it can also be used to extend fields of your own extensions.


## Features

The extension `iconpack` is different from previous implementations and will probably be the only icon extension you will need in the future.

The biggest differences are among others:

- Not limited to a specific iconset! Existing iconpacks can be installed, or custom iconpacks can be created, depending on your requirements.
- Easy to use: Install, add the provided TypoScript-template & use it, no further configuration needed (but possible!).
- Offers the use of various icons in the header, page, in the bodytext (CKEditor) and in your own fields
- All required assets (JavaScripts and StyleSheets) are automatically added in frontend and backend by default
- All settings of an iconpack can be overridden via an individual configuration (YAML).
- Individual iconsets can be easily added (see instructions for [creating your own iconpack provider](#creating-your-own-iconpack-provider))
- Can also be integrated into own extensions via the provided form wizard
- Multilingual labels
- Uses the backend caching integrated in TYPO3 for the configuration of the installed iconpacks in order not to slow down the backend
- The frontend rendering of icons can be changed afterwards (easily switch from Webfont to SVG with TypoScript)
- Works with [EXT:bootstrap_package](https://github.com/benjaminkott/bootstrap_package/) and other extensions out of the box (please note the TypoScript template!)
- No dependency on other extensions or third-party code

For icons only a string is stored (e.g. `fa:solid,star`), which is then rendered to an icon by this extension depending on the configuration in the frontend/backend.
The way an icon is rendered there (SVG, inline, webfont, etc.) can be changed afterwards, so that the greatest possible flexibility is guaranteed.

> Currently, only webfonts are possible in the RTE (CKEditor). SVG elements (Sprite, Inline, or IMG) would be possible in principle, but the handling is much more complicated and relatively error-prone when using with additional options. Therefore, in the best case, iconpacks should also be available as webfont, even if it is not used in the frontend.

This extension does ***NOT*** have the same approach as the TYPO3 integrated `IconRegistry` with its approach to cache all icons including their HTML markup for the backend and consider them as absolute, but focuses on handling icons for editors and frontend output!


## Installation

1. Install this extension from TER or with Composer
2. Install one of the existing iconpack providers:
    - [Bootstrap (includes Glyphicons & v1)](https://github.com/quellenform/t3x-iconpack-boostrap)
    - [Dripicons](https://github.com/quellenform/t3x-iconpack-dripicons)
    - [Elegant Icons](https://github.com/quellenform/t3x-iconpack-elegant)
    - [Feather Icons](https://github.com/quellenform/t3x-iconpack-feather)
    - [Font Awesome (includes v4, v5-free & v6-free)](https://github.com/quellenform/t3x-iconpack-fontawesome)
    - [Helium Icons](https://github.com/quellenform/t3x-iconpack-helium)
    - [Linea Icons](https://github.com/quellenform/t3x-iconpack-linea)
    - [Linearicons](https://github.com/quellenform/t3x-iconpack-linearicons)
    - [Lineicons](https://github.com/quellenform/t3x-iconpack-lineicons)
    - [MFG Icons](https://github.com/quellenform/t3x-iconpack-mfg)
    - [Themify Icons](https://github.com/quellenform/t3x-iconpack-themify)
    - ...or create your own iconpack provider
3. Add the provided TypoScript to your template


## Extension Configuration

You can control whether the required assets should be included automatically and whether the CKEditor should be configured automatically.

Some iconpacks additionally offer the possibility to choose between different icon set versions and other options.

> Important: Changing the extension configuration requires an emptying of the TYPO3 cache for the changes to take effect!

| Value | Description |
| ----- | ----------- |
| `autoConfigRte` | If enabled, the `span(*)[*]` value is added to the `extraAllowedContent` parameter, and the `aria-hidden` and `role` attributes are allowed under `RTE.default.proc.HTMLparser_db.tags.span.allowedAttribs`, so that the icons inserted in the RTE and their values are preserved when saving. If this option is disabled, these parameters must be inserted manually in the custom YAML configuration for the CKEditor. |
| `autoAddAssets` | If enabled, all CSS files required by web fonts are automatically integrated in the frontend. |

Example of manual CKEditor configuration:
```yaml
editor:
  config:
    extraAllowedContent:
      - span(*)[*]

processing:
  HTMLparser_db:
    tags:
      span:
        allowedAttribs: "class, id, title, dir, lang, xml:lang, itemscope, itemtype, itemprop, data-iconfig, style, aria-hidden, role"

```


### Overriding settings

The basic representation of icons is defined in the respective iconpack via the "renderTypes" key, and can be subsequently overwritten at any time using TypoScript for the frontend. The defined values are keywords separated by commas, which represent a sequence. If an iconpack does not contain the requested *renderType*, the next defined *renderType* is used.

To override the default settings for rendering iconpacks, the key `plugin.tx_iconpack.overrides.renderTypes` is created in the template or in the respective page. The settings can apply to all iconpacks with the key `default`, or can refer to a specific iconpack.

The following examples show such a configuration for the frontend.


#### Override settings in the frontend

The settings for the frontend are made in the *TypoScript Setup*.
Rendering in the frontend is done via `dataProcessing`, which can be assigned to any custom fields.

```
plugin.tx_iconpack {
  settings {
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
```


## Use in own extensions

### Database

The wizard for adding icons can be used arbitrarily in own database fields.
To do this, simply assign the value `IconpackWizard` to the `renderType` of the corresponding field.

Here is an example with `/Configuration/TCA/Overrides/tt_content.php`:


```php
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tt_content',
    [
        'header_icon' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:tt_content.header_icon',
            'config' => [
                'type' => 'user',
                'renderType' => 'IconpackWizard',
            ]
        ],

    ]
);
```


### Fluid Template

Icons can be inserted directly from a fluid template using the provided ViewHelper.
All that needs to be done is to add the namespace `http://typo3.org/ns/Quellenform/Iconpack/ViewHelpers` and a corresponding *iconfig* string. Optionally `renderTypes`, `additionalAttributes` and `style` can be used.

```html
<html xmlns:i="http://typo3.org/ns/Quellenform/Iconpack/ViewHelpers" data-namespace-typo3-fluid="true">
   <i:icon iconfig="{headerIcon}" renderTypes="svgSprite,webfont"/>
</html>
```


## Creating your own Iconpack Provider

Any custom iconsets can be registered and provided via custom extensions.

> Tipp: Have a look at the existing extensions!

Iconsets that meet at least one of the following criteria can be used:
- Available as webfont (currently required for use in the CKEditor!)
- Available as SVG sprite (preferred alternative)
- Available as single SVG icons

An individual Iconpack extension consists of the necessary assets (SVG files, StyleSheets, JavaScripts) and a configuration file, which is described below.

If you are so kind and want to make your iconpack extension available to the community, it would be great if you use the name `iconpack_*` as extension key, e.g. `iconpack_supericons`.


### YAML-Configuration
Detailed information on the use of YAML can be found [here](https://www.w3schools.io/file/yaml-arrays/).

Example 1:

```yaml
iconpack:
  title: "Custom Icons"
  key: "ci"
  version: "1.0"

  renderTypes:
    webfont:
      css: "EXT:iconpack_mfg/Resources/Public/Css/CustomWebfont.css"
      prefix: "mfg-"
      attributes:
        class: "mfg"
        aria-hidden: "true"
        role: "img"

  icons:
    - cloud
    - at
    - plus
    - minus
    - arrow_up
    - arrow_down
    - arrow_right
    - arrow_left
```

Example 2 (simple):

```yaml
iconpack:
  title: "Bootstrap Icons"
  key: "bi1"
  version: 1.10.2
  url: "https://icons.getbootstrap.com/"

  renderTypes:
    webfont:
      css: "EXT:iconpack_bootstrap/Resources/Public/Vendor/icons-1.10.2/font/bootstrap-icons.css"
      prefix: "bi-"
      attributes:
        aria-hidden: "true"
        role: "img"

    svg:
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
```

Example 3 (extended):

```yaml
iconpack:
  # The title which is used in the backend [mandatory]
  title: "Fontawesome Icons 5"
  # The main key for this iconpack [mandatory]
  key: "fa5"
  version: "5.15.4"
  url: "https://fontawesome.com/"

  # Preferred types (The first available type is selected) [optional]
  preferredRenderTypes:
    backend:
      native: "svgSprite,svgInline,webfont"
      rte: "webfont"
    frontend:
      native: "svgInline,svgSprite,webfont,svg"
      rte: "svgInline,webfont"

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
          shared: "EXT:iconpack_fontawesome/Resources/Public/Css/StylesForAllWebfonts.css"
        attributes:
          aria-hidden: "true"
          role: "img"
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
          role: "img"
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
          role: "img"
      regular:
        source: "EXT:iconpack_fontawesome/Resources/Public/Svg/regular/"
      solid:
        source: "EXT:iconpack_fontawesome/Resources/Public/Svg/solid/"
      brands:
        source: "EXT:iconpack_fontawesome/Resources/Public/Svg/brands/"


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
```

## Additional Information

### WIP | Planned Features
- Optimize inserting icons, which are not available as webfont (SVG, Inline-SVG, JavaScript, ...), directly in the RTE
- Contextmenu in CKEditor to edit a previously added icon
- Adding icons to the *IconRegistry* so that they are also available in the classic style in the backend (...does that make any sense?)
- Writing detailed documentation for the YAML configuration file
- **Upgrade the extension for the use of TYPO3 v12 (help wanted!)**


### Why I made this extension?

...because this feature is simply missing in TYPO3!

Various existing extensions have so far only ever handled a single icon pack, and even that was not optimally integrated into TYPO3. Most of them can either only be used in the RTE, and others only in a single additional field. All extensions so far also lack the possibility to influence the icon rendering afterwards. Furthermore, other extensions don't really offer the possibility to use an icon set flexibly in their own database fields and to achieve a consistent rendering across the whole website.

It took me several months to find out how an optimal flexible iconpack system should work. There is still room for improvement in the programming, but I tried to create a mechanism that offers the greatest possible flexibility and consistency for current and future requirements by analyzing various icon sets and extensive testing.

The main focus for me was that every possible icon set should be able to be used with it, and at the same time it should be possible to use it in all TYPO3 fields (native fields, RTE, fields in own extensions, ...).

Another focus was on the extensibility and modification of existing iconpack extensions. These should be integrated into the system as easy as possible (YAML file).


### Why I published this extension?

I wrote this extension at the end of 2020, and unfortunately didn't make it public right then.

The reason why the whole thing is now published by me after all is that I am convinced that such a system can really help to improve and simplify the handling with icons in TYPO3.

If you think that this is also a step in the right direction for you and you have wishes, thanks or improvements, please share your contribution!


## Contribute | Say Thanks!

- If you like this extension, use it.
- If you think you can do something better, be so kind and contribute your part to it
- If you love the extension or if you want to support further development, [donate an amount of your choice via PayPal](https://www.paypal.me/quellenform)
