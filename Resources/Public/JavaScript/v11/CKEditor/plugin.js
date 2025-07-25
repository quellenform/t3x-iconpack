/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
'use strict';(function(){const t='data-iconfig',n=['span','svg','img'];function e(t){return!(!t||!t.hasClass('cke_widget_iconpack'))}function i(e){let i=null;const c=e.getFirst();var o;return(o=c)&&n.includes(o.getName())&&o.getAttribute(t)&&(i=c.getAttribute(t)),i}function c(t,n){require('TYPO3/CMS/Iconpack/v11/IconpackModal').openIconpackModal(TYPO3.lang['js.label.iconRte'],{fieldType:'rte',iconfigString:n},function(n,e){e&&t.insertHtml(e)},function(){t.insertHtml('')})}CKEDITOR.dtd.$removeEmpty.span=null,CKEDITOR.plugins.add('iconpack',{lang:'en,de,da',requires:'widget',icons:'iconpack',hidpi:!0,init:function(o){const l='!'+t+',id,name,class,style';let s=[],a=[];s.push('span['+l+',alt,title]'),s.push('svg['+l+',viewbox,fill]'),s.push('img['+l+',alt,title,src]'),a.push('span['+t+']'),a.push('svg['+t+']'),a.push('img['+t+']'),s=s.join(';'),a=a.join(';'),o.widgets.add('iconpack',{button:o.lang.iconpack.toolbar,allowedContent:s,requiredContent:a,inline:!0,upcast:function(e){let i=t in e.attributes;if(n.includes(e.name)&&i){const t=e.getFirst(null);return'span'==e.name?e.setHtml(''):'svg'==e.name&&t&&'use'===t.name&&t.setHtml(''),!0}return!1},downcast:function(t){'span'==t.name&&t.setHtml('')}}),o.addCommand('iconpack',{exec:t=>{let n=null;const o=t.getSelection().getSelectedElement();return e(o)&&(n=i(o)),c(t,n),!0},allowedContent:s,requiredContent:a}),o.on('doubleclick',t=>{const n=o.getSelection().getSelectedElement();if(e(n)){const e=i(n);t.stop(),c(o,e)}})}})})();
