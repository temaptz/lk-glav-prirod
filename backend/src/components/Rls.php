<?php
namespace app\components;

use Yii;

class Rls
{
    public static function apply(): void
    {
        $userId = Yii::$app->user->id;
        if ($userId) {
            Yii::$app->db->createCommand("SELECT set_config('app.user_id', :uid, true)", [':uid' => $userId])->execute();
            $ids = Yii::$app->db->createCommand('SELECT org_id FROM auth.users_orgs WHERE user_id=:u')
                ->bindValue(':u', $userId)
                ->queryColumn();
            Yii::$app->db->createCommand("SELECT set_config('app.org_ids', :ids, true)", [':ids' => implode(',', $ids)])->execute();
        }
    }
}
