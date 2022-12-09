<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\Html;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Html\RteHtmlParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use Quellenform\Iconpack\Html\IconpackHtmlParser;

class IconpackRteTransformation
{
    /**
     * Iconpack parser
     *
     * @var IconpackHtmlParser
     */
    protected $iconpackHtmlParser = null;

    public function __construct()
    {
        $this->iconpackHtmlParser = GeneralUtility::makeInstance(IconpackHtmlParser::class);
    }

    public function transform_db(string $content, RteHtmlParser $rteHtmlParser): string
    {
        return $this->iconpackHtmlParser->transformDb($content, $rteHtmlParser);
    }

    public function transform_rte(string $content, RteHtmlParser $rteHtmlParser): string
    {
        return $this->iconpackHtmlParser->transformRte($content, $rteHtmlParser);
    }
}
