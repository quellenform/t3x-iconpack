<?php

declare(strict_types=1);

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Quellenform\Iconpack\EventListener;

use Quellenform\Iconpack\IconpackFactory;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
        /** @var IconpackFactory $iconpackFactory */
        $iconpackFactory = GeneralUtility::makeInstance(IconpackFactory::class);
        // Add CSS for CKEditor
        $iconpackAssets = $iconpackFactory->queryAssets('css', 'ckeditor');
        $iconpackAssets[] = 'EXT:iconpack/Resources/Public/Css/Backend/Ckeditor.min.css';
        foreach ($iconpackAssets as $asset) {
            $iconpackProviderConfiguration['contentsCss'][] = $asset;
        }
        // Add CSS for the modal
        $iconpackAssets = $iconpackFactory->queryAssets('css', 'backend');
        $iconpackAssets[] = 'EXT:iconpack/Resources/Public/Css/Backend/IconpackWizard.min.css';
        foreach ($iconpackAssets as $asset) {
            $iconpackProviderConfiguration['modalCss'][] = $asset;
        }
        // RTE only: Allow various tags in icon-elements (Important for "aria-hidden" and other parameters!)
        if ((bool) GeneralUtility::makeInstance(ExtensionConfiguration::class)->get('iconpack', 'autoConfigRte')) {
            // Configuration: https://ckeditor.com/docs/ckeditor4/latest/examples/acfcustom.html
            $iconpackProviderConfiguration['extraAllowedContent'] = [
                'span(*)[data-*,style]',
                // Allow SVG-specific tags
                'svg(*)[!data-iconfig,data-*,title,style,fill,width,height,viewbox]{color,background-*,margin*,padding*}',
                'use[xlink*]',
                'path[!d]',
                'g[*]',
                'line[*]',
                'polyline[*]',
                'line[*]',
                'circle[*]',
                'rect[*]',
                'ellispe[*]',
            ];
        }
        $event->setConfiguration(array_merge_recursive($configuration, $iconpackProviderConfiguration));
    }
}
