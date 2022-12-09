<?php

defined('TYPO3') or die();

// Add new field type to NodeFactory in order to render the icon fields
$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][1591191626] = [
    'nodeName' => 'IconpackWizard',
    'priority' => '70',
    'class' => \Quellenform\Iconpack\Form\Element\IconpackWizardElement::class,
];

// Register extension icon
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
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['iconpack'] = [
        'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
        'backend' => \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend::class,
        'options' => [
            'defaultLifetime' => 0
        ],
        'groups' => ['system']
    ];
}

// Override HTML sanitizer to allow SVG tags and attributes in bodytext
$GLOBALS['TYPO3_CONF_VARS']['SYS']['htmlSanitizer']['default'] = \Quellenform\Iconpack\Sanitizer\IconpackHtmlSanitizer::class;

// Add transformation class for parsing the bodytext content (RTE <-> DB)
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_parsehtml_proc.php']['transformation']['icon'] = \Quellenform\Iconpack\Html\IconpackRteTransformation::class;

// Set overrule mode to allow icon-transformations
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('RTE.default.proc.overruleMode = default,icon');

// Add some values to the list of allowed attributes for span-tags
if ((bool) \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(
    \TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class
)->get('iconpack', 'autoConfigRte')) {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
    RTE.default.proc {
        # Allow additional attributes in SPAN-tags on the way from RTE to DB
        HTMLparser_db.tags.span.allowedAttribs := addToList(data-iconfig, style)
        # Allow various tags to be processed and transformed
        # TODO: addToList doesnt work in this case, so we use this ugly thing instead
        allowTags {
            101 = icon
            102 = svg
            103 = use
            104 = path
        }
    }
');
}
