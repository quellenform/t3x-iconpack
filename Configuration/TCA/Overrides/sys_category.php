<?php

defined('TYPO3') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
    'sys_category',
    [
        'category_icon' => [
            'exclude' => 1,
            'label' => 'LLL:EXT:iconpack/Resources/Private/Language/locallang_be.xlf:tt_content.header_icon',
            'config' => [
                'type' => 'user',
                'renderType' => 'IconpackWizard',
            ]
        ],

    ]
);

// Add custom field to TCA
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'sys_category',
    'category_icon',
    '',
    'after:title'
);