<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "catalogos.municipio".
 *
 * @property string $codigo_municipio
 * @property string $municipio
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property string $updated_ip
 * @property int $id
 * @property int $id_estado
 *
 * @property Parroquia[] $parroquias
 */
class Municipio extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'catalogos.municipio';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigo_municipio', 'municipio', 'created_at', 'updated_at', 'created_by', 'updated_by', 'updated_ip', 'id_estado'], 'default', 'value' => null],
            [['codigo_municipio', 'municipio', 'id_estado'], 'required'],
            [['created_at', 'updated_at', 'created_by', 'updated_by', 'id_estado'], 'integer'],
            [['updated_ip'], 'string'],
            [['codigo_municipio'], 'string', 'max' => 4],
            [['municipio'], 'string', 'max' => 50],
            [['codigo_municipio'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'codigo_municipio' => Yii::t('app', 'Codigo Municipio'),
            'municipio' => Yii::t('app', 'Municipio'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'updated_ip' => Yii::t('app', 'Updated Ip'),
            'id' => Yii::t('app', 'ID'),
            'id_estado' => Yii::t('app', 'Id Estado'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParroquias()
    {
        return $this->hasMany(Parroquia::className(), ['id_municipio' => 'id']);
    }
}
