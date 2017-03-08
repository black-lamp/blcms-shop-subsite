<?php

namespace bl\cms\shop\subsite\models;

use bl\cms\shop\common\entities\ProductAvailability;
use bl\cms\shop\common\entities\ProductAvailabilityTranslation;
use bl\cms\shop\subsite\models\entities\ShopEntityQueen;
use yii\base\Model;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 *
 * @property ProductAvailability $queenProductAvailability
 */
class AvailabilityModel extends Model
{
    public static $entityName = 'ProductAvailability';

    public function save($data)
    {
        /* @var ProductAvailability $availability */
        $availability = null;
        $queenId = $data['id'];
        $availabilityId = ShopEntityQueen::findEntityId(self::$entityName, $queenId);

        \Yii::error("availabilityId: " . $availabilityId, $this::className());

        if(!empty($availabilityId)) {
            $availability = ProductAvailability::find()
                ->where(['id' => $availabilityId])
                ->one();
        }
        else {
            $availability = new ProductAvailability();
        }

        if($availability->load($data, '')) {
            if($availability->save()) {
                if(!empty($data['translations'])) {
                    foreach ($data['translations'] as $translationData) {
                        $translation = ProductAvailabilityTranslation::find()
                            ->where([
                                'language_id' => $translationData['language_id'],
                                'availability_id' => $availability->id
                            ])->one();

                        if(empty($translation)) {
                            $translation = new ProductAvailabilityTranslation();
                        }

                        if($translation->load($translationData, '')) {
                            $translation->availability_id = $availability->id;
                            $translation->save();
                        }
                    }
                }

                ShopEntityQueen::saveQueenId(self::$entityName, $availability->id, $queenId);
                return true;
            }
            else {
                $this->addError('data', 'ProductAvailability saving error');
            }
        }

        return false;
    }
}