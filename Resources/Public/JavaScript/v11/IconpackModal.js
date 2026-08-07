/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
define(['require','exports','TYPO3/CMS/Backend/ActionButton/DeferredAction','TYPO3/CMS/Backend/Modal','TYPO3/CMS/Iconpack/v11/Iconpack'],function(n,e,t,a,c){'use strict';return new class{openIconpackModal(n,e,i,o){const s=e.fieldType?e.fieldType:'native',l=e.iconfigString?e.iconfigString:null;let r=TYPO3.settings.ajaxUrls.iconpack_modal+'&fieldType='+s,d=[{text:TYPO3.lang['js.button.cancel']||'Cancel',active:!0,name:'cancel',trigger:function(){a.dismiss()}},{text:TYPO3.lang['js.button.ok']||'OK',btnClass:'btn-success',name:'ok',action:new t(()=>{const n=c.convertIconfigToString(c.iconfig);null===n||n!==l&&c.getIconpackIcon(TYPO3.settings.ajaxUrls.iconpack_icon,i,n,!0)})}];l&&(r+='&iconfig='+encodeURIComponent(l),d.unshift({text:TYPO3.lang['js.button.clear']||'Clear',btnClass:'btn-warning',name:'clear',action:new t(()=>{o()})})),a.advanced({type:a.types.ajax,title:n,content:r,buttons:d,size:a.sizes.large,additionalCssClasses:['modal-iconpack'],callback:n=>{const e=n[0];c.initialize(e,l,s)},ajaxCallback:()=>{c.initializeContent()}}).on('modal-destroyed',()=>{c.unlinkCSS()})}}});
