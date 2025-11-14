<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\components\JwtHttpBearerAuth;

class DebugController extends Controller
{
    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => JwtHttpBearerAuth::class,
            ],
        ];
    }

    /**
     * Test RLS - shows current user and their organizations
     * GET /debug/rls-test
     */
    public function actionRlsTest()
    {
        $userId = Yii::$app->user->id;
        
        if (!$userId) {
            return [
                'error' => 'Not authenticated',
            ];
        }
        
        // Get user info
        $user = Yii::$app->db->createCommand('SELECT id, email, role FROM auth.users WHERE id = :id', [':id' => $userId])->queryOne();
        
        // Get user's organizations from users_orgs table
        $userOrgs = Yii::$app->db->createCommand('
            SELECT o.id, o.name, o.inn, o.category 
            FROM compliance.organizations o
            JOIN auth.users_orgs uo ON o.id = uo.org_id
            WHERE uo.user_id = :userId
        ', [':userId' => $userId])->queryAll();
        
        // Check what RLS shows (should be same as userOrgs if RLS works)
        // First, apply RLS
        Yii::$app->db->createCommand("SELECT set_config('app.user_id', :uid, true)", [':uid' => $userId])->execute();
        
        $rlsOrgs = Yii::$app->db->createCommand('
            SELECT id, name, inn, category 
            FROM compliance.organizations
        ')->queryAll();
        
        return [
            'user' => $user,
            'organizations_from_users_orgs' => $userOrgs,
            'organizations_with_rls' => $rlsOrgs,
            'rls_working' => count($userOrgs) === count($rlsOrgs),
            'message' => count($userOrgs) === count($rlsOrgs) 
                ? '✅ RLS works correctly' 
                : '❌ RLS not working - showing ' . count($rlsOrgs) . ' orgs instead of ' . count($userOrgs),
        ];
    }
}
