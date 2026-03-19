<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\Form\Element;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Quellenform\Iconpack\IconpackFactory;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconSize;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Utility\StringUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;

/**
 * Iconpack input element.
 *
 * Shows the form elements for iconpack fields.
 */
class IconpackWizardElement extends AbstractFormElement
{
    /**
     * @var IconFactory
     */
    protected $iconFactory;

    /**
     * @var IconpackFactory
     */
    protected $iconpackFactory;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $itemId;

    /**
     * @var string
     */
    private $itemName;

    /**
     * @var string
     */
    private $itemValue;

    /**
     * Default field information enabled for this element.
     *
     * @var array
     */
    protected $defaultFieldInformation = [
        'tcaDescription' => [
            'renderType' => 'tcaDescription',
        ],
    ];

    /**
     * Default field wizards enabled for this element.
     *
     * @var array
     */
    protected $defaultFieldWizard = [
        'localizationStateSelector' => [
            'renderType' => 'localizationStateSelector',
        ],
        'otherLanguageContent' => [
            'renderType' => 'otherLanguageContent',
            'after' => [
                'localizationStateSelector',
            ],
        ],
        'defaultLanguageDifferences' => [
            'renderType' => 'defaultLanguageDifferences',
            'after' => [
                'otherLanguageContent',
            ],
        ],
    ];

    /**
     * This will render a button, which allows to select an Icon for the header.
     *
     * @return array As defined in initializeResultArray() of AbstractNode
     */
    public function render(): array
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->iconpackFactory = GeneralUtility::makeInstance(IconpackFactory::class);

        /** @var PageRenderer $pageRenderer */
        $pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
        // Add inline labels for JavaScript
        $pageRenderer->addInlineLanguageLabelFile('EXT:iconpack/Resources/Private/Language/locallang_be.xlf', 'js.');

        $parameterArray = $this->data['parameterArray'];

        $this->itemId = StringUtility::getUniqueId();
        $this->config = $parameterArray['fieldConf']['config'];
        $this->itemName = (string) ($parameterArray['itemFormElName'] ?? '');
        $this->itemValue = (string) ($parameterArray['itemFormElValue'] ?? '');

        $resultArray = $this->initializeResultArray();

        $this->addJavaScriptModules($resultArray);
        $this->addFormFields($resultArray);

