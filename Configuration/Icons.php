<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
    // Icon identifier
    'ext-iconpack' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:iconpack/Resources/Public/Icons/Extension.svg'
    ]
];
