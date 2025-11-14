<?php
namespace app\models;

use yii\db\ActiveRecord;

class Requirement extends ActiveRecord
{
    public static function tableName()
    {
        return 'compliance.requirements';
    }

    public function rules()
    {
        return [
            [['code', 'title'], 'required'],
            [['code', 'title', 'npa_ref'], 'string', 'max' => 255],
            [['category_mask'], 'integer'],
            [['need_water', 'need_byproduct'], 'boolean'],
        ];
    }
}
