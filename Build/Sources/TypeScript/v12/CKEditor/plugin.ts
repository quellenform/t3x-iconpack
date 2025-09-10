/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

import { Plugin, Command } from '@ckeditor/ckeditor5-core';
import { Widget, toWidget } from '@ckeditor/ckeditor5-widget';
import { ButtonView } from '@ckeditor/ckeditor5-ui';
import { DomEventObserver } from '@ckeditor/ckeditor5-engine';
import IconpackModal from '@quellenform/iconpack-modal.js';
import type { Element as ModelElement } from '@ckeditor/ckeditor5-engine';

const iconpackIcon = '<svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><g stroke="#000" stroke-linecap="round" stroke-linejoin="bevel" stroke-width=".295"><path d="m0.2628 1.9151 6.6836 3.9405 12.791-2.9519-8.6056-2.7421z" fill="#a5a6a5"/><path d="m0.2628 1.9151 1.2416 11.367 5.8931 6.5566-0.45127-13.983z" fill="#8c8d8c"/><path d="m19.737 2.9037-1.4892 12.311-10.851 4.6234-0.45127-13.983z" fill="#bec0be" style="paint-order:normal"/></g></svg>';

interface UrlParams {
  fieldType: string,
  iconfigString: string,
}

/**
 * Add observer for double click and extend a generic DomEventObserver class by a native DOM dblclick event.
 * https://ckeditor.com/docs/ckeditor5/latest/framework/how-tos.html#how-to-listen-on-a-double-click-for-example-link-elements
 * https://ckeditor.com/docs/ckeditor5/latest/api/module_utils_emittermixin-Emitter.html#function-listenTo:BASE_EMITTER
 */
class IconpackDoubleClickObserver extends DomEventObserver<'dblclick'> {
  get domEventType(): 'dblclick' {
    return 'dblclick';
  }
  onDomEvent(domEvent: MouseEvent): void {
    this.fire('dblclick:iconpack', domEvent);
  }
}

export class Iconpack extends Plugin {

  static pluginName: string = 'Iconpack';

  static get requires() {
    return [
      IconpackCommand,
      IconpackEditing,
      IconpackUI
    ];
  }
}

export class IconpackEditing extends Plugin {

  private svgElements: Array<string> = null;
  private attributes: Array<string> = null;
  private svgGlobalAttributes: Array<string> = null;
  private svgPresentationAttributes: Array<string> = null;

  /**
   * @inheritdoc
   */
  static get requires() {
    return [Widget];
  }

  /**
   * @inheritdoc
   */
  init() {
    this.svgElements = [
      'use',
      'image',
      'title',
      'desc',
      'defs',
      'linearGradient',
      'radialGradient',
      'stop',
      'g',
      'line',
      'path',
      'polyline',
      'polygon',
      'rect',
      'circle',
      'ellipse'
    ];
    this.attributes = [
      'data-iconfig',
      'name',
      'id',
      'class',
      'style',
      'alt',
      'title'
    ];
    this.svgGlobalAttributes = [
      'class',
      'id',
      'title'
    ];
    this.svgPresentationAttributes = [
      'clip-path',
      'clip-rule',
      'color',
      'display',
      'fill',
      'fill-opacity',
      'fill-rule',
      'filter',
      'mask',
      'opacity',
      'shape-rendering',
      'stroke',
      'stroke-dasharray',
      'stroke-dashoffset',
      'stroke-linecap',
      'stroke-linejoin',
      'stroke-miterlimit',
      'stroke-opacity',
      'stroke-width',
      'transform',
      'visibility'
    ];

    this._defineSchema();
    this._defineWebfontConverters();
    this._defineImageConverters();
    this._defineInlineSvgConverters();

    this.editor.commands.add(
      'iconpack',
      new IconpackCommand(this.editor),
    );
  }

