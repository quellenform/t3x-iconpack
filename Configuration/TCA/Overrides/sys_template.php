<?php

defined('TYPO3') || die();

// Add static typoscript configuration
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'iconpack',
    'Configuration/TypoScript/',
    'Iconpack'
);
