/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */
'use strict';(function(){function t(t){return!(!t||!t.hasClass('cke_widget_iconpack'))}function n(t){let n=null;const e=t.getFirst();var o;return o=e,o&&c.includes(o.getName())&&o.getAttribute(i)&&(n=e.getAttribute(i)),n}function e(t,n){const e=require('TYPO3/CMS/Iconpack/v11/IconpackModal');e.openIconpackModal(TYPO3.lang['js.label.iconRte'],{fieldType:'rte',iconfigString:n},function(n,e){e&&t.insertHtml(e)},function(){t.insertHtml('')})}const i='data-iconfig',c=['span','svg','img'];CKEDITOR.dtd.$removeEmpty.span=null,CKEDITOR.plugins.add('iconpack',{lang:'en,de',requires:'widget',icons:'iconpack',hidpi:!0,init:function(o){const s='!'+i+',id,name,class,style';let l=[],a=[];l.push('span['+s+',alt,title]'),l.push('svg['+s+',viewbox,fill]'),l.push('img['+s+',alt,title,src]'),a.push('span['+i+']'),a.push('svg['+i+']'),a.push('img['+i+']'),l=l.join(';'),a=a.join(';'),o.widgets.add('iconpack',{button:o.lang.iconpack.toolbar,allowedContent:l,requiredContent:a,inline:!0,upcast:function(t){let n=i in t.attributes;if(c.includes(t.name)&&n){const n=t.getFirst(null);return'span'==t.name?t.setHtml(''):'svg'==t.name&&n&&'use'===n.name&&n.setHtml(''),!0}return!1},downcast:function(t){'span'==t.name&&t.setHtml('')}}),o.addCommand('iconpack',{exec:i=>{let c=null;const o=i.getSelection().getSelectedElement();return t(o)&&(c=n(o)),e(i,c),!0},allowedContent:l,requiredContent:a}),o.on('doubleclick',i=>{const c=o.getSelection().getSelectedElement();if(t(c)){const t=n(c);i.stop(),e(o,t)}})}})})();
