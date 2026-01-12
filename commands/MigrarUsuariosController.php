<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use app\models\AtletasRegistro;
use app\models\RegistroRepresentantes;
use app\models\User;

class MigrarUsuariosController extends Controller
{
    /**
     * Migración usando SQL directo para evitar problemas con behaviors
     */
    public function actionIndex()
    {
        $this->stdout("🚀 INICIANDO MIGRACIÓN CON SQL DIRECTO...\n", Console::FG_YELLOW);
        
        $totalRepresentantes = $this->migrarRepresentantes();
        $totalAtletas = $this->migrarAtletas();
        
        $this->stdout("✅ MIGRACIÓN COMPLETADA:\n", Console::FG_GREEN);
        $this->stdout("   - Representantes migrados: {$totalRepresentantes}\n", Console::FG_CYAN);
        $this->stdout("   - Atletas migrados: {$totalAtletas}\n", Console::FG_CYAN);
        $this->stdout("   - Total: " . ($totalAtletas + $totalRepresentantes) . " usuarios\n", Console::FG_CYAN);
    }
    
    private function migrarRepresentantes()
    {
        $this->stdout("\n👨‍👩‍👧‍👦 MIGRANDO REPRESENTANTES...\n", Console::FG_BLUE);
        
        $representantes = RegistroRepresentantes::find()->all();
        $this->stdout("   Representantes encontrados: " . count($representantes) . "\n", Console::FG_CYAN);
        
        $contador = 0;
        $errors = 0;
        
        foreach ($representantes as $representante) {
            if (empty($representante->identificacion)) {
                continue;
            }
            
            $this->stdout("   Procesando: {$representante->identificacion} - {$representante->p_nombre}... ", Console::FG_CYAN);
            
            // Verificar si ya tiene usuario
            if ($representante->user_id) {
                $userExistente = User::findOne($representante->user_id);
                if ($userExistente) {
                    $this->stdout("✅ YA TIENE USUARIO\n", Console::FG_GREEN);
                    $contador++;
                    continue;
                }
            }
            
            // Crear usuario SOLO con campos existentes
            $user = $this->crearUsuarioSoloCamposExistentes($representante, 'representante');
            
            if ($user && $user->id) {
                // Actualizar usando SQL directo para evitar behaviors
                if ($this->actualizarUserIdRepresentante($representante->id, $user->id)) {
                    $this->stdout("✅ CREADO (User ID: {$user->id})\n", Console::FG_GREEN);
                    $contador++;
                } else {
                    $this->stdout("❌ ERROR ACTUALIZANDO USER_ID\n", Console::FG_RED);
                    $errors++;
                }
            } else {
                $this->stdout("❌ ERROR CREANDO USUARIO\n", Console::FG_RED);
                $errors++;
            }
        }
        
        $this->stdout("   Resumen: {$contador} migrados, {$errors} errores\n", Console::FG_CYAN);
        return $contador;
    }
    
    private function migrarAtletas()
    {
        $this->stdout("\n🏃 MIGRANDO ATLETAS...\n", Console::FG_BLUE);
        
        $atletas = AtletasRegistro::find()->all();
        $this->stdout("   Atletas encontrados: " . count($atletas) . "\n", Console::FG_CYAN);
        
        $contador = 0;
        $errors = 0;
        
        foreach ($atletas as $atleta) {
            if (empty($atleta->identificacion)) {
                continue;
            }
            
            $this->stdout("   Procesando: {$atleta->identificacion} - {$atleta->p_nombre}... ", Console::FG_CYAN);
            
            // Verificar si ya tiene usuario
            if ($atleta->user_id) {
                $userExistente = User::findOne($atleta->user_id);
                if ($userExistente) {
                    $this->stdout("✅ YA TIENE USUARIO\n", Console::FG_GREEN);
                    $contador++;
                    continue;
                }
            }
            
            // Crear usuario SOLO con campos existentes
            $user = $this->crearUsuarioSoloCamposExistentes($atleta, 'atleta');
            
            if ($user && $user->id) {
                // Actualizar usando SQL directo para evitar behaviors
                if ($this->actualizarUserIdAtleta($atleta->id, $user->id)) {
                    $this->stdout("✅ CREADO (User ID: {$user->id})\n", Console::FG_GREEN);
                    $contador++;
                } else {
                    $this->stdout("❌ ERROR ACTUALIZANDO USER_ID\n", Console::FG_RED);
                    $errors++;
                }
            } else {
                $this->stdout("❌ ERROR CREANDO USUARIO\n", Console::FG_RED);
                $errors++;
            }
        }
        
        $this->stdout("   Resumen: {$contador} migrados, {$errors} errores\n", Console::FG_CYAN);
        return $contador;
    }
    
