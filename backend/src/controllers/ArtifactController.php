<?php
namespace app\controllers;

use app\components\Storage;
use app\models\Artifact;
use Yii;
use yii\rest\Controller;
use yii\web\BadRequestHttpException;
use yii\filters\Cors;

class ArtifactController extends Controller
{
    const MAX_FILE_SIZE = 10485760; // 10MB

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        
        $behaviors['cors'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];
        
        $behaviors['authenticator'] = [
            'class' => \app\components\JwtHttpBearerAuth::class,
        ];
        
        return $behaviors;
    }

    public function verbs()
    {
        return ['upload' => ['POST'], 'index' => ['GET']];
    }

    public function actionIndex($orgId)
    {
        // Validate orgId is integer
        if (!is_numeric($orgId) || $orgId <= 0) {
            throw new BadRequestHttpException('Invalid organization ID');
        }
        
        return Artifact::find()->where(['org_id' => (int)$orgId])->all();
    }

    public function actionUpload($orgId)
    {
        // Validate orgId
        if (!is_numeric($orgId) || $orgId <= 0) {
            throw new BadRequestHttpException('Invalid organization ID');
        }
        
        $file = Yii::$app->request->bodyParams['file'] ?? null;
        $filename = Yii::$app->request->bodyParams['filename'] ?? 'unnamed.bin';
        
        if (!$file) {
            throw new BadRequestHttpException('file required');
        }
        
        // Validate base64
        if (!preg_match('/^[a-zA-Z0-9\/+]*={0,2}$/', $file)) {
            throw new BadRequestHttpException('Invalid file format');
        }
        
        $decoded = base64_decode($file, true);
        if ($decoded === false) {
            throw new BadRequestHttpException('Invalid base64 encoding');
        }
        
        // Check file size
        if (strlen($decoded) > self::MAX_FILE_SIZE) {
            throw new BadRequestHttpException('File too large (max 10MB)');
        }
        
        // Generate unique path with extension from original filename
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $path = $orgId . '/' . uniqid('', true) . ($ext ? '.' . $ext : '.bin');
        
        try {
            Storage::put($path, $decoded);
        } catch (\Exception $e) {
            Yii::error('Storage error: ' . $e->getMessage());
            throw new \yii\web\ServerErrorHttpException('Failed to store file');
        }

        $model = new Artifact();
        $model->org_id = (int)$orgId;
        $model->path = $path;
        $model->filename = $filename;
        $model->mime = 'application/octet-stream';
        $model->uploaded_by = Yii::$app->user->id;
        
        if (!$model->save()) {
            throw new \yii\web\ServerErrorHttpException('Failed to save artifact');
        }
        
        return $model;
    }
}
