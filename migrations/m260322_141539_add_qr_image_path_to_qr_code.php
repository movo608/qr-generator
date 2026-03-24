<?php

use yii\db\Migration;

class m260322_141539_add_qr_image_path_to_qr_code extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%qr_code}}', 'qr_image_path', $this->string()->after('url'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%qr_code}}', 'qr_image_path');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260322_141539_add_qr_image_path_to_qr_code cannot be reverted.\n";

        return false;
    }
    */
}
