<?php

namespace bl\cms\shop\subsite\models;

use bl\cms\shop\common\components\user\models\UserGroup;
use bl\cms\shop\common\entities\Combination;
use bl\cms\shop\common\entities\CombinationAttribute;
use bl\cms\shop\common\entities\CombinationImage;
use bl\cms\shop\common\entities\CombinationPrice;
use bl\cms\shop\common\entities\CombinationTranslation;
use bl\cms\shop\common\entities\Param;
use bl\cms\shop\common\entities\ParamTranslation;
use bl\cms\shop\common\entities\Price;
use bl\cms\shop\common\entities\Product;
use bl\cms\shop\common\entities\ProductImage;
use bl\cms\shop\common\entities\ProductImageTranslation;
use bl\cms\shop\common\entities\ProductPrice;
use bl\cms\shop\common\entities\ProductTranslation;
use bl\cms\shop\common\entities\ShopAttributeValue;
use bl\cms\shop\subsite\models\entities\ShopEntityQueen;
use yii\base\Model;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 *
 */
class ProductModel extends Model
{
    public static $entityName = 'Product';

    public function save($data)
    {
        /* @var Product $product */
        $product = null;
        $queenId = $data['id'];
        $productId = ShopEntityQueen::findEntityId(self::$entityName, $queenId);
        $categoryId = ShopEntityQueen::findEntityId(CategoryModel::$entityName, $data['category_id']);
        $vendorId = ShopEntityQueen::findEntityId(VendorModel::$entityName, $data['vendor_id']);
        $availabilityId = ShopEntityQueen::findEntityId(AvailabilityModel::$entityName, $data['availability']);
        $countryId = ShopEntityQueen::findEntityId(CountryModel::$entityName, $data['country_id']);

        if(!empty($productId)) {
            $product = Product::find()
                ->where(['id' => $productId])
                ->one();
        }

        if(empty($product)) {
            $product = new Product();
        }

        if($product->load($data, '')) {
            $product->category_id = $categoryId;
            $product->vendor_id = $vendorId;
            $product->country_id = $countryId;
            $product->availability = $availabilityId;

            if($product->save()) {
                // prices
                if(!empty($data['productPrices'])) {
                    foreach ($data['productPrices'] as $combinationPriceData) {
                        $userGroupId = ShopEntityQueen::findEntityId(UserGroup::className(), $combinationPriceData['user_group_id']);

                        if(!empty($userGroupId)) {
                            /* @var ProductPrice $productPrice */
                            $productPrice = ProductPrice::find()
                                ->where([
                                    'user_group_id' => $userGroupId,
                                    'product_id' => $product->id
                                ])->one();

                            if(empty($productPrice)) {
                                $productPrice = new ProductPrice();
                                $price = new Price();
                            }
                            else {
                                $price = $productPrice->price;
                            }

                            $price->price = $combinationPriceData['price']['price'];
                            if($price->isNewRecord) {
                                $price->discount = $combinationPriceData['price']['discount'];
                                $price->discount_type_id = $combinationPriceData['price']['discount_type_id'];
                            }

                            if($price->save()) {
                                $productPrice->product_id = $product->id;
                                $productPrice->price_id = $price->id;
                                $productPrice->user_group_id = $userGroupId;
                                $productPrice->save();
                            }

                        }
                    }
                }

                // params
                if(!empty($data['params'])) {
                    foreach ($data['params'] as $paramData) {
                        /* @var Param $param */
                        $param = null;
                        $paramId = ShopEntityQueen::findEntityId(Param::className(), $paramData['id']);

                        if(!empty($paramId)) {
                            $param = Param::find()
                                ->where(['id' => $paramId])
                                ->one();
                        }

                        if(empty($param)) {
                            $param = new Param();
                        }

                        $param->position = $paramData['position'];
                        $param->product_id = $product->id;

                        if($param->save()) {
                            ShopEntityQueen::saveQueenId(Param::className(), $param->id, $paramData['id']);

                            // param translations
                            if(!empty($paramData['translations'])) {
                                foreach ($paramData['translations'] as $paramTranslationData) {
                                    $translation = ParamTranslation::find()
                                        ->where([
                                            'language_id' => $paramTranslationData['language_id'],
                                            'param_id' => $param->id
                                        ])->one();

                                    if(empty($translation)) {
                                        $translation = new ParamTranslation();
                                    }

                                    if($translation->load($paramTranslationData, '')) {
                                        $translation->param_id = $param->id;
                                        $translation->language_id = $paramTranslationData['language_id'];
                                        $translation->save();
                                    }
                                }
                            }
                        }
                    }
                }

                // images
                if(!empty($data['images'])) {
                    foreach ($data['images'] as $imageData) {
                        /* @var ProductImage $image */
                        $image = null;
                        $imageId = ShopEntityQueen::findEntityId(ProductImage::className(), $imageData['id']);

                        if(!empty($imageId)) {
                            $image = ProductImage::find()
                                ->where(['id' => $imageId])
                                ->one();
                        }

                        if(empty($image)) {
                            $image = new ProductImage();
                        }

                        $image->position = $imageData['position'];
                        $image->product_id = $product->id;
                        $image->file_name = $imageData['file_name'];

                        if($image->save()) {
                            ShopEntityQueen::saveQueenId(ProductImage::className(), $image->id, $imageData['id']);

                            // image translations
                            if(!empty($imageData['translations'])) {
                                foreach ($imageData['translations'] as $imageTranslationData) {
                                    $translation = ProductImageTranslation::find()
                                        ->where([
                                            'language_id' => $imageTranslationData['language_id'],
                                            'image_id' => $image->id
                                        ])->one();

                                    if(empty($translation)) {
                                        $translation = new ProductImageTranslation();
                                    }

                                    if($translation->load($imageTranslationData, '')) {
                                        $translation->image_id = $image->id;
                                        $translation->language_id = $imageTranslationData['language_id'];
                                        $translation->save();
                                    }
                                }
                            }
                        }
                    }
                }

                // combinations
                if(!empty($data['combinations'])) {
                    foreach ($data['combinations'] as $combinationData) {
                        /* @var Combination $combination */
                        $combination = null;
                        $combinationId = ShopEntityQueen::findEntityId(Combination::className(), $combinationData['id']);

                        if(!empty($combinationId)) {
                            $combination = Combination::find()
                                ->where(['id' => $combinationId])
                                ->one();
                        }

                        if(empty($combination)) {
                            $combination = new Combination();
                        }

                        $combinationData['product_id'] = $product->id;
                        $combinationData['availability'] = ShopEntityQueen::findEntityId(AvailabilityModel::$entityName, $combinationData['availability']);

                        if($combination->load($combinationData, '')) {
                            if($combination->save()) {
                                ShopEntityQueen::saveQueenId(Combination::className(), $combination->id, $combinationData['id']);

                                // combination translations
                                if(!empty($combinationData['shopCombinationTranslations'])) {
                                    foreach ($combinationData['shopCombinationTranslations'] as $combinationTranslationData) {
                                        $translation = CombinationTranslation::find()
                                            ->where([
                                                'language_id' => $combinationTranslationData['language_id'],
                                                'combination_id' => $combination->id
                                            ])->one();

                                        if(empty($translation)) {
                                            $translation = new CombinationTranslation();
                                        }

                                        $combinationTranslationData['combination_id'] = $combination->id;
                                        if($translation->load($combinationTranslationData, '')) {
                                            $translation->save();
                                        }
                                    }
                                }

                                // combination prices
                                if(!empty($combinationData['combinationPrices'])) {
                                    foreach ($combinationData['combinationPrices'] as $combinationPriceData) {
                                        $userGroupId = ShopEntityQueen::findEntityId(UserGroup::className(), $combinationPriceData['user_group_id']);

                                        if(!empty($userGroupId)) {
                                            /* @var CombinationPrice $combinationPrice */
                                            $combinationPrice = CombinationPrice::find()
                                                ->where([
                                                    'user_group_id' => $userGroupId,
                                                    'combination_id' => $combination->id
                                                ])->one();

                                            if(empty($combinationPrice)) {
                                                $combinationPrice = new CombinationPrice();
                                                $price = new Price();
                                            }
                                            else {
                                                $price = $combinationPrice->price;
                                            }

                                            $price->price = $combinationPriceData['price']['price'];
                                            if($price->isNewRecord) {
                                                $price->discount = $combinationPriceData['price']['discount'];
                                                $price->discount_type_id = $combinationPriceData['price']['discount_type_id'];
                                            }

                                            if($price->save()) {
                                                $combinationPrice->combination_id = $combination->id;
                                                $combinationPrice->price_id = $price->id;
                                                $combinationPrice->user_group_id = $userGroupId;
                                                $combinationPrice->save();
                                            }

                                        }
                                    }
                                }

                                // combination attributes
                                if(!empty($combinationData['combinationAttributes'])) {
                                    foreach ($combinationData['combinationAttributes'] as $combinationAttributeData) {
                                        $attributeId = ShopEntityQueen::findEntityId(AttributeModel::$entityName, $combinationAttributeData['attribute_id']);
                                        $attributeValueId = ShopEntityQueen::findEntityId(ShopAttributeValue::className(), $combinationAttributeData['attribute_value_id']);

                                        /* @var CombinationAttribute $combinationAttribute */
                                        $combinationAttribute = CombinationAttribute::find()
                                            ->where([
                                                'attribute_id' => $attributeId,
                                                'attribute_value_id' => $attributeValueId,
                                                'combination_id' => $combination->id
                                            ])->one();

                                        if(empty($combinationAttribute)) {
                                            $combinationAttribute = new CombinationAttribute();
                                            $combinationAttributeData['attribute_id'] = $attributeId;
                                            $combinationAttributeData['attribute_value_id'] = $attributeValueId;
                                            $combinationAttributeData['combination_id'] = $combination->id;

                                            if($combinationAttribute->load($combinationAttributeData, '')) {
                                                $combinationAttribute->save();
                                            }
                                        }
                                    }
                                }

                                // combination images
                                if(!empty($combinationData['images'])) {
                                    foreach ($combinationData['images'] as $imageAttributeData) {
                                        $productImageId = ShopEntityQueen::findEntityId(ProductImage::className(), $imageAttributeData['product_image_id']);

                                        /* @var CombinationImage $combinationImage */
                                        $combinationImage = CombinationImage::find()
                                            ->where([
                                                'product_image_id' => $productImageId,
                                                'combination_id' => $combination->id
                                            ])->one();

                                        if(empty($combinationImage)) {
                                            $combinationImage = new CombinationImage();
                                            $imageAttributeData['product_image_id'] = $productImageId;
                                            $imageAttributeData['combination_id'] = $combination->id;

                                            if($combinationImage->load($imageAttributeData, '')) {
                                                $combinationImage->save();
                                            }
                                        }
                                    }

                                    // TODO: delete images relations
                                }
                            }
                        }

                    }
                }

                // translations
                if(!empty($data['translations'])) {
                    foreach ($data['translations'] as $translationData) {
                        $translation = ProductTranslation::find()
                            ->where([
                                'language_id' => $translationData['language_id'],
                                'product_id' => $product->id
                            ])->one();

                        if(empty($translation)) {
                            $translation = new ProductTranslation();
                        }

                        if(!$translation->isNewRecord) {
                            $translationData['title'] = $translation->title;
                            $translationData['seoTitle'] = $translation->seoTitle;
                        }

                        if($translation->load($translationData, '')) {
                            $translation->product_id = $product->id;
                            $translation->save();
                        }
                    }
                }

                ShopEntityQueen::saveQueenId(self::$entityName, $product->id, $queenId);
                return true;
            }
            else {
                $this->addError('data', 'Product saving error');
            }
        }

        return false;
    }
}