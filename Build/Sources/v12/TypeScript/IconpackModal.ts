/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

import DeferredAction from '@typo3/backend/action-button/deferred-action.js';
import Modal from '@typo3/backend/modal.js';
import Iconpack from '@quellenform/iconpack.js';

interface UrlParams {
  fieldType: string,
  iconfigString: string,
}

/**
 * Module: @quellenform/iconpack-modal.js
 * This module is used for the Iconpack modal
 * @exports @quellenform/iconpack-modal.js
 */
class IconpackModal {

  public openIconpackModal(
    modalTitle: string,
    urlParams: UrlParams,
    callbackAddIcon: CallableFunction,
    callbackCearIcon: CallableFunction
  ) {
    console.groupCollapsed('IconpackModal::openIconpackModal()'); //? DEBUG GROUP

    // Set fieldType
    const fieldType: string = urlParams.fieldType ? urlParams.fieldType : 'native';
    // Set iconfig
    const iconfigString: string | null = urlParams.iconfigString ? urlParams.iconfigString : null;
    // Set Ajax-URL
    let url = TYPO3.settings.ajaxUrls.iconpack_modal + '&fieldType=' + fieldType;

    // Set the modal buttons
    let buttons = [{
      text: TYPO3.lang['js.button.cancel'] || 'Cancel',
      active: true,
      name: 'cancel',
      trigger: function () {
        Modal.dismiss();
      }
    },
    {
      text: TYPO3.lang['js.button.ok'] || 'OK',
      btnClass: 'btn-success',
      name: 'ok',
      action: new DeferredAction((() => {
        const iconfigStringNew = Iconpack.convertIconfigToString(Iconpack.iconfig);
        if (iconfigStringNew === null) {
          console.log('Ⓘ No icon chosen, closing modal...'); //# DEBUG MESSAGE
        } else {
          if (iconfigStringNew !== iconfigString) {
            console.log('Ⓘ Icon has been changed...'); //# DEBUG MESSAGE
            Iconpack.getIconpackIcon(
              TYPO3.settings.ajaxUrls.iconpack_icon,
              callbackAddIcon,
              iconfigStringNew,
              true
            );
          } else {
            console.log('Ⓘ Icon has NOT been changed, keeping old...'); //# DEBUG MESSAGE
          }
        }
      }))
    }
    ];

    if (iconfigString) {
      url += '&iconfig=' + encodeURIComponent(iconfigString);
      // Show button to clear the icon element only if an icon is currently selected
      buttons.unshift({
        text: TYPO3.lang['js.button.clear'] || 'Clear',
        btnClass: 'btn-warning',
        name: 'clear',
        action: new DeferredAction((() => {
          callbackCearIcon();
        }))
      });
    }

    // Create modal
    const modal = Modal.advanced({
      type: Modal.types.ajax,
      title: modalTitle,
      content: url,
      buttons: buttons,
      size: Modal.sizes.large,
      additionalCssClasses: ['modal-iconpack'],
      callback: (currentModal: HTMLElement) => {
        console.log('⮐ Modal.advanced.callback()'); //# DEBUG MESSAGE
        Iconpack.initialize(currentModal, iconfigString, fieldType);
      },
      ajaxCallback: () => {
        console.log('⮐ Modal.advanced.ajaxCallback()'); //# DEBUG MESSAGE
        Iconpack.initializeContent();
      }
    });
    // Add eventlistener for v12+
    modal.addEventListener('typo3-modal-hidden', () => {
      console.log('⭘ TRIGGER: Modal has been closed'); //# DEBUG MESSAGE
      Iconpack.unlinkCSS();
    });

    console.groupEnd(); //? DEBUG GROUP
  }
}

const iconpackModal = new IconpackModal;
export default iconpackModal;
