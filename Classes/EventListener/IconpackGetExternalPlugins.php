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

use Quellenform\Iconpack\Utility\IconpackUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\RteCKEditor\Form\Element\Event\BeforeGetExternalPluginsEvent;

/**
 * Add the CKEditor-Plugin (TYPO3 v10/11)
 */
final class IconpackGetExternalPlugins
{
    /**
     * This event is fired before processing external plugin configuration.
     *
     * @param BeforeGetExternalPluginsEvent $event
     *
     * @return void
     */
    public function __invoke(BeforeGetExternalPluginsEvent $event): void
    {
        if (
            (bool) GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('iconpack', 'autoConfigRte')
            && version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.0.0', '<')
        ) {
            $configuration = $event->getConfiguration();
            // Get the external plugin configuration from YAML file
            $yaml = IconpackUtility::loadYamlFile(
                'EXT:iconpack/Configuration/RTE/IconpackConfig-v11.yaml'
            );
            $iconpackConfiguration = $yaml['editor']['externalPlugins'];
            $event->setConfiguration(array_merge_recursive($configuration, $iconpackConfiguration));
        }
    }
}
