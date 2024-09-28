<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\EventListener;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Quellenform\Iconpack\Html\IconpackHtmlParser;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Html\Event\BeforeTransformTextForPersistenceEvent;
use TYPO3\CMS\Core\Html\HtmlParser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsEventListener('IconpackBeforeTransformTextForPersistence')]
final class IconpackBeforeTransformTextForPersistence
{

    public function __invoke(BeforeTransformTextForPersistenceEvent $event): void
    {
        // Convert HTML markup to icon elements
        $event->setHtmlContent(
            GeneralUtility::makeInstance(IconpackHtmlParser::class)->transformIconsForPersistence(
                $event->getHtmlContent(),
                GeneralUtility::makeInstance(HtmlParser::class)
            )
        );
    }
}
