<?php

namespace bl\cms\shop\subsite\models;

use bl\cms\shop\common\entities\ShopAttribute;
use bl\cms\shop\common\entities\ShopAttributeTranslation;
use bl\cms\shop\common\entities\ShopAttributeValue;
use bl\cms\shop\common\entities\ShopAttributeValueColorTexture;
use bl\cms\shop\common\entities\ShopAttributeValueTranslation;
use bl\cms\shop\subsite\models\entities\ShopEntityQueen;
use Yii;
use yii\base\Model;
use yii\web\ServerErrorHttpException;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 */
class AttributeModel extends Model
{
    public static $entityName = 'ShopAttribute';

    public function save($data)
    {
        /* @var ShopAttribute $attribute */
        $attribute = null;
        $queenId = $data['id'];
        $attributeId = ShopEntityQueen::findEntityId(self::$entityName, $queenId);

        if(!empty($attributeId)) {
            $attribute = ShopAttribute::find()
                ->where(['id' => $attributeId])
                ->one();
        }
        else {
            $attribute = new ShopAttribute();
        }

        if($attribute->load($data, '')) {
            if($attribute->save()) {
                // translations
                if(!empty($data['translations'])) {
                    foreach ($data['translations'] as $translationData) {
                        $translation = ShopAttributeTranslation::find()
                            ->where([
                                'language_id' => $translationData['language_id'],
                                'attr_id' => $attribute->id
                            ])->one();

                        if(empty($translation)) {
                            $translation = new ShopAttributeTranslation();
                        }

                        if($translation->load($translationData, '')) {
                            $translation->attr_id = $attribute->id;
                            $translation->save();
                        }
                    }
                }

                // values
                if(!empty($data['attributeValues'])) {
                    foreach ($data['attributeValues'] as $attributeValuesData) {
                        /* @var ShopAttributeValue $attributeValue */
                        $attributeValue = null;
                        $valueId = ShopEntityQueen::findEntityId(ShopAttributeValue::className(), $attributeValuesData['id']);

                        if(!empty($valueId)) {
                            $attributeValue = ShopAttributeValue::find()
                                ->where(['id' => $valueId])
                                ->one();
                        }

                        if(empty($attributeValue)) {
                            $attributeValue = new ShopAttributeValue();
                        }

                        $attributeValue->attribute_id = $attribute->id;
                        if($attributeValue->save()) {
                            ShopEntityQueen::saveQueenId(ShopAttributeValue::className(), $attributeValue->id, $attributeValuesData['id']);

                            // value translations
                            if(!empty($attributeValuesData['shopAttributeValueTranslations'])) {
                                foreach ($attributeValuesData['shopAttributeValueTranslations'] as $valueTranslationData) {
                                    $translation = ShopAttributeValueTranslation::find()
                                        ->where([
                                            'language_id' => $valueTranslationData['language_id'],
                                            'value_id' => $attributeValue->id
                                        ])->one();

                                    if(empty($translation)) {
                                        $translation = new ShopAttributeValueTranslation();
                                    }

                                    if($attribute->type->title == "Color" || $attribute->type->title == "Texture") {
                                        if(!empty($valueTranslationData['shopAttributeValueColorTexture'])) {
                                            $valueColorTextureId = ShopEntityQueen::findEntityId(ShopAttributeValueColorTexture::className(), $valueTranslationData['shopAttributeValueColorTexture']['id']);

                                            $valueColorTexture = ShopAttributeValueColorTexture::find()
                                                ->where(['id' => $valueColorTextureId])
                                                ->one();

                                            if(empty($valueColorTexture)) {
                                                $valueColorTexture = new ShopAttributeValueColorTexture();
                                            }

                                            if($valueColorTexture->load($valueTranslationData['shopAttributeValueColorTexture'], '')) {
                                                if($valueColorTexture->save()) {
                                                    ShopEntityQueen::saveQueenId(ShopAttributeValueColorTexture::className(), $valueColorTexture->id, $valueTranslationData['shopAttributeValueColorTexture']['id']);
                                                    $valueTranslationData['value'] = strval($valueColorTexture->id);
                                                }
                                            }

                                        }
                                    }

                                    $translation->value_id = $attributeValue->id;
                                    $translation->language_id = $valueTranslationData['language_id'];
                                    $translation->value = $valueTranslationData['value'];

                                    if(!$translation->save()) {
                                        throw new ServerErrorHttpException();
                                    }
                                }
                            }
                        }
                    }
                }

                ShopEntityQueen::saveQueenId(self::$entityName, $attribute->id, $queenId);
                return true;
            }
            else {
                $this->addError('data', 'ShopAttribute saving error');
            }
        }

        return false;
    }
}