<?php

use yii\db\Migration;

/**
 * Adds sku, creator columns and renames name to product on qr_code table.
 */
class m260325_000000_add_sku_creator_product_to_qr_code extends Migration
{
    public function safeUp()
    {
        $this->renameColumn('{{%qr_code}}', 'name', 'product');
        $this->addColumn('{{%qr_code}}', 'sku', $this->string()->notNull()->after('user_id'));
        $this->addColumn('{{%qr_code}}', 'creator', $this->string()->notNull()->after('sku'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%qr_code}}', 'creator');
        $this->dropColumn('{{%qr_code}}', 'sku');
        $this->renameColumn('{{%qr_code}}', 'product', 'name');
    }
}
