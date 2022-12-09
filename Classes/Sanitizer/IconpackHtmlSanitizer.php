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
 * Custom sanitizer to allow output of SVG-Sprites in the frontend (bodytext!).
 */
class IconpackHtmlSanitizer extends DefaultSanitizerBuilder
{
    public function createBehavior(): Behavior
    {
        return parent::createBehavior()
            ->withName('default')
            ->withTags(
                (new Tag('svg', Tag::ALLOW_CHILDREN))->addAttrs(
                    (new Attr('fill')),
                    (new Attr('xmlns'))->addValues(
                        new RegExpAttrValue('#^?:https?://#')
                    ),
                    (new Attr('xmlns:xlink')),
                    ...$this->globalAttrs
                ),
                (new Tag('use'))->addAttrs(
                    (new Attr('xmlns:xlink')),
                    (new Attr('xlink:href')),
                    ...$this->globalAttrs
                ),
                (new Tag('path'))->addAttrs(
                    (new Attr('d')),
                    (new Attr('g')),
                    ...$this->globalAttrs
                ),
            );
    }
}
