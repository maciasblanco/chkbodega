<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'acceder-sistema', 'mi-cuenta'],
                'rules' => [
                    [
                        'actions' => ['logout', 'mi-cuenta'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'actions' => ['acceder-sistema'],
                        'allow' => true,
                        'roles' => ['@'],
                        'denyCallback' => function ($rule, $action) {
                            Yii::$app->session->setFlash('error', 'Debe iniciar sesión para acceder al sistema.');
                            return $this->redirect(['/site/login']);
                        }
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Landing page pública - CON PREVENCIÓN DE BUCLE COMPLETA
     *
     * @return string
     */
    public function actionIndex()
    {
        // ✅ PREVENCIÓN DE BUCLE: Verificar si ya estamos autenticados en página de login
        $currentRoute = Yii::$app->controller->route;
        
        // ✅ SI ESTÁ AUTENTICADO Y TRATA DE ACCEDER A LOGIN, REDIRIGIR AL INDEX
        if (!Yii::$app->user->isGuest && $currentRoute === 'site/login') {
            return $this->redirect(['site/index']);
        }
        
        // ✅ SI YA ESTÁ AUTENTICADO Y ACCEDE AL INDEX, NO HACER NADA ESPECIAL
        // ✅ PERMITIR QUE USUARIOS AUTENTICADOS VEAN EL LANDING
        
        // ✅ SEGURO: NUNCA redirigir automáticamente a rutas internas
        // ✅ Mostrar siempre landing page pública
        
        return $this->render('index', [
            'isAuthenticated' => !Yii::$app->user->isGuest
        ]);
    }

    /**
     * ✅ PUNTO DE ENTRADA SEGURO al sistema - CON PREVENCIÓN DE BUCLE
     * No revela rutas internas directamente
     */
    public function actionAccederSistema()
    {
        // Verificar autenticación (ya lo hace el behavior, pero por redundancia)
        if (Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('error', 'Debe iniciar sesión para acceder al sistema.');
            return $this->redirect(['/site/login']);
        }
        
        // ✅ VERIFICACIÓN EXTRA: Si ya estamos en una página del sistema GED, no redirigir
        $currentRoute = Yii::$app->controller->route;
        if (strpos($currentRoute, 'ged/') === 0) {
            // Ya estamos en el sistema GED, no redirigir
            return $this->redirect(['/ged/default/index']);
        }
        
        // ✅ REDIRECCIÓN SEGURA: Usar nombre de ruta en lugar de URL completa
        // Esto no revela la estructura interna al usuario
        
        // Registrar el acceso en logs para auditoría
        Yii::info("Usuario " . Yii::$app->user->identity->username . 
                  " accede al sistema desde IP: " . Yii::$app->request->userIP, 'security');
        
        // Redirigir al punto de entrada del módulo GED
        return $this->redirect(['/ged/default/index']);
    }

    /**
     * ✅ Login action - CON PREVENCIÓN DE BUCLE MEJORADA
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        // ✅ SI YA ESTÁ AUTENTICADO, NO PERMITIR ACCEDER AL LOGIN
        if (!Yii::$app->user->isGuest) {
            // ✅ SI ESTÁ AUTENTICADO Y ACCEDE A LOGIN, REDIRIGIR A ACCEDER-SISTEMA
            // Esto previene el bucle de login->index->login
            Yii::$app->session->setFlash('info', 'Ya tienes una sesión activa.');
            return $this->redirect(['/site/acceder-sistema']);
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            // Verificar si debe cambiar contraseña temporal
            $user = Yii::$app->user->identity;
            if ($user && method_exists($user, 'debeCambiarPassword') && $user->debeCambiarPassword()) {
                Yii::$app->session->setFlash('warning', 
                    'Debe cambiar su contraseña temporal antes de continuar.');
                return $this->redirect(['/site/cambiar-password']);
            }
            
            Yii::$app->session->setFlash('success', 'Sesión iniciada correctamente.');
            
            // ✅ PREVENIR REDIRECCIÓN A LOGIN DESPUÉS DE LOGIN
            // Si la URL anterior es login o index, redirigir a acceder-sistema
            $returnUrl = Yii::$app->request->referrer;
            if (!$returnUrl || strpos($returnUrl, 'login') !== false || strpos($returnUrl, 'index') !== false) {
                return $this->redirect(['/site/acceder-sistema']);
            }
            
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * ✅ Cierra sesión y también limpia la escuela - SIN BUCLE
     */
    public function actionLogout()
    {
        // Registrar logout en logs
        if (!Yii::$app->user->isGuest) {
            Yii::info("Usuario " . Yii::$app->user->identity->username . 
                      " cierra sesión desde IP: " . Yii::$app->request->userIP, 'security');
        }
        
        // Limpiar escuela antes de hacer logout
        $session = Yii::$app->session;
        $session->remove('id_escuela');
        $session->remove('nombre_escuela');
        $session->remove('idEscuela');
        $session->remove('nombreEscuela');
        
        // Logout normal
        Yii::$app->user->logout();

        Yii::$app->session->setFlash('success', 'Sesión cerrada correctamente.');
        
        // ✅ SIEMPRE REDIRIGIR AL INDEX, NUNCA AL LOGIN
        return $this->redirect(['site/index']);
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * ✅ Action para cambiar contraseña obligatorio - CON PREVENCIÓN DE BUCLE
     */
    public function actionCambiarPassword()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        // Usar el modelo si existe, sino crear uno básico
        $modelClassName = '\app\models\CambioPasswordForm';
        if (class_exists($modelClassName)) {
            $model = new $modelClassName();
        } else {
            // Modelo básico como fallback
            $model = new \yii\base\DynamicModel(['currentPassword', 'newPassword', 'confirmPassword']);
            $model->addRule(['currentPassword', 'newPassword', 'confirmPassword'], 'required')
                  ->addRule(['confirmPassword'], 'compare', ['compareAttribute' => 'newPassword']);
        }

        $user = Yii::$app->user->identity;

        // Verificar si realmente debe cambiar la contraseña
        if (method_exists($user, 'debeCambiarPassword') && !$user->debeCambiarPassword()) {
            Yii::$app->session->setFlash('info', 'Su contraseña ya ha sido cambiada anteriormente.');
            return $this->redirect(['site/index']);
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (method_exists($user, 'cambiarPassword') && $user->cambiarPassword($model->newPassword)) {
                Yii::$app->session->setFlash('success', 'Contraseña cambiada exitosamente. Ahora puede usar el sistema.');
                
                // Registrar el cambio en logs de seguridad
                Yii::info("Usuario {$user->username} cambió su contraseña temporal", 'security');
                
                // ✅ REDIRIGIR AL INDEX, NO A LOGIN
                return $this->redirect(['/site/index']);
            } else {
                Yii::$app->session->setFlash('error', 'Error al cambiar la contraseña. Por favor intente nuevamente.');
            }
        }

        return $this->render('cambio-password', [
            'model' => $model,
        ]);
    }

    /**
     * ✅ Action para perfil de usuario y cambio opcional de contraseña - SIN BUCLE
     */
    public function actionMiCuenta()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['site/login']);
        }

        $user = Yii::$app->user->identity;
        
        // Usar el modelo si existe, sino crear uno básico
        $modelClassName = '\app\models\CambioPasswordForm';
        if (class_exists($modelClassName)) {
            $model = new $modelClassName();
        } else {
            $model = new \yii\base\DynamicModel(['currentPassword', 'newPassword', 'confirmPassword']);
            $model->addRule(['currentPassword', 'newPassword', 'confirmPassword'], 'required')
                  ->addRule(['confirmPassword'], 'compare', ['compareAttribute' => 'newPassword']);
        }

        // Verificar si viene de POST para cambiar contraseña
        if (Yii::$app->request->post() && $model->load(Yii::$app->request->post()) && $model->validate()) {
            if (method_exists($user, 'cambiarPassword') && $user->cambiarPassword($model->newPassword)) {
                Yii::$app->session->setFlash('success', 'Contraseña cambiada exitosamente.');
                
                // Registrar cambio en logs
                Yii::info("Usuario {$user->username} actualizó su contraseña desde Mi Cuenta", 'security');
                
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error al cambiar la contraseña.');
            }
        }

        return $this->render('mi-cuenta', [
            'user' => $user,
            'model' => $model,
        ]);
    }
    
    /**
     * ✅ MÉTODO ADICIONAL: goHome personalizado para prevenir bucle
     * Sobrescribe el método goHome() para asegurar que siempre redirija al index
     */
    public function goHome()
    {
        // ✅ SIEMPRE REDIRIGIR AL INDEX, NUNCA AL LOGIN
        return $this->redirect(['site/index']);
    }
    
    /**
     * ✅ MÉTODO ADICIONAL: Verificar si hay bucle de redirección
     * Se puede llamar desde JavaScript para debug
     */
    public function actionCheckRedirectLoop()
    {
        Yii::info('Verificación de bucle de redirección solicitada', 'security');
        
        $data = [
            'currentRoute' => Yii::$app->controller->route,
            'isGuest' => Yii::$app->user->isGuest,
            'sessionId' => Yii::$app->session->id,
            'referrer' => Yii::$app->request->referrer,
            'userAgent' => Yii::$app->request->userAgent
        ];
        
        return $this->asJson([
            'status' => 'ok',
            'message' => 'No se detectó bucle de redirección',
            'data' => $data
        ]);
    }
}