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

use Quellenform\Iconpack\Domain\Model\IconpackProvider;
use Quellenform\Iconpack\Exception\IconpackException;
use Quellenform\Iconpack\IconpackCache;
use Quellenform\Iconpack\IconpackRegistry;
use Quellenform\Iconpack\Utility\IconpackRenderer;
use Quellenform\Iconpack\Utility\IconpackUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Localization\Locale;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

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
     * @var string|null
     */
    protected static $langCode = null;

    /**
     * @var array|null
     */
    protected static $availableIconpacks = null;

    /**
     * @var array|null
     */
    protected static $preferredRenderTypes = null;

    /**
     * @var string|null
     */
    protected static $defaultCssClass = '';

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
     * @throws IconpackException
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
                throw new IconpackException(
                    'No valid context specified. Must be \'backend \' or \'frontend\'',
                    2100109275
                );
            }
            static::$context = $context;
        }
    }

    /**
     * Set the preferred renderTypes according to the current context.
     *
     * @return void
     */
    public function getConfigurationFromTypoScript()
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
            // Get the default CSS class from TypoScript/TS, which is prepended to all icons
            static::$defaultCssClass = preg_replace(
                '/[^A-Za-z0-9\-]/',
                '',
                $settings['cssClass'] ?? ''
            );
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
            $this->getConfigurationFromTypoScript();
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
                        $this->queryConfigFromAvailableIconpacks();
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
     * Query configuration from all available iconpacks.
     *
     * @return void
     */
    private function queryConfigFromAvailableIconpacks(): void
    {
        $defaultConfiguration = $this->getDefaultConfigurationFromYaml();
        // Create a new configuration from all registered iconpacks
        foreach (static::$availableIconpacks as $iconpack) {
            /** @var IconpackProvider $iconpackProvider */
            $iconpackProvider = $this->iconpackRegistry->getIconpackProviderByIdentifier($iconpack);
            $iconpackProvider->setAdditionalOptionsCss(
                $defaultConfiguration
            );
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
                'options' => $iconpackProvider->getAdditionalOptions(
                    $defaultConfiguration['options'] ?? []
                )
            ];
        }
        if ($this->config) {
            $this->iconpackCache->setCacheByIdentifier(
                $this->getCacheIdentifier('config'),
                $this->config
            );
        }
    }

    /**
     * Load default configuration for all iconpacks from YAML file.
     *
     * @return array
     */
    protected function getDefaultConfigurationFromYaml(): array
    {
        $configuration = [];
        /** @var ExtensionConfiguration $extConf */
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $yamlFile = (string) trim($extConf->get('iconpack', 'defaultConfig'));
        if (!empty($yamlFile)) {
            $configuration = IconpackUtility::loadYamlFile($yamlFile, 'iconpack');
            return $configuration['iconpack'];
        } else {
            return [];
        }
    }

    /**
     * Query all assets from registered iconpack providers of a specific scope.
     *
     * @param string $assetType The asset type (css|js)
     * @param string $scope The asset scope (backend|ckeditor|frontend)
     * @param bool $streamlined Converting the path to a streamlined version
     *
     * @return array
     */
    public function queryAssets(string $assetType, string $scope, bool $streamlined = false): array
    {
        $assets = [];
        foreach (static::$availableIconpacks as $iconpack) {
            $fieldTypes = ($scope === 'ckeditor' ? ['rte'] : ['native', 'rte']);
            $renderTypesConfig = $this->queryConfig($iconpack, 'renderTypes');
            foreach ($fieldTypes as $fieldType) {
                $renderType = $this->resolvePreferredRenderType($renderTypesConfig, $iconpack, $fieldType);
                if ($renderType) {
                    foreach ($renderTypesConfig[$renderType] as $styleConf) {
                        if (isset($styleConf[$assetType][$scope])) {
                            $assets[] = $styleConf[$assetType][$scope];
                        }
                    }
                }
            }
        }
        // Return flattened asset array with unique values
        return IconpackUtility::uniqueFlattenedAssetArray($assets, $streamlined);
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
            $iconpackIcons = $this->iconpackCache->getCacheByIdentifier($cacheIdentifier);
            if ($iconpackIcons) {
                return $iconpackIcons;
            }
            $configuration = $this->queryConfig($iconfig['iconpack']);
            if ($configuration) {
                $style = $this->validateIconpackStyle($iconfig['style'] ?? null, $configuration['stylesEnabled']);
                if ($configuration['icons']) {
                    $preparedIconpackIcons = IconpackUtility::prepareIconSet(
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
                        $iconpackIcons = $this->getRenderedIconpackIcons($preparedIconpackIcons, $renderConf);
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
     * Iterate through styles and query rendered icons.
     *
     * @param array $iconpackIcons
     * @param array $renderConf
     *
     * @return array|null
     */
    public function getRenderedIconpackIcons(array $iconpackIcons, array $renderConf): ?array
    {
        foreach ($iconpackIcons as $styleKey => $_) {
            foreach ($iconpackIcons[$styleKey]['icons'] as $iconKey => $iconLabel) {
                $iconpackIcons[$styleKey]['icons'][$iconKey] = [
                    'label' => $iconLabel,
                    'markup' => IconpackRenderer::renderIcon(
                        IconpackRenderer::createIconElement(
                            (string) $iconKey,
                            $renderConf,
                            static::$context
                        )
                    )
                ];
            }
        }
        return $iconpackIcons;
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
    public function getIconElement(
        array $iconfig,
        ?array $additionalAttributes = null,
        ?array $preferredRenderTypes = null
    ): ?array {
        // Check if the required fields are set and if the requested iconpack is installed
        if (
            isset($iconfig['iconpack']) &&
            isset($iconfig['icon']) &&
            isset($iconfig['fieldType']) &&
            $this->isIconpackInstalled($iconfig['iconpack'])
        ) {
            // Check if a configuration exists for the requested iconpack
            $configuration = $this->queryConfig($iconfig['iconpack']);
            if ($configuration) {
                // Check if the requested icon is available
                if (isset($iconfig['icon']) && isset($configuration['icons'][$iconfig['icon']])) {
                    $icon = $configuration['icons'][$iconfig['icon']];
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
                        $conf['defaultCssClass'] = static::$defaultCssClass;
                        // Everything looks fine so far, try to set the icon element
                        try {
                            $iconElement = IconpackRenderer::createIconElement(
                                $iconfig['icon'],
                                $conf,
                                static::$context
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
                            $additionalAttributes
                                = $conf[$optionKey]['values'][$optionValue]['attributes'] ?? null;
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
        $langCode = $this->getLanguageCode();
        //$cacheIdentifier = 'Iconpack_' . $langCode . '_' . str_replace(':', '-', $cacheIdentifier); // DEV
        $cacheIdentifier = 'Iconpack_' . md5($langCode . '_' . $cacheIdentifier);
        return $cacheIdentifier;
    }

    /**
     * Get current language code.
     *
     * @return string
     */
    private function getLanguageCode(): string
    {
        if (empty(static::$langCode)) {
            $langCode = 'default';
            if (static::$context === 'backend') {
                if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.4', '>=')) {
                    $localeFactory = GeneralUtility::makeInstance(Locales::class);
                    /** @var Locale|null $locale */
                    $locale = $localeFactory->createLocaleFromRequest($GLOBALS['TYPO3_REQUEST'] ?? null);
                    if ($langCode) {
                        $langCode = $locale->getLanguageCode();
                    }
                } else {
                    if (isset($GLOBALS['BE_USER']->uc['lang'])) {
                        $langCode = $GLOBALS['BE_USER']->uc['lang'];
                    }
                }
            } else {
                $context = GeneralUtility::makeInstance(Context::class);
                /** @var Site $site */
                $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
                $langId = $context->getPropertyFromAspect('language', 'id');
                $langCode = $site->getLanguageById($langId)->getTypo3Language();
            }
            static::$langCode = (empty($langCode) || $langCode === 'en') ? 'default' : $langCode;
        }
        return static::$langCode;
    }
}
