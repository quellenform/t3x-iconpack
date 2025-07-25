<?php

defined('TYPO3') || die();

if (version_compare(TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '12.3.0', '<')) {
    // https://docs.typo3.org/c/typo3/cms-core/main/en-us/Changelog/12.3/Deprecation-100232-TBE_STYLESSkinningFunctionality.html
    // @extensionScannerIgnoreLine
    $GLOBALS['TBE_STYLES']['skins']['iconpack']['stylesheetDirectories']['css']
        = 'EXT:iconpack/Resources/Public/Css/Backend/FormEngine/v12/';
}