  /**
   * Define schema
   *
   * @private
   */
  private _defineSchema(): void {
    const schema = this.editor.model.schema;

    schema.register('iconpackWebfont', {
      allowAttributes: Object.values(this.attributes),
      allowAttributesOf: '$text',
      allowWhere: '$text',
      isInline: true,
      isObject: true,
      isLimit: true
    });

    schema.register('iconpackImage', {
      allowAttributes: Object.values(this.attributes).concat('src'),
      allowAttributesOf: '$text',
      allowWhere: '$text',
      isInline: true,
      isObject: true,
      isLimit: true
    });

    schema.register('iconpackSvg', {
      allowAttributes: this._getAllowedAttributes(Object.values(this.attributes).concat([
        'width',
        'height',
        'fill',
        'viewBox',
        'xmlns',
        'xmlns:xlink'
      ]), true, true),
      allowAttributesOf: '$text',
      allowWhere: '$text',
      isInline: true,
      isObject: true,
      isLimit: true,
      allowChildren: [
        'svgUse',
        'svgImage',
        'svgTitle',
        'svgDesc',
        'svgDefs',
        'svgLinearGradient',
        'svgRadialGradient',
        'svgStop',
        'svgG',
        'svgLine',
        'svgPath',
        'svgPolyline',
        'svgPolygon',
        'svgRect',
        'svgCircle',
        'svgEllipse'
      ]
    });

    schema.register('svgUse', {
      allowIn: ['iconpackSvg', 'svgG'],
      allowAttributes: this._getAllowedAttributes([
        'href',
        'xlink:href',
        'x',
        'y',
        'width',
        'height'
      ], true, true)
    });

    schema.register('svgImage', {
      allowIn: ['iconpackSvg', 'svgG'],
      allowAttributes: this._getAllowedAttributes([
        'href',
        'x',
        'y',
        'width',
        'height',
        'preserveAspectRatio'
      ])
    });

    schema.register('svgTitle', {
      allowIn: ['iconpackSvg', 'svgG'],
      allowAttributes: this._getAllowedAttributes(['id'])
    });

    schema.register('svgDesc', {
      allowIn: ['iconpackSvg', 'svgG'],
      allowAttributes: this._getAllowedAttributes(['id'])
    });

    schema.register('svgDefs', {
      allowIn: ['iconpackSvg', 'svgG'],
      allowAttributes: this._getAllowedAttributes([], true, true)
    });

    schema.register('svgLinearGradient', {
      allowIn: ['iconpackSvg', 'svgDefs'],
      allowChildren: ['svgStop'],
      allowAttributes: this._getAllowedAttributes([
        'gradientUnits',
        'gradientTransform',
        'spreadMethod',
        'x1',
        'x2',
        'y1',
        'y2'
      ], true, true)
    });

    schema.register('svgRadialGradient', {
      allowIn: ['iconpackSvg', 'svgDefs'],
      allowChildren: ['svgStop'],
      allowAttributes: this._getAllowedAttributes([
        'gradientUnits',
        'gradientTransform',
        'spreadMethod',
        'cx',
        'cy',
        'fr',
        'fx',
        'fy',
        'r'
      ], true, true)
    });

    schema.register('svgStop', {
      allowIn: ['svgLinearGradient', 'svgRadialGradient'],
      allowAttributes: this._getAllowedAttributes([
        'stop-color',
        'stop-opacity',
        'offset'
      ], true, true)
    });

    schema.register('svgG', {
      allowIn: 'iconpackSvg',
      allowContentOf: 'iconpackSvg',
      allowAttributes: this._getAllowedAttributes([], true, true)
    });

    schema.register('svgLine', {
      allowIn: ['iconpackSvg', 'svgG', 'svgDefs'],
      allowAttributes: this._getAllowedAttributes([
        'x1',
        'y1',
        'x2',
        'y2'
      ], true)
    });

    schema.register('svgPath', {
      allowIn: ['iconpackSvg', 'svgG', 'svgDefs'],
      isLimit: true,
      isInline: true,
      isObject: true,
      allowAttributes: this._getAllowedAttributes([
        'd',
        'style'
      ], true)
    });

    schema.register('svgPolyline', {
      allowIn: ['iconpackSvg', 'svgG', 'svgDefs'],
      allowAttributes: this._getAllowedAttributes([
        'points'
      ], true)
    });

    schema.register('svgPolygon', {
      allowIn: ['iconpackSvg', 'svgG', 'svgDefs'],
      allowAttributes: this._getAllowedAttributes([
        'points'
      ], true)
    });

    schema.register('svgRect', {
      allowIn: ['iconpackSvg', 'svgG', 'svgDefs'],
      allowAttributes: this._getAllowedAttributes([
        'width',
        'height',
        'x',
        'y',
        'rx',
        'ry',
      ], true)
    });

    schema.register('svgCircle', {
      allowIn: ['iconpackSvg', 'svgG', 'svgDefs'],
      allowAttributes: this._getAllowedAttributes([
        'cx',
        'cy',
        'r'
      ], true)
    });

    schema.register('svgEllipse', {
      allowIn: ['iconpackSvg', 'svgG', 'svgDefs'],
      allowAttributes: this._getAllowedAttributes([
        'cx',
        'cy',
        'rx',
        'ry'
      ], true)
    });

    this.editor.editing.view.domConverter.inlineObjectElements.push('iconpackWebfont');
    this.editor.editing.view.domConverter.inlineObjectElements.push('iconpackImage');
    this.editor.editing.view.domConverter.inlineObjectElements.push('iconpackSvg');
  }

