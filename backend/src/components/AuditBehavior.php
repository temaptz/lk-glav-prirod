<?php
namespace app\components;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;

/**
 * AuditBehavior - automatically logs changes to models
 * Usage: Add to model behaviors()
 */
class AuditBehavior extends Behavior
{
    public $actions = ['insert', 'update', 'delete']; // Which actions to log
    public $attributes = []; // Which attributes to track (empty = all)

    public function events()
    {
        $events = [];
        if (in_array('insert', $this->actions)) {
            $events[ActiveRecord::EVENT_AFTER_INSERT] = 'afterInsert';
        }
        if (in_array('update', $this->actions)) {
            $events[ActiveRecord::EVENT_AFTER_UPDATE] = 'afterUpdate';
        }
        if (in_array('delete', $this->actions)) {
            $events[ActiveRecord::EVENT_AFTER_DELETE] = 'afterDelete';
        }
        return $events;
    }

    public function afterInsert($event)
    {
        $this->log('create', null, $this->owner->attributes);
    }

    public function afterUpdate($event)
    {
        $changed = [];
        foreach ($event->changedAttributes as $attr => $oldValue) {
            if (empty($this->attributes) || in_array($attr, $this->attributes)) {
                $changed[$attr] = ['old' => $oldValue, 'new' => $this->owner->$attr];
            }
        }
        if (!empty($changed)) {
            $this->log('update', $event->changedAttributes, $this->owner->attributes);
        }
    }

    public function afterDelete($event)
    {
        $this->log('delete', $this->owner->oldAttributes, null);
    }

    protected function log($action, $oldValue, $newValue)
    {
        if (Yii::$app->user->isGuest) {
            return; // Don't log anonymous actions
        }

        $entityType = (new \ReflectionClass($this->owner))->getShortName();
        $entityId = $this->owner->primaryKey ?? null;

        Yii::$app->db->createCommand()->insert('audit.logs', [
            'user_id' => Yii::$app->user->id,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => is_array($entityId) ? implode(',', $entityId) : $entityId,
            'old_value' => $oldValue ? json_encode($oldValue, JSON_UNESCAPED_UNICODE) : null,
            'new_value' => $newValue ? json_encode($newValue, JSON_UNESCAPED_UNICODE) : null,
            'ip_address' => Yii::$app->request->userIP ?? null,
            'user_agent' => Yii::$app->request->userAgent ?? null,
        ])->execute();
    }
}
