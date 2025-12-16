<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Iconpack',
    'description' => 'The ultimate iconpack extension for TYPO3, which makes it easier for editors/developers to use icons in the BE/FE/RTE and ViewHelper. Flexible, future-proof, easy to use.',
    'category' => 'fe',
    'state' => 'stable',
    'clearcacheonload' => true,
    'author' => 'Stephan Kellermayr',
    'author_email' => 'typo3@quellenform.at',
    'author_company' => 'Kellermayr KG',
    'version' => '1.3.4',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.11-14.9.99'
        ],
        'conflicts' => [],
        'suggests' => []
    ]
];
