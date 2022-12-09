<?php

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'tt_content',
    [
        'header_icon' => [
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
    'tt_content',
    'headers',
    'header_icon;LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:tt_content.header_icon',
    'after:header_position'
);
