/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

import DocumentService from '@typo3/core/document-service.js';
import FormEngine from '@typo3/backend/form-engine.js';
import FormEngineValidation from '@typo3/backend/form-engine-validation.js';
import IconpackModal from '@quellenform/iconpack-modal.js';

// Define selector names
enum Selectors {
  palette = '.t3js-formengine-field-item',
  iconField = '.t3js-icon',
}

/**
 * Module: @quellenform/iconpack-wizard.js
 * This module is used for the Iconpack wizard
 * @exports @quellenform/iconpack-wizard.js
 */
class IconpackWizard {

  // The Iconpack wizard-button
  private controlElement: HTMLElement = null;
  // The form palette which contains the form elements
  private palette: HTMLElement = null;
  // The input field for the icon
  private formengineInput: HTMLInputElement = null;
  // The icon field which displays the final icon
  private iconField: HTMLElement = null;
  // The name of the input field
  private itemName: string = null;


  constructor(controlElementId: string) {
    DocumentService.ready().then((): void => {

      console.groupCollapsed('⏻ IconpackWizard has been initialized'); //? DEBUG GROUP

      this.controlElement = <HTMLElement>document.querySelector(controlElementId);
      console.debug('controlElement:', this.controlElement); //! DEBUG VALUE

      let elementId = this.controlElement.getAttribute('id');
      console.debug('elementId:', elementId); //! DEBUG VALUE

      this.itemName = this.controlElement.dataset.itemName;
      console.debug('itemName:', this.itemName); //! DEBUG VALUE

      this.palette = this.controlElement.closest(Selectors.palette);
      console.debug('palette:', this.palette); //! DEBUG VALUE

      this.formengineInput = this.palette.querySelector('input[name="' + this.itemName + '"]');
      console.debug('formengineInput:', this.formengineInput); //! DEBUG VALUE

      this.iconField = this.controlElement.querySelector(Selectors.iconField);
      console.debug('iconField:', this.iconField); //! DEBUG VALUE

      this.controlElement.addEventListener('click', this.handleControlClick);

      console.groupEnd(); //? DEBUG GROUP
    });
  }

  /**
   * @param {Event} e
   */
  private readonly handleControlClick = (e: Event): void => {
    console.log('⭘ IconpackWizard TRIGGER: Button has been clicked!'); //# DEBUG MESSAGE

    e.preventDefault();

    IconpackModal.openIconpackModal(
      TYPO3.lang['js.label.iconNative'],
      {
        fieldType: 'native',
        iconfigString: FormEngine.getFieldElement(this.itemName).val()
      },
      this.addIconToField.bind(this),
      this.clearIconField.bind(this)
    );
  }

  /**
   * Add icon to the initiating form field.
   */
  addIconToField(iconfigString: string, iconMarkup: string): void {
    console.log('⮜ IconpackWizard: Add icon to field'); //# DEBUG MESSAGE

    this.formengineInput.value = iconfigString;
    this.iconField.innerHTML = iconMarkup;
    FormEngineValidation.markFieldAsChanged(this.palette);
  }

  /**
   * Clear icon field.
   */
  clearIconField(): void {
    console.log('⮜ IconpackWizard: Icon has been removed'); //# DEBUG MESSAGE

    this.formengineInput.value = '';
    this.iconField.innerHTML = '';
    FormEngineValidation.markFieldAsChanged(this.palette);
  }
}

export default IconpackWizard;
