/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/*
var __importDefault = this && this.__importDefault || function(t) {
  return t && t.__esModule ? t : {
    default: t
  }
};
*/

define(['require', 'exports', 'jquery', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Core/Ajax/AjaxRequest', 'TYPO3/CMS/Backend/Icons'], (function(r, exports, $, Modal, AjaxRequest, Icon) {
  'use strict';
  //console.log('TYPO3/CMS/Iconpack/Backend/Iconpack()');

  //$ = __importDefault($);

  class Iconpack {

    constructor() {
      console.log('Iconpack::constructor()'); //? DEBUG

      // The Iconpack Modal
      this.$imodal = null;
      // The current fieldType [native|rte]
      this.fieldType = null;
      // The Iconpack Configuration
      this.iconfig = null;
      this.iconfigBackup = null;
      // The StyleSheet array
      this.styleSheets = null;
      // The target editor for RTE-icons
      this.editor = null;
      // Define selector names
      this.sel = {
        // The form palette which contains the form elements
        palette: '.t3js-formengine-palette-field',
        // the input field for the header icon
        formengineInput: 'formengine-input-name',
        // The icon field which displays the final icon
        iconField: '.t3js-icon',
        // The footer element of the modal
        modalFooter: '.modal-footer',
        // The dropdown which selects the iconspack
        styles: '#iconpack-style',
        // The section with additional options
        options: '#iconpack-options',
        icons: '#iconpack-icons',
        iconSelection: '#iconpack-selected > div',
        search: '#iconpack-search',
        optionsSection: '.form-section'
      };
      // Set jquery elements
      this.el = {
        $palette: null,
        $formengineInput: null,
        $iconField: null,
        $modalFooter: null,
        $styles: null,
        $options: null,
        $icons: null,
        $iconSelection: null,
        $search: null,
        $optionsSection: null
      };
    }

    /**
     * Show Iconpack Modal in case of native icons.
     *
     * @param {Object} anchorElement
     */
    showFieldIconModal(anchorElement) {
      console.group('Iconpack.showFieldIconModal()'); //? DEBUG GROUP
      console.log(anchorElement, '= anchorElement'); //! DEBUG VALUE

      let $cssLinks = $('link[title^=iconpack]');
      if ($cssLinks.length) {
        this.styleSheets = [];
        $.each($cssLinks, function(index, cssLink) {
          iconpackInstance.styleSheets.push($(cssLink).attr('href'));
        });
      } else {
        this.styleSheets = null
      }

      this.fieldType = 'native';
      this.el.$palette = anchorElement.closest(this.sel.palette);
      this.el.$formengineInput = this.el.$palette.find('input[name="' + anchorElement.data(this.sel.formengineInput) + '"]');
      this.el.$iconField = anchorElement.find(iconpackInstance.sel.iconField);

      // The current header icon
      let iconfigString = this.el.$formengineInput.val();
      this.iconfig = this.convertIconfigToObject(iconfigString);
      this.iconfigBackup = this.iconfig;

      // Create the Iconpack Modal
      var $modal = this.createModal(
        // The Ajax-URL which loads the iconpack icons
        TYPO3.settings.ajaxUrls['iconpack_modal'] + '&fieldType=' + this.fieldType + '&iconfig=' + iconfigString,
        // The title of the Iconpack Modal
        TYPO3.lang['js.label.iconNative'],
      );
      $modal.on('button.clicked', function(e) {
        console.log('--> TRIGGER: Modal button clicked (native)'); //! DEBUG VALUE
        // Clear modal content to avoid flicker
        iconpackInstance.$imodal.find('.modal-body').css('display', 'none');
        if (e.target.name === 'ok') {
          iconpackInstance.ajaxRequest(
            TYPO3.settings.ajaxUrls.iconpack_icon,
            iconpackInstance.addIconToField,
            iconpackInstance.convertIconfigToString(iconpackInstance.iconfig)
          );
        } else if (e.target.name === 'clear') {
          iconpackInstance.addIconToField(null, '');
        }
        Modal.dismiss(iconpackInstance.unlinkCSS());
      });

      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Show Iconpack Modal in case of CKEditor.
     *
     * @param {Object} editor
     */
    showCkeditorModal(editor) {
      console.group('Iconpack.showCkeditorModal()'); //? DEBUG GROUP
      console.log(editor, '= editor'); //! DEBUG VALUE

      this.editor = editor;
      this.fieldType = 'rte';
      this.styleSheets = editor.config.modalCss;
      this.iconfig = null;
      this.iconfigBackup = this.iconfig;

      // Create the Iconpack Modal
      var $modal = this.createModal(
        // The Ajax-URL which loads the iconpack icons
        TYPO3.settings.ajaxUrls['iconpack_modal'] + '&fieldType=' + this.fieldType,
        // The title of the Iconpack Modal
        TYPO3.lang['js.label.iconRte'],
      );
      $modal.on('button.clicked', function(e) {
        console.log('--> TRIGGER: Modal button clicked (rte)'); //! DEBUG VALUE
        if (e.target.name === 'ok') {
          iconpackInstance.ajaxRequest(
            TYPO3.settings.ajaxUrls.iconpack_icon,
            iconpackInstance.addIconToRte,
            iconpackInstance.convertIconfigToString(iconpackInstance.iconfig)
          );
        }
        Modal.dismiss(iconpackInstance.unlinkCSS());
      });

      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Create a TYPO3-Modal.
     *
     * @param {String} url - The URL
     * @param {String} title - The Title of the Modal
     * @returns {Object}
     */
    createModal(url, title) {
      //console.log('Iconpack.createModal()'); //? DEBUG GROUP
      console.log(url, '= url'); //! DEBUG VALUE

      // Inject the requred CSS into the TYPO3 main frame
      this.injectCSS();
      // Set the modal buttons
      let buttons = [{
          text: $(this).data('button-cancel-text') || TYPO3.lang['js.button.cancel'] || 'Cancel',
          active: true,
          name: 'cancel'
        },
        {
          text: $(this).data('button-ok-text') || TYPO3.lang['js.button.ok'] || 'OK',
          btnClass: 'btn-success',
          name: 'ok'
        }
      ];
      // Show button to clear the field only if an icon is currently selected
      if (this.iconfig) {
        buttons.unshift({
          text: $(this).data('button-clear-text') || TYPO3.lang['js.button.clear'] || 'Clear',
          btnClass: 'btn-warning',
          name: 'clear'
        });
      }
      // Return the modal instance
      return Modal.advanced({
        type: Modal.types.ajax,
        title: title,
        content: url,
        buttons: buttons,
        size: Modal.sizes.large,
        callback: function(currentModal) {
          currentModal.find('.t3js-modal-content').addClass('modal-iconpack');
          iconpackInstance.$imodal = currentModal;
        },
        ajaxCallback: function() {
          iconpackInstance.initializeContent();
        }
      });
    }

    /**
     * Ask the controller politely if he might have a small icon for us.
     *
     * @param {String} url
     * @param {*} callback
     * @param {String} iconfig
     */
    ajaxRequest(url, callback, iconfig) {
      console.group('Calling \'' + url + '&fieldType=' + iconpackInstance.fieldType + '&iconfig=' + iconfig + '\'', '// Ask the controller politely if he might have a small icon for us.'); //? DEBUG GROUP

      new AjaxRequest(url)
        .withQueryArguments({
          fieldType: iconpackInstance.fieldType,
          iconfig: iconfig
        })
        .get()
        .then(async function(response) {
          const data = await response.resolve();
          console.log(data, '// AJAX returned!') //! DEBUG VALUE
          if (data) {
            callback(data, iconfig);
          } else {
            console.warn('Invalid response or Icon not available!')
          }
        });
      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Add icon to the initiating form field.
     *
     * @callback addIconToField
     * @param {Object} data - The JSON-Result
     * @param {String} iconfig - The iconfig-String
     */
    addIconToField(data, iconfig) {
      let value = '';
      if (data && data.icon) {
        value = data.icon ? iconpackInstance.convertIcon(data.icon, iconfig) : '';
      }
      iconpackInstance.el.$formengineInput.val(iconfig);
      iconpackInstance.el.$palette.addClass('has-change');
      iconpackInstance.el.$iconField.html(value);
    }

    /**
     * Add icon to the initiating RTE.
     *
     * @callback addIconToRte
     * @param {Object} data - The JSON-Result
     * @param {String} iconfig - The iconfig-String
     */
    addIconToRte(data, iconfig) {
      if (data && data.icon) {
        iconpackInstance.editor.insertElement(
          CKEDITOR.dom.element.createFromHtml(
            iconpackInstance.convertIcon(data.icon, iconfig) + '&nbsp'
          )
        );
      }
    }

    /**
     * Callback to update the modal content.
     *
     * @callback updateContent
     * @param {Object} data - The JSON-Result
     * @param {String} iconfig - Unused here
     */
    updateContent(data, iconfig) {
      console.group('Iconpack::updateContent()'); //? DEBUG GROUP

      // Clear search field after update
      iconpackInstance.el.$search.find('input').val('');

      if (data.iconpackOptions !== null) {
        iconpackInstance.el.$options.html(data.iconpackOptions)
      }
      if (data.iconpackIcons !== null) {
        iconpackInstance.el.$icons.html(data.iconpackIcons)
      }
      iconpackInstance.initializeOptionFields();
      iconpackInstance.initializeIconWall();

      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Initialize the content at first run.
     */
    initializeContent() {
      console.group('Iconpack::initializeContent()'); //? DEBUG GROUP

      // Set jQuery fields
      iconpackInstance.el.$modalFooter = iconpackInstance.$imodal.find(iconpackInstance.sel.modalFooter);
      iconpackInstance.el.$styles = iconpackInstance.$imodal.find(iconpackInstance.sel.styles);
      iconpackInstance.el.$options = iconpackInstance.$imodal.find(iconpackInstance.sel.options);
      iconpackInstance.el.$icons = iconpackInstance.$imodal.find(iconpackInstance.sel.icons);
      iconpackInstance.el.$iconSelection = iconpackInstance.$imodal.find(iconpackInstance.sel.iconSelection);
      iconpackInstance.el.$search = iconpackInstance.$imodal.find(iconpackInstance.sel.search);

      /*
      console.log(iconpackInstance.$imodal, '= iconpackInstance.$imodal'); //! DEBUG VALUE
      console.log(iconpackInstance.el.$options, '= iconpackInstance.el.$options'); //! DEBUG VALUE
      console.log(iconpackInstance.el.$icons, '= iconpackInstance.el.$icons'); //! DEBUG VALUE
      console.log(iconpackInstance.el.$modalFooter, '= iconpackInstance.el.$modalFooter'); //! DEBUG VALUE
      console.log(iconpackInstance.el.$styles, '= iconpackInstance.el.$styles'); //! DEBUG VALUE
      console.log(iconpackInstance.el.$search, '= iconpackInstance.el.$search'); //! DEBUG VALUE
      */
      console.log(iconpackInstance.iconfig, '= iconpackInstance.iconfig'); //! DEBUG VALUE

      if (!iconpackInstance.el.$styles || iconpackInstance.el.$styles.length === 0) {
        iconpackInstance.el.$modalFooter.find('.btn-success').css('display', 'none');
        iconpackInstance.el.$modalFooter.find('.btn-warning').css('display', 'none');
      } else {
        iconpackInstance.initializeStyleField();
        iconpackInstance.initializeOptionFields();
        iconpackInstance.initializeIconWall();
        iconpackInstance.initializeSearchField();
      }

      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Initialize the style selector.
     */
    initializeStyleField() {
      console.group('Iconpack::initializeStyleField()'); //? DEBUG GROUP

      // Add OnChange handler that becomes active when another style is selected
      iconpackInstance.el.$styles.on('change', function() {
        console.log('--> TRIGGER: Style has been changed'); //! DEBUG VALUE
        // Add loading icon to the icons section
        Icon.getIcon(
          'spinner-circle',
          Icon.sizes.default,
          null,
          null,
          Icon.markupIdentifiers.inline
        ).then(loadingIcon => {
          iconpackInstance.el.$icons.html(
            '<div class="icons-loading">' + loadingIcon + '</div>'
          );
        });
        // Reset iconfig
        iconpackInstance.iconfig = iconpackInstance.convertIconfigToObject($(this).val());
        if (iconpackInstance.iconfigBackup && iconpackInstance.iconfigBackup.iconpack) {
          if (iconpackInstance.iconfigBackup.iconpackStyle === iconpackInstance.iconfig.iconpackStyle) {
            iconpackInstance.iconfig = iconpackInstance.iconfigBackup;
          } else if (iconpackInstance.iconfigBackup.iconpack === iconpackInstance.iconfig.iconpack) {
            iconpackInstance.iconfig.options = iconpackInstance.iconfigBackup.options;
          }
        }
        // Query the chosen iconpack
        iconpackInstance.ajaxRequest(
          TYPO3.settings.ajaxUrls.iconpack_modal_update,
          iconpackInstance.updateContent,
          $(this).val()
        );
      });

      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Initialize the option fields.
     */
    initializeOptionFields() {
      console.group('Iconpack::initializeOptionFields()'); //? DEBUG GROUP

      iconpackInstance.el.$options.find('.iconpack-option').each(function() {
        let $field = $(this);
        let optionKey = $field.data('key');

        console.log($field, '// Configure option field \'' + optionKey + '\'with predefined values and set triggers '); //! DEBUG VALUE

        if (iconpackInstance.iconfig && iconpackInstance.iconfig.options[optionKey]) {
          iconpackInstance.setFieldValue($field, iconpackInstance.iconfig.options[optionKey]);
        }
        $field.on('change', function() {
          console.log('--> TRIGGER: Option has been changed'); //! DEBUG VALUE
          console.log('optionKey = ' + optionKey);

          let iconIdentifier = iconpackInstance.iconfig['icon'];
          if (iconIdentifier) {
            let iconMarkup = iconpackInstance.el.$icons.find('[name="' + iconIdentifier + '"]').html();
            iconpackInstance.el.$iconSelection.html(iconMarkup);
          }
          iconpackInstance.applyOptions();
        });
      });

      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Get the attributes from the given field.
     * The return value is used as iconfig string.
     *
     * @param {Object} $field
     * @returns {Object}
     */
    getFieldAttributes($field) {
      if ($field) {
        if ($field.is(':checkbox')) {
          if ($field.is(':checked')) {
            return $field.data('attributes');
          }
        } else if ($field.is('select')) {
          let selected = $field.val();
          if (selected) {
            return $field.find('[value=' + selected + ']').data('attributes');
          }
        }
      }
    }

    /**
     * Get the value from the given field.
     *
     * @param {Object} $field
     * @returns {String}
     */
    getFieldValue($field) {
      if ($field) {
        if ($field.is(':checkbox')) {
          return $field.is(':checked') ? true : false;
        } else if ($field.is('select')) {
          return $field.val();
        }
      }
      return null;
    }

    /**
     * Set the value of the given field.
     *
     * @param {Object} $field
     * @param {String} value
     */
    setFieldValue($field, value) {
      if ($field) {
        if ($field.is(':checkbox')) {
          if (value) {
            $field.prop('checked', value);
          }
        } else if ($field.is('select')) {
          $field.val(value);
        }
      }
    }

    /**
     * Initialize the search field.
     */
    initializeSearchField() {
      console.group('Iconpack::initializeSearchField()'); //? DEBUG GROUP

      let $searchFieldInput = iconpackInstance.el.$search.find('input');
      let $searchFieldClearButton = iconpackInstance.el.$search.find('button.close');
      // Move search field to footer
      iconpackInstance.el.$search.detach().prependTo(iconpackInstance.el.$modalFooter);
      // Add triggers to empty the field
      $searchFieldClearButton.click(function() {
        $searchFieldInput.val('').trigger('input');
      });
      // Add trigger, which will be called when entered into the field
      $searchFieldInput.on('input', function() {
        console.log('--> TRIGGER: Search field changed'); //! DEBUG VALUE
        let searchTerm = $searchFieldInput.val();
        if (searchTerm !== '') {
          $searchFieldClearButton.css('visibility', 'visible');
        } else {
          $searchFieldClearButton.css('visibility', 'hidden');
        }
        iconpackInstance.searchIcon(searchTerm);
      });

      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Initialize the icons.
     */
    initializeIconWall() {
      console.group('Iconpack::initializeIconWall()'); //? DEBUG GROUP

      iconpackInstance.el.$icons.find('li').each(function() {
        let $icon = $(this);
        let iconIdentifier = $icon.attr('name');
        if (iconpackInstance.iconfig && iconpackInstance.iconfig.icon) {
          if (iconpackInstance.iconfig.icon === iconIdentifier) {
            $(this).addClass('active');
          }
        }
        // Add onclick to icons
        $icon.click(function() {
          console.log('--> TRIGGER: Icon has been selected'); //! DEBUG VALUE
          // Add class 'active' to the selected icon and remove this class from all other icons
          $(this).closest('section').parent().find('li').removeClass('active');
          $(this).addClass('active');
          iconpackInstance.selectIcon(iconIdentifier);
        });
      });

      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Update the currently displayed icon when an icon is clicked.
     *
     * @param {String} iconIdentifier
     */
    selectIcon(iconIdentifier) {
      console.group('Iconpack::selectIcon()'); //? DEBUG GROUP
      console.log(iconIdentifier, '= iconIdentifier'); //! DEBUG VALUE

      let iconpackStyle = iconpackInstance.el.$styles.val();
      let iconMarkup = iconpackInstance.el.$icons.find('[name="' + iconIdentifier + '"]').html();

      iconpackInstance.el.$iconSelection.html(iconMarkup);
      iconpackInstance.iconfig = iconpackInstance.convertIconfigToObject(iconpackStyle + ',' + iconIdentifier);
      iconpackInstance.applyOptions();

      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Apply currently selected options to iconfig and selected icon.
     */
    applyOptions() {
      console.group('Iconpack::applyOptions()'); //? DEBUG GROUP

      let additionalAttributes = [];
      let iconfig = {
        options: {}
      };
      iconpackInstance.el.$options.find('.iconpack-option').each(function() {
        let $field = $(this);
        let optionKey = $field.data('key');

        let attribute = iconpackInstance.getFieldAttributes($field)
        if (attribute) {
          additionalAttributes.push(attribute);
        }
        let value = iconpackInstance.getFieldValue($field)
        if (value) {
          iconfig.options[optionKey] = value;
        }
      });

      iconpackInstance.mergeIconfig(iconfig);
      iconpackInstance.mergeAttributesIntoIconSelection(additionalAttributes);

      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Merge attributes into selected icon element.
     *
     * @param {Object} additionalAttributes
     */
    mergeAttributesIntoIconSelection(additionalAttributes) {
      console.group('Iconpack::mergeAttributesIntoIconSelection()'); //? DEBUG GROUP
      console.log(additionalAttributes, '= additionalAttributes'); //! DEBUG VALUE

      let $iconElement = iconpackInstance.el.$iconSelection.children().first();

      console.log($iconElement, '= iconElement // BEFORE'); //! DEBUG VALUE

      $.each(additionalAttributes, function(index, attributes) {
        $.each(attributes, function(key, value) {
          switch (key) {
            case 'class':
              $iconElement.addClass(value);
              break;
            case 'style':
              $iconElement.css(value);
              break;
            default:
              $iconElement.attr(key, value);
          }
        });
      });

      console.log($iconElement, '= iconElement // AFTER'); //! DEBUG VALUE
      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Search for a specific icon name in the currently available list.
     *
     * @param {string} searchString - The search string
     */
    searchIcon(searchString) {
      console.group('Iconpack::searchIcon()'); //? DEBUG GROUP
      console.log(searchString, '= searchString'); //! DEBUG VALUE

      iconpackInstance.el.$icons.find('section').each(function() {
        let $this = $(this);
        let enableSection = false;

        $this.find('li').each(function() {
          let $icon = $(this);
          let name = $icon.attr('name');

          if (name && name.indexOf(searchString) >= 0) {
            $icon.css('display', 'inline-flex');
            enableSection = true;
          } else {
            $icon.css('display', 'none');
          }
        });

        if (enableSection) {
          $this.css('display', 'block');
        } else {
          $this.css('display', 'none');
        }
      });

      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Converts an icon object into a HTML string.
     *
     * @param {Object} iconObject - The icon object from Ajax response
     * @param {String} iconfig - The iconfig string
     * @returns {String}
     */
    convertIcon(iconObject, iconfig) {
      console.group('Iconpack::convertIcon()'); //? DEBUG GROUP

      let $icon = null;
      if (iconObject) {
        iconObject.attributes['data-iconfig'] = iconfig;
        $icon = $('<' + iconObject.elementName + '>', iconObject.attributes);
        console.log(iconObject, 'iconObject');
        // Insert space to keep Icon selectable in CKEditor
        if (iconObject.innerHtml === '') {
          $icon.html(' ');
        } else {
          if (iconObject.type === 'svgInline') {
            // Create and parse XML-Element to allow short opened tags in SVG
            let xmlDoc = $.parseXML($icon.prop('outerHTML')),
              $xml = $(xmlDoc);
            $icon = $xml.find('svg');
          }
          $icon.html(iconObject.innerHtml);
        }
      }

      console.log($icon ? $icon.prop('outerHTML') : '', '// Final icon markup'); //! DEBUG VALUE

      console.groupEnd(); //? DEBUG GROUP

      return $icon ? $icon.prop('outerHTML') : '';
    }

    /**
     * Load CSS files into parent main frame.
     */
    injectCSS() {
      console.group('Iconpack::injectCSS()'); //? DEBUG GROUP

      $.each(this.styleSheets, function(index, cssFile) {
        console.log(cssFile, '// Inject CSS-file'); //! DEBUG VALUE
        if (!window.parent.$('link[href="' + cssFile + '"]').length) {
          let $cssLink = $('<link />', {
            rel: 'stylesheet',
            type: 'text/css',
            href: cssFile,
            name: 'iconpack[' + index + ']'
          });
          window.parent.$('head').append($cssLink);
        }
      });

      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Unload CSS files from parent main frame.
     */
    unlinkCSS() {
      console.group('Iconpack::unlinkCSS()'); //? DEBUG GROUP

      let cssLinks = window.parent.$('link[name^=iconpack]');
      if (cssLinks.length) {
        $.each(cssLinks, function(index, cssLink) {
          console.log(cssLink, '// Unlink CSS-file'); //! DEBUG VALUE
          cssLink.remove();
        });
      }

      console.groupEnd(); //? DEBUG GROUP
    }

    /**
     * Convert an iconfig string to object.
     *
     * @param {string} iconfig
     * @returns {Object}
     */
    convertIconfigToObject(iconfig) {
      console.group('Iconpack::convertIconfigToObject()'); //? DEBUG GROUP
      console.log(iconfig, '// iconfig {String}'); //! DEBUG VALUE

      let result = null;
      if (iconfig) {
        iconfig = iconfig.split(',');
        let iconpackStyle = iconfig[0].split(':');
        result = {
          iconpackStyle: iconfig[0] || null,
          iconpack: iconpackStyle[0] || null,
          style: iconpackStyle[1] || null,
          icon: iconfig[1] || null,
          options: {},
        }
        for (let i = 2; i < iconfig.length; i++) {
          let options = iconfig[i].split(':');
          result.options[options[0]] = options[1];
        }
      }

      console.log(result, '// iconfig {Object}'); //! DEBUG VALUE
      console.groupEnd(); //? DEBUG GROUP

      return result;
    }

    /**
     * Convert an iconfig object to string.
     *
     * @param {Object} iconfig
     * @returns {string}
     */
    convertIconfigToString(iconfig) {
      console.group('Iconpack::convertIconfigToString()'); //? DEBUG GROUP
      console.log(iconfig, '// iconfig {Object}'); //! DEBUG VALUE

      let result = '';
      if (iconfig) {
        result = iconfig.iconpackStyle + ',' + iconfig.icon;
        if (iconfig.options) {
          for (var key in iconfig.options) {
            result += ',' + key + ':' + iconfig.options[key];
          }
        }
      }

      console.log(result, '// iconfig {String}'); //! DEBUG VALUE
      console.groupEnd(); //? DEBUG GROUP

      return result;
    }

    /**
     * Merge options into iconfig.
     *
     * @param {Object} options
     */
    mergeIconfig(iconfig) {
      console.group('Iconpack::mergeIconfig()'); //? DEBUG GROUP
      console.log(iconfig.options, '// Options to merge'); //! DEBUG VALUE
      console.log(iconpackInstance.iconfig.options, '= iconpackInstance.iconfig.options // BEFORE'); //! DEBUG VALUE

      iconpackInstance.iconfig = {
        ...iconpackInstance.iconfig,
        ...iconfig
      };

      // Backup iconfig
      iconpackInstance.iconfigBackup = iconpackInstance.iconfig;

      console.log(iconpackInstance.iconfig.options, '= iconpackInstance.iconfig.options // AFTER'); //! DEBUG VALUE
      console.groupEnd(); //? DEBUG GROUP
    }

  }

  let iconpackInstance;
  return iconpackInstance || (iconpackInstance = new Iconpack), iconpackInstance
}));
