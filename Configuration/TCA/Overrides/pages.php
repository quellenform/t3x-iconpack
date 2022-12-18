<?php

defined('TYPO3') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'pages',
    [
        'page_icon' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:tt_content.header_icon',
            'config' => [
                'type' => 'user',
                'renderType' => 'IconpackWizard',
            ]
        ],

    ]
);

// Add custom fields to TCA
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'pages',
    'title',
    'page_icon;LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:tt_content.header_icon',
    'after:title'
);
