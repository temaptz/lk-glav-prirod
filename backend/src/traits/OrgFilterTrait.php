<?php
namespace app\traits;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * Trait for filtering data by user's organizations
 * Applies JOIN with auth.users_orgs table to enforce access control
 */
trait OrgFilterTrait
{
    /**
     * Override actions to use custom data provider
     */
    public function actions()
    {
        $actions = parent::actions();
        
        // Customize the data provider for index action
        if (isset($actions['index'])) {
            $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        }
        
        return $actions;
    }
    
    /**
     * Prepare data provider with organization filtering
     * Override this method in controller if custom filtering needed
     */
    public function prepareDataProvider()
    {
        $userId = Yii::$app->user->id;
        
        if (!$userId) {
            throw new \yii\web\UnauthorizedHttpException('Authentication required');
        }
        
        $modelClass = $this->modelClass;
        $tableName = $modelClass::tableName();
        
        // Build query with JOIN to users_orgs
        $query = $modelClass::find()
            ->innerJoin('auth.users_orgs', "auth.users_orgs.org_id = {$tableName}.org_id")
            ->where(['auth.users_orgs.user_id' => $userId]);
        
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);
    }
}
