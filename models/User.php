<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;
use yii\db\Exception as DbException;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $cedula
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'seguridad.user';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'auth_key', 'password_hash', 'email'], 'required'],
            [['status', 'created_at', 'updated_at'], 'default', 'value' => null],
            [['status', 'created_at', 'updated_at'], 'integer'],
            [['username'], 'string', 'max' => 32],
            [['auth_key'], 'string', 'max' => 32],
            [['password_hash', 'password_reset_token', 'email'], 'string', 'max' => 255],
            [['cedula'], 'string', 'max' => 20],
            [['username'], 'unique'],
            [['email'], 'unique'],
            [['cedula'], 'unique'],
            [['password_reset_token'], 'unique'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Usuario',
            'cedula' => 'Cédula',
            'email' => 'Correo Electrónico',
            'status' => 'Estado',
            'created_at' => 'Creado En',
            'updated_at' => 'Actualizado En',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['auth_key' => $token, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by cédula
     *
     * @param string $cedula
     * @return static|null
     */
    public static function findByCedula($cedula)
    {
        return static::findOne(['cedula' => $cedula, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int) substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'] ?? 3600;
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Asignar rol al usuario
     */
    public function asignarRol($rol)
    {
        try {
            $auth = Yii::$app->authManager;
            $role = $auth->getRole($rol);
            
            if ($role) {
                $auth->assign($role, $this->id);
                return true;
            }
            Yii::warning("Rol '{$rol}' no encontrado", 'user');
            return false;
        } catch (\Exception $e) {
            Yii::error("Error asignando rol {$rol} al usuario {$this->id}: " . $e->getMessage(), 'user');
            return false;
        }
    }

    /**
     * Remover todos los roles del usuario
     */
    public function revocarRoles()
    {
        try {
            $auth = Yii::$app->authManager;
            $auth->revokeAll($this->id);
            return true;
        } catch (\Exception $e) {
            Yii::error("Error revocando roles del usuario {$this->id}: " . $e->getMessage(), 'user');
            return false;
        }
    }

    /**
     * Obtener los roles del usuario
     */
    public function getRoles()
    {
        try {
            $auth = Yii::$app->authManager;
            return $auth->getRolesByUser($this->id);
        } catch (\Exception $e) {
            Yii::error("Error obteniendo roles del usuario {$this->id}: " . $e->getMessage(), 'user');
            return [];
        }
    }

    /**
     * Crea un usuario automáticamente con la cédula como username y un email opcional.
     * Si no se proporciona email, se genera uno por defecto.
     * 
     * @param string $cedula
     * @param string|null $email
     * @param string|null $nombreCompleto
     * @param string|null $rol
     * @return User|null
     */
    public static function crearUsuarioAutomatico($cedula, $email = null, $nombreCompleto = null, $rol = null)
    {
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Verificar si ya existe un usuario con esa cédula
            $existingUser = static::findByCedula($cedula);
            if ($existingUser) {
                Yii::warning("Ya existe un usuario con la cédula: $cedula", 'user');
                $transaction->commit();
                return $existingUser;
            }

            // Verificar si ya existe un usuario con ese username (cedula)
            $existingUserByUsername = static::findByUsername($cedula);
            if ($existingUserByUsername) {
                Yii::warning("Ya existe un usuario con el username (cédula): $cedula", 'user');
                $transaction->commit();
                return $existingUserByUsername;
            }

            $user = new User();
            $user->username = $cedula;
            $user->cedula = $cedula;
            $user->email = $email ?? $cedula . '@sistema-ged.com';
            $user->status = self::STATUS_ACTIVE;

            // Generar auth_key y password
            $user->generateAuthKey();
            $password = Yii::$app->security->generateRandomString(8);
            $user->setPassword($password);

            if ($user->save()) {
                if ($rol) {
                    $user->asignarRol($rol);
                }
                
                $transaction->commit();
                Yii::info("Usuario creado automáticamente: {$user->username} (ID: {$user->id})", 'user');
                return $user;
            } else {
                $transaction->rollBack();
                Yii::error('Error al guardar el usuario automático: ' . json_encode($user->getErrors()), 'user');
                return null;
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Excepción al crear usuario automático: ' . $e->getMessage(), 'user');
            return null;
        }
    }

    /**
     * Crea un usuario con username, email y rol específicos
     * 
     * @param string $username
     * @param string $email
     * @param string $password
     * @param string|null $rol
     * @return User|null
     */
    public static function crearUsuario($username, $email, $password, $rol = null)
    {
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            $user = new User();
            $user->username = $username;
            $user->email = $email;
            $user->status = self::STATUS_ACTIVE;

            // Generar auth_key y password
            $user->generateAuthKey();
            $user->setPassword($password);

            if ($user->save()) {
                if ($rol) {
                    $user->asignarRol($rol);
                }
                
                $transaction->commit();
                return $user;
            } else {
                $transaction->rollBack();
                Yii::error('Error al guardar el usuario: ' . json_encode($user->getErrors()), 'user');
                return null;
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Excepción al crear usuario: ' . $e->getMessage(), 'user');
            return null;
        }
    }

    /**
     * Verificar si el usuario tiene un rol específico
     * 
     * @param string $rol
     * @return bool
     */
    public function tieneRol($rol)
    {
        try {
            $auth = Yii::$app->authManager;
            $roles = $auth->getRolesByUser($this->id);
            return isset($roles[$rol]);
        } catch (\Exception $e) {
            Yii::error("Error verificando rol {$rol} para usuario {$this->id}: " . $e->getMessage(), 'user');
            return false;
        }
    }

    /**
     * Obtener el nombre completo del usuario (si está disponible)
     * 
     * @return string
     */
    public function getNombreCompleto()
    {
        return $this->username;
    }

    /**
     * Activar usuario
     */
    public function activar()
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save(false, ['status']);
    }

    /**
     * Desactivar usuario
     */
    public function desactivar()
    {
        $this->status = self::STATUS_DELETED;
        return $this->save(false, ['status']);
    }

    /**
     * Verificar si el usuario está activo
     * 
     * @return bool
     */
    public function estaActivo()
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}