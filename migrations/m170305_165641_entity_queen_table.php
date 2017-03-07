<?php

use yii\db\Migration;

class m170305_165641_entity_queen_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('shop_entity_queen', [
            'id' => $this->primaryKey(),
            'entity_name' => $this->string(),
            'entity_id' => $this->integer(),
            'queen_id' => $this->integer()
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('shop_entity_queen');
    }
}
