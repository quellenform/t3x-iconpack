<?php

declare(strict_types=1);

namespace Quellenform\Iconpack;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */


use Quellenform\Iconpack\Exception\IconpackException;
use Quellenform\Iconpack\IconpackCache;
use Quellenform\Iconpack\IconpackRegistry;
use Quellenform\Iconpack\Utility\IconpackRenderer;
use Quellenform\Iconpack\Utility\IconpackUtility;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * The main factory class, which acts as the entrypoint for generating an Iconpack object which
 * is responsible for providing iconpacks. Checks for the correct Iconpack Provider through the IconpackRegistry.
 *
 * Normal usage:
 *   $iconpack = GeneralUtility::makeInstance(\Quellenform\Iconpack\IconpackFactory::class);
 *   $iconpack->getIconMarkup($iconfigString);
 *
 * Usage with discrete context and no caching:
 *   $iconpack = GeneralUtility::makeInstance(\Quellenform\Iconpack\IconpackFactory::class, false);
 *   $iconpack->setContext('backend');
 *   $iconpack->getIconMarkup($iconfigString);
 */
class IconpackFactory implements SingletonInterface
{

    /**
     * @var IconpackRegistry
     */
    protected $iconpackRegistry;

    /**
     * @var IconpackCache
     */
    protected $iconpackCache;

    /**
     * @var array|null
     */
    protected $config = null;

    /**
     * @var array|null
     */
    protected $configArray = null;

    /**
     * @var string|null
     */
    protected static $context = null;

    /**
     * @var array|null
     */
    protected static $availableIconpacks = null;

    /**
     * @var array|null
     */
    protected static $preferredRenderTypes = null;

    /**
     * @param bool $cacheEnabled
     */
    public function __construct(bool $cacheEnabled = true)
    {
        //$cacheEnabled = false; // DEV
        $this->iconpackRegistry = GeneralUtility::makeInstance(IconpackRegistry::class);
        $this->setAvailableIconpacks();
        $this->iconpackCache = GeneralUtility::makeInstance(IconpackCache::class, $cacheEnabled);
        $this->setContext();
    }

    /**
     * @internal
     */
    private function setAvailableIconpacks()
    {
        if (!static::$availableIconpacks) {
            static::$availableIconpacks = $this->iconpackRegistry->getIconpackProviderIdentifiers();
        }
    }

    /**
     * Get all keys of the available iconpack providers.
     *
     * @return array
     */
    public function getAvailableIconpacks(): array
    {
        return static::$availableIconpacks ?? [];
    }

