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

use SimpleXMLElement;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IconpackRenderer
{

    /**
     * The SVG content for SVG inline element
     *
     * @var array
     */
    private static $svgData = [];

    /**
     * The viewBox attributes for SVG sprites
     *
     * @var array
     */
    private static $svgViewBox = [];

    /**
     * Transform the icon element into HTML-markup.
     *
     * @param array|null $iconElement
     *
     * @return string
     */
    public static function renderIcon(?array $iconElement): string
    {
        $html = '';
        if ($iconElement) {
            $html = '<' . $iconElement['elementName'] . ' ' .
                GeneralUtility::implodeAttributes($iconElement['attributes'], true, true);

            if ($iconElement['elementName'] === 'img') {
                $html .= ' />';
            } else {
                $html .= '>' . $iconElement['innerHtml'];
                $html .= '</' . $iconElement['elementName'] . '>';
            }
        }
        return $html;
    }

    /**
     * Get the final con element as array according to the given configuration.
     *
     * @param string $iconKey
     * @param array $conf
     * @param string $context
     *
     * @return array
     */
    public static function createIconElement(string $iconKey, array $conf, string $context): array
    {
        switch ($conf['type']) {
            case 'svgInline':
                [$elementName, $attributes, $innerHtml] = self::createSvgInlineElement($iconKey, $conf);
                break;
            case 'svgSprite':
                [$elementName, $attributes, $innerHtml] = self::createSvgSpriteElement($iconKey, $conf);
                break;
            case 'svg':
                [$elementName, $attributes, $innerHtml] = self::createImageElement($iconKey, $conf);
                break;
            case 'webfont':
                [$elementName, $attributes, $innerHtml] = self::createWebfontElement($iconKey, $conf);
                break;
        }

        if ($context === 'backend') {
            self::finalizeElementForBackend($conf['type'], $attributes);
        } else {
            self::finalizeElementForFrontend($conf['type'], $attributes, $innerHtml);
        }
        return [
            'type' => $conf['type'],
            'elementName' => $conf['elementName'] ?? $elementName,
            'attributes' => IconpackUtility::flattenAttributes($attributes),
            'innerHtml' => $innerHtml
        ];
    }

    /**
     * Create inline <svg> element.
     *
     * @param string $iconKey
     * @param array $conf
     *
     * @return array
     */
    private static function createSvgInlineElement(string $iconKey, array $conf): array
    {
        $attributes = self::getAttributes($iconKey, $conf);

        $sourcePath = self::resolveSourcePath($iconKey, $conf);
        $source = $sourcePath . $iconKey . '.svg';
        [$attributes, $innerHtml] = self::getSvgData($source, $attributes);

        return ['svg', $attributes, $innerHtml];
    }

    /**
     * Create <svg> sprite element.
     *
     * @param string $iconKey
     * @param array $conf
     *
     * @return array
     */
    private static function createSvgSpriteElement(string $iconKey, array $conf): array
    {
        $attributes = self::getAttributes($iconKey, $conf);

        $sourcePath = self::resolveSourcePath($iconKey, $conf);
        $innerHtml = '<use href="' . $sourcePath . '#' . $iconKey . '" />';
        $attributes['viewBox'] = self::getSvgViewBox($iconKey, $sourcePath);

        return ['svg', $attributes, $innerHtml];
    }

    /**
     * Create <img> element which contains the file in the src tag.
     *
     * @param string $iconKey
     * @param array $conf
     *
     * @return array
     */
    private static function createImageElement(string $iconKey, array $conf): array
    {
        $attributes = self::getAttributes($iconKey, $conf);

        $sourcePath = self::resolveSourcePath($iconKey, $conf);
        $attributes['src'] = $sourcePath . $iconKey . '.svg';
        $attributes['loading'] = 'lazy';

        return ['img', $attributes, ''];
    }

    /**
     * Create <span> element for webfont.
     *
     * @param string $iconKey
     * @param array $conf
     *
     * @return array
     */
    private static function createWebfontElement(string $iconKey, array $conf): array
    {
        $attributes = self::getAttributes($iconKey, $conf);

        return ['span', $attributes, ''];
    }

    /**
     * Get the attributes from the given configuration.
     *
     * @param string $iconKey
     * @param array $conf
     *
     * @return array
     */
    private static function getAttributes(string $iconKey, array $conf): array
    {
        $attributes = $conf['attributes'] ?? [];

        // Add default CSS class
        if (!empty($conf['defaultCssClass'])) {
            if (!empty($attributes['class']) && is_array($attributes['class'])) {
                array_unshift($attributes['class'], $conf['defaultCssClass']);
            } else {
                $attributes['class'] = [$conf['defaultCssClass']];
            }
        }

        // Add prefixed icon as CSS class
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

        return $attributes;
    }

    /**
     * Replace placeholders in the given path with parts of the iconKey.
     *
     * @param string $iconKey
     * @param array $conf
     *
     * @return string
     */
    private static function resolveSourcePath(string $iconKey, array $conf): string
    {
        $source = ($conf['source'] ?? '');
        if (strpos($source, '%') !== false) {
            $keyArray = explode('-', $iconKey);
            $source = vsprintf($source, $keyArray);
        }
        return $source;
    }

    /**
     * Get the content from a given SVG file.
     *
     * @param string $sourceFile
     * @param array $attributes
     *
     * @return array
     */
    private static function getSvgData(
        string $sourceFile,
        array $attributes
    ): array {
        if (isset(self::$svgData[$sourceFile])) {
            [$svgAttributes, $innerHtml] = self::$svgData[$sourceFile];
        } else {
            $sourcePath = Environment::getPublicPath() . $sourceFile;
            $svgAttributes = [];
            $innerHtml = '';
            if (file_exists($sourcePath)) {
                $svgFileContent = file_get_contents($sourcePath);
                if ($svgFileContent) {
                    // Strip scripts from the SVG-content
                    $svgFileContent = preg_replace('/<script[\s\S]*?>[\s\S]*?<\/script>/i', '', $svgFileContent) ?? '';
                    // Load the content into SimpleXMLElement
                    $xml = new SimpleXMLElement($svgFileContent);
                    // Register the default namespace
                    $xml->registerXPathNamespace('xmlns', 'http://www.w3.org/2000/svg');
                    // Get the SVG-node
                    $svgNode = $xml->xpath('//xmlns:svg');
                    // Get attributes from SVG-node
                    $nodeAttributes = $svgNode[0]->attributes() ?? [];
                    foreach ($nodeAttributes as $key => $value) {
                        $svgAttributes[strtolower($key)] = $value->__toString();
                    }
                    // Unset additional CSS-classes from SVG file
                    unset($svgAttributes['class']);
                    // Add all child nodes to innerHtml
                    $svgNodes = $xml->xpath('//xmlns:svg/*') ?? [];
                    foreach ($svgNodes as $value) {
                        $innerHtml .= $value->asXML();
                    }
                    $svgAttributes = IconpackUtility::explodeAttributes($svgAttributes);
                }
            }
            self::$svgData[$sourceFile] = [$svgAttributes, $innerHtml];
        }

        // Override/merge those attributes with the existing attributes of the icon element
        $content = [
            IconpackUtility::mergeAttributes(
                $svgAttributes,
                $attributes
            ),
            $innerHtml
        ];
        return $content;
    }

    /**
     * Get the viewBox attribute from a specific SVG sprite symbol.
     *
     * @param string $iconKey
     * @param string $sourceFile
     * @param bool $isSprite
     *
     * @return ?string
     */
    private static function getSvgViewBox(
        string $iconKey,
        string $sourceFile,
        bool $isSprite = true
    ): ?string {
        $viewBox = null;
        $key = $sourceFile . '#' . $iconKey;
        if (isset(self::$svgViewBox[$key])) {
            $viewBox = self::$svgViewBox[$key];
        } else {
            $sourcePath = Environment::getPublicPath() . $sourceFile;
            if (file_exists($sourcePath)) {
                $svgFileContent = file_get_contents($sourcePath);
                if ($svgFileContent) {
                    // Load the content into SimpleXMLElement
                    $xml = new SimpleXMLElement($svgFileContent);
                    // Register the default namespace
                    $xml->registerXPathNamespace('xmlns', 'http://www.w3.org/2000/svg');
                    if ($isSprite) {
                        // Get the SVG-node
                        $svgNode = $xml->xpath('//*[@id="' . $iconKey . '"]');
                        // Get the viewBox attribute from the selected node
                        $viewBox = $svgNode[0]->attributes()->viewBox->__toString();
                    } else {
                        // Get the SVG-node
                        $svgNode = $xml->xpath('//xmlns:svg');
                        // Get attributes from SVG-node
                        $nodeAttributes = $svgNode[0]->attributes() ?? [];
                        $viewBox = $nodeAttributes->viewBox->__toString();
                    }
                }
            }
            self::$svgViewBox[$key] = $viewBox;
        }
        return $viewBox;
    }

    /**
     * Cleanup attributes for backend output.
     *
     * @param string $elementName
     * @param array $attributes
     *
     * @return void
     */
    private static function finalizeElementForBackend(
        string $elementName,
        array &$attributes
    ): void {
        switch ($elementName) {
            case 'svgSprite':
            case 'svgInline':
                $attributes['xmlns'] = 'http://www.w3.org/2000/svg';
                break;
        }
        unset($attributes['role']);
        $attributes['aria-hidden'] = 'true';
    }

    /**
     * Cleanup attributes for frontend output.
     *
     * @param string $elementName
     * @param array $attributes
     * @param string $innerHtml
     *
     * @return void
     */
    private static function finalizeElementForFrontend(
        string $elementName,
        array &$attributes,
        string &$innerHtml
    ): void {
        // Remove data attribute in the frontend output, we don't need it there...
        unset($attributes['data-iconfig']);
        // Add aria-hidden if there is no title or alt attribute to hide it in screen readers
        if (empty($attributes['title']) && empty($attributes['alt'])) {
            $attributes['aria-hidden'] = 'true';
        }
        switch ($elementName) {
            case 'svg':
                // Set empty alt attribute if it does not exist
                if (!isset($attributes['alt'])) {
                    $attributes['alt'] = '';
                }
                break;
            case 'svgSprite':
            case 'svgInline':
                unset($attributes['name']);
                $attributes['xmlns'] = 'http://www.w3.org/2000/svg';
                // Moves the alt and title attribute to the innerHtml.
                if (!empty($attributes['alt'])) {
                    $innerHtml = '<desc>' . $attributes['alt'] . '</desc>' . $innerHtml;
                    unset($attributes['alt']);
                }
                if (!empty($attributes['title'])) {
                    $innerHtml = '<title>' . $attributes['title'] . '</title>' . $innerHtml;
                    unset($attributes['title']);
                }
                break;
        }
    }
}
