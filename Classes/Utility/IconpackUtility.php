<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\Utility;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Various Helpers and Utilities for the IconpackFactory.
 */
class IconpackUtility
{

    /**
     * Parse the TypoScript/TS configuration for overriding the current rendertypes in Backend and Frontend.
     *
     * @param string $key
     * @param array|null $settings
     * @param array|null $renderTypes
     *
     * @return array
     */
    public static function parseRenderTypeFromTypoScript(string $key, ?array $settings, ?array $renderTypes = null): array
    {
        $renderTypes = $renderTypes ?? [];
        $conf = $settings['renderTypes'][$key] ?? null;
        if ($conf && !empty($conf)) {
            if (!is_array($conf)) {
                $values = GeneralUtility::trimExplode(',', $conf, true);
                $renderTypes = [
                    'native' => $values,
                    'rte' => $values
                ];
            } else {
                foreach (['native', 'rte'] as $fieldType) {
                    if (isset($conf[$fieldType]) && !empty($conf[$fieldType])) {
                        $renderTypes[$fieldType] = GeneralUtility::trimExplode(',', $conf[$fieldType], true);
                    }
                }
            }
        }
        return $renderTypes;
    }

    /**
     * Prepare the icons of a specific iconset for dropdowns.
     *
     * @param array|null $icons
     * @param array|null $categories
     * @param string|null $style
     *
     * @return array
     */
    public static function prepareIconSet(?array $icons, ?array $categories, ?string $style = null): array
    {
        $preparedIcons = [];
        if ($icons) {
            if ($categories) {
                foreach ($categories as $categoryKey => $category) {
                    $iconSet = null;
                    foreach ($category['icons'] as $iconKey) {
                        if (array_key_exists($iconKey, $icons)) {
                            if (self::isIconStyleAllowed($icons[$iconKey], $style)) {
                                $iconSet[$iconKey] = $icons[$iconKey]['label'];
                            }
                        }
                    }
                    if ($iconSet) {
                        $preparedIcons[$categoryKey] = [
                            'label' => $category['label'],
                            'icons' => $iconSet
                        ];
                    }
                }
            } else {
                $iconSet = null;
                foreach ($icons as $iconKey => $icon) {
                    if (self::isIconStyleAllowed($icon, $style)) {
                        $iconSet[$iconKey] = $icon['label'];
                    }
                }
                if ($iconSet) {
                    $preparedIcons[] = [
                        'icons' => $iconSet
                    ];
                }
            }
        }
        return $preparedIcons;
    }

