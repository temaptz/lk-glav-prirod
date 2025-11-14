<?php
namespace app\models;

use yii\db\ActiveRecord;

class CalendarEvent extends ActiveRecord
{
    public static function tableName()
    {
        return 'compliance.calendar_events';
    }

    public function rules()
    {
        return [
            [['org_id', 'title', 'event_date'], 'required'],
            [['org_id', 'requirement_id'], 'integer'],
            [['event_date'], 'safe'],
            [['title'], 'string', 'max' => 255],
        ];
    }
}