  /**
   * Define webfont converters
   *
   * @private
   */
  private _defineWebfontConverters(): void {
    const conversion = this.editor.conversion;

    conversion.for('upcast').elementToElement({
      view: {
        name: 'span',
        attributes: ['data-iconfig'],
      },
      model: (viewElement, {
        writer: modelWriter
      }) => {
        return modelWriter.createElement(
          'iconpackWebfont',
          viewElement.getAttributes()
        );
      },
      converterPriority: 'highest',
    });
    conversion.for('dataDowncast').elementToElement({
      model: 'iconpackWebfont',
      view: (modelElement, {
        writer
      }) => {
        return writer.createEmptyElement(
          'span',
          this._getViewAttributes(modelElement)
        );
      }
    });
    conversion.for('editingDowncast').elementToElement({
      model: 'iconpackWebfont',
      view: (modelElement, {
        writer: viewWriter
      }) => {
        const iconElement = viewWriter.createContainerElement(
          'span',
          this._getViewAttributes(modelElement)
        );
        const viewElement = viewWriter.createContainerElement(
          'data'
        );
        viewElement._appendChild(iconElement);
        viewElement._addClass('ck-widget-iconpack');
        return toWidget(viewElement, viewWriter, {
          label: 'Iconpack widget'
        });
      }
    });
  }

  /**
   * Define image converters
   *
   * @private
   */
  private _defineImageConverters(): void {
    const conversion = this.editor.conversion;

    conversion.for('upcast').elementToElement({
      view: {
        name: 'img',
        attributes: ['data-iconfig'],
      },
      model: (viewElement, {
        writer: modelWriter
      }) => {
        return modelWriter.createElement(
          'iconpackImage',
          viewElement.getAttributes()
        );
      },
      converterPriority: 'highest',
    });
    conversion.for('dataDowncast').elementToElement({
      model: 'iconpackImage',
      view: (modelElement, {
        writer
      }) => {
        modelElement._removeAttribute('htmlImgAttributes');
        modelElement._removeAttribute('loading');
        return writer.createEmptyElement(
          'img',
          this._getViewAttributes(modelElement, ['src'])
        );
      }
    });
    conversion.for('editingDowncast').elementToElement({
      model: 'iconpackImage',
      view: (modelElement, {
        writer: viewWriter
      }) => {
        const iconElement = viewWriter.createContainerElement(
          'img',
          this._getViewAttributes(modelElement, ['src'])
        );
        const viewElement = viewWriter.createContainerElement(
          'data'
        );
        viewElement._appendChild(iconElement);
        viewElement._addClass('ck-widget-iconpack');
        return toWidget(viewElement, viewWriter, {
          label: 'Iconpack widget'
        });

      }
    });
  }

  /**
   * Define svg converters
   *
   * @private
   */
  private _defineInlineSvgConverters(): void {
    const conversion = this.editor.conversion;

    conversion.for('upcast').elementToElement({
      view: {
        name: 'svg',
        attributes: ['data-iconfig'],
      },
      model: (viewElement, {
        writer: modelWriter
      }) => {
        return modelWriter.createElement(
          'iconpackSvg',
          viewElement.getAttributes()
        );
      },
      converterPriority: 'highest',
    });

    conversion.for('dataDowncast').elementToElement({
      model: 'iconpackSvg',
      view: (modelElement, {
        writer: viewWriter, consumable
      }) => {
        modelElement._removeAttribute('htmlImgAttributes');

        for (const { item } of this.editor.model.createRangeIn(modelElement)) {
          consumable.consume(item, 'insert');
        }
        const content = this._childrenToHtml(modelElement.getChildren() as IterableIterator<ModelElement>);
        const attributes = this._getAllowedAttributes([
          'width', 'height', 'fill', 'viewBox', 'xmlns', 'xmlns:xlink'
        ], true, true)

        let iconElement = viewWriter.createRawElement(
          'svg',
          this._getViewAttributes(modelElement, attributes),
          function (domElement) {
            domElement.innerHTML = content;
          });

        return iconElement;
      },
      converterPriority: 'highest',
    });

    conversion.for('editingDowncast').elementToElement({
      model: 'iconpackSvg',
      view: (modelElement, {
        writer: viewWriter, consumable
      }) => {
        modelElement._removeAttribute('htmlImgAttributes');

        for (const { item } of this.editor.model.createRangeIn(modelElement)) {
          consumable.consume(item, 'insert');
        }
        const content = this._childrenToHtml(modelElement.getChildren() as IterableIterator<ModelElement>);
        const attributes = this._getAllowedAttributes([
          'width', 'height', 'fill', 'viewBox', 'xmlns', 'xmlns:xlink'
        ], true, true)

        let iconElement = viewWriter.createRawElement(
          'svg',
          this._getViewAttributes(modelElement, attributes),
          function (domElement) {
            domElement.innerHTML = content;
          });

        const viewElement = viewWriter.createContainerElement(
          'data'
        );
        viewElement._appendChild(iconElement);
        viewElement._addClass('ck-widget-iconpack');
        return toWidget(viewElement, viewWriter, {
          label: 'Iconpack widget'
        });
      },
      converterPriority: 'highest',
    });

    // Add upcasts for all other SVG elements
    for (const element of this.svgElements) {
      conversion.for('upcast').elementToElement({
        view: element,
        model: (viewElement, { writer }) => {
          return writer.createElement(
            'svg' + this._capitalizeFirstLetter(element),
            viewElement.getAttributes()
          );
        }
      });
    }
  }

