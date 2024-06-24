<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\Hooks;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use Psr\Http\Message\ServerRequestInterface;
use Quellenform\Iconpack\IconpackFactory;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Page\AssetCollector;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * AssetRenderer hook to add iconpack assets for FE/BE calls
 *
 * @internal This is a specific hook implementation and is not considered part of the Public TYPO3 API.
 */
final class AssetRenderer
{

    public function addCss()
    {
        if (($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface) {
            /** @var IconpackFactory $iconpackFactory */
            $iconpackFactory = GeneralUtility::makeInstance(IconpackFactory::class);

            /** @var AssetCollector $assetCollector */
            $assetCollector = GeneralUtility::makeInstance(AssetCollector::class);

            $styleSheets = [];
            if (
                ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend() &&
                (bool) \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
                    \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
                )->get('iconpack', 'autoAddAssets')
            ) {
                $styleSheets = $iconpackFactory->queryAssets('css', 'frontend');
            } elseif (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
                $styleSheets = $iconpackFactory->queryAssets('css', 'backend');
            }
            // Add StyleSheets
            foreach ($styleSheets as $key => $styleSheet) {
                // @extensionScannerIgnoreLine
                $assetCollector->addStyleSheet(
                    'iconpack_' . $key,
                    $styleSheet,
                    ['media' => 'all']
                );
            }
        }
    }
}
