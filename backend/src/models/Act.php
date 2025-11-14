<?php
namespace app\models;

use yii\db\ActiveRecord;

class Act extends ActiveRecord
{
    public static function tableName()
    {
        return 'finance.acts';
    }

    public function rules()
    {
        return [
            [['contract_id', 'number'], 'required'],
            [['contract_id'], 'integer'],
            [['accepted_at'], 'safe'],
            [['number'], 'string', 'max' => 100],
        ];
    }
}
