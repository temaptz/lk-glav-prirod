<?php
namespace app\controllers;

use app\models\Invoice;
use Yii;
use yii\data\ActiveDataProvider;

class InvoiceController extends BaseRestController
{
    public $modelClass = Invoice::class;
    
    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }
    
    /**
     * Invoices filtered through contracts
     */
    public function prepareDataProvider()
    {
        $userId = Yii::$app->user->id;
        
        if (!$userId) {
            throw new \yii\web\UnauthorizedHttpException('Authentication required');
        }
        
        $query = Invoice::find()
            ->innerJoin('finance.contracts', 'finance.contracts.id = finance.invoices.contract_id')
            ->innerJoin('auth.users_orgs', 'auth.users_orgs.org_id = finance.contracts.org_id')
            ->where(['auth.users_orgs.user_id' => $userId]);
        
        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);
    }
}
