<?php

namespace bl\cms\shop\subsite\models;

use bl\cms\shop\common\entities\ProductCountry;
use bl\cms\shop\common\entities\ProductCountryTranslation;
use bl\cms\shop\subsite\models\entities\ShopEntityQueen;
use yii\base\Model;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 *
 * @property ProductCountry ProductCountry
 */
class CountryModel extends Model
{
    public static $entityName = 'ProductCountry';

    public function save($data)
    {
        /* @var ProductCountry $country */
        $country = null;
        $queenId = $data['id'];
        $countryId = ShopEntityQueen::findEntityId(self::$entityName, $queenId);

        \Yii::error("countryId: " . $countryId, $this::className());

        if(!empty($countryId)) {
            $country = ProductCountry::find()
                ->where(['id' => $countryId])
                ->one();
        }
        else {
            $country = new ProductCountry();
        }

        if($country->load($data, '')) {
            if($country->save()) {
                if(!empty($data['translations'])) {
                    foreach ($data['translations'] as $translationData) {
                        $translation = ProductCountryTranslation::find()
                            ->where([
                                'language_id' => $translationData['language_id'],
                                'country_id' => $country->id
                            ])->one();

                        if(empty($translation)) {
                            $translation = new ProductCountryTranslation();
                        }

                        if($translation->load($translationData, '')) {
                            $translation->country_id = $country->id;
                            $translation->save();
                        }
                    }
                }

                ShopEntityQueen::saveQueenId(self::$entityName, $country->id, $queenId);
                return true;
            }
            else {
                $this->addError('data', 'ProductCountry saving error');
            }
        }

        return false;
    }
}