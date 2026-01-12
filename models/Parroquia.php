<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "catalogos.parroquia".
 *
 * @property string $codigo_parroquia
 * @property string $parroquia
 * @property int $id
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property string $updated_ip
 * @property int $id_municipio
 *
 * @property Comunidad[] $comunidads
 * @property Comunidad[] $comunidads0
 * @property Municipio $municipio
 */
class parroquia extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'catalogos.parroquia';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigo_parroquia', 'parroquia', 'created_at', 'updated_at', 'created_by', 'updated_by', 'updated_ip', 'id_municipio'], 'default', 'value' => null],
            [['codigo_parroquia', 'parroquia', 'id_municipio'], 'required'],
            [['created_at', 'updated_at', 'created_by', 'updated_by', 'id_municipio'], 'integer'],
            [['updated_ip'], 'string'],
            [['codigo_parroquia'], 'string', 'max' => 12],
            [['parroquia'], 'string', 'max' => 50],
            [['codigo_parroquia'], 'unique'],
            [['id_municipio'], 'exist', 'skipOnError' => true, 'targetClass' => Municipio::className(), 'targetAttribute' => ['id_municipio' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'codigo_parroquia' => Yii::t('app', 'Codigo Parroquia'),
            'parroquia' => Yii::t('app', 'Parroquia'),
            'id' => Yii::t('app', 'ID'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'updated_ip' => Yii::t('app', 'Updated Ip'),
            'id_municipio' => Yii::t('app', 'Id Municipio'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComunidads()
    {
        return $this->hasMany(Comunidad::className(), ['id_parroquia' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComunidads0()
    {
        return $this->hasMany(Comunidad::className(), ['id_parroquia' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMunicipio()
    {
        return $this->hasOne(Municipio::className(), ['id' => 'id_municipio']);
    }
}
