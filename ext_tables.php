<?php

defined('TYPO3') || die();

// Hook to add iconpack assets to the PageRenderer in the frontend:
if (
    (bool) \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
    )->get('iconpack', 'autoAddAssets')
) {
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess'][]
        = \Quellenform\Iconpack\Hooks\PageRendererHook::class . '->addIconpackAssets';
}

$GLOBALS['TBE_STYLES']['skins']['iconpack']['stylesheetDirectories']['css']
    = 'EXT:iconpack/Resources/Public/Css/Backend/FormEngine/';
