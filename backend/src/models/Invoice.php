<?php
namespace app\models;

use yii\db\ActiveRecord;

class Invoice extends ActiveRecord
{
    public static function tableName()
    {
        return 'finance.invoices';
    }

    public function rules()
    {
        return [
            [['contract_id', 'number', 'amount', 'issued_at'], 'required'],
            [['contract_id'], 'integer'],
            [['amount'], 'number'],
            [['issued_at', 'paid_at'], 'safe'],
            [['number'], 'string', 'max' => 100],
        ];
    }
}
