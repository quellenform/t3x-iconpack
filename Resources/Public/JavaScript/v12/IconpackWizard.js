/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
import DocumentService from'@typo3/core/document-service.js';import FormEngine from'@typo3/backend/form-engine.js';import FormEngineValidation from'@typo3/backend/form-engine-validation.js';import IconpackModal from'@quellenform/iconpack-modal.js';var Selectors;(function(e){e.palette='.t3js-formengine-field-item',e.iconField='.t3js-icon'})(Selectors||(Selectors={}));class IconpackWizard{constructor(e){this.controlElement=null,this.palette=null,this.formengineInput=null,this.iconField=null,this.itemName=null,this.handleControlClick=(e=>{e.preventDefault(),IconpackModal.openIconpackModal(TYPO3.lang['js.label.iconNative'],{fieldType:'native',iconfigString:FormEngine.getFieldElement(this.itemName).val()},this.addIconToField.bind(this),this.clearIconField.bind(this))}),DocumentService.ready().then(()=>{this.controlElement=document.querySelector(e);this.controlElement.getAttribute('id');this.itemName=this.controlElement.dataset.itemName,this.palette=this.controlElement.closest(Selectors.palette),this.formengineInput=this.palette.querySelector('input[name="'+this.itemName+'"]'),this.iconField=this.controlElement.querySelector(Selectors.iconField),this.controlElement.addEventListener('click',this.handleControlClick)})}addIconToField(e,t){this.formengineInput.value=e,this.iconField.innerHTML=t,FormEngineValidation.markFieldAsChanged(this.palette)}clearIconField(){this.formengineInput.value='',this.iconField.innerHTML='',FormEngineValidation.markFieldAsChanged(this.palette)}}export default IconpackWizard;
