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

use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;

/**
 * Iconpack cache helper.
 */
class IconpackCache
{
    /**
     * Cache manager
     *
     * @var FrontendInterface
     */
    private $cache;

    /**
     * Constructor
     */
    public function __construct(
        FrontendInterface $cache
    ) {
        $this->cache = $cache;
    }

    /**
     * Save data to the cache.
     *
     * @param string $cacheIdentifier
     * @param array|null $data
     *
     * @return void
     */
    public function setCacheByIdentifier(string $cacheIdentifier, ?array $data = null): void
    {
        $this->cache->set($this->getCacheIdentifier($cacheIdentifier), $data);
    }

    /**
     * Get data from the cache by identifier.
     *
     * @param string $identifier
     *
     * @return array|null
     */
    public function getCacheByIdentifier(string $cacheIdentifier): ?array
    {
        $data = $this->cache->get($this->getCacheIdentifier($cacheIdentifier));
        if ($data !== false) {
            return $data;
        }
        return null;
    }

    /**
     * Check if cache has data with the given identifier.
     *
     * @param string $identifier
     *
     * @return bool
     */
    public function hasCacheIdentifier(string $cacheIdentifier): bool
    {
        return $this->cache->has($this->getCacheIdentifier($cacheIdentifier));
    }

    /**
     * Get the final cache identifier.
     *
     * @param string $cacheIdentifier
     *
     * @return string
     */
    public function getCacheIdentifier(string $cacheIdentifier): string
    {
        return 'Iconpack_' . str_replace(':', '-', $cacheIdentifier);
    }
}
