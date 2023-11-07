<?php

return [
    'dependencies' => [
        'backend'
    ],
    'tags' => [
        'backend.form'
    ],
    'imports' => [
        '@quellenform/iconpack.js' => 'EXT:iconpack/Resources/Public/JavaScript/v12/Iconpack.js',
        '@quellenform/iconpack-modal.js' => 'EXT:iconpack/Resources/Public/JavaScript/v12/IconpackModal.js',
        '@quellenform/iconpack-wizard.js' => 'EXT:iconpack/Resources/Public/JavaScript/v12/IconpackWizard.js',
        '@quellenform/iconpack-ckeditor.js' => 'EXT:iconpack/Resources/Public/JavaScript/v12/CKEditor/plugin.js'
    ],
];