    /**
     * Check if requested icon is allowed in the currently selected style.
     * This value is defined in the YAML-file of the iconpack.
     *
     * @param array $icon
     * @param string|null $style
     *
     * @return boolean
     */
    public static function isIconStyleAllowed(array $icon, ?string $style): bool
    {
        if (!$style || empty($style) || !isset($icon['styles'])) {
            return true;
        } else {
            if (
                isset($icon['styles'])
                && is_array($icon['styles'])
                && in_array($style, $icon['styles'])
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Select a specific style configuration from an iconpack.
     * Returns the first element if the requested style could not be found.
     *
     * @param array $styles
     * @param string|null $style
     *
     * @return array
     */
    public static function selectIconpackStyleConf(array $styles, ?string $style): array
    {
        if ($style) {
            return $styles[$style];
        } else {
            return reset($styles);
        }
    }

    /**
     * Splits a one-dimensional array of attributes into a multidimensional array.
     *
     * @param array|null $attributes
     *
     * @return array
     */
    public static function explodeAttributes(?array &$attributes): array
    {
        if ($attributes) {
            foreach ($attributes as $attributeKey => $attributeValue) {
                switch ($attributeKey) {
                    case 'style':
                        $attributes[$attributeKey] =
                            array_column(array_map(function ($styleValue) {
                                return GeneralUtility::trimExplode(':', $styleValue, true);
                            }, GeneralUtility::trimExplode(';', $attributeValue, true)), 1, 0);
                        break;
                    case 'class':
                        $attributes[$attributeKey] =
                            GeneralUtility::trimExplode(' ', $attributeValue, true);
                        break;
                }
            }
        }
        return $attributes ?? [];
    }

    /**
     * Accepts a multidimensional array of attributes and returns a one-dimensional array.
     *
     * @param array $attributes
     *
     * @return array
     */
    public static function flattenAttributes(array $attributes): array
    {
        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                $delimiter = ';';
                switch ($key) {
                    case 'class':
                        $delimiter = ' ';
                        break;
                    case 'style':
                        foreach ($value as $k => $v) {
                            $value[$k] = $k . ':' . $v;
                        }
                        break;
                }
                $attributes[$key] = implode($delimiter, $value);
            }
        }
        return $attributes;
    }

    /**
     * Merges two multidimensional attribute arrays.
     *
     * @param array $arr1
     * @param array|null $arr2
     *
     * @return array
     */
    public static function mergeAttributes(array $arr1, ?array $arr2): array
    {
        if ($arr2) {
            foreach ($arr2 as $key => $value) {
                switch ($key) {
                    case 'class':
                        if (isset($arr1[$key])) {
                            if (!isset($arr1[$key])) {
                                $arr1[$key] = [];
                            }
                            foreach ($value as $subValue) {
                                if (!in_array($subValue, $arr1[$key]) && !empty($subValue)) {
                                    $arr1[$key][] = $subValue;
                                }
                            }
                        } else {
                            $arr1[$key] = $value;
                        }
                        break;
                    case 'style':
                        foreach ($value as $subKey => $subValue) {
                            $arr1[$key][$subKey] = $subValue;
                        }
                        break;
                    default:
                        $arr1[$key] = $value;
                }
            }
        }
        return $arr1;
    }

    /**
     * Removes redundant values of two multidimensional attribute arrays.
     *
     * @param array $arr1
     * @param array $arr2
     *
     * @return array
     */
    public static function removeDuplicateAttributes(array $arr1, array $arr2): array
    {
        $diff = [];
        foreach ($arr2 as $key => $value) {
            switch ($key) {
                case 'class':
                case 'style':
                    if (isset($arr1[$key])) {
                        $diffValue = array_diff($value, $arr1[$key]);
                        if (count($diffValue)) {
                            $diff[$key] = $diffValue;
                        }
                    }
                    break;
                default:
                    if (!isset($arr1[$key]) || $arr1[$key] !== $value) {
                        $diff[$key] = $value;
                    }
            }
        }
        return $diff;
    }

    /**
     * Split the iconfig string into an array.
     *   Example input: 'fa5:solid,star,sizes:2x,orientations:r180'
     *
     * @param string $fieldType
     * @param string|null $iconfigString
     *
     * @return array
     */
    public static function convertIconfigToArray(string $fieldType, ?string $iconfigString = ''): array
    {
        // fieldType is always set!
        $iconfig = [
            'fieldType' => $fieldType
        ];
        if (!$iconfigString || empty($iconfigString)) {
            return $iconfig;
        }
        // Split string into configuration parts
        $parts = explode(',', $iconfigString);
        // Split first part into 'iconpack' and 'syle'
        $iconpackStyle = explode(':', $parts[0]);
        // The combination of the iconpack identifier and an iconpack style
        $iconfig['iconpackStyle'] = $parts[0];
        // The iconpack identifier
        $iconfig['iconpack'] = $iconpackStyle[0];
        // The iconpack style
        if (isset($iconpackStyle[1])) {
            $iconfig['style'] = $iconpackStyle[1];
        }
        // The icon identifier
        if (isset($parts[1])) {
            $iconfig['icon'] = $parts[1];
        }
        // The rest of the parts are additional options...
        $options = isset($parts[2]) ? array_slice($parts, 2) : null;
        if ($options) {
            foreach ($options as $option) {
                $keyValue = explode(':', $option);
                if (!empty($keyValue[1])) {
                    $iconfig['options'][$keyValue[0]] = $keyValue[1];
                }
            }
        }
        return $iconfig;
    }

    /**
     * Converts a string into a human readable word.
     *
     * @param string $value
     *
     * @return string
     */
    public static function keyToWord(string $value): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $value));
    }
}
