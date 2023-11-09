<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Iconpack',
    'description' => 'Provides an iconpack-registry for various iconpacks.',
    'category' => 'fe',
    'state' => 'stable',
    'clearcacheonload' => true,
    'author' => 'Stephan Kellermayr',
    'author_email' => 'typo3@quellenform.at',
    'author_company' => 'Kellermayr KG',
    'version' => '1.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.11-12.4.99'
        ],
        'conflicts' => [],
        'suggests' => []
    ],
    'autoload' => [
        'psr-4' => ['Quellenform\\Iconpack\\' => 'Classes']
    ],
];
