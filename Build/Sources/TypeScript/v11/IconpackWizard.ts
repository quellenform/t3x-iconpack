/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

import DocumentService = require('TYPO3/CMS/Core/DocumentService');
import FormEngine = require('TYPO3/CMS/Backend/FormEngine');
import FormEngineValidation = require('TYPO3/CMS/Backend/FormEngineValidation');
import IconpackModal = require('TYPO3/CMS/Iconpack/v11/IconpackModal');

// Define selector names
enum Selectors {
  palette = '.t3js-formengine-field-item',
  iconField = '.input-group-icon .icon-markup'
}

/**
 * Module: TYPO3/CMS/Iconpack/v11/IconpackWizard
 * This module is used for the Iconpack wizard
 * @exports TYPO3/CMS/Iconpack/v11/IconpackWizard
 */
class IconpackWizard {

  // The Iconpack wizard-button
  private controlElement?: HTMLElement | null;
  // The form palette which contains the form elements
  private palette?: HTMLElement | null;
  // The visible input field for the iconfig string
  private formengineInput?: HTMLInputElement | null;
  // The hidden input field for the iconfig string
  private hiddenInput?: HTMLInputElement | null;
  // The icon field which displays the final icon
  private iconField?: HTMLElement | null;
  // The name of the input field
  private itemName?: string | null;


  constructor(controlElementId: string) {
    DocumentService.ready().then((): void => {

      console.groupCollapsed('⏻ IconpackWizard has been initialized'); //? DEBUG GROUP

      this.controlElement = <HTMLElement>document.querySelector('#formengine-button-' + controlElementId);
      console.debug('controlElement:', this.controlElement); //! DEBUG VALUE

      this.itemName = this.controlElement.dataset.itemName;
      console.debug('itemName:', this.itemName); //! DEBUG VALUE

      this.palette = this.controlElement.closest(Selectors.palette);
      console.debug('palette:', this.palette); //! DEBUG VALUE

      this.formengineInput = this.palette!.querySelector('#formengine-input-' + controlElementId);
      console.debug('formengineInput:', this.formengineInput); //! DEBUG VALUE

      this.hiddenInput = this.palette!.querySelector('input[name="' + this.itemName + '"]');
      console.debug('hiddenInput:', this.hiddenInput); //! DEBUG VALUE

      this.iconField = this.palette!.querySelector(Selectors.iconField);
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

    this.changeIconfigValue(iconfigString);
    this.iconField!.innerHTML = iconMarkup;
  }

  /**
   * Clear icon field.
   */
  clearIconField(): void {
    console.log('⮜ IconpackWizard: Icon has been removed'); //# DEBUG MESSAGE

    this.changeIconfigValue('');
    this.iconField!.innerHTML = '';
  }

  /**
   * Change iconfig value in input fields.
   */
  changeIconfigValue(iconfigString: string): void {
    if (this.formengineInput !== null) {
      this.formengineInput!.value = iconfigString;
    }
    if (this.hiddenInput !== null) {
      this.hiddenInput!.value = iconfigString;
    }
    FormEngineValidation.markFieldAsChanged(this.palette);
  }
}

export = IconpackWizard;
