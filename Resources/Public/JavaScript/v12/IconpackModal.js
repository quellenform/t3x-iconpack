/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
import DeferredAction from'@typo3/backend/action-button/deferred-action.js';import Modal from'@typo3/backend/modal.js';import Iconpack from'@quellenform/iconpack.js';class IconpackModal{openIconpackModal(n,t,a){const e=n.fieldType?n.fieldType:'native',o=n.iconfigString?n.iconfigString:null;let c,i=TYPO3.settings.ajaxUrls.iconpack_modal+'&fieldType='+e;c='rte'===e?this.translate('js.label.iconRte','Insert Icon'):this.translate('js.label.iconNative','Choose Icon');let l=[{text:this.translate('js.button.cancel','Cancel'),active:!0,name:'cancel',trigger:function(){Modal.dismiss()}},{text:this.translate('js.button.ok','OK'),btnClass:'btn-success',name:'ok',action:new DeferredAction(()=>{const n=Iconpack.convertIconfigToString(Iconpack.iconfig);null===n||n!==o&&Iconpack.getIconpackIcon(TYPO3.settings.ajaxUrls.iconpack_icon,t,n,!0)})}];o&&(i+='&iconfig='+encodeURIComponent(o),l.unshift({text:this.translate('js.button.clear','Clear'),btnClass:'btn-warning',name:'clear',action:new DeferredAction(()=>{a()})})),Modal.advanced({type:Modal.types.ajax,title:c,content:i,buttons:l,size:Modal.sizes.large,additionalCssClasses:['modal-iconpack'],callback:n=>{Iconpack.initialize(n,o,e)},ajaxCallback:()=>{Iconpack.initializeContent()}}).addEventListener('typo3-modal-hidden',()=>{Iconpack.unlinkCSS()})}translate(n,t){return'undefined'!=typeof TYPO3&&TYPO3.lang&&'string'==typeof TYPO3.lang[n]?TYPO3.lang[n]:t||''}}const iconpackModal=new IconpackModal;export default iconpackModal;
