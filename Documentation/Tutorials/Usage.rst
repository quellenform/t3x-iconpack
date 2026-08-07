.. include:: /Includes.rst.txt

.. _usage:

=====================
Use in own extensions
=====================


You can also use Iconpack in your own extensions and add the wizard to your own
database fields as well as the RTE.


Native Fields
=============

The wizard for adding icons can be used arbitrarily in own database fields. To
do this, simply assign the value `IconpackWizard` to the `renderType` of the
corresponding field.

Here is an example with `/Configuration/TCA/Overrides/tt_content.php`:

.. code-block:: php

   \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
      'tt_content', [
         'my_custom_field' => [
            'label' => 'My Label',
            'config' => [
               'type' => 'user',
               'renderType' => 'IconpackWizard'
            ]
         ]
      ]
   );

You can also customize the input field to suit your needs and change the button however you like.
Something like this:

.. code-block:: php

   \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
      'tt_content', [
         'my_custom_field' => [
            'label' => 'My Label',
            'config' => [
               'type' => 'user',
               'renderType' => 'IconpackWizard'
               'formElementStyle' => 'iconInputButton', // See extension configuration
               'buttonIcon' => 'ext-iconpack', // Use 'false' to hide the button icon completely
               'buttonLabel' => 'LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:js.label.iconNative',
               'buttonTooltip' => 'My Custom Button Tooltip',
            ]
         ]
      ]
   );


RTE Fields
==========

If you want to use Iconpack in your own RTE fields, the configuration is done
automatically in the TypoScript setup, where `lib.parseFunc_RTE.nonTypoTagUserFunc`
performs the transformation of the icons.


Using DataProcessor
-------------------

If this is not desired or possible, the content can optionally be preprocessed in
the field via a DataProcessor.

Use the following TypoScript to add the DataProcessor to your own RTE field:

.. code-block:: typoscript

   # Set templates and dataProcessing
   lib.contentElement {
      dataProcessing {
         # This is required to render icons in RTE fields!
         # The output is controlled exclusively by the DataProcessor and then cleaned up by the Sanitizer.
         [number] = Quellenform\Iconpack\DataProcessing\IconpackProcessor
         [number] {
            fieldName = myCustomRteField
            fieldType = rte
         }
      }
   }

Note, however, that for this to work, all content must also be accepted by the HTMLparser, which
is why — depending on the requirements or desired output — additional TypoScript may be necessary.

For example, if you need to output inline SVG, you may need the following TypoScript code:

.. code-block:: typoscript

   # Here all tags are defined, which are NOT removed by the parser in principle.
   # Without this instruction the content from the database will not be displayed in the frontend when using DataProcessor!
   # If you use your own configuration, make sure that this statement is included at the end, or is not overwritten by other statements.
   # Note: Even if you allow certain tags here, the sanitizer still decides whether these tags actually end up in the frontend or not!
   lib.parseFunc_RTE.allowTags := addToList(icon, svg, use, g, line, path, polyline, polygon, rect, circle, ellipse, image, desc, defs, linearGradient, radialGradient, stop)

If you don't use inline SVG, but only SVG sprites, you can also reduce this to the following statement:

.. code-block:: typoscript

   lib.parseFunc_RTE.allowTags := addToList(svg, use)

If you want to additionally restrict various attributes in SVG elements, use the following TypoScript:

.. code-block:: typoscript

   lib.parseFunc_RTE.nonTypoTagStdWrap.HTMLparser.tags.svg.allowedAttribs = id, name, class, style, fill, viewBox, xmlns, width, height, role, aria-hidden


Fluid Template
==============

Icons can be inserted directly from a fluid template using the provided
ViewHelper. All that needs to be done is to add the namespace
`http://typo3.org/ns/Quellenform/Iconpack/ViewHelpers` and a corresponding
*iconfig* string. Optionally `additionalAttributes`, `preferredRenderTypes` and
`style` can be used.

.. code-block:: html

   <html xmlns:i="http://typo3.org/ns/Quellenform/Iconpack/ViewHelpers" data-namespace-typo3-fluid="true">
      <i:icon iconfig="{headerIcon}" preferredRenderTypes="svgInline,webfont" />
   </html>

You can also call up a specific icon directly from Fluid:

.. code-block:: html

   <i:icon iconfig="fa7:brands,youtube" additionalAttributes="{class:'social'}" />
