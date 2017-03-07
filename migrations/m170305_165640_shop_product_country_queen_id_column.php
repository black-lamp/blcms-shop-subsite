<?php

use yii\db\Migration;

class m170305_165640_shop_product_country_queen_id_column extends Migration
{
    public function safeUp()
    {
        $this->addColumn('shop_product_country', 'queen_id', $this->integer());
    }

    public function safeDown()
    {
        $this->dropColumn('shop_product_country', 'queen_id');
    }
}
