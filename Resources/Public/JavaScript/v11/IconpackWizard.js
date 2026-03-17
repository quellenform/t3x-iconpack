/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
define(['require','exports','TYPO3/CMS/Core/DocumentService','TYPO3/CMS/Backend/FormEngine','TYPO3/CMS/Backend/FormEngineValidation','TYPO3/CMS/Iconpack/v11/IconpackModal'],function(e,t,n,i,l,o){'use strict';var c;(function(e){e.palette='.t3js-formengine-field-item',e.iconField='.input-group-icon .icon-markup'})(c||(c={}));return class{constructor(e){this.handleControlClick=(e=>{e.preventDefault(),o.openIconpackModal(TYPO3.lang['js.label.iconNative'],{fieldType:'native',iconfigString:i.getFieldElement(this.itemName).val()},this.addIconToField.bind(this),this.clearIconField.bind(this))}),n.ready().then(()=>{this.controlElement=document.querySelector('#formengine-button-'+e),this.itemName=this.controlElement.dataset.itemName,this.palette=this.controlElement.closest(c.palette),this.formengineInput=this.palette.querySelector('#formengine-input-'+e),this.hiddenInput=this.palette.querySelector('input[name="'+this.itemName+'"]'),this.iconField=this.palette.querySelector(c.iconField),this.controlElement.addEventListener('click',this.handleControlClick)})}addIconToField(e,t){this.changeIconfigValue(e),this.iconField.innerHTML=t}clearIconField(){this.changeIconfigValue(''),this.iconField.innerHTML=''}changeIconfigValue(e){null!==this.formengineInput&&(this.formengineInput.value=e),null!==this.hiddenInput&&(this.hiddenInput.value=e),l.markFieldAsChanged(this.palette)}}});