  /**
   * Iterate through children and return their content as raw HTML.
   *
   * @param {IterableIterator<ModelElement>} children
   *
   * @private
   */
  private _childrenToHtml(
    children: IterableIterator<ModelElement>
  ): string {
    let content = '';
    for (const child of children) {
      let itemType = child.name.toString().replace('svg', '').toLowerCase();
      let attributes: IterableIterator<[string, any]> = child.getAttributes();
      let params = '';
      for (const [key, value] of attributes) {
        params += ' ' + key + '="' + value + '"';
      }
      if (child.childCount > 0) {
        const childContent = this._childrenToHtml(child.getChildren() as IterableIterator<ModelElement>)
        content += '<' + itemType + params + '>' + childContent + '</' + itemType + '>';
      } else {
        content += '<' + itemType + params + ' />'
      }
    }
    return content;
  };

  /**
   * Get all the necessary attributes for the Iconpack view element.
   *
   * @param {ModelElement} modelElement
   * @param {Array<string>} additionalAttributes
   *
   * @private
   */
  private _getViewAttributes(
    modelElement: ModelElement,
    additionalAttributes?: Array<string>
  ): Record<string, any> {
    const viewAttributes: Record<string, any> = {};
    let attributes = this.attributes;
    if (additionalAttributes) {
      attributes = Object.values(this.attributes).concat(Object.values(additionalAttributes));
    }
    attributes.forEach((attributeName: string) => {
      const attributeValue = modelElement.getAttribute(attributeName);
      if (attributeValue && attributeValue != '') {
        viewAttributes[attributeName] = attributeValue;
      }
    });
    return viewAttributes;
  }

  /**
   * Concatenate attributes.
   *
   * @param {Array<string>} attributes
   * @param {boolean} enablePresentationAttributes
   * @param {boolean} enableGlobalAttributes
   *
   * @private
   */
  private _getAllowedAttributes(
    attributes: Array<string>,
    enablePresentationAttributes: boolean = false,
    enableGlobalAttributes: boolean = false
  ): Array<string> {
    if (enableGlobalAttributes) {
      attributes = Object.values(this.svgGlobalAttributes).concat(attributes);
    }
    if (enablePresentationAttributes) {
      attributes = Object.values(this.svgPresentationAttributes).concat(attributes);
    }
    return attributes;
  }

  /**
   * Capitalize first letter of a given string value.
   *
   * @private
   */
  private _capitalizeFirstLetter(value: string) {
    return String(value).charAt(0).toUpperCase() + String(value).slice(1);
  }
}

export class IconpackCommand extends Command {

  /**
   * @inheritdoc
   */
  execute(): void {
    const model = this.editor.model;
    const selection = model.document.selection;

    this.editor.model.change(writer => {
      const selectedElement = selection.getSelectedElement();
      let iconfigString: string = null;
      if (selectedElement) {
        iconfigString = <string>selectedElement.getAttribute('data-iconfig');
      }
      const urlParams: UrlParams = {
        fieldType: 'rte',
        iconfigString: iconfigString
      };
      IconpackModal.openIconpackModal(
        TYPO3.lang['js.label.iconRte'],
        urlParams,
        this._addIconToRte.bind(this),
        this._removeIconFromRte.bind(this)
      );
    });
  }

