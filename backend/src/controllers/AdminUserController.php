<?php
namespace app\controllers;

use app\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Admin User Management Controller
 * Only accessible by admin role
 */
class AdminUserController extends BaseRestController
{
    public $modelClass = User::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        // Only admins can access
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['@'],
                    'matchCallback' => function () {
                        return !Yii::$app->user->isGuest && Yii::$app->user->identity->role === 'admin';
                    }
                ],
            ],
        ];
        
        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        unset($actions['create'], $actions['update'], $actions['delete']); // Custom implementations
        return $actions;
    }

    /**
     * Create new user
     * POST /admin-user
     */
    public function actionCreate()
    {
        $data = Yii::$app->request->bodyParams;
        
        if (empty($data['email']) || empty($data['password']) || empty($data['role'])) {
            throw new BadRequestHttpException('Email, password, and role are required');
        }

        if (User::find()->where(['email' => $data['email']])->exists()) {
            throw new BadRequestHttpException('Email already exists');
        }

        $user = new User();
        $user->email = $data['email'];
        $user->password_hash = password_hash($data['password'], PASSWORD_ARGON2ID);
        $user->role = $data['role'];
        $user->is_active = $data['is_active'] ?? true;

        if (!$user->save()) {
            throw new BadRequestHttpException('Failed to create user: ' . json_encode($user->errors));
        }

        return $user;
    }

    /**
     * Update user
     * PATCH /admin-user/{id}
     */
    public function actionUpdate($id)
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $data = Yii::$app->request->bodyParams;

        if (isset($data['email'])) {
            $user->email = $data['email'];
        }
        if (isset($data['password']) && !empty($data['password'])) {
            $user->password_hash = password_hash($data['password'], PASSWORD_ARGON2ID);
        }
        if (isset($data['role'])) {
            $user->role = $data['role'];
        }
        if (isset($data['is_active'])) {
            $user->is_active = (bool)$data['is_active'];
        }

        if (!$user->save()) {
            throw new BadRequestHttpException('Failed to update user: ' . json_encode($user->errors));
        }

        return $user;
    }

    /**
     * Delete user
     * DELETE /admin-user/{id}
     */
    public function actionDelete($id)
    {
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        // Prevent self-deletion
        if ($user->id == Yii::$app->user->id) {
            throw new BadRequestHttpException('Cannot delete yourself');
        }

        $user->delete();

        return ['success' => true, 'message' => 'User deleted'];
    }
}
