<?php
namespace app\controllers;

use app\models\Act;
use Yii;
use yii\data\ActiveDataProvider;

class ActController extends BaseRestController
{
    public $modelClass = Act::class;
    
    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }
    
    /**
     * Acts filtered through contracts
     */
    public function prepareDataProvider()
    {
        $userId = Yii::$app->user->id;
        
        if (!$userId) {
            throw new \yii\web\UnauthorizedHttpException('Authentication required');
        }
        
        $query = Act::find()
            ->innerJoin('finance.contracts', 'finance.contracts.id = finance.acts.contract_id')
            ->innerJoin('auth.users_orgs', 'auth.users_orgs.org_id = finance.contracts.org_id')
            ->where(['auth.users_orgs.user_id' => $userId]);
        
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);
    }
}
