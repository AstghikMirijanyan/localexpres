<?php

use yii\db\Migration;

class m130524_201444_store_product extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%store_product}}', [
            'id' => $this->primaryKey(),
            'store_id' => $this->bigInteger(20)->unsigned()->notNull(),
            'title' => $this->string(255)->null(),
            'upc' => $this->string(255)->notNull(),
            'price' => $this->float(10)->null()->defaultValue(0),

        ], $tableOptions);

        $this->addForeignKey('fk_store_product_store_id', '{{%store_product}}', 'store_id', '{{%store}}','id','CASCADE','CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('fk_store_product_store_id','{{%store_product}}');
        $this->dropTable('{{%store_product}}');
    }
}
