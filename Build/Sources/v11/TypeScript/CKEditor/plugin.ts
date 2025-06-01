/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

'use strict';
(function () {
  // Set iconfig attribute
  const iconfigAttribute = 'data-iconfig';
  // Set iconfig tag names
  const iconfigTagNames = ['span', 'svg', 'img'];
  // Do not remove empty SPAN-tags
  CKEDITOR.dtd.$removeEmpty.span = null;
  // Add plugin Iconpack
  CKEDITOR.plugins.add('iconpack', {

    lang: 'en,de,da',
    requires: 'widget',
    icons: 'iconpack',
    hidpi: true,

    init: function (editor: CKEDITOR.editor) {

      const defaultAttributes = '!' + iconfigAttribute + ',id,name,class,style';
      let allowedContent: string[] | string = [];
      let requiredContent: string[] | string = [];

      // webfont: Allow <span> tags
      allowedContent.push('span[' + defaultAttributes + ',alt,title]');
      // svgSprite: Allow SVG (inline, sprites)
      allowedContent.push('svg[' + defaultAttributes + ',viewbox,fill]');
      // image: Allow svg images
      allowedContent.push('img[' + defaultAttributes + ',alt,title,src]');

      requiredContent.push('span[' + iconfigAttribute + ']');
      requiredContent.push('svg[' + iconfigAttribute + ']');
      requiredContent.push('img[' + iconfigAttribute + ']');
      allowedContent = allowedContent.join(';');
      requiredContent = requiredContent.join(';');

      editor.widgets.add('iconpack', {
        button: editor.lang.iconpack.toolbar,
        allowedContent: allowedContent,
        requiredContent: requiredContent,
        inline: true,
        upcast: function (element: CKEDITOR.htmlParser.element) {
          let hasDataIconfig = iconfigAttribute in element.attributes;
          if (iconfigTagNames.includes(element.name) && hasDataIconfig) {
            const firstElement = <CKEDITOR.htmlParser.element>element.getFirst(null);
            // Remove inner HTML
            if (element.name == 'span') {
              element.setHtml('');
            } else if (element.name == 'svg' && firstElement && firstElement.name === 'use') {
              firstElement.setHtml('');
            }
            return true;
          }
          return false;
        },
        downcast: function (element) {
          if (element.name == 'span') {
            element.setHtml('');
          }
        }
      });

      // Add command
      editor.addCommand('iconpack', {
        exec: (editor) => {
          console.log('⭘ CKEditor: Toolbar button has been clicked!'); //# DEBUG MESSAGE
          let iconfigString = null;
          const element = editor.getSelection().getSelectedElement();
          if (isIconpackElement(element)) {
            iconfigString = getIconfigAttributeFromSelection(element);
          }
          openIconpackModal(editor, iconfigString);
          return true;
        },
        allowedContent: allowedContent,
        requiredContent: requiredContent
      });

      // Override doubleclick opening iconpack modal
      editor.on('doubleclick', (event) => {
        const element: CKEDITOR.dom.element = editor.getSelection().getSelectedElement();
        if (isIconpackElement(element)) {
          console.log('⭘ CKEditor: Iconpack element has been clicked!'); //# DEBUG MESSAGE

          const iconfigString = getIconfigAttributeFromSelection(element);
          event.stop();
          openIconpackModal(editor, iconfigString);
        }
      });
    }
  });

  /**
   * Check if the currently selected element is an iconpack widget.
   */
  function isIconpackElement(element: CKEDITOR.dom.element) {
    if (element && element.hasClass('cke_widget_iconpack')) {
      return true;
    }
    return false;
  }

  /**
   * Get the data attribute from the currently selected element.
   */
  function getIconfigAttributeFromSelection(element: CKEDITOR.dom.element) {
    let attribute = null;
    const iconElement = <CKEDITOR.dom.element>element.getFirst();
    if (checkIfSelectedElementHasIcon(iconElement)) {
      // Get the iconfig string from data-attribute
      attribute = iconElement.getAttribute(iconfigAttribute);
    }
    return attribute;
  }

  /**
   * Check if the selected element is an icon element.
   */
  function checkIfSelectedElementHasIcon(selection: CKEDITOR.dom.element) {
    if (selection && iconfigTagNames.includes(selection.getName()) && selection.getAttribute(iconfigAttribute)) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Open iconpack modal
   */
  function openIconpackModal(editor: CKEDITOR.editor, iconfigString: string) {
    require([
      'TYPO3/CMS/Iconpack/v11/IconpackModal'
    ], function (IconpackModal) {
      IconpackModal.openIconpackModal(TYPO3.lang['js.label.iconRte'], {
        fieldType: 'rte',
        iconfigString: iconfigString
      }, addIconToRte, removeIconFromRte);
    });

    /**
     * Add icon to the initiating RTE.
     */
    function addIconToRte(iconfigString: string, iconMarkup: string) {
      console.log('⮜ CKEditor: Add icon to RTE'); //# DEBUG MESSAGE

      if (iconMarkup) {
        editor.insertHtml(iconMarkup);
      }
    }

    /**
     * Remove icon from the initiating RTE.
     */
    function removeIconFromRte() {
      console.log('⮜ CKEditor: Remove icon from RTE'); //# DEBUG MESSAGE

      editor.insertHtml('');
    }
  }
})();
