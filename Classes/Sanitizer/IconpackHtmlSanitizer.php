<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\Sanitizer;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Html\DefaultSanitizerBuilder;
use TYPO3\HtmlSanitizer\Behavior;
use TYPO3\HtmlSanitizer\Behavior\Attr;
use TYPO3\HtmlSanitizer\Behavior\Attr\UriAttrValueBuilder;
use TYPO3\HtmlSanitizer\Behavior\Tag;
use TYPO3\HtmlSanitizer\Builder\BuilderInterface;

/**
 * Custom sanitizer for SVG content output in frontend (Only content coming from RTE).
 *
 * Note: This is a very rudimentary sanitizer intended for highly simplified SVG icons
 * that contain only essential elements and is not generally suitable for processing SVG.
 */
class IconpackHtmlSanitizer extends DefaultSanitizerBuilder implements BuilderInterface
{
    /**
     * @var Behavior\Attr
     */
    protected $svgPresentationAttrs;

    public function createBehavior(): Behavior
    {
        $svgPresentationAttributes = $this->createSvgPresentationAttributes();
        $xmlnsAttrValueBuilder = (new UriAttrValueBuilder())
            ->allowSchemes('http', 'https');
        $hrefAttrValueBuilder = (new UriAttrValueBuilder())
            ->allowLocal(true);

        return parent::createBehavior()
            ->withName('default')
            ->withTags(
                (new Tag('svg', Tag::ALLOW_CHILDREN))->addAttrs(
                    (new Attr('xmlns'))->addValues(...$xmlnsAttrValueBuilder->getValues()),
                    (new Attr('xmlns:xlink'))->addValues(...$xmlnsAttrValueBuilder->getValues()),
                    (new Attr('viewBox')),
                    (new Attr('width')),
                    (new Attr('height')),
                    (new Attr('preserveAspectRatio')),
                    (new Attr('x')),
                    (new Attr('y')),
                    (new Attr('version')),
                    ...$this->globalAttrs,
                    ...$svgPresentationAttributes
                ),
                (new Tag('use'))->addAttrs(
                    (new Attr('href'))->addValues(...$hrefAttrValueBuilder->getValues()),
                    (new Attr('xlink:href'))->addValues(...$hrefAttrValueBuilder->getValues()),
                    (new Attr('x')),
                    (new Attr('y')),
                    (new Attr('width')),
                    (new Attr('height')),
                    ...$this->globalAttrs,
                    ...$svgPresentationAttributes,
                ),
                (new Tag('image'))->addAttrs(
                    (new Attr('href'))->addValues(...$hrefAttrValueBuilder->getValues()),
                    (new Attr('x')),
                    (new Attr('y')),
                    (new Attr('width')),
                    (new Attr('height')),
                    (new Attr('preserveAspectRatio'))
                ),
                (new Tag('title', Tag::ALLOW_CHILDREN))->addAttrs(
                    (new Attr('id'))
                ),
                (new Tag('desc', Tag::ALLOW_CHILDREN))->addAttrs(
                    (new Attr('id'))
                ),
                (new Tag('defs', Tag::ALLOW_CHILDREN))->addAttrs(
                    ...$this->globalAttrs,
                    ...$svgPresentationAttributes,
                ),
                (new Tag('linearGradient', Tag::ALLOW_CHILDREN))->addAttrs(
                    (new Attr('gradientUnits')),
                    (new Attr('gradientTransform')),
                    (new Attr('spreadMethod')),
                    (new Attr('x1')),
                    (new Attr('x2')),
                    (new Attr('y1')),
                    (new Attr('y2')),
                    ...$this->globalAttrs,
                    ...$svgPresentationAttributes,
                ),
                (new Tag('radialGradient', Tag::ALLOW_CHILDREN))->addAttrs(
                    (new Attr('gradientUnits')),
                    (new Attr('gradientTransform')),
                    (new Attr('spreadMethod')),
                    (new Attr('cx')),
                    (new Attr('cy')),
                    (new Attr('fx')),
                    (new Attr('fy')),
                    (new Attr('r')),
                    ...$this->globalAttrs,
                    ...$svgPresentationAttributes,
                ),
                (new Tag('stop'))->addAttrs(
                    (new Attr('stop-color')),
                    (new Attr('stop-opacity')),
                    (new Attr('offset')),
                    ...$this->globalAttrs,
                    ...$svgPresentationAttributes,
                ),
                (new Tag('g', Tag::ALLOW_CHILDREN))->addAttrs(
                    ...$this->globalAttrs,
                    ...$svgPresentationAttributes
                ),
                (new Tag('line'))->addAttrs(
                    (new Attr('x1')),
                    (new Attr('y1')),
                    (new Attr('x2')),
                    (new Attr('y2')),
                    ...$svgPresentationAttributes
                ),
                (new Tag('path'))->addAttrs(
                    (new Attr('d')),
                    (new Attr('style')),
                    ...$svgPresentationAttributes
                ),
                (new Tag('polyline'))->addAttrs(
                    (new Attr('points')),
                    ...$this->globalAttrs,
                    ...$svgPresentationAttributes
                ),
                (new Tag('polygon'))->addAttrs(
                    (new Attr('points')),
                    ...$svgPresentationAttributes
                ),
                (new Tag('rect'))->addAttrs(
                    (new Attr('width')),
                    (new Attr('height')),
                    (new Attr('x')),
                    (new Attr('y')),
                    (new Attr('rx')),
                    (new Attr('ry')),
                    ...$svgPresentationAttributes
                ),
                (new Tag('circle'))->addAttrs(
                    (new Attr('cx')),
                    (new Attr('cy')),
                    (new Attr('r')),
                    ...$svgPresentationAttributes
                ),
                (new Tag('ellipse'))->addAttrs(
                    (new Attr('cx')),
                    (new Attr('cy')),
                    (new Attr('rx')),
                    (new Attr('ry')),
                    ...$svgPresentationAttributes
                )
            );
    }

    /**
     * Set SVG presentation attributes.
     *
     * @return Behavior\Attr[]
     */
    protected function createSvgPresentationAttributes(): array
    {
        // https://developer.mozilla.org/en-US/docs/Web/SVG/
        $attrs = $this->createAttrs(
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
            'visibility',
        );
        return $attrs;
    }
}
