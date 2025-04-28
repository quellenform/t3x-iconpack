<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\Domain\Model;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Quellenform\Iconpack\Utility\IconpackUtility;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * IconpackProvider
 */
class IconpackProvider
{

    /**
     * Title of the iconpack
     *
     * @var string
     */
    protected $title = 'IconpackProvider';

    /**
     * The key of this iconpack
     *
     * @var string
     */
    protected $key = '';

    /**
     * The version number of the iconpack
     *
     * @var string|null
     */
    protected $version = null;

    /**
     * ID of the iconpack (used as main selector)
     *
     * @var string|null
     */
    protected $url = null;

    /**
     * Logo of the iconpack
     *
     * @var string|null
     */
    protected $logo = null;

    /**
     * Preferred type for the backend and frontend
     *
     * @var array
     */
    protected $preferredRenderTypes = [
        'backend' => [
            'native' => ['svgSprite', 'svgInline', 'webfont', 'svg'],
            // Note: svgSprite is currently implemented and available in principle, but not functional in TYPO3 v12,
            // ...and svgInline causes more problems in RTE than it solves and therefore makes no sense.
            'rte' => ['webfont', 'svg']
        ],
        'frontend' => [
            'native' => ['svgInline', 'svgSprite', 'webfont', 'svg'],
            'rte' => ['svgInline', 'svgSprite', 'webfont', 'svg']
        ]
    ];

    /**
     * Array of enabled iconpack styles
     *
     * @var array|null
     */
    protected $stylesEnabled = null;

    /**
     * Array of available renderTypes
     *
     * @var array|null
     */
    protected $renderTypes = null;

    /**
     * Additional options
     *
     * @var array|null
     */
    protected $options = null;
    /**
     * Iconpack categories
     *
     * @var array|null
     */
    protected $categories = null;

    /**
     * The icon array
     *
     * @var array|null
     */
    protected $icons = null;

    public function __construct(array $config)
    {
        $this->setTitle($config['title']);
        $this->setKey($config['key']);
        $this->setVersion($config['version'] ?? null);
        $this->setUrl($config['url'] ?? null);
        $this->setLogo($config['logo'] ?? null);

        $this->setStylesEnabled($config['stylesEnabled'] ?? null);

        $this->setPreferredRenderTypes($config['preferredRenderTypes'] ?? null);
        $this->setRenderTypes($config['renderTypes'] ?? null);

        $this->setAdditionalOptions($config['options'] ?? null);
        $this->setCategories($config['categories'] ?? null);
        $this->setIcons($config['icons'] ?? null);
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getTranslatedLabel($this->title);
    }

    /**
     * @param string $key
     */
    public function setKey(string $key)
    {
        $this->key = $key;
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return trim(strtolower($this->key));
    }

    /**
     * @param string|null $version
     */
    public function setVersion(?string $version)
    {
        $this->version = $version;
    }

