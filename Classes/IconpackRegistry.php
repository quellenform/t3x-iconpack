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

use InvalidArgumentException;
use Quellenform\Iconpack\Domain\Model\IconpackProvider;
use Quellenform\Iconpack\Utility\IconpackUtility;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class IconpackRegistry, which makes it possible to register custom Iconpack-Providers from within an extension.
 *
 * Usage:
 *   $iconpackRegistry = GeneralUtility::makeInstance(\Quellenform\Iconpack\IconpackRegistry::class);
 *   $iconpackRegistry->registerIconpack(
 *       'MY_UNIQUE_ICONPACK_IDENTIFIER',
 *       'EXT:my_iconpack_extension/Configuration/Iconpackconfiguration.yaml'
 *   );
 */
class IconpackRegistry implements SingletonInterface
{

    /**
     * Array of iconpack provider objects.
     *
     * @var array
     */
    protected $iconpackProviders = [];

    /**
     * Registers an Iconpack Provider to be available inside the Iconpack Factory
     *
     * @param string $configurationFile
     * @param string|null $configurationFileMerge
     * @param string|null $iconpackConfigClassName
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function registerIconpack(
        string $configurationFile,
        ?string $configurationFileMerge = null,
        ?string $iconpackConfigClassName = null
    ): void {
        if ($iconpackConfigClassName) {
            if (!in_array(IconpackConfigurationInterface::class, class_implements($iconpackConfigClassName) ?: [], true)) {
                throw new InvalidArgumentException(
                    'An IconpackConfiguration must implement ' . IconpackConfigurationInterface::class,
                    2100109270
                );
            }
        }
        $sourceFile = GeneralUtility::getFileAbsFileName($configurationFile);
        if (!file_exists($sourceFile)) {
            throw new InvalidArgumentException(
                'The YAML configuration file could not be found.',
                2100109271
            );
        }

        $configuration = IconpackUtility::loadYamlFile($configurationFile, 'iconpack');

        // Merge configuration files
        if ($configurationFileMerge && !empty($configurationFileMerge)) {
            $sourceFileMerge = GeneralUtility::getFileAbsFileName($configurationFileMerge);
            if (file_exists($sourceFileMerge)) {
                $configurationMerge = IconpackUtility::loadYamlFile($configurationFileMerge, 'iconpack');
                // Merge only main keys!
                $configuration['iconpack'] = array_merge(
                    $configuration['iconpack'],
                    $configurationMerge['iconpack']
                );
            }
        }

        // Check if there is a key given
        if (!isset($configuration['iconpack']['key']) || empty($configuration['iconpack']['key'])) {
            throw new InvalidArgumentException(
                'Iconpack key is required and must not be empty',
                2100109273
            );
        }
        // Set identifier for the iconpackProvider
        $configuration['iconpack']['key'] = preg_replace('/[\W]/', '', $configuration['iconpack']['key']);
        $iconpackIdentifier = $configuration['iconpack']['key'];
        if (isset($this->iconpackProviders[$iconpackIdentifier])) {
            throw new InvalidArgumentException(
                'Iconpack with key \'' . $iconpackIdentifier . '\' is already registered',
                2100109274
            );
        }

        // Merge configuration from external files
        $iconpackProviderConfig = [];
        foreach ($configuration['iconpack'] as $confKey => $conf) {
            if (in_array($confKey, ['config', 'icons', 'categories', 'options']) && !is_array($conf) && !empty($conf)) {
                if (preg_match('/.*\\.([^\\.]*$)/', $conf, $reg)) {
                    $fileExt = strtolower($reg[1]);
                    $sourceFile = GeneralUtility::getFileAbsFileName($conf);
                    if (file_exists($sourceFile)) {
                        if ($fileExt === 'yml' || $fileExt === 'yaml') {
                            $conf = IconpackUtility::loadYamlFile($sourceFile);
                        } elseif ($fileExt === 'json') {
                            $jsonData = file_get_contents($sourceFile);
                            $conf = json_decode($jsonData, true);
                        }
                    } else {
                        throw new InvalidArgumentException(
                            'Iconpack configuration file \'' . $sourceFile . '\' could not be found!',
                            2100109275
                        );
                    }
                }
            }
            $iconpackProviderConfig[$confKey] = $conf;
        }
        // Post-Process the configuration by a custom Processor
        if ($iconpackConfigClassName) {
            $iconpackProviderConfig = GeneralUtility::makeInstance(
                $iconpackConfigClassName
            )->configureIconpack($iconpackIdentifier, $iconpackProviderConfig);
        }

        /** @var IconpackProvider $iconpackProvider */
        $iconpackProvider = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \Quellenform\Iconpack\Domain\Model\IconpackProvider::class,
            $iconpackProviderConfig
        );

        $this->iconpackProviders[$iconpackIdentifier] = $iconpackProvider;
    }

    /**
     * Get Iconpack-Provider by Identifier.
     *
     * @param string $iconpackIdentifier
     *
     * @return IconpackProvider|null
     */
    public function getIconpackProviderByIdentifier(string $iconpackIdentifier): ?IconpackProvider
    {
        return $this->iconpackProviders[$iconpackIdentifier];
    }

    /**
     * Get an array with the keys of all registered Iconpack-Providers.
     *
     * @return array
     */
    public function getIconpackProviderIdentifiers(): array
    {
        $iconpackProviderIdentifiers = [];
        foreach ($this->iconpackProviders as $key => $_) {
            $iconpackProviderIdentifiers[] = $key;
        }
        return $iconpackProviderIdentifiers;
    }
}
