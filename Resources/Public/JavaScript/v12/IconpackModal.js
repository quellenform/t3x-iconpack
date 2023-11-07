/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
import n from'@typo3/backend/action-button/deferred-action.js';import t from'@typo3/backend/modal.js';import e from'@quellenform/iconpack.js';class a{openIconpackModal(a,i,o,c){const l=i.fieldType?i.fieldType:'native',s=i.iconfigString?i.iconfigString:null;let r=TYPO3.settings.ajaxUrls.iconpack_modal+'&fieldType='+l,d=[{text:TYPO3.lang['js.button.cancel']||'Cancel',active:!0,name:'cancel',trigger:function(){t.dismiss()}},{text:TYPO3.lang['js.button.ok']||'OK',btnClass:'btn-success',name:'ok',action:new n(()=>{const n=e.convertIconfigToString(e.iconfig);null===n||n!==s&&e.getIconpackIcon(TYPO3.settings.ajaxUrls.iconpack_icon,o,n,!0)})}];s&&(r+='&iconfig='+encodeURIComponent(s),d.unshift({text:TYPO3.lang['js.button.clear']||'Clear',btnClass:'btn-warning',name:'clear',action:new n(()=>{c()})})),t.advanced({type:t.types.ajax,title:a,content:r,buttons:d,size:t.sizes.large,additionalCssClasses:['modal-iconpack'],callback:n=>{e.initialize(n,s,l)},ajaxCallback:()=>{e.initializeContent()}}).addEventListener('typo3-modal-hidden',()=>{e.unlinkCSS()})}}const i=new a;export default i;
