<?php

defined('TYPO3') or die();

// Add static typoscript configuration
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'iconpack',
    'Configuration/TypoScript/',
    'Iconpack'
);

// Add static typoscript configuration for EXT:bootstrap_package
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'iconpack',
    'Configuration/TypoScript/BootstrapPackage/',
    'Iconpack for "Bootstrap Package"'
);
