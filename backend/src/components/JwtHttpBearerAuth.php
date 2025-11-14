<?php
namespace app\components;

use Yii;
use yii\filters\auth\AuthMethod;
use yii\web\UnauthorizedHttpException;

class JwtHttpBearerAuth extends AuthMethod
{
    public $header = 'Authorization';
    public $pattern = '/^Bearer\s+(.*?)$/';

    public function authenticate($user, $request, $response)
    {
        $authHeader = $request->getHeaders()->get($this->header);
        if ($authHeader !== null && preg_match($this->pattern, $authHeader, $matches)) {
            try {
                $data = Jwt::decode($matches[1]);
                $identity = $user->loginByAccessToken($matches[1], get_class($this));
                if ($identity === null) {
                    $identity = \app\models\User::findOne($data->sub ?? 0);
                    if ($identity) {
                        $user->switchIdentity($identity);
                        Yii::$app->params['jwt_payload'] = $data;
                        return $identity;
                    }
                }
            } catch (\Throwable $e) {
                throw new UnauthorizedHttpException('Invalid JWT: ' . $e->getMessage());
            }
        }
        return null;
    }
}
