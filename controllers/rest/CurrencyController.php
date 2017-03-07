<?php
namespace bl\cms\shop\subsite\controllers\rest;

use bl\cms\shop\common\entities\Currency;
use yii\web\BadRequestHttpException;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 */
class CurrencyController extends BaseApiController
{
    public function actionUpdate() {
        if(\Yii::$app->request->isPost) {
            $value = \Yii::$app->request->post('value');
            if(!empty($value)) {
                $currency = new Currency();
                $currency->value = $value;
                $currency->save();

                return $currency->id;
            }
        }

        throw new BadRequestHttpException();
    }
}