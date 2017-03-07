<?php

namespace bl\cms\shop\subsite\models;

use bl\cms\shop\common\entities\Category;
use bl\cms\shop\common\entities\CategoryTranslation;
use bl\cms\shop\subsite\models\entities\ShopEntityQueen;
use yii\base\Model;

/**
 * @author Gutsulyak Vadim <guts.vadim@gmail.com>
 *
 */
class CategoryModel extends Model
{
    private $entityName = 'Category';

    public function save($data)
    {
        /* @var Category $category */
        $category = null;
        $queenId = $data['id'];
        $categoryId = ShopEntityQueen::findEntityId($this->entityName, $queenId);
        $parentId = ShopEntityQueen::findEntityId($this->entityName, $data['parent_id']);

        \Yii::error("categoryId: " . $categoryId, $this::className());

        if(!empty($categoryId)) {
            $category = Category::find()
                ->where(['id' => $categoryId])
                ->one();
        }
        else {
            $category = new Category();
        }

        if($category->load($data, '')) {
            $category->parent_id = $parentId;
            if($category->save()) {
                if(!empty($data['translations'])) {
                    foreach ($data['translations'] as $translationData) {
                        $translation = CategoryTranslation::find()
                            ->where([
                                'language_id' => $translationData['language_id'],
                                'category_id' => $category->id
                            ])->one();

                        if(empty($translation)) {
                            $translation = new CategoryTranslation();
                        }

                        if($translation->load($translationData, '')) {
                            $translation->category_id = $category->id;
                            $translation->save();
                        }
                    }
                }

                ShopEntityQueen::saveQueenId($this->entityName, $category->id, $queenId);
                return true;
            }
            else {
                $this->addError('data', 'Category saving error');
            }
        }

        return false;
    }
}