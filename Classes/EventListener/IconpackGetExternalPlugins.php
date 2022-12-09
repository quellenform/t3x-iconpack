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

use Quellenform\Iconpack\IconpackFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\RteCKEditor\Form\Element\Event\BeforeGetExternalPluginsEvent;

/**
 * Add CKEditor-Plugins.
 */
class IconpackGetExternalPlugins
{

    /**
     * This event is fired before processing external plugin configuration.
     */
    public function __invoke(BeforeGetExternalPluginsEvent $event): void
    {
        $iconpackProviderConfiguration = [];
        // Get the external plugin configuration
        $configuration = $event->getConfiguration();
        $configuration['iconpack'] = [
            'resource' => 'EXT:iconpack/Resources/Public/JavaScript/CKEditor/plugin.min.js',
            'route' => 'ajax_iconpack_modal'
        ];
        $iconpackAssets = GeneralUtility::makeInstance(IconpackFactory::class)
            ->queryAssets('js', 'ckeditor');
        foreach ($iconpackAssets as $identifier => $asset) {
            $iconpackProviderConfiguration['iconpack_' . $identifier] = [
                'resource' => $asset
            ];
        }
        $event->setConfiguration(array_merge($configuration, $iconpackProviderConfiguration));
    }
}