        return $resultArray;
    }

    /**
     * Add the form field for iconpack.
     *
     * @param array $resultArray
     *
     * @return void
     */
    private function addFormFields(array &$resultArray): void
    {
        $width = $this->formMaxWidth(
            MathUtility::forceIntegerInRange(
                $this->config['size'] ?? $this->defaultInputWidth,
                $this->minimumInputWidth,
                $this->maxInputWidth
            )
        );

        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldInformationResult, false);

        $fieldWizardResult = $this->renderFieldWizard();
        $fieldWizardHtml = $fieldWizardResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($resultArray, $fieldWizardResult, false);

        $html = [];
        $html[] = '<div class="form-control-wrap" style="max-width: ' . $width . 'px">';
        $html[] = '<div class="form-wizards-wrap">';
        $html[] = '<div class="form-wizards-item-element">';
        $html[] = '<div class="input-group t3js-form-field-iconpack">';
        $html[] = $this->createFormFieldElements();
        $html[] = '</div>';
        $html[] = '</div>';
        if (!empty($fieldWizardHtml)) {
            $html[] = '<div class="form-wizards-item-bottom">' . $fieldWizardHtml . '</div>';
        }
        $html[] = '</div>';
        $html[] = '</div>';

        $resultArray['html'] = $this->maybeAddLabelforTYPO3v13();

        $resultArray['html'] .= $this->createElement(
            'typo3-formengine-element-iconpack',
            [
                'class' => 'formengine-field-item t3js-formengine-field-item',
                'recordFieldId' => htmlspecialchars('formengine-input-' . $this->itemId)
            ],
            $fieldInformationHtml . implode(LF, $html)
        );
    }

    /**
     * Create the form field elements that displays the iconpack selector in different ways.
     *
     * @return string
     */
    private function createFormFieldElements(): string
    {
        $formElementStyle = $this->getFormElementStyle();
        $innerItems = [];

        $hiddenField = $this->createElement(
            'input',
            [
                'type' => 'hidden',
                'name' => $this->itemName,
                'value' => htmlspecialchars($this->itemValue)
            ],
            '',
            true
        );

        $iconClass = 'input-group-text input-group-icon';

        $iconContent = $this->createIconContent();
        if (stripos($formElementStyle, 'input') !== false) {
            $innerItems[1] = $this->createInputElement() . $hiddenField;
        } else {
            $iconContent .= $hiddenField;
        }
        if (stripos($formElementStyle, 'button') !== false) {
            $innerItems[3] = $this->createModalOpener(
                $this->createButtonContent()
            );
            // v10 compatibility: Add CSS class
            $iconClass .= ' input-group-addon';
            $innerItems[0] = $this->createElement(
                'span',
                ['class' => $iconClass],
                $iconContent
            );
        } else {
            $innerItems[2] = $this->createModalOpener(
                $iconContent,
                $iconClass
            );
        }
        ksort($innerItems);
        return implode(LF, $innerItems);
    }

    /**
     * Create the icon element.
     *
     * @return string
     */
    private function createIconContent(): string
    {
        $iconMarkup = '';
        if (
            !empty($this->itemValue)
            && substr($this->itemValue, 0, 4) !== 'EXT:'
        ) {
            $iconMarkup = $this->iconpackFactory->getIconMarkup($this->itemValue);
            if (empty($iconMarkup)) {
                $iconMarkup = $this->getSystemIcon('default-not-found');
            }
        }
        return $this->createElement(
            'span',
            [
                'class' => 't3js-icon icon',
                'aria-hidden' => 'true'
            ],
            $this->createElement(
                'span',
                ['class' => 'icon-markup'],
                $iconMarkup
            )
        );
    }

    /**
     * Create the input field.
     *
     * @return string
     */
    private function createInputElement(): string
    {
        $this->config['eval'] = 'nospace';
        $attributes = [
            'type' => 'text',
            'value' => '',
            'id' => 'formengine-input-' . $this->itemId,
            'class' => 'form-control t3js-clearable',
            'data-formengine-validation-rules' => $this->getValidationDataAsJsonString($this->config),
            'data-formengine-input-params' => (string) json_encode(
                [
                    'field' => $this->itemName,
                    'evalList' => $this->config['eval']
                ],
                JSON_THROW_ON_ERROR
            ),
            'data-formengine-input-name' => $this->itemName,
        ];
        return $this->createElement(
            'input',
            $attributes,
            '',
            true
        );
    }

    /**
     * Create the element which opens the iconpack modal window.
     *
     * @param string $content
     * @param string $additionalClasses
     *
     * @return string
     */
    private function createModalOpener(string $content, string $additionalClasses = ''): string
    {
        $buttonTooltip = (string) (
            $this->config['buttonTooltip']
            ?? 'LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:js.label.iconNative'
        );
        if (!empty($buttonTooltip)) {
            $buttonTooltip = $this->getLanguageService()->sL($buttonTooltip);
        }
        $attributes = [
            'type' => 'button',
            'title' => htmlspecialchars($buttonTooltip),
            'class' => 'btn btn-default' . (!empty($additionalClasses) ? ' ' . $additionalClasses : ''),
            'id' => 'formengine-button-' . $this->itemId,
            'data-item-name' => htmlspecialchars($this->itemName)
        ];
        $button = $this->createElement(
            'button',
            $attributes,
            $content
        );
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.0.0', '>=')) {
            return $button;
        } else {
            return $this->createElement(
                'span',
                ['class' => 'input-group-btn'],
                $button
            );
        }
    }

    /**
     * Create the button content.
     *
     * @return string
     */
    private function createButtonContent(): string
    {
        $buttonIcon = (string) ($this->config['buttonIcon'] ?? 'actions-iconpack');
        if (!empty($buttonIcon)) {
            $buttonIcon = $this->getSystemIcon($buttonIcon);
        }

        $buttonLabel = (string) ($this->config['buttonLabel'] ?? '');
        if (!empty($buttonLabel)) {
            $buttonLabel = $this->getLanguageService()->sL($buttonLabel);
        }
        return $buttonIcon . $buttonLabel;
    }

    /**
     * Create an HTML element with the given attributes and content.
     *
     * @param string $elementName
     * @param array $attributes
     * @param string $content
     * @param boolean $closeTag
     *
     * @return string
     */
    private function createElement(
        string $elementName,
        array $attributes = [],
        string $content = '',
        bool $closeTag = false
    ): string {
        $html = '<' .  $elementName . ' ' . GeneralUtility::implodeAttributes($attributes, true);
        if (empty($content) && $closeTag) {
            $html .= ' />';
        } else {
            $html .= '>' . $content . '</' . $elementName . '>';
        }
        return $html;
    }

    /**
     * Add JavaScript modules to the resulting array.
     *
     * @param array $resultArray
     *
     * @return void
     */
    private function addJavaScriptModules(array &$resultArray): void
    {
        // Add JavaScript module
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.0.0', '>=')) {
            $resultArray['javaScriptModules'][] = JavaScriptModuleInstruction::create(
                '@quellenform/iconpack-wizard.js'
            )->instance($this->itemId);
        } elseif (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.5.0', '>=')) {
            /**
             * @disregard P1013 (Undefined method 'forRequireJS')
             * @extensionScannerIgnoreLine
             */
            $resultArray['requireJsModules'][] = JavaScriptModuleInstruction::forRequireJS(
                'TYPO3/CMS/Iconpack/v11/IconpackWizard'
            )->instance($this->itemId);
        } else {
            $resultArray['requireJsModules'][] = [
                'TYPO3/CMS/Iconpack/v11/IconpackWizard' => '
                function(IconpackWizard) {
                    new IconpackWizard(' . GeneralUtility::quoteJSvalue($this->itemId) . ');
                }
                '
            ];
        }
    }

    /**
     * Try to get the form element style from the extension configuration or directly from TCA.
     *
     * @return string
     */
    private function getFormElementStyle(): string
    {
        try {
            $formElementStyleExtConf = GeneralUtility::makeInstance(
                ExtensionConfiguration::class
            )->get('iconpack', 'formElementStyle');
        } catch (ExtensionConfigurationPathDoesNotExistException $e) {
            $formElementStyleExtConf = null;
        }
        $formElementStyleTca = (
            isset($this->config['formElementStyle']) && !empty($this->config['formElementStyle'])
        ) ? $this->config['formElementStyle'] : null;
        return $formElementStyleTca ?? $formElementStyleExtConf ?? 'iconButton';
    }

    /**
     * Get a specific system icon directly from the IconFactory.
     *
     * @param string $identifier
     *
     * @return string
     */
    private function getSystemIcon(string $identifier): string
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.0.0', '>=')) {
            return $this->iconFactory->getIcon($identifier, IconSize::SMALL)->render();
        } else {
            /**
             * @disregard P1007 ('SIZE_SMALL' is deprecated)
             * @extensionScannerIgnoreLine
             */
            return $this->iconFactory->getIcon($identifier, Icon::SIZE_SMALL)->render();
        }
    }

    /**
     * Changed in version 13.3
     * The label of a custom field does not get rendered automatically anymore but must be
     * rendered with $this->renderLabel($fieldId) or $this->wrapWithFieldsetAndLegend().
     * see https://docs.typo3.org/m/typo3/reference-tca/main/en-us/ColumnsConfig/Type/User/Index.html
     *
     * @return array
     */
    protected function maybeAddLabelforTYPO3v13(): string
    {
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '13.3', '>=')) {
            /**
             * method may not exist prior to TYPO3 v13.3
             * @noinspection PhpUndefinedMethodInspection
             */
            return $this->renderLabel('formengine-button-' . $this->itemId);
        }
        return '';
    }

    /**
     * @return LanguageService
     */
    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
