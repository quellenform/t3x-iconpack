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
use Quellenform\Iconpack\IconpackCache;
use Quellenform\Iconpack\Utility\IconpackUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Class IconpackRegistry, which makes it possible to register custom Iconpack-Providers from within an extension.
 *
 * Usage:
 *   $iconpackRegistry = GeneralUtility::makeInstance(\Quellenform\Iconpack\IconpackRegistry::class);
 *   $iconpackRegistry->registerIconpack(
 *       'EXT:my_iconpack_extension/Configuration/Main.yaml',
 *       'EXT:my_iconpack_extension/Configuration/Override.yaml',
 *       '\Namespace\ExtensionName\Configuration\CustomPreProcessor::class'
 *   );
 */
final class IconpackRegistry
{
    /**
     * Array of iconpack provider objects.
     *
     * @var array|null
     */
    private $iconpackProviders = [];

    /**
     * Array of iconpack provider configuration files collected at low level.
     *
     * @var array
     */
    private $iconpackRegister = [];

    /**
     * @var IconpackCache
     */
    private $iconpackCache = null;

    /**
     * Registers an Iconpack Provider to be available inside the Iconpack Factory.
     *
     * @param string $configurationFile
     * @param string|null $configurationFileMerge
     * @param string|null $configurationPreProcessor
     *
     * @return void
     * @throws InvalidArgumentException
     */
    public function registerIconpack(
        string $configurationFile,
        ?string $configurationFileMerge = null,
        ?string $configurationPreProcessor = null
    ): void {
        $configurationIdentifier = $this->validateIconpackPreProcessor($configurationPreProcessor);
        $configurationIdentifier .= $this->validateIconpackConfigurationFile($configurationFile);
        $configurationIdentifier .= $this->validateIconpackConfigurationFile($configurationFileMerge);

        $hash = sha1($configurationIdentifier);
        $register = [
            $configurationFile,
            $configurationFileMerge,
            $configurationPreProcessor
        ];
        $this->iconpackRegister[$hash] = $register;

        // No pre-caching, ...retain old behavior for backward compatibility with version 10
        if (version_compare(VersionNumberUtility::getCurrentTypo3Version(), '11.4.0', '<')) {
            $iconpackProvider = $this->parseIconpackConfiguration($register);
            $this->iconpackProviders[$iconpackProvider->getKey()] = $iconpackProvider;
        }
    }

    /**
     * Build the base configuration array for all registered iconpacks after TYPO3 has been booted successfully.
     *
     * @return void
     */
    public function buildIconpackProviderConfiguration(): void
    {
        $hasCachedRegister = $this->getIconpackCache()->hasCacheIdentifier('register');

        // Self-healing cache when an iconpack has been installed/changed.
        if ($hasCachedRegister) {
            $hashes = $this->getIconpackCache()->getCacheByIdentifier('hashes');
            if (
                count(array_diff(array_keys($this->iconpackRegister), $hashes))
            ) {
                $hasCachedRegister = false;
                $this->iconpackProviders = [];
            }
        }

        // Build configuration cache
        if (!$hasCachedRegister && !count($this->iconpackProviders)) {
            $hashes = [];
            foreach ($this->iconpackRegister as $hash => $register) {
                $hashes[] = $hash;
                $iconpackProvider = $this->parseIconpackConfiguration($register);
                $this->iconpackProviders[$iconpackProvider->getKey()] = $iconpackProvider;
            }
            $this->getIconpackCache()->setCacheByIdentifier('hashes', $hashes);
            $this->getIconpackCache()->setCacheByIdentifier('register', $this->iconpackProviders);
        }
    }

    /**
     * Parse the iconpack configuration and build the final provider model.
     *
     * @param array $register
     *
     * @return IconpackProvider
     * @throws InvalidArgumentException
     */
    private function parseIconpackConfiguration(array $register): IconpackProvider
    {
        // Read and merge iconpack configuration files
        $configuration = IconpackUtility::loadYamlFile($register[0], 'iconpack');
        if (!empty($register[1])) {
            $configurationMerge = IconpackUtility::loadYamlFile($register[1], 'iconpack');
            // Merge only main keys!
            $configuration['iconpack'] = array_merge(
                $configuration['iconpack'],
                $configurationMerge['iconpack']
            );
        }

        // Set identifier for the iconpackProvider
        $iconpackIdentifier = $this->validateIconpackKey($configuration);

        // Merge configuration from external files
        $iconpackProviderConfig = $this->prepareConfigurationArray($configuration);

        // Pre-process the configuration by a custom processor
        if (!empty($register[2])) {
            $iconpackProviderConfig = GeneralUtility::makeInstance(
                $register[2]
            )->configureIconpack($iconpackIdentifier, $iconpackProviderConfig);
        }

        /** @var IconpackProvider $iconpackProvider */
        $iconpackProvider = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \Quellenform\Iconpack\Domain\Model\IconpackProvider::class,
            $iconpackProviderConfig
        );

