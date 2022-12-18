<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Iconpack',
    'description' => 'Provides an iconpack-registry for various iconpacks.',
    'category' => 'fe',
    'state' => 'beta',
    'clearcacheonload' => true,
    'author' => 'Stephan Kellermayr',
    'author_email' => 'typo3@quellenform.at',
    'author_company' => 'Kellermayr KG',
    'version' => '0.2.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.11-11.5.99'
        ],
        'conflicts' => [],
        'suggests' => []
    ],
    'autoload' => [
        'psr-4' => ['Quellenform\\Iconpack\\' => 'Classes']
    ],
];
