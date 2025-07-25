/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
import DeferredAction from'@typo3/backend/action-button/deferred-action.js';import Modal from'@typo3/backend/modal.js';import Iconpack from'@quellenform/iconpack.js';class IconpackModal{openIconpackModal(n,a,o,c){const e=a.fieldType?a.fieldType:'native',t=a.iconfigString?a.iconfigString:null;let i=TYPO3.settings.ajaxUrls.iconpack_modal+'&fieldType='+e,l=[{text:TYPO3.lang['js.button.cancel']||'Cancel',active:!0,name:'cancel',trigger:function(){Modal.dismiss()}},{text:TYPO3.lang['js.button.ok']||'OK',btnClass:'btn-success',name:'ok',action:new DeferredAction(()=>{const n=Iconpack.convertIconfigToString(Iconpack.iconfig);null===n||n!==t&&Iconpack.getIconpackIcon(TYPO3.settings.ajaxUrls.iconpack_icon,o,n,!0)})}];t&&(i+='&iconfig='+encodeURIComponent(t),l.unshift({text:TYPO3.lang['js.button.clear']||'Clear',btnClass:'btn-warning',name:'clear',action:new DeferredAction(()=>{c()})})),Modal.advanced({type:Modal.types.ajax,title:n,content:i,buttons:l,size:Modal.sizes.large,additionalCssClasses:['modal-iconpack'],callback:n=>{Iconpack.initialize(n,t,e)},ajaxCallback:()=>{Iconpack.initializeContent()}}).addEventListener('typo3-modal-hidden',()=>{Iconpack.unlinkCSS()})}}const iconpackModal=new IconpackModal;export default iconpackModal;
