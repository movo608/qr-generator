<?php

namespace app\modules\qrcode\models;

use Yii;
use yii\data\ActiveDataProvider;

class QrCodeSearch extends QrCode
{
    public function rules()
    {
        return [
            [['sku', 'creator', 'product'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = QrCode::find()->where(['user_id' => Yii::$app->user->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'sku', $this->sku])
              ->andFilterWhere(['like', 'creator', $this->creator])
              ->andFilterWhere(['like', 'product', $this->product]);

        return $dataProvider;
    }
}