  /**
   * @inheritdoc
   */
  refresh(): void {
    const model = this.editor.model;
    const selection = model.document.selection;
    // Note: iconpackWebfont and iconpackImage are allowed inside same parents
    const allowedIn = model.schema.findAllowedParent(
      selection.getFirstPosition(),
      'iconpackWebfont'
    );

    // Flag indicating whether a command is enabled or disabled
    this.isEnabled = allowedIn !== null;
  }

  /**
   * Add icon to the initiating RTE.
   *
   * @param {string} iconfigString
   * @param {string} iconMarkup
   *
   * @private
   */
  private _addIconToRte(
    iconfigString: string,
    iconMarkup: string
  ): void {
    console.log('⮜ CKEditor: Add icon to RTE'); //# DEBUG MESSAGE
    if (iconMarkup) {
      // Wrap the iconMarkup in an A-tag and take the attributes from the parent node
      // This is a nasty hack (help needed!), but it works...
      const parent: any = this._getSelectionParent();
      const linkAttributes: Record<string, any> = {};
      if (parent && parent.name === 'a') {
        if (parent.hasAttribute('href')) {
          linkAttributes.href = parent.getAttribute('href');
        }
        if (parent.hasAttribute('target')) {
          linkAttributes.target = parent.getAttribute('target');
        }
        if (parent.hasAttribute('title')) {
          linkAttributes.title = parent.getAttribute('title');
        }
        if (parent.hasClass()) {
          let classes: Array<string> = [...parent.getClassNames()];
          classes = classes.filter(
            (item) => (item != 'ck-link_selected')).map((item) => item);
          if (classes.length != 0) {
            linkAttributes.class = Object.values(classes).join(' ');
          }
        }
        let aParams: string = '';
        Object.entries(linkAttributes).forEach(([key, value]) => {
          aParams += ' ' + key + '="' + value + '"';
        });
        iconMarkup = '<a' + aParams + '>' + iconMarkup + '</a>';
      }

      const viewFragment = this.editor.data.processor.toView(iconMarkup);
      const modelFragment = this.editor.data.toModel(viewFragment);
      this.editor.model.insertContent(modelFragment);
    }
  }

  /**
   * Get the parent element of the current selection.
   *
   * @private
   */
  private _getSelectionParent(): any {
    const viewDocument = this.editor.editing.view.document;
    return viewDocument.selection.focus.getAncestors()
      .reverse()
      .find(node => node.is('element'));
  }

  /**
   * Remove icon from the initiating RTE.
   *
   * @private
   */
  private _removeIconFromRte(): void {
    console.log('⮜ CKEditor: Remove icon from RTE'); //# DEBUG MESSAGE
    const selection = this.editor.model.document.selection;
    const range = selection.getFirstRange();
    this.editor.model.change(writer => {
      writer.remove(range);
    });
  }
}

export class IconpackUI extends Plugin {

  /**
   * @inheritdoc
   */
  init(): void {
    const editor = this.editor;
    const t = editor.t;

    // Add the "iconpack" button to the toolbar
    editor.ui.componentFactory.add('iconpack', (locale) => {
      // The state of the button will be bound to the widget command
      const command: any = editor.commands.get('iconpack');
      // The button will be an instance of ButtonView
      const buttonView = new ButtonView(locale);

      buttonView.set({
        withText: true,
        tooltip: t('Insert Icon'),
        icon: iconpackIcon,
        isToggleable: true
      });

      // Bind the state of the button to the command.
      buttonView.bind('isOn', 'isEnabled').to(command, 'value', 'isEnabled');

      // Execute the command when the button is clicked (executed)
      this.listenTo(buttonView, 'execute', () => {
        console.log('⭘ CKEditor: Toolbar button has been clicked!'); //# DEBUG MESSAGE
        editor.execute('iconpack')
      });

      return buttonView;
    });

    const view = editor.editing.view;
    const viewDocument = view.document;

    view.addObserver(IconpackDoubleClickObserver);

    // https://ckeditor.com/docs/ckeditor5/latest/framework/deep-dive/event-system.html
    editor.listenTo(viewDocument, 'dblclick:iconpack', (evt, data) => {
      if (data.target.parent) {
        const modelElement = editor.editing.mapper.toModelElement(data.target.parent);
        if (modelElement && typeof modelElement.name !== 'undefined' && (
          modelElement.name === 'iconpackWebfont' || modelElement.name === 'iconpackImage' || modelElement.name === 'iconpackSvg'
        )) {
          console.log('⭘ CKEditor: Iconpack element has been double-clicked!'); //# DEBUG MESSAGE
          editor.execute('iconpack')
        }
      }
    });
  }
}

export default Iconpack;
