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

/**
 * Interface IconpackParserInterface
 */
interface IconpackConfigurationInterface
{
    /**
     * Post-configure an iconpack ocnfiguration (used by iconpack-extensions).
     * This can be useful if you want to override values in the given YAML-file afterwards.
     *
     * @param string $iconpackIdentifier
     * @param array $configuration
     *
     * @return array
     */
    public function configureIconpack(string $iconpackIdentifier, array $configuration): array;
}
