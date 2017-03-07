<?php
namespace bl\cms\shop\subsite\controllers\rest;

use bl\cms\shop\subsite\models\VendorModel;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 */
class VendorController extends BaseApiController
{
    public function actionUpdate() {
        if(\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();
            $model = new VendorModel();
            if($model->save($post)) {
                return true;
            }
            else {
                throw new ServerErrorHttpException(Json::encode($model->errors));
            }
        }

        throw new BadRequestHttpException();
    }
}