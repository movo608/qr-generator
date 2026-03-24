<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%qr_code}}`.
 */
class m260322_140443_create_qr_code_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%qr_code}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'url' => $this->text()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-qr_code-user_id',
            '{{%qr_code}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-qr_code-user_id', '{{%qr_code}}');
        $this->dropTable('{{%qr_code}}');
    }
}
