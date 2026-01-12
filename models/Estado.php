<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "catalogos.estado".
 *
 * @property string $codigo_estado
 * @property string $estado
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property string $updated_ip
 * @property int $id
 * @property bool $eliminado
 *
 * @property User[] $users
 */
class Estado extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'catalogos.estado';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['codigo_estado', 'estado', 'created_at', 'updated_at', 'created_by', 'updated_by', 'updated_ip', 'eliminado'], 'default', 'value' => null],
            [['codigo_estado', 'estado'], 'required'],
            [['created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['updated_ip'], 'string'],
            [['eliminado'], 'boolean'],
            [['codigo_estado'], 'string', 'max' => 2],
            [['estado'], 'string', 'max' => 50],
            [['codigo_estado'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'codigo_estado' => Yii::t('app', 'Codigo Estado'),
            'estado' => Yii::t('app', 'Estado'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'created_by' => Yii::t('app', 'Created By'),
            'updated_by' => Yii::t('app', 'Updated By'),
            'updated_ip' => Yii::t('app', 'Updated Ip'),
            'id' => Yii::t('app', 'ID'),
            'eliminado' => Yii::t('app', 'Eliminado'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['id_estado' => 'id']);
    }
}
