<?php
namespace bl\cms\shop\subsite\controllers\rest;

use bl\cms\shop\common\entities\Currency;
use bl\cms\shop\common\entities\ProductCountry;
use bl\cms\shop\common\entities\ProductCountryTranslation;
use yii\web\BadRequestHttpException;
use yii\web\JsonParser;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 */
class CountryController extends BaseApiController
{
    public function actionUpdate() {
        if(\Yii::$app->request->isPost) {
            $post = \Yii::$app->request->post();

            if(!empty($post)) {
                $queenId = $post['id'];
                if(!empty($queenId)) {
                    /* @var $country ProductCountry */
                    $country = ProductCountry::findOne(['queen_id' => $queenId]);
                    if(empty($country)) {
                        $country = new ProductCountry();
                    }

                    $country->queen_id = $queenId;
                    $country->image = $post['image'];
                    $country->save();

                    foreach ($post['translations'] as $postTranslation) {
                        if(!$country->isNewRecord) {
                            $translation = ProductCountryTranslation::findOne([
                                'country_id' => $country->id,
                                'language_id' => $postTranslation['language_id']
                            ]);
                        }

                        if(empty($translation)) {
                            $translation = new ProductCountryTranslation();
                        }

                        // TODO: change to queen_language_id
                        $translation->country_id = $country->id;
                        $translation->language_id = $postTranslation['language_id'];
                        $translation->title = $postTranslation['title'];
//                        $translation->save();
                    }
                }
            }

        }

//        throw new BadRequestHttpException();
    }
}