/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
import e from'@typo3/core/document-service.js';import t from'@typo3/backend/form-engine.js';import i from'@typo3/backend/form-engine-validation.js';import n from'@quellenform/iconpack-modal.js';var l;!function(e){e.palette='.t3js-formengine-field-item',e.iconField='.t3js-icon'}(l||(l={}));class o{constructor(i){this.controlElement=null,this.palette=null,this.formengineInput=null,this.iconField=null,this.itemName=null,this.handleControlClick=e=>{e.preventDefault(),n.openIconpackModal(TYPO3.lang['js.label.iconNative'],{fieldType:'native',iconfigString:t.getFieldElement(this.itemName).val()},this.addIconToField.bind(this),this.clearIconField.bind(this))},e.ready().then(()=>{this.controlElement=document.querySelector(i);this.controlElement.getAttribute('id');this.itemName=this.controlElement.dataset.itemName,this.palette=this.controlElement.closest(l.palette),this.formengineInput=this.palette.querySelector('input[name="'+this.itemName+'"]'),this.iconField=this.controlElement.querySelector(l.iconField),this.controlElement.addEventListener('click',this.handleControlClick)})}addIconToField(e,t){this.formengineInput.value=e,this.iconField.innerHTML=t,i.markFieldAsChanged(this.palette)}clearIconField(){this.formengineInput.value='',this.iconField.innerHTML='',i.markFieldAsChanged(this.palette)}}export default o;
