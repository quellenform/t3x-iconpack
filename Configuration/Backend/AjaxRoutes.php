<?php

return [
    'iconpack_icon' => [
        'path' => '/iconpack/icon',
        'referrer' => 'required,refresh-empty',
        'target' => \Quellenform\Iconpack\Controller\IconpackController::class . '::getIconAction',
    ],
    'iconpack_modal' => [
        'path' => '/iconpack/modal',
        'referrer' => 'required,refresh-empty',
        'target' => \Quellenform\Iconpack\Controller\IconpackController::class . '::getModalAction',
    ],
    'iconpack_modal_update' => [
        'path' => '/iconpack/modal/update',
        'referrer' => 'required,refresh-empty',
        'target' => \Quellenform\Iconpack\Controller\IconpackController::class . '::updateModalAction',
    ]
];
