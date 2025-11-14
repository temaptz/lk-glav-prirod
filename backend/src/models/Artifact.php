<?php
namespace app\models;

use app\components\AuditBehavior;
use yii\db\ActiveRecord;

class Artifact extends ActiveRecord
{
    public static function tableName()
    {
        return 'compliance.artifacts';
    }

    public function behaviors()
    {
        return [
            [
                'class' => AuditBehavior::class,
                'actions' => ['insert', 'delete'], // Log uploads and deletions
            ],
        ];
    }

    public function rules()
    {
        return [
            [['org_id', 'path'], 'required'],
            [['org_id', 'uploaded_by', 'requirement_id'], 'integer'],
            [['path', 'mime', 'filename', 'original_name'], 'string', 'max' => 255],
            [['uploaded_at'], 'safe'],
            [['with_audit'], 'boolean'],
        ];
    }

    public function fields()
    {
        return array_merge(parent::fields(), [
            'url' => fn() => \app\components\Storage::url($this->path),
        ]);
    }
}