    /**
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * @param string|null $url
     */
    public function setUrl(?string $url)
    {
        $this->url = $url;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string|null $logo
     */
    public function setLogo(?string $logo)
    {
        $this->logo = $logo;
    }

    /**
     * @return string|null
     */
    public function getLogo(): ?string
    {
        return $this->logo;
    }

    /**
     * Set the preferred renderTypes.
     *
     * @param array|null $preferredRenderTypes
     */
    public function setPreferredRenderTypes(?array $preferredRenderTypes)
    {
        if ($preferredRenderTypes && is_array($preferredRenderTypes)) {
            foreach (['backend', 'frontend'] as $context) {
                foreach (['native', 'rte'] as $fieldType) {
                    if (
                        isset($preferredRenderTypes[$context][$fieldType]) &&
                        !empty($preferredRenderTypes[$context][$fieldType])
                    ) {
                        $this->preferredRenderTypes[$context][$fieldType]
                            = GeneralUtility::trimExplode(
                                ',',
                                $preferredRenderTypes[$context][$fieldType],
                                true
                            );
                    }
                }
            }
        }
    }

    /**
     * Get the preferred renderTypes.
     *
     * @return array
     */
    public function getPreferredRenderTypes(): array
    {
        return $this->preferredRenderTypes;
    }

    /**
     * Set the enabled styles.
     *
     * @param string|null $stylesEnabled
     */
    public function setStylesEnabled(?string $stylesEnabled)
    {
        if ($stylesEnabled) {
            $this->stylesEnabled = GeneralUtility::trimExplode(',', $stylesEnabled, true);
        }
    }

    /**
     * Get the enabled styles.
     *
     * @return array
     */
    public function getStylesEnabled(): ?array
    {
        return $this->stylesEnabled;
    }

    /**
     * Set the renderTypes configuration array.
     *
     * @param array|null $renderTypes
     */
    public function setRenderTypes(?array $renderTypes)
    {
        $this->renderTypes = $renderTypes;
    }

    /**
     * Get the configuration of the iconpack rendertypes.
     *
     * @return array
     */
    public function getRenderTypes(): array
    {
        $renderTypes = [];
        if ($this->renderTypes) {
            $mergeConfig = [];
            if (isset($this->renderTypes['_default']) && is_array($this->renderTypes['_default'])) {
                $mergeConfig = $this->parseConfig($this->renderTypes['_default']);
                unset($this->renderTypes['_default']);
            }
            foreach ($this->renderTypes as $typeKey => $typeConf) {
                if (is_array($typeConf)) {
                    $renderTypes[$typeKey] = array_replace_recursive(
                        $mergeConfig,
                        $this->parseConfig($typeConf)
                    );
                }
            }
        }
        // Explode attributes into array after merging
        foreach ($renderTypes as $typeKey => $typeConf) {
            foreach ($typeConf as $styleKey => $styleConf) {
                if (isset($styleConf['attributes'])) {
                    $renderTypes[$typeKey][$styleKey]['attributes']
                        = IconpackUtility::explodeAttributes($styleConf['attributes']);
                }
            }
        }
        return $renderTypes;
    }

    /**
     * Parse the given configuration.
     *
     * @param array $typeConf
     *
     * @return array
     */
    private function parseConfig(array $typeConf): array
    {
        $typeConfigArray = [];
        if ($this->stylesEnabled) {
            $mergeConfig = [];
            if (isset($typeConf['_default']) && is_array($typeConf['_default'])) {
                $mergeConfig = $this->prepareConfigArray($typeConf['_default']);
                unset($typeConf['_default']);
            }
            foreach ($this->stylesEnabled as $styleKey) {
                if (isset($typeConf[$styleKey]) && is_array($typeConf[$styleKey])) {
                    $typeConfigArray[$styleKey] = $this->prepareConfigArray(
                        $typeConf[$styleKey],
                        $mergeConfig
                    );
                }
            }
        } else {
            $typeConfigArray[] = $this->prepareConfigArray($typeConf);
        }
        return $typeConfigArray;
    }

    /**
     * Fill the configuration array with some useful values.
     *
     * @param array $config
     * @param array $mergeConfig
     *
     * @return array
     */
    private function prepareConfigArray(array $config, array $mergeConfig = []): array
    {
        $configArray = [];
        $allowedKeys = [
            'label',
            'css',
            'source',
            'elementName',
            'attributeName',
            'prefix',
            'attributes'
        ];
        foreach ($config as $key => $value) {
            if (in_array($key, $allowedKeys)) {
                switch ($key) {
                    case 'label':
                        $configArray[$key] = $this->getTranslatedLabel($value, null);
                        break;
                    case 'css':
                        $configArray[$key] = $this->mergeAsset($key, $config);
                        break;
                    case 'source':
                        $source = $value;
                        if (substr($source, 0, 4) !== 'http') {
                            if (strpos($source, 'EXT:') === 0 || strpos($source, '/') !== 0) {
                                $source = GeneralUtility::getFileAbsFileName($source);
                            }
                            $source = PathUtility::getAbsoluteWebPath($source);
                        }
                        $configArray[$key] = $source;
                        break;
                    case 'attributes':
                        if (is_array($value)) {
                            foreach ($value as $attributeKey => $attributeValue) {
                                $configArray[$key][$attributeKey] = $attributeValue;
                            }
                        }
                        break;
                    default:
                        $configArray[$key] = $value;
                        break;
                }
            }
        }
        if (count($mergeConfig)) {
            $configArray = array_replace_recursive($mergeConfig, $configArray);
        }
        return $configArray;
    }

    /**
     * Merge the asset tree into 'backend', 'ckeditor' and 'frontend'.
     *
     * @param string $assetType
     * @param array $config
     *
     * @return array
     */
    public function mergeAsset(string $assetType, array $config): array
    {
        $newAssets = [];
        if (isset($config[$assetType])) {
            $scopes = ['backend', 'ckeditor', 'frontend'];
            if (!is_array($config[$assetType])) {
                // The value is a string
                foreach ($scopes as $scope) {
                    $newAssets[$scope][] = $config[$assetType];
                }
            } else {
                // The value is an array
                foreach ($config[$assetType] as $key => $assets) {
                    if (is_int($key)) {
                        // The array has numeric keys: css[0]...
                        foreach ($scopes as $scope) {
                            $newAssets[$scope][] = $assets;
                        }
                    } else {
                        // The array is associative: css['...']...
                        if (in_array($key, $scopes)) {
                            // Scope specific asset is defined: css['backend']
                            $scope = $key;
                            if (!is_array($assets)) {
                                $newAssets[$scope][] = $assets;
                            } else {
                                foreach ($assets as $customKey => $asset) {
                                    // The scope specific asset-array has a custom or numeric key: css['backend']['custom_key']
                                    $newAssets[$scope][$customKey] = $asset;
                                }
                            }
                        } else {
                            // The asset is defined by a custom key and has no scope defined: css['custom_key']...
                            if (!is_array($assets)) {
                                // The custom key is a string: css['custom_key']
                                foreach ($scopes as $scope) {
                                    $newAssets[$scope][$key] = $assets;
                                }
                            } else {
                                // The custom key has some more assets as array: css['custom_key'][]
                                foreach ($assets as $asset) {
                                    foreach ($scopes as $scope) {
                                        $newAssets[$scope][$key][] = $asset;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return $newAssets;
    }

    /**
     * Set the additional options for this iconpack.
     *
     * @param array|null $options
     */
    public function setAdditionalOptions(?array $options)
    {
        $this->options = $options;
    }

    /**
     * Get additional options for this iconpack.
     *
     * @return array|null
     */
    public function getAdditionalOptions(): ?array
    {
        $additionalOptions = null;
        if ($this->options) {
            foreach ($this->options as $optionKey => $option) {
                if (isset($option['type']) && !empty($option['type'])) {
                    $optionConf = [
                        'label' => $this->getTranslatedLabel($option['label'], $optionKey),
                        'type' => $option['type']
                    ];
                    switch ($option['type']) {
                        case 'select':
                            if (isset($option['values']) && is_array($option['values'])) {
                                foreach ($option['values'] as $key => $values) {
                                    if (isset($values['attributes']) && is_array($values['attributes'])) {
                                        $optionConf['values'][$key]['label']
                                            = $this->getTranslatedLabel($values['label']);
                                        $optionConf['values'][$key]['attributes']
                                            = IconpackUtility::explodeAttributes($values['attributes']);
                                        // This is required for JavaScript
                                        $optionConf['values'][$key]['attributesString']
                                            = json_encode($values['attributes']);
                                    }
                                }
                            }
                            break;
                        case 'checkbox':
                            if (isset($option['attributes']) && is_array($option['attributes'])) {
                                $optionConf['attributes'] = IconpackUtility::explodeAttributes($option['attributes']);
                                // This is required for JavaScript
                                $optionConf['attributesString'] = json_encode($option['attributes']);
                            }
                            break;
                    }
                    $additionalOptions[$optionKey] = $optionConf;
                }
            }
        }
        return $additionalOptions;
    }

    /**
     * Set the categories array.
     *
     * @param array|null $categories
     */
    public function setCategories(?array $categories)
    {
        $this->categories = $categories;
    }

    /**
     * Get the prepared categories array with translated labels.
     *
     * @return array|null
     */
    public function getCategories(): ?array
    {
        $categories = null;
        if ($this->categories) {
            foreach ($this->categories as $key => $category) {
                $categories[$key] = [
                    'label' => $this->getTranslatedLabel($category['label'], IconpackUtility::keyToWord($key)),
                    'icons' => $category['icons'] ?? []
                ];
            }
        }
        return $categories;
    }

    /**
     * Set icon array.
     *
     * @param array|null $icons
     */
    public function setIcons(?array $icons)
    {
        $this->icons = $icons;
    }

    /**
     * Get the complete icon array.
     *
     * @return array|null
     */
    public function getIcons(): ?array
    {
        $icons = null;
        if ($this->icons) {
            $icons = [];
            foreach ($this->icons as $key => $icon) {
                if (is_array($icon)) {
                    if (isset($icon['label']) && is_array($icon['label'])) {
                        $icons[$key]['label'] = (string) $icon['label'];
                    } else {
                        $icons[$key]['label'] = IconpackUtility::keyToWord((string) $key);
                    }
                    if (isset($icon['styles']) && is_array($icon['styles'])) {
                        $icons[$key]['styles'] = $icon['styles'];
                    }
                } else {
                    if (is_int($icon)) {
                        $icons[$key]['label'] = IconpackUtility::keyToWord((string) $key);
                    } else {
                        $icons[$icon]['label'] = IconpackUtility::keyToWord($icon);
                    }
                }
            }
        }
        return $icons;
    }

    /**
     * Get a translated label.
     *
     * @param string $label
     * @param string|null $default
     *
     * @return string
     */
    protected function getTranslatedLabel(string $label, ?string $default = ''): string
    {
        if (!empty($label)) {
            // TODO: Optimize this nasty way to get a translated label
            if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
                if ($GLOBALS['LANG']) {
                    $label = $GLOBALS['LANG']->sL($label);
                }
            } else {
                if ($GLOBALS['TSFE']) {
                    $label = $GLOBALS['TSFE']->sL($label);
                }
            }
        } else {
            $label = $default;
        }
        return $label;
    }
}
