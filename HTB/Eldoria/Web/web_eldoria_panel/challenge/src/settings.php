<?php
$settings = [
    'templatesPath' => getenv('CRAFT_TEMPLATES_PATH') ?: __DIR__ . '/../templates',
    'db' => [
        'driver'   => 'sqlite',
        'database' => __DIR__ . '/../data/database.sqlite'
    ],
    'adminCookieName' => 'admin'
];

$GLOBALS['settings'] = $settings;
return $settings;
