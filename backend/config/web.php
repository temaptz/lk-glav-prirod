<?php
return [
    'id' => 'gp-ecology-backend',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'components' => [
        'request' => [
            'cookieValidationKey' => getenv('COOKIE_KEY') ?: 'changeme',
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
            'enableCsrfValidation' => false,
        ],
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'user' => [
            'identityClass' => app\models\User::class,
            'enableSession' => false,
            'loginUrl' => null,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'db' => require __DIR__ . '/db.php',
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                // Auth
                'POST auth/login' => 'auth/login',
                'POST auth/register' => 'auth/register',
                
                // Debug endpoint
                'GET debug/rls-test' => 'debug/rls-test',
                
                // REST controllers
                ['class' => yii\rest\UrlRule::class, 'controller' => 'organization', 'pluralize' => false],
                ['class' => yii\rest\UrlRule::class, 'controller' => 'requirement', 'pluralize' => false],
                ['class' => yii\rest\UrlRule::class, 'controller' => 'client-requirement', 'pluralize' => false],
                ['class' => yii\rest\UrlRule::class, 'controller' => 'calendar', 'pluralize' => false],
                ['class' => yii\rest\UrlRule::class, 'controller' => 'risk', 'pluralize' => false],
                ['class' => yii\rest\UrlRule::class, 'controller' => 'contract', 'pluralize' => false],
                ['class' => yii\rest\UrlRule::class, 'controller' => 'invoice', 'pluralize' => false],
                ['class' => yii\rest\UrlRule::class, 'controller' => 'act', 'pluralize' => false],
                
                // Artifact with custom actions
                'GET,HEAD artifact/index' => 'artifact/index',
                'POST artifact/upload' => 'artifact/upload',
                
                // Admin panel
                ['class' => yii\rest\UrlRule::class, 'controller' => 'admin-user', 'pluralize' => false],
            ],
        ],
    ],
    'params' => [],
];
