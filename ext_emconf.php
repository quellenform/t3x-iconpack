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
    'version' => '1.1.8',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.11-13.9.99'
        ],
        'conflicts' => [],
        'suggests' => []
    ],
];
