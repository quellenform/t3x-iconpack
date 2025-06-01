<?php

declare(strict_types=1);

namespace Quellenform\Iconpack\Utility;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Localization utilities for Iconpack.
 */
final class IconpackLocalization
{

    /**
     * @var LanguageService
     */
    private static $languageService;

    /**
     * @var string
     */
    private static $langCode = null;

    /**
     * Get a translated label.
     *
     * @param string $label
     * @param string|null $default
     *
     * @return string
     */
    public function getTranslatedLabel(string $label, ?string $default = ''): string
    {
        $labelBefore = $label;
        if (!empty($label)) {
            $label = $this->getLanguageService()->sL($label);
        } else {
            $label = $default;
        }
        return $label;
    }

    /**
     * Get the current language code in the backend/frontend context.
     *
     * @return string
     */
    public function getLanguageCode(): string
    {
        if (empty(static::$langCode)) {
            if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
                if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.4', '>=')) {
                    $languageService = $this->getLanguageService();
                    $langCode = $languageService->getLocale()->getLanguageCode();
                } else {
                    if (isset($GLOBALS['BE_USER']->uc['lang'])) {
                        //$langCode = str_replace('default', 'en', $GLOBALS['BE_USER']->uc['lang']);
                        $langCode = $GLOBALS['BE_USER']->uc['lang'];
                    }
                }
            } else {
                $siteLanguage = $this->getSiteLanguage();
                if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.4', '>=')) {
                    $langCode = $siteLanguage->getLocale()->getLanguageCode();
                } else {
                    // @extensionScannerIgnoreLine
                    $langCode = $siteLanguage->getTwoLetterIsoCode();
                }
            }
            static::$langCode = (empty($langCode) || $langCode == 'default') ? 'en' : $langCode;
        }
        return static::$langCode;
    }

    /**
     * Returns the language service in the backend/frontend context.
     *
     * @return LanguageService
     */
    private function getLanguageService(): LanguageService
    {
        if (!static::$languageService) {
            if (ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isBackend()) {
                static::$languageService = GeneralUtility::makeInstance(
                    LanguageServiceFactory::class
                )->createFromUserPreferences($GLOBALS['BE_USER']);
            } else {
                $siteLanguage = $this->getSiteLanguage();
                if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '12.0', '>=')) {
                    static::$languageService = GeneralUtility::makeInstance(
                        LanguageServiceFactory::class
                    )->createFromSiteLanguage($siteLanguage);
                } else {
                    // @extensionScannerIgnoreLine
                    static::$languageService = GeneralUtility::makeInstance(
                        LanguageService::class
                    )->createFromSiteLanguage($siteLanguage);
                }
            }
        }
        return static::$languageService;
    }

    /**
     * Returns the frontend site language.
     *
     * @return SiteLanguage
     */
    private function getSiteLanguage(): SiteLanguage
    {
        /** @var Site $site */
        $site = $GLOBALS['TYPO3_REQUEST']->getAttribute('site');
        $siteLanguage = $site->getLanguageById(
            GeneralUtility::makeInstance(
                Context::class
            )->getPropertyFromAspect('language', 'id')
        );
        return $siteLanguage;
    }
}
