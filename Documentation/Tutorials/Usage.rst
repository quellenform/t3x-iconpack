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



RTE Fields
==========

If you want to use Iconpack in your own RTE fields, the configuration is done
via TypoScript in `setup.txt`, which activates the transformation of the icons
via a DataProcessor during output in the frontend.

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

   <i:icon iconfig="fa6:brands,youtube" additionalAttributes="{class:'social'}" />