        return $iconpackProvider;
    }



    /**
     * Validate the iconpack preProcessor and return its class name.
     *
     * @param string|null $className
     *
     * @return string
     * @throws InvalidArgumentException
     */
    private function validateIconpackPreProcessor(?string $className): string
    {
        if ($className) {
            if (!in_array(IconpackConfigurationInterface::class, class_implements($className) ?: [], true)) {
                throw new InvalidArgumentException(
                    'An IconpackConfiguration must implement ' . IconpackConfigurationInterface::class,
                    2100109270
                );
            }
            return $className;
        }
        return '';
    }

    /**
     * Validate if a given configuration file exists and return an unique identifier.
     *
     * @param string|null $filePath
     *
     * @return string
     * @throws InvalidArgumentException
     */
    private function validateIconpackConfigurationFile(?string $filePath): string
    {
        if (!empty($filePath)) {
            $fileAbsFileName = GeneralUtility::getFileAbsFileName($filePath);
            if (!file_exists($fileAbsFileName)) {
                throw new InvalidArgumentException(
                    'The YAML configuration file "' . $filePath . '" could not be found.',
                    2100109271
                );
            }
            return $filePath . '?' . (string) filemtime($fileAbsFileName);
        }
        return '';
    }

    /**
     * Verify that the specified iconpack configuration contains a valid key and return a unique identifier.
     *
     * @param array $configuration
     *
     * @return string
     * @throws InvalidArgumentException
     */
    private function validateIconpackKey(array &$configuration): string
    {
        if (
            !isset($configuration['iconpack']['key'])
            || empty($configuration['iconpack']['key'])
        ) {
            throw new InvalidArgumentException(
                'The icon pack key is required, must not be empty, and may only contain alphanumeric characters.',
                2100109273
            );
        }
        $configuration['iconpack']['key'] = preg_replace('/[\W]/', '', $configuration['iconpack']['key']);
        $this->checkDuplicateIconpackKey($configuration['iconpack']['key']);
        return $configuration['iconpack']['key'];
    }

    /**
     * Check if the given iconpack identifier is already registered.
     *
     * @param string $iconpackIdentifier
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function checkDuplicateIconpackKey(string $iconpackIdentifier): void
    {
        if (isset($this->iconpackProviders[$iconpackIdentifier])) {
            throw new InvalidArgumentException(
                'An iconpack with the key \'' . $iconpackIdentifier . '\' is already registered',
                2100109274
            );
        }
    }

    /**
     * Iterate through the configuration and load additional content if it refers to an external file (YAML/JSON).
     *
     * @param array $configuration
     *
     * @return array
     * @throws InvalidArgumentException
     */
    private function prepareConfigurationArray(array $configuration): array
    {
        $iconpackProviderConfig = [];
        foreach ($configuration['iconpack'] as $confKey => $conf) {
            if (in_array($confKey, ['icons', 'categories', 'options']) && !is_array($conf) && !empty($conf)) {
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
        return $iconpackProviderConfig;
    }

    /**
     * Get a specific iconpack provider using a specified identifier.
     *
     * @param string $iconpackIdentifier
     *
     * @return IconpackProvider|null
     * @throws InvalidArgumentException
     */
    public function getIconpackProviderByIdentifier(string $iconpackIdentifier): ?IconpackProvider
    {
        if (!$this->iconpackProviders) {
            $this->iconpackProviders = $this->getIconpackCache()->getCacheByIdentifier('register') ?? [];
        }
        return $this->iconpackProviders[$iconpackIdentifier];
    }

    /**
     * Get a (cached) array containing the identifiers of all registered iconpack providers.
     *
     * @param bool $visibleOnly Only return iconpacks that are not marked as hidden
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getIconpackProviderIdentifiers(bool $visibleOnly = false): array
    {
        if (!$this->iconpackProviders) {
            $this->iconpackProviders = $this->getIconpackCache()->getCacheByIdentifier('register') ?? [];
        }
        $iconpackProviderIdentifiers = [];
        foreach ($this->iconpackProviders as $key => $iconpackProvider) {
            if ($visibleOnly && $iconpackProvider->getHidden()) {
                continue;
            }
            $iconpackProviderIdentifiers[] = $key;
        }
        return $iconpackProviderIdentifiers;
    }

    /**
     * Get an instance of the iconpack cache.
     *
     * @return IconpackCache
     */
    private function getIconpackCache(): IconpackCache
    {
        if (!$this->iconpackCache) {
            return GeneralUtility::makeInstance(IconpackCache::class);
        } else {
            return $this->iconpackCache;
        }
    }
}
