<?php

use bl\cms\shop\common\components\user\models\UserGroup;
use bl\cms\shop\subsite\models\entities\ShopEntityQueen;
use yii\db\Migration;

class m170305_165642_user_group_entity_queen_row extends Migration
{
    public function safeUp()
    {
        ShopEntityQueen::saveQueenId(UserGroup::className(), 1, 1);
    }

    public function safeDown()
    {
        ShopEntityQueen::deleteAll([
            'entity_name' => UserGroup::className(),
            'entity_id' => 1,
            'queen_id' => 1
        ]);
    }
}
