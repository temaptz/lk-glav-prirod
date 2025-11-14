<?php
namespace app\components;

use Yii;
use yii\base\Behavior;
use yii\web\Controller;

/**
 * CORS + RLS behavior - apply to controllers
 */
class CorsAndRls extends Behavior
{
    public function events()
    {
        return [
            Controller::EVENT_BEFORE_ACTION => 'beforeAction',
        ];
    }

    public function beforeAction($event)
    {
        // Set CORS headers
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        if (Yii::$app->request->method === 'OPTIONS') {
            Yii::$app->response->statusCode = 200;
            Yii::$app->end();
        }

        // Apply RLS after user is authenticated
        if (!Yii::$app->user->isGuest) {
            Rls::apply();
        }

        return true;
    }
}
