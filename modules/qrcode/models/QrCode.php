<?php

namespace app\modules\qrcode\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use app\models\User;
use chillerlan\QRCode\QRCode as QRCodeGenerator;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QRGdImagePNG;

/**
 * @property int $id
 * @property int $user_id
 * @property string $sku
 * @property string $creator
 * @property string $product
 * @property string $url
 * @property string $qr_image_path
 * @property int $created_at
 * @property int $updated_at
 * @property User $user
 */
class QrCode extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%qr_code}}';
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    public function rules()
    {
        return [
            [['sku', 'creator', 'product', 'url'], 'required'],
            [['sku', 'creator', 'product'], 'string', 'max' => 255],
            ['url', 'url', 'defaultScheme' => 'https'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sku' => 'SKU',
            'creator' => 'Artist / Band',
            'product' => 'Album',
            'url' => 'URL',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);

        if ($insert || isset($changedAttributes['url'])) {
            $this->generateQrImage();
        }
    }

    public function afterDelete()
    {
        parent::afterDelete();

        if ($this->qr_image_path) {
            $filePath = Yii::getAlias('@webroot') . $this->qr_image_path;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    protected function generateQrImage()
    {
        // Delete old image if regenerating
        if ($this->qr_image_path) {
            $oldPath = Yii::getAlias('@webroot') . $this->qr_image_path;
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $options = new QROptions();
        $options->outputInterface = QRGdImagePNG::class;
        $options->scale = 10;
        $options->outputBase64 = false;

        $qrcode = new QRCodeGenerator($options);
        $imageData = $qrcode->render($this->url);

        $filename = $this->id . '_' . time() . '.png';
        $relativePath = '/uploads/qrcodes/' . $filename;
        $absolutePath = Yii::getAlias('@webroot') . $relativePath;

        file_put_contents($absolutePath, $imageData);

        $this->updateAttributes(['qr_image_path' => $relativePath]);
    }

    public function getQrImageUrl()
    {
        if ($this->qr_image_path) {
            return Yii::getAlias('@web') . $this->qr_image_path;
        }
        return null;
    }
}
