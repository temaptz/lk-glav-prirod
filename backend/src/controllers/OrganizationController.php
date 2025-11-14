<?php
namespace app\controllers;

use app\models\Organization;
use Yii;
use yii\data\ActiveDataProvider;

class OrganizationController extends BaseRestController
{
    public $modelClass = Organization::class;
    
    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }
    
    /**
     * Organizations use direct ID mapping (not org_id column)
     */
    public function prepareDataProvider()
    {
        $userId = Yii::$app->user->id;
        
        if (!$userId) {
            throw new \yii\web\UnauthorizedHttpException('Authentication required');
        }
        
        $query = Organization::find()
            ->innerJoin('auth.users_orgs', 'auth.users_orgs.org_id = compliance.organizations.id')
            ->where(['auth.users_orgs.user_id' => $userId]);
        
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);
    }
}
