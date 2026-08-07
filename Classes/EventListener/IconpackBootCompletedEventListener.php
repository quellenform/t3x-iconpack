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

use Quellenform\Iconpack\IconpackRegistry;
use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Pre-Cache the iconpack configuration on boot to avoid having to read these files with every request.
 */
final class IconpackBootCompletedEventListener
{
    /**
     * This event is fired on every request when TYPO3 has been fully booted,
     * right after all configuration files have been added.
     *
     * @param BootCompletedEvent $event
     *
     * @return void
     */
    public function __invoke(BootCompletedEvent $event): void
    {
        GeneralUtility::makeInstance(IconpackRegistry::class)->buildIconpackProviderConfiguration();
    }
}
