/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

import AjaxRequest from '@typo3/core/ajax/ajax-request.js';
import { AjaxResponse } from '@typo3/core/ajax/ajax-response.js';
import BackendIcons from '@typo3/backend/icons.js';

// Define selector names
enum iconpackSelectors {
  // The footer element of the modal
  modalFooter = '.modal-footer',
  // The dropdown which selects the iconpack
  styles = '#iconpack-style',
  // The section with additional options
  options = '#iconpack-options',
  // The icon wall
  icons = '#iconpack-icons',
  // The tooltip
  tooltip = '#iconpack-tooltip',
  // The currently selected icon
  iconSelection = '#iconpack-selected > div',
  // The searchbox
  search = '#iconpack-search',
  // Select options
  optionsSection = '.form-section',
  // Element which contains the StyleSheets
  styleSheets = '#iconpack-stylesheets',
}

interface AssociativeArray {
  [key: string]: string | boolean | number;
}
interface IconfigOptions {
  options: AssociativeArray
}
interface IconfigObject {
  iconpackStyle: string | true;
  iconpack: string | true;
  style: string | true;
  icon: string | true;
  options: AssociativeArray
}
interface IconObject {
  attributes: AssociativeArray
  elementName: keyof HTMLElementTagNameMap,
  innerHtml: string,
  type: string,
}

type AjaxResponse = typeof AjaxResponse;

/**
 * Module: @quellenform/iconpack.js
 * The main Iconpack class
 * @exports @quellenform/iconpack.js
 */
class Iconpack {

  // The Iconpack Modal
  private iconpackModal: HTMLElement = null;
  // The current fieldType [native|rte]
  private fieldType: string = null;
  // The Iconpack configuration
  public iconfig: IconfigObject = null;
  // Backup of the Iconpack configuration (important for style changes)
  private iconfigBackup: IconfigObject = null;

  // The Iconpack elements
  private elementSearch: HTMLElement = null;
  private elementOptions: HTMLElement = null;
  private elementIcons: HTMLElement = null;
  private elementTooltip: HTMLElement = null;
  private elementStyles: HTMLSelectElement = null;
  private elementModalFooter: HTMLElement = null;
  private elementIconSelection: HTMLElement = null;

