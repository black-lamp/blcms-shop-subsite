<?php

namespace bl\cms\shop\subsite\models;

use bl\cms\shop\common\components\user\models\UserGroup;
use bl\cms\shop\common\entities\Param;
use bl\cms\shop\common\entities\ParamTranslation;
use bl\cms\shop\common\entities\Price;
use bl\cms\shop\common\entities\Product;
use bl\cms\shop\common\entities\ProductImage;
use bl\cms\shop\common\entities\ProductImageTranslation;
use bl\cms\shop\common\entities\ProductPrice;
use bl\cms\shop\common\entities\ProductTranslation;
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
                    foreach ($data['productPrices'] as $productPriceData) {
                        $userGroupId = ShopEntityQueen::findEntityId(UserGroup::className(), $productPriceData['user_group_id']);

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

                            $price->price = $productPriceData['price']['price'];
                            if($price->isNewRecord) {
                                $price->discount = $productPriceData['price']['discount'];
                                $price->discount_type_id = $productPriceData['price']['discount_type_id'];
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