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
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * PageRenderer hook to add iconpack assets for frontend calls
 *
 * @internal This is a specific hook implementation and is not considered part of the Public TYPO3 API.
 */
final class PageRendererHook
{
    public function addIconpackAssets(array $params, PageRenderer $pageRenderer): void
    {
        if (
            ($GLOBALS['TYPO3_REQUEST'] ?? null) instanceof ServerRequestInterface &&
            ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()
        ) {
            /** @var IconpackFactory $iconpackFactory */
            $iconpackFactory = GeneralUtility::makeInstance(IconpackFactory::class);
            // Add StyleSheets
            $styleSheets = $iconpackFactory->queryAssets('css', 'frontend');
            foreach ($styleSheets as $styleSheet) {
                $pageRenderer->addCssFile($styleSheet);
            }
            // Add JavaScripts
            $javaScripts = $iconpackFactory->queryAssets('js', 'frontend');
            foreach ($javaScripts as $javaScript) {
                $pageRenderer->addJsFile($javaScript);
            }
        }
    }
}
