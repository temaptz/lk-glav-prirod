<?php
namespace app\controllers;

use app\components\Jwt;
use app\models\User;
use Yii;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;

class AuthController extends Controller
{
    public $enableCsrfValidation = false;

    public function verbs()
    {
        return [
            'login' => ['POST'],
            'register' => ['POST'],
        ];
    }

    public function actionRegister()
    {
        $data = Yii::$app->request->bodyParams;
        if (empty($data['email']) || empty($data['password'])) {
            throw new BadRequestHttpException('Email and password required');
        }
        if (User::find()->where(['email' => $data['email']])->exists()) {
            throw new BadRequestHttpException('Email already registered');
        }

        $tx = Yii::$app->db->beginTransaction();
        try {
            // Create user
            $user = new User();
            $user->email = $data['email'];
            $user->password_hash = password_hash($data['password'], PASSWORD_ARGON2ID);
            $user->role = $data['role'] ?? 'client';
            if (!$user->save()) {
                throw new BadRequestHttpException('User creation failed: ' . json_encode($user->errors));
            }

            // For clients, create organization and auto-generate requirements
            if ($user->role === 'client') {
                if (empty($data['company_name']) || empty($data['category'])) {
                    throw new BadRequestHttpException('Company name and category required for client registration');
                }

                $org = new \app\models\Organization();
                $org->name = $data['company_name'];
                $org->inn = $data['inn'] ?? null;
                $org->ogrn = $data['ogrn'] ?? null;
                $org->category = (int)$data['category']; // 1-4 (НВОС)
                $org->water_source = $data['water_source'] ?? null; // 'скважина', 'река', null
                $org->has_byproduct = !empty($data['has_byproduct']);
                
                if (!$org->save()) {
                    throw new BadRequestHttpException('Organization creation failed: ' . json_encode($org->errors));
                }

                // Link user to organization
                Yii::$app->db->createCommand()->insert('auth.users_orgs', [
                    'user_id' => $user->id,
                    'org_id' => $org->id,
                ])->execute();

                // Requirements auto-generated via Organization::afterSave()
            }

            $tx->commit();

            return [
                'access_token' => Jwt::encode(['sub' => $user->id, 'email' => $user->email, 'role' => $user->role, 'iat' => time(), 'exp' => time() + 3600]),
                'user_id' => $user->id,
                'role' => $user->role,
            ];
        } catch (\Exception $e) {
            $tx->rollBack();
            throw new BadRequestHttpException('Registration failed: ' . $e->getMessage());
        }
    }

    public function actionLogin()
    {
        $data = Yii::$app->request->bodyParams;
        if (empty($data['email']) || empty($data['password'])) {
            throw new BadRequestHttpException('Email and password required');
        }
        $user = User::find()->where(['email' => $data['email']])->one();
        if (!$user || !password_verify($data['password'], $user->password_hash)) {
            throw new UnauthorizedHttpException('Invalid credentials');
        }
        return [
            'access_token' => Jwt::encode(['sub' => $user->id, 'email' => $user->email, 'role' => $user->role, 'iat' => time(), 'exp' => time() + 3600]),
        ];
    }
}
