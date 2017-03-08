<?php

namespace bl\cms\shop\subsite\models;

use bl\cms\shop\common\entities\Vendor;
use bl\cms\shop\common\entities\VendorTranslation;
use bl\cms\shop\subsite\models\entities\ShopEntityQueen;
use yii\base\Model;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 *
 * @property Vendor $queenVendor
 */
class VendorModel extends Model
{
    public static $entityName = 'Vendor';

    public function save($data)
    {
        /* @var Vendor $vendor */
        $vendor = null;
        $queenId = $data['id'];
        $vendorId = ShopEntityQueen::findEntityId(self::$entityName, $queenId);

        \Yii::error("vendorId: " . $vendorId, $this::className());

        if(!empty($vendorId)) {
            $vendor = Vendor::find()
                ->where(['id' => $vendorId])
                ->one();
        }
        else {
            $vendor = new Vendor();
        }

        if($vendor->load($data, '')) {
            if($vendor->save()) {
                if(!empty($data['translations'])) {
                    foreach ($data['translations'] as $translationData) {
                        $translation = VendorTranslation::find()
                            ->where([
                                'language_id' => $translationData['language_id'],
                                'vendor_id' => $vendor->id
                            ])->one();

                        if(empty($translation)) {
                            $translation = new VendorTranslation();
                        }

                        if($translation->load($translationData, '')) {
                            $translation->vendor_id = $vendor->id;
                            $translation->save();
                        }
                    }
                }

                ShopEntityQueen::saveQueenId(self::$entityName, $vendor->id, $queenId);
                return true;
            }
            else {
                $this->addError('data', 'Vendor saving error');
            }
        }

        return false;
    }
}