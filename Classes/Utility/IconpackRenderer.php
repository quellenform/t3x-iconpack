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
use TYPO3\CMS\Core\Core\Environment;

class IconpackRenderer
{

    /**
     * Transform the icon element into HTML-markup.
     *
     * @param array|null $iconElement
     *
     * @return string
     */
    public static function renderIcon(?array $iconElement): string
    {
        return $iconElement ?
            '<' . $iconElement['elementName'] . ' ' .
            GeneralUtility::implodeAttributes($iconElement['attributes'], true) .
            '>' .
            $iconElement['innerHtml'] .
            '</' . $iconElement['elementName'] . '>' :
            '';
    }

    /**
     * Get the final icon element as array according to the given configuration.
     *
     * @param string $iconKey
     * @param array $conf
     *
     * @return array
     */
    public static function createIconElement(string $iconKey, array $conf): array
    {
        $innerHtml = ' ';
        $source = $conf['source'] ?? '';
        $attributes = $conf['attributes'] ?? [];
        $attributes['title'] = $conf['label'];
        $attributes['class'][] = ($conf['prefix'] ?? '') . $iconKey;

        // Merge/override existing attributes with additional attributes
        $attributes = IconpackUtility::mergeAttributes(
            $attributes,
            $conf['additionalAttributes'] ?? null
        );

        // Override default attribute name (This will replace 'class' with something else)
        if (isset($conf['attributeName']) && !empty($conf['attributeName'])) {
            $attributes[$conf['attributeName']] = $attributes['class'];
            unset($attributes['class']);
        }
        $elementName = 'svg';
        switch ($conf['type']) {
            case 'svg':
                $elementName = $conf['elementName'] ?? 'img';
                $attributes['src'] = $source . $iconKey . '.svg';
                $attributes['alt'] = $attributes['title'];
                $attributes['loading'] = 'lazy';
                break;
            case 'svgSprite':
                $elementName = $conf['elementName'] ?? 'svg';
                $attributes['xmlns'] = 'http://www.w3.org/2000/svg';
                $innerHtml = '<use xlink:href="' . $source . '#' . $iconKey . '"/>';
                break;
            case 'svgInline':
                $elementName = $conf['elementName'] ?? 'svg';
                $innerHtml = '';
                $source = Environment::getPublicPath() . $source . $iconKey . '.svg';
                if (file_exists($source)) {
                    $svgContent = file_get_contents($source);
                    if ($svgContent !== false) {
                        // Strip scripts from the SVG-content
                        $svgContent = preg_replace('/<script[\s\S]*?>[\s\S]*?<\/script>/i', '', $svgContent) ?? '';
                        // Load the content into SimpleXMLElement
                        $xml = new \SimpleXMLElement($svgContent);
                        // Register the default namespace
                        $xml->registerXPathNamespace('xmlns', 'http://www.w3.org/2000/svg');
                        // Get the SVG-node
                        $svgNode = $xml->xpath('//xmlns:svg');
                        // Get attributes from SVG-node
                        $svgAttributes = [];
                        $nodeAttributes = $svgNode[0]->attributes() ?? [];
                        foreach ($nodeAttributes as $key => $value) {
                            $svgAttributes[strtolower($key)] = $value->__toString();
                        }
                        // Unset additional CSS-classes from SVG file
                        unset($svgAttributes['class']);
                        // Override/merge those attributes with the existing attributes of the icon element
                        $attributes = IconpackUtility::mergeAttributes(
                            IconpackUtility::explodeAttributes($svgAttributes),
                            $attributes
                        );
                        // Add all child nodes to innerHtml
                        $svgNodes = $xml->xpath('//xmlns:svg/*') ?? [];
                        foreach ($svgNodes as $value) {
                            $innerHtml .= $value->asXML();
                        }
                    }
                }
                break;
            case 'svgJs':
                $elementName = $conf['elementName'] ?? 'svg';
                break;
            default: // webfont
                $elementName = $conf['elementName'] ?? 'span';
                break;
        }
        if ($conf['type'] !== 'svgInline') {
            unset($attributes['viewbox']);
        }
        return [
            'type' => $conf['type'],
            'elementName' => $elementName,
            'attributes' => IconpackUtility::flattenAttributes($attributes),
            'innerHtml' => $innerHtml
        ];
    }
}
