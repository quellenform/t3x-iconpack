define([
  'jquery',
  'TYPO3/CMS/Iconpack/Backend/Iconpack'
], function($, i) {
  'use strict';
    $(document).on('click', '.iconpack-form-icon', function(evt) {
      evt.preventDefault();
      i.showFieldIconModal($(this));
    });
});
