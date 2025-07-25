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
use Quellenform\Iconpack\Utility\IconpackUtility;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\RteCKEditor\Form\Element\Event\BeforePrepareConfigurationForEditorEvent;

/**
 * Apply configuration data for registered iconpack-providers.
 */
class IconpackPrepareConfigurationForEditor
{
    /**
     * This event is fired before starting the prepare of the editor configuration.
     */
    public function __invoke(BeforePrepareConfigurationForEditorEvent $event): void
    {
        $configuration = $event->getConfiguration();
        $iconpackConfiguration = [];
        /** @var ExtensionConfiguration $extConf */
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        // Auto configure RTE
        if ((bool) $extConf->get('iconpack', 'autoConfigRte')) {
            // Add configuration from YAML
            if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.0.0', '<')) {
                $yaml = IconpackUtility::loadYamlFile(
                    'EXT:iconpack/Configuration/RTE/IconpackConfig-v11.yaml'
                );
                // Get CSS for CKEditor from installed iconpacks
                $editorCss = GeneralUtility::makeInstance(IconpackFactory::class)->queryAssets(
                    'css',
                    'ckeditor'
                );
                // Add CSS for CKEditor 4 from installed iconpacks
                foreach ($editorCss as $cssFile) {
                    $yaml['editor']['config']['contentsCss'][] = $cssFile;
                }
            } else {
                $yaml = IconpackUtility::loadYamlFile(
                    'EXT:iconpack/Configuration/RTE/IconpackConfig-v12.yaml'
                );
            }
            $iconpackConfiguration = $yaml['editor']['config'];
        }
        $event->setConfiguration(array_merge_recursive($configuration, $iconpackConfiguration));
    }
}
