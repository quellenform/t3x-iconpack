.. include:: /Includes.rst.txt

.. _introduction:

============
Introduction
============

This extension provides an iconpack registry for custom iconpacks which can be
used in backend and frontend and are rendered according to the configuration of
the installed iconpacks.

The idea behind the extension is to provide a flexible system by means of which
any icon sets can be used and the desired output can be generated at any time by
separating storage and rendering.

.. image:: /Images/ScreenCapture.gif



Features
========

The extension `iconpack` is different from previous implementations and will
probably be the only icon extension you will need in the future.

The biggest differences are among others:

- Not limited to a specific iconset! Existing iconpacks can be installed, or
  custom iconpacks can be created, depending on your requirements
- Easy to use: Install, :ref:`add the provided TypoScript-template
  <configuration>` & use it, no further configuration needed (but possible!)
- Offers the use of various icons in the header, page, in the bodytext (CKEditor
  4/5) and in your :ref:`own fields <usage>`
- All required assets (JavaScripts, StyleSheets, etc.) are automatically added
  in frontend and backend by default, depending on the configuration of the icon
  set used
- All settings of an iconpack can be overridden via an individual configuration
  (:ref:`YAML <yamlExamples>`)
- Individual iconsets can be easily added (see instructions for :ref:`creating
  your own iconpack provider <customIconpack>`
- Can also be integrated into :ref:`own extensions <usage>` via the provided form wizard
- Multilingual labels for icons
- Uses the backend caching integrated in TYPO3 for the configuration of the
  installed iconpacks in order not to slow down the backend
- The frontend rendering of icons can be changed afterwards (easily switch from
  Webfont to SVG with TypoScript)
- Works with `EXT:bootstrap_package
  <https://github.com/benjaminkott/bootstrap_package/>`_ and other extensions
- No dependency on other extensions or third-party code!



Planned Features
================

- Enable the use of SVG sprites in CKEditor 5
- Add contextmenu in CKEditor to edit a previously added icon (and options)
- Optimize the UI/Modal
- Add more detailed information on using and creating your own iconpacks

.. rst-class:: horizbuttons-tip-m

- `--> Sponsoring required <-- <https://www.paypal.me/quellenform>`_



How does it work?
=================

Instead of storing the entire HTML markup or a static file path for an icon in
the database fields, a short configuration string is stored instead (also in the
RTE). This string is called "iconfig" and looks something like
`fa6:solid,star,transform:spin`. This example will render the icon *Star* from
the iconpack *Font Awesome 6* (solid style) with the additional property of a
spinnig rotation.

This string is only converted into HTML code according to the configuration
during rendering in the backend or frontend, which ensures the greatest possible
flexibility. It is possible to choose whether the icons are to be rendered as a
webfont, sprites, inline SVG or as an SVG image without having to change the
content in the database.

To illustrate this behavior better, here is an example.

Only the following code is always (!) stored in the database (RTE):

.. code-block:: html

   <span data-iconfig="fa6:solid,star"></span>

How this code is then displayed in the backend and frontend depends on the
configured `renderTypes` and the icons provided by the respective iconpack.
In the case of *Font Awesome 6*, for example, all possible types are provided:
- Webfont
- SVG sprites
- SVG images

The following possibilities are therefore available as `renderTypes`, which
are displayed in BE/FE as follows:

svgInline
---------

.. code-block:: html

   <svg viewBox="0 0 576 512" class="iconpack fas fa-star" fill="currentColor" aria-hidden="true">
	   <path d="M316.9 18C311.6 7 300.4 0 288.1 0s-23.4 7-28.8 18L195 150.3 51.4 171.5c-12 1.8-22 10.2-25.7 21.7s-.7 24.2 7.9 32.7L137.8 329 113.2 474.7c-2 12 3 24.2 12.9 31.3s23 8 33.8 2.3l128.3-68.5 128.3 68.5c10.8 5.7 23.9 4.9 33.8-2.3s14.9-19.3 12.9-31.3L438.5 329 542.7 225.9c8.6-8.5 11.7-21.2 7.9-32.7s-13.7-19.9-25.7-21.7L381.2 150.3 316.9 18z"></path>
   </svg>

svgSprite
---------

.. code-block:: html
   <svg class="iconpack fas fa-star" fill="currentColor" viewBox="0 0 576 512" aria-hidden="true">
	   <use href="/_assets/IDENTIFIER/Vendor/fontawesome-free-6.7.2-web/sprites/solid.svg#star"></use>
   </svg>

webfont
-------

.. code-block:: html

   <span class="iconpack fas fa-star" aria-hidden="true"></span>

svg
---

.. code-block:: html

   <img class="iconpack fas fa-star" src="/_assets/IDENTIFIER/Vendor/fontawesome-free-6.7.2-web/svgs/solid/star.svg" loading="lazy" aria-hidden="true" alt="">

The converters included in this extension ensure that only the most necessary
code is stored in the database, and the output in BE/FE always corresponds to
the configuration and requirements.

Furthermore, this extension adds additional icon fields for pages and text
headers, but it can also be used to extend fields of your own extensions.

.. note::
   This extension does **NOT** have the same approach as the TYPO3 integrated
   `IconRegistry` with its approach to cache all icons including their HTML
   markup for the backend and consider them as absolute, but focuses on
   **dynamically handling icons for editors and frontend output**!

.. rst-class:: horizbuttons-tip-m

- :ref:`Additional Background Information <history>`
