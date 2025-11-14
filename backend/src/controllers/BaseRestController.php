<?php
namespace app\controllers;

use app\components\CorsAndRls;
use app\components\JwtHttpBearerAuth;
use yii\rest\ActiveController;
use yii\filters\Cors;

class BaseRestController extends ActiveController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // Add CORS
        $behaviors['cors'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 3600,
            ],
        ];
        
        // Add JWT auth
        $behaviors['authenticator'] = [
            'class' => JwtHttpBearerAuth::class,
        ];
        
        // Add RLS behavior
        $behaviors['rls'] = [
            'class' => CorsAndRls::class,
        ];
        
        return $behaviors;
    }
}
