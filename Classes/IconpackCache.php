<?php

declare(strict_types=1);

namespace Quellenform\Iconpack;

/*
 * This file is part of the "iconpack" Extension for TYPO3 CMS.
 *
 * Conceived and written by Stephan Kellermayr
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Iconpack cache helper.
 */
class IconpackCache
{

    /**
     * @var bool
     */
    protected static $cacheEnabled = true;

    /**
     * @var FrontendInterface
     */
    protected static $cache = null;

    public function __construct(bool $enablCache = true)
    {
        static::$cacheEnabled = $enablCache;
    }

    /**
     * Get cache by identifier.
     *
     * @param string $identifier
     *
     * @return array|null
     */
    public function getCacheByIdentifier(string $cacheIdentifier): ?array
    {
        if (static::$cacheEnabled) {
            /** @var VariableFrontend $iconpackCache */
            $iconpackCache = static::$cache ?? $this->getCache();
            $data = $iconpackCache->get($cacheIdentifier);
            if ($data !== false) {
                return $data;
            }
        }
        return null;
    }

    /**
     * Save data to the cache.
     *
     * @param string $cacheIdentifier
     * @param array|null $data
     *
     * @return void
     */
    public function setCacheByIdentifier(string $cacheIdentifier, ?array $data = null)
    {
        if (static::$cacheEnabled && $data) {
            /** @var VariableFrontend $iconpackCache */
            $iconpackCache = static::$cache ?? $this->getCache();
            $iconpackCache->set($cacheIdentifier, $data);
        }
    }

    /**
     * @param FrontendInterface $cache
     * @internal
     *
     * @return FrontendInterface
     */
    private function getCache(): FrontendInterface
    {
        static::$cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('iconpack');
        return static::$cache;
    }
}
