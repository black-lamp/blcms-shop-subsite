<?php
namespace bl\cms\shop\subsite\controllers\rest;

use bl\cms\shop\subsite\models\CategoryModel;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\ServerErrorHttpException;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 */
class CategoryController extends BaseApiController
{
    public function actionUpdate() {
        if(\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();
            $model = new CategoryModel();
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