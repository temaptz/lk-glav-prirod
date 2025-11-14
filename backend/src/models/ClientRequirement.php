<?php
namespace app\models;

use app\components\AuditBehavior;
use yii\db\ActiveRecord;

class ClientRequirement extends ActiveRecord
{
    public static function tableName()
    {
        return 'compliance.client_requirements';
    }

    public function behaviors()
    {
        return [
            [
                'class' => AuditBehavior::class,
                'actions' => ['update'], // Log status changes
                'attributes' => ['status'], // Track only status changes
            ],
        ];
    }

    public function rules()
    {
        return [
            [['org_id', 'requirement_id'], 'required'],
            [['org_id', 'requirement_id', 'status', 'responsible_user_id'], 'integer'],
            [['deadline'], 'safe'],
        ];
    }

    public function fields()
    {
        return array_merge(parent::fields(), [
            'requirement' => function() { return $this->requirement; }
        ]);
    }

    public function getRequirement()
    {
        return $this->hasOne(Requirement::class, ['id' => 'requirement_id']);
    }

    /**
     * Dynamic calendar: when requirement is marked as done (status=2),
     * close current event and create new one for next period
     */
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        // Only trigger if status changed to "done" (2)
        if ($this->status == 2 && isset($changedAttributes['status']) && $changedAttributes['status'] != 2) {
            // Delete old calendar event
            \Yii::$app->db->createCommand()->delete('compliance.calendar_events', [
                'org_id' => $this->org_id,
                'requirement_id' => $this->requirement_id,
            ])->execute();

            // Create new event for next period (+1 year from deadline)
            if ($this->deadline) {
                $nextDate = date('Y-m-d', strtotime($this->deadline . ' +1 year'));
                $requirement = $this->requirement;
                
                \Yii::$app->db->createCommand()->insert('compliance.calendar_events', [
                    'org_id' => $this->org_id,
                    'requirement_id' => $this->requirement_id,
                    'title' => 'Сдача отчётности: ' . ($requirement ? $requirement->code : 'REQ'),
                    'event_date' => $nextDate,
                ])->execute();
            }
        }
    }
}