  public initialize(currentModal: HTMLElement, iconfigString: string, fieldType: string) {
    console.group('Iconpack::initialize()'); //? DEBUG GROUP

    this.iconpackModal = currentModal;
    this.iconfig = this.convertIconfigToObject(iconfigString);
    this.iconfigBackup = this.iconfig;
    this.fieldType = fieldType;

    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Initialize the content at first run.
   */
  public initializeContent(): void {
    console.groupCollapsed('Iconpack::initializeContent()'); //? DEBUG GROUP

    // Initialize elements
    this.initializeElements();
    // Inject the required CSS into the TYPO3 main frame
    this.injectCSS();
    if (typeof (this.elementStyles) != 'undefined' && this.elementStyles != null) {
      // Iconpacks are installed, go ahead...
      this.initializeStyleField();
      this.initializeOptionFields();
      this.initializeIconWall();
      this.initializeSearchField();
    } else {
      // Unset buttons if there are no iconpacks installed
      const clearButton: HTMLElement = this.elementModalFooter.querySelector('button[name="clear"]');
      clearButton.style.display = 'none';
      const okButton: HTMLElement = this.elementModalFooter.querySelector('button[name="ok"]');
      okButton.style.display = 'none';
    }

    console.groupEnd(); //? DEBUG GROUP
  }

  private initializeElements(): void {
    console.groupCollapsed('Iconpack::initializeElements()'); //? DEBUG GROUP

    this.elementModalFooter = this.iconpackModal.querySelector(iconpackSelectors.modalFooter);
    this.elementStyles = this.iconpackModal.querySelector(iconpackSelectors.styles);
    this.elementOptions = this.iconpackModal.querySelector(iconpackSelectors.options);
    this.elementIcons = this.iconpackModal.querySelector(iconpackSelectors.icons);
    this.elementTooltip = this.iconpackModal.querySelector(iconpackSelectors.tooltip);
    this.elementIconSelection = this.iconpackModal.querySelector(iconpackSelectors.iconSelection);
    this.elementSearch = this.iconpackModal.querySelector(iconpackSelectors.search);

    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Request an icon from  the controller.
   */
  public getIconpackIcon(
    baseUrl: string,
    callback: CallableFunction,
    iconfigString: string,
    returnAsMarkup = false
  ): void {
    console.groupCollapsed('Iconpack::getIconpackIcon()'); //? DEBUG GROUP
    console.debug('Ⓘ Query icons from URL:', '⮑', baseUrl + '&fieldType=' + this.fieldType + '&iconfig=' + iconfigString); //! DEBUG VALUE

    new AjaxRequest(baseUrl)
      .withQueryArguments({
        fieldType: this.fieldType,
        iconfig: iconfigString
      })
      .get()
      .then(async (response: AjaxResponse) => {
        const data = await response.resolve();
        console.log('⮐ AJAX returned...'); //# DEBUG MESSAGE
        if (data) {
          if (returnAsMarkup) {
            // Return icon as HTML-markup
            callback(iconfigString, this.convertIcon(data.icon, iconfigString));
          } else {
            // Return icon as object
            callback(data, iconfigString);
          }
        } else {
          console.warn('⮿ Invalid response or Icon not available!');
        }
      });

    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Callback to update the modal content.
   */
  private updateContent(
    data: AjaxResponse,
    iconfig: string
  ): void {
    console.groupCollapsed('Iconpack::updateContent()'); //? DEBUG GROUP

    // Clear search field after update
    this.elementSearch.querySelector('input').value = '';

    if (data.iconpackOptions !== null) {
      this.elementOptions.innerHTML = data.iconpackOptions;
    }
    if (data.iconpackIcons !== null) {
      this.elementIcons.innerHTML = data.iconpackIcons;
    }
    this.initializeOptionFields();
    this.initializeIconWall();

    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Initialize the style selector.
   */
  private initializeStyleField(): void {
    console.groupCollapsed('Iconpack::initializeStyleField()'); //? DEBUG GROUP

    // Add OnChange handler that becomes active when another style is selected
    this.elementStyles.addEventListener('change', () => {
      console.log('⭘ TRIGGER: Style has been changed'); //# DEBUG MESSAGE
      // Add loading icon to the icons section
      BackendIcons.getIcon(
        'spinner-circle',
        BackendIcons.sizes.large,
        null,
        null,
        BackendIcons.markupIdentifiers.inline
      ).then(async (response: AjaxResponse) => {
        this.elementIcons.innerHTML = '<div class="icons-loading">' + response + '</div>'
      });
      // Reset iconfig
      if (this.iconfigBackup && this.iconfigBackup.iconpack) {
        if (this.iconfigBackup.iconpackStyle === this.iconfig.iconpackStyle) {
          this.iconfig = this.iconfigBackup;
        } else if (this.iconfigBackup.iconpack === this.iconfig.iconpack) {
          this.iconfig.options = this.iconfigBackup.options;
        }
      }
      // Query the chosen iconpack
      this.getIconpackIcon(
        TYPO3.settings.ajaxUrls.iconpack_modal_update,
        this.updateContent.bind(this),
        this.elementStyles.value
      );
    });

    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Initialize the option fields.
   */
  private initializeOptionFields(): void {
    console.groupCollapsed('Iconpack::initializeOptionFields()'); //? DEBUG GROUP

    this.elementOptions.querySelectorAll('.iconpack-option').forEach((optionElement: HTMLInputElement) => {
      const optionKey = optionElement.getAttribute('data-key');
      console.debug('Ⓘ Configure Iconpack option:', optionKey); //! DEBUG VALUE
      if (this.iconfig && this.iconfig.options[optionKey]) {
        this.setFieldValue(optionElement, this.iconfig.options[optionKey]);
      }
      // Add onchange to options field
      optionElement.addEventListener('change', () => {
        console.log('⭘ TRIGGER: Option "' + optionKey + '" has been changed'); //# DEBUG MESSAGE
        if (this.iconfig && this.iconfig.icon) {
          const iconpackIcons = this.elementIcons.querySelector('[name="' + this.iconfig.icon + '"]');
          if (iconpackIcons) {
            this.elementIconSelection.innerHTML = iconpackIcons.innerHTML;
          }
        }
        this.applyOptions();
      });
    });

    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Get the attributes from the given field.
   * The return value is used as iconfig string.
   */
  private getFieldAttributes(
    element: HTMLElement | HTMLInputElement | HTMLSelectElement
  ): string[] | null {
    let value: string = null;

    if (element) {
      if (element.matches('[type="checkbox"]')) {
        value = this.getDataAttributeFromCheckbox(<HTMLInputElement>element, 'attributes');
      } else if (element.tagName.toLowerCase() === 'select') {
        value = this.getDataAttributeFromSelectOption(<HTMLSelectElement>element, 'attributes');
      }
    }
    return value ? JSON.parse(value) : null;
  }
  private getDataAttributeFromCheckbox(element: HTMLInputElement, dataAttribute: string): string | null {
    return element.checked ? element.getAttribute('data-attributes') : null;
  }
  private getDataAttributeFromSelectOption(element: HTMLSelectElement, dataAttribute: string): string | null {
    return element.options[element.selectedIndex].dataset[dataAttribute];
  }

  /**
   * Get the value from the given field.
   */
  private getFieldValue(
    element: HTMLInputElement
  ): string | boolean {
    let value: string | boolean = null;
    if (element) {
      if (element.matches('[type="checkbox"]')) {
        value = element.checked;
      } else if (element.tagName.toLowerCase() === 'select') {
        value = element.value;
      }
    }
    return value !== '' ? value : null;
  }

  /**
   * Set the value of the given field.
   */
  private setFieldValue(
    element: HTMLInputElement,
    value: string | number | boolean
  ): void {
    if (element.matches('[type="checkbox"]')) {
      element.checked = <boolean>value;
    } else if (element.tagName.toLowerCase() === 'select') {
      element.value = value.toString();
    }
  }

  /**
   * Initialize the search field.
   */
  private initializeSearchField(): void {
    console.groupCollapsed('Iconpack::initializeSearchField()'); //? DEBUG GROUP

    const searchFieldInput: HTMLInputElement = this.elementSearch.querySelector('input');
    const searchFieldClearButton: HTMLButtonElement = this.elementSearch.querySelector('button');

    // Move search field to footer
    const searchField = this.elementSearch.parentElement.removeChild(this.elementSearch);
    this.elementModalFooter.prepend(searchField);

    // Add triggers to empty the field
    searchFieldClearButton.addEventListener('click', () => {
      console.log('⭘ TRIGGER: Search field has been cleared'); //# DEBUG MESSAGE
      searchFieldInput.value = '';
      searchFieldInput.dispatchEvent(new Event('input'));
    });
    // Add trigger, which will be called when entered into the field
    searchFieldInput.addEventListener('input', () => {
      console.log('⭘ TRIGGER: Search field has been changed'); //# DEBUG MESSAGE
      const searchTerm = searchFieldInput.value;
      if (searchTerm !== '') {
        searchFieldClearButton.style.visibility = 'visible';
      } else {
        searchFieldClearButton.style.visibility = 'hidden';
      }
      this.searchIcon(searchTerm.toLowerCase());
    });

    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Initialize the icons.
   */
  private initializeIconWall(): void {
    console.groupCollapsed('Iconpack::initializeIconWall()'); //? DEBUG GROUP

    this.elementIcons.querySelectorAll('li').forEach((iconElement) => {
      const iconIdentifier = iconElement.getAttribute('name');
      if (this.iconfig && this.iconfig.icon) {
        if (this.iconfig.icon === iconIdentifier) {
          iconElement.classList.add('active');
        }
      }
      // Add onclick to icons
      iconElement.addEventListener('click', (event) => {
        console.log('⭘ TRIGGER: Icon has been selected'); //# DEBUG MESSAGE
        const element = <HTMLElement>event.currentTarget;
        element.parentNode.querySelectorAll('li').forEach((siblingIconElement) => {
          siblingIconElement.classList.remove('active');
        });
        element.classList.add('active');
        this.selectIcon(iconIdentifier);
      }, true);
      iconElement.addEventListener('dblclick', () => {
        console.log('⭘ TRIGGER: Icon has been double-clicked!'); //# DEBUG MESSAGE
        const okButton =
            this.elementModalFooter.querySelector('button[name="ok"]');
          okButton.dispatchEvent(new Event("click"));
      }, true);
      iconElement.addEventListener('mouseover', (event) => {
        const element = <HTMLElement>event.currentTarget;
        const iconTitle = element.getAttribute('data-title');
        this.elementTooltip.innerHTML = iconTitle;
        this.elementTooltip.style.display = 'block';
      }, true);
      iconElement.addEventListener('mouseleave', () => {
        this.elementTooltip.innerHTML = '';
        this.elementTooltip.style.display = 'none';
      }, true);
    });

    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Update the currently displayed icon when an icon is clicked.
   */
  private selectIcon(
    iconIdentifier: string
  ): void {
    console.groupCollapsed('Iconpack::selectIcon()'); //? DEBUG GROUP

    const iconpackStyle = this.elementStyles.options[this.elementStyles.selectedIndex].value;
    const iconMarkup = this.elementIcons.querySelector('[name="' + iconIdentifier + '"]').innerHTML;

    this.elementIconSelection.innerHTML = iconMarkup;
    this.iconfig = this.convertIconfigToObject(iconpackStyle + ',' + iconIdentifier);
    this.applyOptions();

    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Apply currently selected options to iconfig and selected icon.
   */
  private applyOptions(): void {
    console.groupCollapsed('Iconpack::applyOptions()'); //? DEBUG GROUP

    let additionalAttributes: string[][] = [];
    const iconfig: IconfigOptions = {
      options: {}
    };
    this.elementOptions.querySelectorAll('.iconpack-option').forEach((optionElement: HTMLInputElement) => {
      const optionKey = optionElement.getAttribute('data-key');
      const attribute: string[] = this.getFieldAttributes(optionElement);
      if (attribute) {
        additionalAttributes.push(attribute);
      }
      const value: string | boolean | null = this.getFieldValue(optionElement);
      if (value) {
        iconfig.options[optionKey] = value;
      }
    });

    this.mergeIconfig(iconfig);
    this.mergeAttributesIntoIconSelection(additionalAttributes);

    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Merge active attributes into selected icon element.
   */
  private mergeAttributesIntoIconSelection(
    additionalAttributes: string[][]
  ): void {
    console.groupCollapsed('Iconpack::mergeAttributesIntoIconSelection()'); //? DEBUG GROUP
    console.debug('Ⓘ Attributes to be merged:', additionalAttributes); //! DEBUG VALUE

    let iconElement = <HTMLElement>this.elementIconSelection.firstElementChild;

    for (const i in additionalAttributes) {
      for (const attributeName in additionalAttributes[i]) {
        let data: any = additionalAttributes[i][attributeName];
        switch (attributeName) {
          case 'class':
            console.debug('Ⓘ Add class:', data); //! DEBUG VALUE
            iconElement.classList.add(data);
            break;
          case 'style':
            const styleArray = data.split(';');
            for (let j = 0; j < styleArray.length; j++) {
              const style = styleArray[j].split(':');
              if (style[0] !== '') {
                console.debug('Ⓘ Add style "' + style[0] + ':' + style[1] + ';\"'); //! DEBUG VALUE
                iconElement.style.setProperty(style[0], style[1]);
              }
            }
            break;
          default:
            console.debug('Ⓘ Add attribute ' + attributeName + '=\"' + data + '\"'); //! DEBUG VALUE
            iconElement.setAttribute(attributeName, data.toString());
        }
      }
    }

    console.debug('Ⓘ Resulting icon element:', iconElement); //! DEBUG VALUE
    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Search for a specific icon name in the currently available list.
   */
  private searchIcon(
    searchString: string
  ): void {
    console.group('Iconpack::searchIcon()'); //? DEBUG GROUP
    console.debug('Ⓘ Search for:', searchString); //! DEBUG VALUE

    this.elementIcons.querySelectorAll('section').forEach((sectionItem) => {
      let enableSection = false;
      sectionItem.querySelectorAll('li').forEach((iconItem) => {
        const name = iconItem.getAttribute('name').toLowerCase();
        if (name && name.indexOf(searchString) >= 0) {
          iconItem.style.display = 'inline-flex';
          enableSection = true;
        } else {
          iconItem.style.display = 'none';
        }
      });
      if (enableSection) {
        sectionItem.style.display = 'block';
      } else {
        sectionItem.style.display = 'none';
      }
    });

    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Converts an icon object into a HTML string.
   */
  private convertIcon(
    iconObject: IconObject,
    iconfig: string
  ): string {
    console.groupCollapsed('Iconpack::convertIcon()'); //? DEBUG GROUP

    if (iconObject) {
      // Add iconfig to data-attribute
      iconObject.attributes['data-iconfig'] = iconfig;
      console.debug('Ⓘ Final iconObject:', iconObject); //! DEBUG VALUE

      // Create new HTMLElement
      let iconElement: SVGElement | string = document.createElementNS(
        'http://www.w3.org/2000/svg',
        iconObject.elementName
      );
      // Add attributes to SVGElement
      for (const key in iconObject.attributes) {
        const value = iconObject.attributes[key].toString();
        iconElement.setAttribute(key, value);
      }
      // Render iconElement
      if (<string>iconObject.elementName === 'svg') {
        // Render iconElement as XML (svg*)
        iconElement.innerHTML = iconObject.innerHtml;
        // Create and parse XML-Element to allow short opened tags in SVG
        const parser = new DOMParser();
        const xmlDoc = parser.parseFromString(iconElement.outerHTML.toString(), 'image/svg+xml');
        const xmlText = new XMLSerializer().serializeToString(xmlDoc)
        iconElement = xmlText;
      } else {
        // Render iconElement as HTML
        iconElement = iconElement.outerHTML.toString();
      }

      console.debug('❤ Final HTML:', iconElement); //! DEBUG VALUE
      console.groupEnd(); //? DEBUG GROUP

      return iconElement;
    }
    return '';
  }

  /**
   * Load CSS files into parent main frame.
   */
  private injectCSS(): void {
    console.groupCollapsed('Iconpack::injectCSS()'); //? DEBUG GROUP

    const styleSheetElement: HTMLInputElement = this.iconpackModal.querySelector(iconpackSelectors.styleSheets);
    const styleSheets: string[] = JSON.parse(styleSheetElement.value);
    for (let index in styleSheets) {
      const cssFile = styleSheets[index];
      if (!window.parent.document.head.querySelector('link[href="' + cssFile + '"]')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.type = 'text/css';
        link.href = cssFile;
        link.dataset.iconpack = 'iconpack[' + index + ']';
        console.debug('⮊ Inject CSS-link:', link); //! DEBUG VALUE
        window.parent.document.head.appendChild(link);
      }
    }

    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Unload CSS files from parent document.
   */
  public unlinkCSS(): void {
    console.groupCollapsed('Iconpack::unlinkCSS()'); //? DEBUG GROUP

    const linkElements: NodeListOf<HTMLLinkElement> = window.parent.document.head.querySelectorAll('link[data-iconpack]');
    for (let i = 0; i < linkElements.length; i++) {
      console.debug('⮈ Unlink CSS-link:', linkElements[i].getAttribute('href')); //! DEBUG VALUE
      linkElements[i].parentNode.removeChild(linkElements[i]);
    }

    console.groupEnd(); //? DEBUG GROUP
  }

  /**
   * Convert an iconfig string to object.
   */
  private convertIconfigToObject(
    iconfigString: string
  ): IconfigObject {
    console.groupCollapsed('Iconpack::convertIconfigToObject()'); //? DEBUG GROUP
    console.debug('Ⓘ iconfig (string):', iconfigString); //! DEBUG VALUE

    let iconfigObject = null;
    if (iconfigString) {
      const iconfigArray = iconfigString.split(',');
      const iconpackStyle = iconfigArray[0].split(':');
      const options: AssociativeArray = {};
      iconfigObject = {
        iconpackStyle: iconfigArray[0] || null,
        iconpack: iconpackStyle[0] || null,
        style: iconpackStyle[1] || null,
        icon: iconfigArray[1] || null,
        options: options
      }
      for (let i = 2; i < iconfigArray.length; i++) {
        const options = iconfigArray[i].split(':');
        if (options[1].match(/^true|false$/ig)) {
          if ((options[1] === 'true')) {
            iconfigObject.options[options[0]] = true;
          }
        } else {
          iconfigObject.options[options[0]] = options[1];
        }
      }
    }

    console.debug('Ⓘ iconfig (object):', iconfigObject); //! DEBUG VALUE
    console.groupEnd(); //? DEBUG GROUP

    return iconfigObject;
  }

  /**
   * Convert an iconfig object to string.
   */
  public convertIconfigToString(
    iconfigObject: IconfigObject
  ): string | null {
    console.groupCollapsed('Iconpack::convertIconfigToString()'); //? DEBUG GROUP
    console.debug('Ⓘ iconfig (object):', iconfigObject); //! DEBUG VALUE

    let iconfigString = null;
    if (iconfigObject) {
      iconfigString = iconfigObject.iconpackStyle + ',' + iconfigObject.icon;
      if (iconfigObject.options) {
        for (let key in iconfigObject.options) {
          iconfigString += ',' + key + ':' + iconfigObject.options[key];
        }
      }
    }

    console.debug('Ⓘ iconfig (string):', iconfigString); //! DEBUG VALUE
    console.groupEnd(); //? DEBUG GROUP

    return iconfigString;
  }

  /**
   * Merge options into iconfig.
   */
  private mergeIconfig(
    iconfig: IconfigOptions
  ): void {
    console.groupCollapsed('Iconpack::mergeIconfig()'); //? DEBUG GROUP
    console.debug('❶ iconfig.options:', this.iconfig.options); //! DEBUG VALUE

    this.iconfig = {
      ...this.iconfig,
      ...iconfig
    };
    // Backup iconfig
    this.iconfigBackup = this.iconfig;

    console.debug('❷ iconfig.options:', this.iconfig.options); //! DEBUG VALUE
    console.groupEnd(); //? DEBUG GROUP
  }
}

const iconpack = new Iconpack;
export default iconpack;
