<?php
namespace app\models;

use yii\db\ActiveRecord;

class Contract extends ActiveRecord
{
    public static function tableName()
    {
        return 'finance.contracts';
    }

    public function rules()
    {
        return [
            [['org_id', 'number'], 'required'],
            [['org_id'], 'integer'],
            [['signed_at'], 'safe'],
            [['number', 'status'], 'string', 'max' => 100],
        ];
    }
}
