<?php
namespace app\models;

use yii\db\ActiveRecord;

class Risk extends ActiveRecord
{
    public static function tableName()
    {
        return 'compliance.risks';
    }

    public function rules()
    {
        return [
            [['koap_article', 'min_fine', 'max_fine'], 'required'],
            [['koap_article'], 'string', 'max' => 50],
            [['min_fine', 'max_fine'], 'integer'],
            [['description'], 'string'],
        ];
    }
}
