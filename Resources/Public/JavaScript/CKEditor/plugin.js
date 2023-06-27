/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

'use strict';

(function() {
  // Do not remove empty SPAN-tags
  CKEDITOR.dtd.$removeEmpty.span = 0;
  // Add plugin Iconpack
  CKEDITOR.plugins.add('iconpack', {
    icons: 'iconpack',
    init: function(editor) {
      editor.addCommand('iconpack', {
        exec: openIconpackModal,
      });
      editor.ui.addButton('Iconpack', {
        label: TYPO3.lang['js.label.iconRte'],
        command: 'iconpack',
        toolbar: 'insert',
        icon: this.path + 'icons/iconpack.png',
      });
    }
  });

  /**
   * Load the iconpack Modal
   *
   * @param {Object} editor - The CKEditor instance
   */
  function openIconpackModal(editor) {
    require([
      'TYPO3/CMS/Iconpack/Backend/Iconpack'
    ], function(Iconpack) {
      Iconpack.showCkeditorModal(editor);
    });
  }
})();
