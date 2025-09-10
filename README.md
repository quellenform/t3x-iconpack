[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg?style=for-the-badge)](https://www.paypal.me/quellenform)
[![Latest Stable Version](https://img.shields.io/packagist/v/quellenform/t3x-iconpack?style=for-the-badge)](https://packagist.org/packages/quellenform/t3x-iconpack)
[![TYPO3](https://img.shields.io/badge/TYPO3-10|11|12|13-%23f49700.svg?style=for-the-badge)](https://get.typo3.org/)
[![License](https://img.shields.io/packagist/l/quellenform/t3x-iconpack?style=for-the-badge)](https://packagist.org/packages/quellenform/t3x-iconpack)

# Iconpack

TYPO3 CMS Extension `iconpack`

The most flexible icon system for TYPO3 10, 11, 12 and 13+!

---

This extension provides an iconpack registry for custom iconpacks which can be
used in backend and frontend and are rendered according to the configuration of
the installed iconpacks.

The idea behind the extension is to provide a flexible system by means of which
any icon sets can be used and the desired output can be generated at any time by
separating storage and rendering.

![Iconpack Preview](Documentation/Images/ScreenCapture.gif?raw=true)


## Features

The extension `iconpack` is different from previous implementations and will
probably be the only icon extension you will need in the future.

The biggest differences are among others:

- Not limited to a specific iconset! Existing iconpacks can be installed, or
  custom iconpacks can be created, depending on your requirements
- Easy to use: Install, add the provided TypoScript-template & use it, no
  further configuration needed (but possible!)
- Offers the use of various icons in the header, page, in the bodytext (CKEditor
  4/5) and in your own fields
- All required assets (JavaScripts, StyleSheets, etc.) are automatically added
  in frontend and backend by default, depending on the configuration of the icon
  set used
- All settings of an iconpack can be overridden via an individual configuration
  (YAML)
- Individual iconsets can be easily added (see
  [instructions](https://docs.typo3.org/p/quellenform/t3x-iconpack/main/en-us/Tutorials/CustomIconpack.html)
  for creating your own iconpack provider)
- Can also be integrated into own extensions via the provided form wizard
- Multilingual labels for icons
- Uses the backend caching integrated in TYPO3 for the configuration of the
  installed iconpacks in order not to slow down the backend
- The frontend rendering of icons can be changed afterwards (easily switch from
  Webfont to SVG with TypoScript)
- Works with [Bootstrap
  Package](https://github.com/benjaminkott/bootstrap_package/) and other
  extensions
- No dependency on other extensions or third-party code!


## Planned Features

- Add contextmenu in CKEditor to edit a previously added icon (and options)
- Optimize the UI/Modal
- Add more detailed information on using and creating your own iconpacks

[Sponsoring required](https://www.paypal.me/quellenform)


## How does it work?

Instead of storing the entire HTML markup or a static file path for an icon in
the database fields, a short configuration string is stored instead (also in the
RTE). This string is called "iconfig" and looks something like
`fa6:solid,star,transform:spin`. This example will render the icon *Star* from
the iconpack *Font Awesome 6* (solid style) with the additional property of a
spinning rotation.

This string is only converted into HTML code according to the configuration
during rendering in the backend or frontend, which ensures the greatest possible
flexibility. It is possible to choose whether the icons are to be rendered as a
webfont, sprites, inline SVG or as an SVG image without having to change the
content in the database.

To illustrate this behavior better, here is an example.

Only the following code is always (!) stored in the database (RTE):

```html
<span data-iconfig="fa6:solid,star"></span>
```

How this code is then displayed in the backend and frontend depends on the
configured `renderTypes` and the icons provided by the respective iconpack.
In the case of *Font Awesome 6*, for example, all possible types are provided:
- Webfont
- SVG sprites
- SVG images

The following possibilities are therefore available as `renderTypes`, which
are displayed in BE/FE as follows:

### svgInline
```html
<svg viewBox="0 0 576 512" class="iconpack fas fa-star" fill="currentColor" aria-hidden="true">
	<path d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z"></path>
</svg>
```

### svgSprite
```html
<svg class="iconpack fas fa-star" fill="currentColor" viewBox="0 0 576 512" aria-hidden="true">
	<use href="/_assets/IDENTIFIER/Vendor/fontawesome-free-6.7.2-web/sprites/solid.svg#star"></use>
</svg>
```

### webfont
```html
<span class="iconpack fas fa-star" aria-hidden="true"></span>
```

### svg
```html
<img class="iconpack fas fa-star" src="/_assets/IDENTIFIER/Vendor/fontawesome-free-6.7.2-web/svgs/solid/star.svg" loading="lazy" aria-hidden="true" alt="">
```

The converters included in this extension ensure that only the most necessary
code is stored in the database, and the output in BE/FE always corresponds to
the configuration and requirements.

Furthermore, this extension adds additional icon fields for pages and text
headers, but it can also be used to extend fields of your own extensions.

> Note: This extension does **NOT** have the same approach as the TYPO3
  integrated `IconRegistry` with its approach to cache all icons including their
  HTML markup for the backend and consider them as absolute, but focuses on
  **dynamically handling icons for editors and frontend output**!


## Installation

1. Install this extension from TER or with Composer
2. Install one of the existing iconpack providers:
    - [Bootstrap](https://github.com/quellenform/t3x-iconpack-bootstrap)
    - [Boxicons](https://github.com/quellenform/t3x-iconpack-boxicons)
    - [Dripicons](https://github.com/quellenform/t3x-iconpack-dripicons)
    - [Elegant Icons](https://github.com/quellenform/t3x-iconpack-elegant)
    - [Feather Icons](https://github.com/quellenform/t3x-iconpack-feather)
    - [Flag Icons](https://github.com/CMS-Internetsolutions/iconpack_flagicons)
    - [Font Awesome](https://github.com/quellenform/t3x-iconpack-fontawesome)
    - [Helium Icons](https://github.com/quellenform/t3x-iconpack-helium)
    - [Iconoir Icon](https://github.com/quellenform/t3x-iconpack-iconoir)
    - [Ikons vector icons](https://github.com/quellenform/t3x-iconpack-ikons)
    - [Ionicons](https://github.com/quellenform/t3x-iconpack-ionicons)
    - [Linea Icons](https://github.com/quellenform/t3x-iconpack-linea)
    - [Linearicons](https://github.com/quellenform/t3x-iconpack-linearicons)
    - [Lineicons](https://github.com/quellenform/t3x-iconpack-lineicons)
    - [Material Icons](https://github.com/quellenform/t3x-iconpack-material)
    - [Octicons](https://github.com/quellenform/t3x-iconpack-octicons)
    - [Tabler Icons](https://github.com/quellenform/t3x-iconpack-tabler-icons)
    - [Themify Icons](https://github.com/quellenform/t3x-iconpack-themify)
    - [TYPO3 Icons](https://github.com/quellenform/t3x-iconpack-typo3)
    - ...or create your own iconpack provider
3. Add the provided TypoScript to your template
4. [Optional] Install the [Iconpack for Bootstrap
   Package](https://github.com/quellenform/t3x-bootstrap-package-iconpack)
   extension if you want to use iconpacks in conjunction with [Bootstrap
   Package](https://github.com/benjaminkott/bootstrap_package/).


## Configuration

You can find a detailed description of the extension here: [Iconpack
Documentation](https://docs.typo3.org/p/quellenform/t3x-iconpack/main/en-us/)


## Contribute | Say Thanks!

- If you like this extension, **use it**.
- If you think you can do something better, be so kind and **contribute your
  part** to it
- If you love the extension or if you want to support further development,
  [donate an amount of your choice](https://www.paypal.me/quellenform)

## Credits

I would like to thank everyone who contributed in any way to the creation of this extension, but especially:

- [easyTherm Infrarotheizung](https://www.easy-therm.com/ "easyTherm Infrarotheizung – Effiziente Heizungssysteme")
- Benjamin Mack
- Werner Groß
- Kurt Dirnbauer
- All [contributors](https://github.com/quellenform/t3x-iconpack/graphs/contributors)
