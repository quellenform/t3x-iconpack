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
        $iconpackProviderConfiguration = [];
        /** @var ExtensionConfiguration $extConf */
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        if ((bool) $extConf->get('iconpack', 'enablePlugin')) {
            // Add CSS for CKEditor
            $iconpackProviderConfiguration['contentsCss']
                = GeneralUtility::makeInstance(IconpackFactory::class)->queryAssets('css', 'ckeditor');
            array_unshift(
                $iconpackProviderConfiguration['contentsCss'],
                'EXT:iconpack/Resources/Public/Css/Backend/Ckeditor.min.css'
            );
        }
        // RTE only: Allow various tags in icon-elements (Important for "aria-hidden" and other parameters!)
        if ((bool) $extConf->get('iconpack', 'autoConfigRte')) {
            // Configuration:
            // https://ckeditor.com/docs/ckeditor4/latest/examples/acfcustom.html
            // https://ckeditor.com/docs/ckeditor4/latest/guide/dev_advanced_content_filter.html
            $iconpackProviderConfiguration['extraAllowedContent'] = [
                'span(*)[data-*,style]',
                // Allow SVG-specific tags
                // TODO
                //'svg(*)[!data-iconfig,data-*,title,style,fill,stroke,width,height,viewbox]{color,background-*,margin*,padding*}',
                'svg(*)[*]',
                'use[xlink*]',
                'g[*]',
                'line[*]',
                'path[!d]',
                'polyline[*]',
                'polygon[*]',
                'rect[*]',
                'circle[*]',
                'ellipse[*]',
            ];
        }
        $event->setConfiguration(array_merge_recursive($configuration, $iconpackProviderConfiguration));
    }
}
