<?php

defined('TYPO3') || die();

call_user_func(static function () {
    // Add new field type to NodeFactory in order to render the icon fields
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1591191626] = [
        'nodeName' => 'IconpackWizard',
        'priority' => '70',
        'class' => \Quellenform\Iconpack\Form\Element\IconpackWizardElement::class,
    ];

    // Register extension icon for the backend
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
        \TYPO3\CMS\Core\Imaging\IconRegistry::class
    )->registerIcon(
        'ext-iconpack',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        [
            'source' => 'EXT:iconpack/Resources/Public/Icons/Extension.svg'
        ]
    );

    // Configure the caching frontend/backend
    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['iconpack'])) {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['iconpack'] ??= [];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['iconpack']['backend']
            ??= \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class;
    }

    // Extend HTML sanitizer to allow SVG tags and attributes in bodytext
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['htmlSanitizer']['default']
        = \Quellenform\Iconpack\Sanitizer\IconpackHtmlSanitizer::class;

    if (version_compare(TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '13.3', '<')) {
        // XLCASS \TYPO3\CMS\Core\Html\RteHtmlParser for transforming the bodytext content (RTE <-> persistence) in TYPO3 <13.3
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\TYPO3\CMS\Core\Html\RteHtmlParser::class] = [
            'className' => \Quellenform\Iconpack\Xclass\RteHtmlParser::class
        ];
    }

    if (
        (bool) \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
            \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
        )->get('iconpack', 'autoConfigRte')
    ) {
        // Allow additional attributes in <span> tags on the way from RTE to DB (used by HTMLcleaner)
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig(
            'RTE.default.proc.HTMLparser_db.tags.span.allowedAttribs:=addToList(data-iconfig,id,name,class,style,alt,title)'
        );
    }

    if (version_compare(TYPO3\CMS\Core\Utility\VersionNumberUtility::getCurrentTypo3Version(), '12.3.0', '>=')) {
        // https://docs.typo3.org/c/typo3/cms-core/12.4/en-us/Changelog/12.3/Deprecation-100033-TBE_STYLESStylesheetAndStylesheet2.html
        $GLOBALS['TYPO3_CONF_VARS']['BE']['stylesheets']['iconpack']
            = 'EXT:iconpack/Resources/Public/Css/Backend/FormEngine/IconpackWizard.min.css';
    }

    // Hook to add iconpack assets to the AssetsCollector in the FE/BE:
    $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_pagerenderer.php']['render-preProcess']['quellenform/assetcollector']
        = \Quellenform\Iconpack\Hooks\AssetRenderer::class . '->addCss';
});
