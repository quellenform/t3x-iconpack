/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
define(['require','exports','TYPO3/CMS/Core/DocumentService','TYPO3/CMS/Backend/FormEngine','TYPO3/CMS/Backend/FormEngineValidation','TYPO3/CMS/Iconpack/v11/IconpackModal'],(function(e,t,i,n,l,o){'use strict';var c;!function(e){e.palette='.t3js-formengine-field-item',e.iconField='.t3js-icon'}(c||(c={}));return class{constructor(e){this.controlElement=null,this.palette=null,this.formengineInput=null,this.iconField=null,this.itemName=null,this.handleControlClick=e=>{e.preventDefault(),o.openIconpackModal(TYPO3.lang['js.label.iconNative'],{fieldType:'native',iconfigString:n.getFieldElement(this.itemName).val()},this.addIconToField.bind(this),this.clearIconField.bind(this))},i.ready().then(()=>{this.controlElement=document.querySelector(e);this.controlElement.getAttribute('id');this.itemName=this.controlElement.dataset.itemName,this.palette=this.controlElement.closest(c.palette),this.formengineInput=this.palette.querySelector('input[name="'+this.itemName+'"]'),this.iconField=this.controlElement.querySelector(c.iconField),this.controlElement.addEventListener('click',this.handleControlClick)})}addIconToField(e,t){this.formengineInput.value=e,this.iconField.innerHTML=t,l.markFieldAsChanged(this.palette)}clearIconField(){this.formengineInput.value='',this.iconField.innerHTML='',l.markFieldAsChanged(this.palette)}}}));
