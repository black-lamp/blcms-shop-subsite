<?php

namespace bl\cms\shop\subsite\models\entities;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "shop_entity_queen".
 *
 * @property integer $id
 * @property string $entity_name
 * @property integer $entity_id
 * @property integer $queen_id
 */
class ShopEntityQueen extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_entity_queen';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['entity_id', 'queen_id'], 'integer'],
            [['entity_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'entity_name' => 'Entity Name',
            'entity_id' => 'Entity ID',
            'queen_id' => 'Queen ID',
        ];
    }

    public static function findEntityId($entityName, $queenId) {
        /* @var ShopEntityQueen $entityQueen */
        $entityQueen = self::find()
            ->where(['entity_name' => $entityName,])
            ->andWhere(['queen_id' => $queenId])
            ->one();

        if(!empty($entityQueen)) {
            return $entityQueen->entity_id;
        }

        return null;
    }

    public static function findQueenId($entityName, $entityId) {
        /* @var ShopEntityQueen $entityQueen */
        $entityQueen = self::find()
            ->where(['entity_name' => $entityName,])
            ->andWhere(['entity_id' => $entityId])
            ->one();

        if(!empty($entityQueen)) {
            return $entityQueen->queen_id;
        }

        return null;
    }

    public static function saveQueenId($entityName, $entityId, $queenId) {
        $entityQueen = ShopEntityQueen::findOne([
            'entity_name' => $entityName,
            'queen_id' => $queenId,
            'entity_id' => $entityId
        ]);

        if(empty($entityQueen)) {
            $entityQueen = new ShopEntityQueen();
        }

        $entityQueen->entity_name = $entityName;
        $entityQueen->entity_id = $entityId;
        $entityQueen->queen_id = $queenId;
        $entityQueen->save();
    }
}
