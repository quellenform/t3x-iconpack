/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
define(['require','exports','TYPO3/CMS/Backend/ActionButton/DeferredAction','TYPO3/CMS/Backend/Modal','TYPO3/CMS/Iconpack/v11/Iconpack'],function(n,t,e,a,i){'use strict';return new class{openIconpackModal(n,t,c){const o=n.fieldType?n.fieldType:'native',s=n.iconfigString?n.iconfigString:null;let l,r=TYPO3.settings.ajaxUrls.iconpack_modal+'&fieldType='+o;l='rte'===o?this.translate('js.label.iconRte','Insert Icon'):this.translate('js.label.iconNative','Choose Icon');let d=[{text:this.translate('js.button.cancel','Cancel'),active:!0,name:'cancel',trigger:function(){a.dismiss()}},{text:this.translate('js.button.ok','OK'),btnClass:'btn-success',name:'ok',action:new e(()=>{const n=i.convertIconfigToString(i.iconfig);null===n||n!==s&&i.getIconpackIcon(TYPO3.settings.ajaxUrls.iconpack_icon,t,n,!0)})}];s&&(r+='&iconfig='+encodeURIComponent(s),d.unshift({text:this.translate('js.button.clear','Clear'),btnClass:'btn-warning',name:'clear',action:new e(()=>{c()})})),a.advanced({type:a.types.ajax,title:l,content:r,buttons:d,size:a.sizes.large,additionalCssClasses:['modal-iconpack'],callback:n=>{const t=n[0];i.initialize(t,s,o)},ajaxCallback:()=>{i.initializeContent()}}).on('modal-destroyed',()=>{i.unlinkCSS()})}translate(n,t){return'undefined'!=typeof TYPO3&&TYPO3.lang&&'string'==typeof TYPO3.lang[n]?TYPO3.lang[n]:t||''}}});
