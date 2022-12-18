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
use TYPO3\HtmlSanitizer\Behavior\Tag;
use TYPO3\HtmlSanitizer\Behavior\RegExpAttrValue;

/**
 * Custom sanitizer for SVG content output in frontend (Only content coming from RTE).
 */
class IconpackHtmlSanitizer extends DefaultSanitizerBuilder
{

    /**
     * @var Behavior\Attr
     */
    protected $svgPresentationAttrs;

    public function createBehavior(): Behavior
    {
        $svgPresentationAttrs = $this->createSvgAttrs();
        return parent::createBehavior()
            ->withName('default')
            ->withTags(
                (new Tag('svg', Tag::ALLOW_CHILDREN))->addAttrs(
                    (new Attr('xmlns'))->addValues(
                        new RegExpAttrValue('#^?:https?://#')
                    ),
                    (new Attr('xmlns:xlink')),
                    (new Attr('viewBox')),
                    (new Attr('width')),
                    (new Attr('height')),
                    ...$this->globalAttrs,
                    ...$svgPresentationAttrs
                ),
                (new Tag('use'))->addAttrs(
                    (new Attr('x')),
                    (new Attr('y')),
                    (new Attr('xmlns:xlink')),
                    (new Attr('xlink:href')),
                    ...$this->globalAttrs
                ),
                (new Tag('g', Tag::ALLOW_CHILDREN))->addAttrs(
                    ...$this->globalAttrs,
                    ...$svgPresentationAttrs
                ),
                (new Tag('line'))->addAttrs(
                    (new Attr('x1')),
                    (new Attr('y1')),
                    (new Attr('x2')),
                    (new Attr('y2')),
                    ...$svgPresentationAttrs
                ),
                (new Tag('path'))->addAttrs(
                    (new Attr('d')),
                    ...$svgPresentationAttrs
                ),
                (new Tag('polyline'))->addAttrs(
                    (new Attr('points')),
                    ...$this->globalAttrs,
                    ...$svgPresentationAttrs
                ),
                (new Tag('polygon'))->addAttrs(
                    (new Attr('points')),
                    ...$svgPresentationAttrs
                ),
                (new Tag('rect'))->addAttrs(
                    (new Attr('x')),
                    (new Attr('y')),
                    (new Attr('width')),
                    (new Attr('height')),
                    (new Attr('rx')),
                    (new Attr('ry')),
                    ...$svgPresentationAttrs
                ),
                (new Tag('circle'))->addAttrs(
                    (new Attr('cx')),
                    (new Attr('cy')),
                    (new Attr('r')),
                    ...$svgPresentationAttrs
                ),
                (new Tag('ellipse'))->addAttrs(
                    (new Attr('cx')),
                    (new Attr('cy')),
                    (new Attr('rx')),
                    (new Attr('ry')),
                    ...$svgPresentationAttrs
                )
            );
    }

    /**
     * Set SVG presentation attributes.
     *
     * @return Behavior\Attr[]
     */
    protected function createSvgAttrs(): array
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
            'visibility'
        );
        return $attrs;
    }
}
