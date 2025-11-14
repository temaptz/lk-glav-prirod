<?php
namespace app\models;

use yii\db\ActiveRecord;
use app\services\RequirementBuilder;

class Organization extends ActiveRecord
{
    public static function tableName()
    {
        return 'compliance.organizations';
    }

    public function rules()
    {
        return [
            [['name', 'category'], 'required'],
            [['name', 'inn', 'ogrn', 'water_source'], 'string', 'max' => 255],
            [['category'], 'integer'],
            [['has_byproduct'], 'boolean'],
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        // Auto-generate requirements when org profile changes
        if ($insert || isset($changedAttributes['category']) || isset($changedAttributes['water_source']) || isset($changedAttributes['has_byproduct'])) {
            RequirementBuilder::build($this);
        }
    }
}