    /**
     * Check if a specific iconpack is installed.
     *
     * @param string|null $iconpackIdentifier
     *
     * @return bool
     */
    public function isIconpackInstalled(?string $iconpackIdentifier): bool
    {
        if ($iconpackIdentifier && in_array($iconpackIdentifier, static::$availableIconpacks)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if there are any iconpacks installed.
     *
     * @return bool
     */
    public function areThereAnyIconpacksInstalled(): bool
    {
        if ((bool) count(static::$availableIconpacks)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set current context.
     *
     * @param string|null $context
     *
     * @return void
     */
    public function setContext(?string $context = null)
    {
        if (!$context) {
            if (!static::$context) {
                static::$context = 'frontend';
                if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
                    static::$context = 'backend';
                }
            }
        } else {
            if (!($context === 'backend' || $context === 'frontend')) {
                throw new IconpackException('No valid context specified. Must be \'backend \' or \'frontend\'', 2100109275);
            }
            static::$context = $context;
        }
    }

    /**
     * Set the preferred renderTypes according to the current context.
     *
     * @return void
     */
    public function setPreferredRenderTypes()
    {
        if (static::$context === 'backend') {
            foreach (static::$availableIconpacks as $iconpack) {
                static::$preferredRenderTypes[$iconpack] = $this->iconpackRegistry
                    ->getIconpackProviderByIdentifier($iconpack)
                    ->getPreferredRenderTypes()[static::$context] ?? [];
            }
        } else {
            // Get the plugin configuration
            $settings = GeneralUtility::makeInstance(ConfigurationManager::class)
                ->getConfiguration(
                    ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS,
                    'iconpack'
                ) ?? null;
            // Get default rendertype configuration from TypoScript/TS
            $defaultRenderTypes = IconpackUtility::parseRenderTypeFromTypoScript('_default', $settings);
            // Override/Merge rendertypes
            foreach (static::$availableIconpacks as $iconpack) {
                $iconpackRenderTypes = $this->iconpackRegistry
                    ->getIconpackProviderByIdentifier($iconpack)
                    ->getPreferredRenderTypes()[static::$context] ?? [];
                $overrideRenderTypes = IconpackUtility::parseRenderTypeFromTypoScript(
                    $iconpack,
                    $settings,
                    $defaultRenderTypes
                );
                static::$preferredRenderTypes[$iconpack] = $this->mergeRenderTypes(
                    $iconpackRenderTypes,
                    $overrideRenderTypes
                );
            }
        }
    }

    /**
     * Merge two renderTypes arrays.
     *
     * @param array $iconpackRenderTypes
     * @param array $overrideRenderTypes
     *
     * @return array
     */
    public function mergeRenderTypes(array $iconpackRenderTypes, array $overrideRenderTypes): array
    {
        foreach (['native', 'rte'] as $fieldType) {
            if (isset($overrideRenderTypes[$fieldType])) {
                $iconpackRenderTypes[$fieldType] = $overrideRenderTypes[$fieldType];
            }
        }
        return $iconpackRenderTypes;
    }

    /**
     * Return first available render type from a specific iconpack or from given array.
     *
     * @param array $dataArray
     * @param string|null $iconpack
     * @param string|null $fieldType
     * @param array|null $preferredRenderTypes
     *
     * @return string|null
     */
    public function resolvePreferredRenderType(
        array $dataArray,
        ?string $iconpack = null,
        ?string $fieldType = null,
        ?array $preferredRenderTypes = null
    ): ?string {
        if (!static::$preferredRenderTypes) {
            $this->setPreferredRenderTypes();
        }
        if ($iconpack && $fieldType) {
            $preferredRenderTypes = static::$preferredRenderTypes[$iconpack][$fieldType] ?? null;
        }
        if ($preferredRenderTypes) {
            foreach ($preferredRenderTypes as $renderType) {
                if (
                    isset($dataArray[$renderType])
                    && is_array($dataArray[$renderType])
                ) {
                    return $renderType;
                }
            }
        }
        return null;
    }

    /**
     * Get cached configuration or get it directly from IconpackProvider.
     *
     * @param string $iconpackKey The name of the iconpack
     * @param string|null $selection Return a specific key of the iconpack configuration array
     *
     * @return array|null
     */
    public function queryConfig(string $iconpackKey, ?string $selection = null): ?array
    {
        // Check if the requested iconpack already exists
        if (!isset($this->config[$iconpackKey])) {
            // Check if the requested iconpack is installed at all
            if ($this->isIconpackInstalled($iconpackKey)) {
                // Get the requested iconpack directly from cache
                $this->config = $this->iconpackCache->getCacheByIdentifier(
                    $this->getCacheIdentifier('config')
                );
                // Check if the requested iconpack exists in the cache
                if (!isset($this->config[$iconpackKey])) {
                    // Check if any iconpacks are installed at all
                    if ($this->areThereAnyIconpacksInstalled()) {
                        // Create a new configuration from all registered iconpacks
                        foreach (static::$availableIconpacks as $iconpack) {
                            /** @var IconpackProvider $iconpackProvider */
                            $iconpackProvider =
                                $this->iconpackRegistry->getIconpackProviderByIdentifier($iconpack);
                            $this->config[$iconpack] = [
                                'title' => $iconpackProvider->getTitle(),
                                'key' => $iconpackProvider->getKey(),
                                'version' => $iconpackProvider->getVersion(),
                                'url' => $iconpackProvider->getUrl(),
                                'logo' => $iconpackProvider->getLogo(),
                                'renderTypes' => $iconpackProvider->getRenderTypes(),
                                'stylesEnabled' => $iconpackProvider->getStylesEnabled(),
                                'categories' => $iconpackProvider->getCategories(),
                                'icons' => $iconpackProvider->getIcons(),
                                'options' => $iconpackProvider->getAdditionalOptions()
                            ];
                        }
                        if ($this->config) {
                            $this->iconpackCache->setCacheByIdentifier(
                                $this->getCacheIdentifier('config'),
                                $this->config
                            );
                        }
                    }
                }
            }
        }
        if ($selection) {
            return $this->config[$iconpackKey][$selection] ?? null;
        }
        return $this->config[$iconpackKey] ?? null;
    }

    /**
     * Query all assets from registered iconpack providers of a speicific scope.
     *
     * @param string $assetType The asset type (css|js)
     * @param string $scope The asset scope (backend|ckeditor|frontend)
     *
     * @return array
     */
    public function queryAssets(string $assetType, string $scope): array
    {
        $assets = [];
        foreach (static::$availableIconpacks as $iconpack) {
            $fieldType = ($scope === 'ckeditor' ? ['rte'] : ['native', 'rte']);
            $renderTypesConfig = $this->queryConfig($iconpack, 'renderTypes');
            foreach (['native', 'rte'] as $fieldType) {
                if ($renderType = $this->resolvePreferredRenderType($renderTypesConfig, $iconpack, $fieldType)) {
                    foreach ($renderTypesConfig[$renderType] as $styleConf) {
                        $assets[] = $styleConf[$assetType][$scope] ?? [];
                    }
                }
            }
        }
        // Flatten the multidimensional array
        $flatArray = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($assets));
        foreach ($iterator as $value) {
            $flatArray[] = $value;
        }
        return $flatArray;
    }

    /**
     * Get all styles from all iconpacks for dropdowns in CKEditor and Backend.
     *
     * @param string $fieldType
     *
     * @return array|null
     */
    public function queryIconpackStyles(string $fieldType): ?array
    {
        $iconpackStyles = null;
        foreach (static::$availableIconpacks as $iconpack) {
            $renderType = $this->resolvePreferredRenderType(
                $this->queryConfig($iconpack, 'renderTypes'),
                $iconpack,
                $fieldType
            );
            if ($renderType) {
                $config = $this->queryConfig($iconpack);
                if ($config['stylesEnabled']) {
                    foreach ($config['renderTypes'][$renderType] as $styleKey => $styleConf) {
                        $key = $config['key'] . ':' . $styleKey;
                        $label = $styleConf['label'] ?? IconpackUtility::keyToWord($styleKey);
                        $iconpackStyles[$key]['label'] = $config['title'] . ' (' . $label . ')';
                    }
                } else {
                    $key = $config['key'];
                    $label = reset($config['renderTypes'][$renderType])['label'] ?? $config['title'];
                    $iconpackStyles[$key]['label'] = $label;
                }
            }
        }
        return $iconpackStyles;
    }

    /**
     * Get all options of a specific iconpack for dropdowns in CKEditor and Backend.
     *
     * @param string $iconpack
     *
     * @return array|null
     */
    public function queryIconpackOptions(string $iconpack): ?array
    {
        return $this->queryConfig($iconpack, 'options');
    }

    /**
     * Get all icons from a specific iconpack and a specific style for the Modal in CKEditor and Backend.
     *
     * @param array $iconfig
     *
     * @return array|null
     */
    public function queryIconpackIcons(array $iconfig): ?array
    {
        if (!isset($iconfig['iconpack'])) {
            return null;
        }
        if ($this->isIconpackInstalled($iconfig['iconpack'])) {
            $cacheIdentifier = $this->getCacheIdentifier('iconpackIcons_' . $iconfig['iconpackStyle']);
            if ($iconpackIcons = $this->iconpackCache->getCacheByIdentifier($cacheIdentifier)) {
                return $iconpackIcons;
            }
            $configuration = $this->queryConfig($iconfig['iconpack']);
            if ($configuration) {
                $style = $this->validateIconpackStyle($iconfig['style'] ?? null, $configuration['stylesEnabled']);
                if ($configuration['icons']) {
                    $iconpackIcons = IconpackUtility::prepareIconSet(
                        $configuration['icons'],
                        $configuration['categories'],
                        $style
                    );
                    $preferredRenderType = $this->resolvePreferredRenderType(
                        $configuration['renderTypes'],
                        $iconfig['iconpack'],
                        $iconfig['fieldType']
                    );
                    if (isset($configuration['renderTypes'][$preferredRenderType])) {
                        $renderConf = IconpackUtility::selectIconpackStyleConf(
                            $configuration['renderTypes'][$preferredRenderType],
                            $style
                        );
                        $renderConf['type'] = $preferredRenderType;
                        // Iterate through styles
                        foreach ($iconpackIcons as $styleKey => $_) {
                            foreach ($iconpackIcons[$styleKey]['icons'] as $iconKey => $iconLabel) {
                                $renderConf['label'] = $iconLabel;
                                $iconpackIcons[$styleKey]['icons'][$iconKey] =
                                    IconpackRenderer::renderIcon(
                                        IconpackRenderer::createIconElement(
                                            (string) $iconKey,
                                            $renderConf
                                        )
                                    );
                            }
                        }
                    }
                }
            }
            if ($iconpackIcons) {
                $this->iconpackCache->setCacheByIdentifier($cacheIdentifier, $iconpackIcons);
                return $iconpackIcons;
            }
        }
        return null;
    }

    /**
     * Get the first available iconpack style if the key is a string, otherwise return null.
     *
     * @param string|null $style
     * @param array|null $styles
     *
     * @return string|null
     */
    public static function validateIconpackStyle(?string $style, ?array $styles): ?string
    {
        if (!$style && is_array($styles)) {
            $style = array_key_first($styles);
            if (empty($style)) {
                return null;
            }
        }
        return $style;
    }

    /**
     * Get a specific icon by iconfigString rendered as HTML.
     *
     * @param string|null $iconfigString
     * @param string $fieldType
     * @param array|null $additionalAttributes
     * @param string|null $preferredRenderTypes
     *
     * @return string
     */
    public function getIconMarkup(
        ?string $iconfigString,
        string $fieldType = 'native',
        ?array $additionalAttributes = null,
        ?string $preferredRenderTypes = null
    ): string {
        $iconMarkup = '';
        if (!empty($iconfigString)) {
            // Split renderTypes into array
            if ($preferredRenderTypes && !empty($preferredRenderTypes)) {
                $preferredRenderTypes = GeneralUtility::trimExplode(',', $preferredRenderTypes, true);
            } else {
                $preferredRenderTypes = null;
            }
            // Get the icon element
            $iconElement = $this->getIconElement(
                IconpackUtility::convertIconfigToArray($fieldType, $iconfigString),
                $additionalAttributes,
                $preferredRenderTypes
            );
            if ($iconElement) {
                // Add iconfig as data-attribute to keep the element transformable (RTE <-> DB)
                $iconElement['attributes']['data-iconfig'] = $iconfigString;
                // Finally render the icon
                $iconMarkup = IconpackRenderer::renderIcon($iconElement);
            }
        }
        return $iconMarkup;
    }

    /**
     * Get a specific icon element for further processing.
     *
     * @param array $iconfig
     * @param array|null $additionalAttributes
     * @param array|null $preferredRenderTypes
     *
     * @return array|null
     */
    public function getIconElement(array $iconfig, ?array $additionalAttributes = null, ?array $preferredRenderTypes = null): ?array
    {
        // Check if the required fields are set and if the requested iconpack is installed
        if (
            isset($iconfig['iconpack']) &&
            isset($iconfig['icon']) &&
            isset($iconfig['fieldType']) &&
            $this->isIconpackInstalled($iconfig['iconpack'])
        ) {
            // Check if a configuration exists for the requested iconpack
            if ($configuration = $this->queryConfig($iconfig['iconpack'])) {
                // Check if the requested icon is available
                if ($icon = $configuration['icons'][$iconfig['icon']]) {
                    // Get the preferred renderType for the requested icon
                    if ($preferredRenderTypes) {
                        // Use the given array of preferred renderTypes
                        $preferredRenderType = $this->resolvePreferredRenderType(
                            $configuration['renderTypes'],
                            null,
                            null,
                            $preferredRenderTypes
                        );
                    } else {
                        // Select the preferred renderTypes from existing array
                        $preferredRenderType = $this->resolvePreferredRenderType(
                            $configuration['renderTypes'],
                            $iconfig['iconpack'],
                            $iconfig['fieldType']
                        );
                    }
                    // Check if the preferred renderType is available
                    if (isset($configuration['renderTypes'][$preferredRenderType])) {
                        // Select the appropriate render configuration for the requested icon
                        $conf = IconpackUtility::selectIconpackStyleConf(
                            $configuration['renderTypes'][$preferredRenderType],
                            $iconfig['style'] ?? null
                        );
                        // Merge/override existing attributes with additional attributes
                        $conf['additionalAttributes'] = IconpackUtility::mergeAttributes(
                            $this->getAdditionalAttributesFromOptions(
                                $iconfig['options'] ?? null,
                                $configuration['options']
                            ),
                            IconpackUtility::explodeAttributes($additionalAttributes),
                        );
                        // Set the renderType which is required for rendering
                        $conf['type'] = $preferredRenderType;
                        $conf['label'] = $icon['label'];
                        // Everything looks fine so far, try to set the icon element
                        try {
                            $iconElement = IconpackRenderer::createIconElement(
                                $iconfig['icon'],
                                $conf
                            );
                            return $iconElement;
                        } catch (IconpackException $e) {
                            // Oops, silently ignore the request
                            return null;
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * Get additional attributes from the options-configuration.
     *
     * @param array|null $options
     * @param array|null $conf
     *
     * @return array
     */
    public function getAdditionalAttributesFromOptions(?array $options, ?array $conf): array
    {
        $attributes = [];
        if ($conf && $options) {
            foreach ($options as $optionKey => $optionValue) {
                if (isset($conf[$optionKey]['type'])) {
                    $additionalAttributes = null;
                    switch ($conf[$optionKey]['type']) {
                        case 'select':
                            $additionalAttributes =
                                $conf[$optionKey]['values'][$optionValue]['attributes'] ?? null;
                            break;
                        case 'checkbox':
                            if (filter_var($optionValue, FILTER_VALIDATE_BOOLEAN)) {
                                $additionalAttributes = $conf[$optionKey]['attributes'] ?? null;
                            }
                            break;
                    }
                    if ($additionalAttributes) {
                        $attributes = IconpackUtility::mergeAttributes(
                            $attributes,
                            $additionalAttributes
                        );
                    }
                }
            }
        }
        return $attributes;
    }

    /**
     * Get the language specific cache identifier.
     *
     * @param string $cacheIdentifier
     *
     * @return string
     */
    private function getCacheIdentifier(string $cacheIdentifier): string
    {
        if (static::$context === 'backend') {
            if (isset($GLOBALS['BE_USER']->uc['lang'])) {
                $language = $GLOBALS['BE_USER']->uc['lang'];
            }
        } else {
            if (isset($GLOBALS['TSFE']->config['config']['language'])) {
                $language = $GLOBALS['TSFE']->config['config']['language'];
            }
        }
        if (!$language || empty($language)) {
            $language = 'default';
        }
        //$cacheIdentifier = 'Iconpack_' . $language . '_' . str_replace(':', '-', $cacheIdentifier); // DEV
        $cacheIdentifier = 'Iconpack_' . md5($language . '_' . $cacheIdentifier);
        return $cacheIdentifier;
    }
}