    /**
     * Actualizar user_id en representantes usando SQL directo
     */
    private function actualizarUserIdRepresentante($representanteId, $userId)
    {
        try {
            $db = Yii::$app->db;
            $command = $db->createCommand('
                UPDATE atletas.registro_representantes 
                SET user_id = :user_id 
                WHERE id = :id
            ', [
                ':user_id' => $userId,
                ':id' => $representanteId
            ]);
            
            return $command->execute() > 0;
        } catch (\Exception $e) {
            $this->stdout("      ❌ ERROR SQL: " . $e->getMessage() . "\n", Console::FG_RED);
            return false;
        }
    }
    
    /**
     * Actualizar user_id en atletas usando SQL directo
     */
    private function actualizarUserIdAtleta($atletaId, $userId)
    {
        try {
            $db = Yii::$app->db;
            $command = $db->createCommand('
                UPDATE atletas.registro 
                SET user_id = :user_id 
                WHERE id = :id
            ', [
                ':user_id' => $userId,
                ':id' => $atletaId
            ]);
            
            return $command->execute() > 0;
        } catch (\Exception $e) {
            $this->stdout("      ❌ ERROR SQL: " . $e->getMessage() . "\n", Console::FG_RED);
            return false;
        }
    }
    
    /**
     * Crear usuario usando SOLO campos que existen en seguridad.user
     */
    private function crearUsuarioSoloCamposExistentes($persona, $tipo)
    {
        try {
            $cedula = (string)$persona->identificacion;
            
            $this->stdout("\n      🔍 DIAGNÓSTICO para {$cedula}:\n", Console::FG_PURPLE);
            
            // 1. Verificar si ya existe usuario con esta cédula COMO USERNAME
            $username = $cedula;
            $usuarioExistente = User::findByUsername($username);
            
            if ($usuarioExistente) {
                $this->stdout("      ✅ Usuario ya existe: {$usuarioExistente->username}\n", Console::FG_GREEN);
                return $usuarioExistente;
            }
            
            // 2. Crear usuario con CAMPOS MÍNIMOS que SÍ existen
            $user = new User();
            $user->username = $username;
            $user->cedula = $cedula;
            $user->email = $cedula . '@temporal.com';
            $user->status = User::STATUS_ACTIVE;
            
            // SOLO campos que sabemos que existen:
            $user->setPassword('12345-aves');
            $user->generateAuthKey();
            $user->created_at = time();
            $user->updated_at = time();
            
            $this->stdout("      📝 Campos usados: username, cedula, email, status, password_hash, auth_key, created_at, updated_at\n", Console::FG_PURPLE);
            
            if ($user->save()) {
                $this->stdout("      ✅ Usuario creado exitosamente\n", Console::FG_GREEN);
                
                // Asignar rol
                $this->asignarRolSeguro($user->id, $tipo);
                
                return $user;
            } else {
                $this->stdout("      ❌ ERRORES AL GUARDAR:\n", Console::FG_RED);
                foreach ($user->errors as $attribute => $errors) {
                    foreach ($errors as $error) {
                        $this->stdout("         - {$attribute}: {$error}\n", Console::FG_RED);
                    }
                }
                return null;
            }
            
        } catch (\Exception $e) {
            $this->stdout("      ❌ EXCEPCIÓN: " . $e->getMessage() . "\n", Console::FG_RED);
            return null;
        }
    }
    
    /**
     * Asignar rol de manera segura
     */
    private function asignarRolSeguro($userId, $rol)
    {
        try {
            $auth = Yii::$app->authManager;
            if ($auth === null) {
                $this->stdout("      ⚠️  authManager no está configurado\n", Console::FG_YELLOW);
                return false;
            }
            
            // Verificar si el rol existe
            $role = $auth->getRole($rol);
            if (!$role) {
                $this->stdout("      ⚠️  El rol '{$rol}' no existe\n", Console::FG_YELLOW);
                return false;
            }
            
            // Asignar rol
            $auth->assign($role, $userId);
            $this->stdout("      ✅ Rol '{$rol}' asignado correctamente\n", Console::FG_GREEN);
            return true;
            
        } catch (\Exception $e) {
            $this->stdout("      ⚠️  Error asignando rol '{$rol}': " . $e->getMessage() . "\n", Console::FG_YELLOW);
            return false;
        }
    }
    
    /**
     * Verificar estado actual
     */
    public function actionStatus()
    {
        $this->stdout("📊 ESTADO ACTUAL:\n", Console::FG_BLUE);
        
        $db = Yii::$app->db;
        
        // Representantes
        $totalReps = $db->createCommand('SELECT COUNT(*) FROM atletas.registro_representantes')->queryScalar();
        $repsConUser = $db->createCommand('SELECT COUNT(*) FROM atletas.registro_representantes WHERE user_id IS NOT NULL')->queryScalar();
        
        // Atletas
        $totalAtletas = $db->createCommand('SELECT COUNT(*) FROM atletas.registro')->queryScalar();
        $atletasConUser = $db->createCommand('SELECT COUNT(*) FROM atletas.registro WHERE user_id IS NOT NULL')->queryScalar();
        
        $this->stdout("   Representantes: {$repsConUser}/{$totalReps}\n", Console::FG_CYAN);
        $this->stdout("   Atletas: {$atletasConUser}/{$totalAtletas}\n", Console::FG_CYAN);
        $this->stdout("   Total: " . ($repsConUser + $atletasConUser) . "/" . ($totalReps + $totalAtletas) . "\n", Console::FG_GREEN);
    }
    
    /**
     * Comando para verificar configuración
     */
    public function actionVerificar()
    {
        $this->stdout("🔍 VERIFICANDO CONFIGURACIÓN...\n", Console::FG_PURPLE);
        
        // Verificar authManager
        $auth = Yii::$app->authManager;
        if ($auth === null) {
            $this->stdout("❌ authManager NO está configurado\n", Console::FG_RED);
        } else {
            $this->stdout("✅ authManager está configurado\n", Console::FG_GREEN);
            
            // Verificar roles
            $roles = ['representante', 'atleta'];
            foreach ($roles as $rol) {
                $role = $auth->getRole($rol);
                if ($role) {
                    $this->stdout("   ✅ Rol '{$rol}' existe\n", Console::FG_GREEN);
                } else {
                    $this->stdout("   ❌ Rol '{$rol}' NO existe\n", Console::FG_RED);
                }
            }
        }
    }
}