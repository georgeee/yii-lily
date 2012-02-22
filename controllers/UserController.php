<?php

/**
 * UserController class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * UserController is a controller class, which manages with user login/logout/activate/edit/list/view actions.
 *
 * @package application.modules.lily.controllers
 */
class UserController extends Controller {
    /**
     * @var string the name of the default action
     */
    public $defaultAction='view';


    /**
     * Declares filters for the controller
     * @return array filters
     */
    public function filters() {
        return array(
            'accessControl',
        );
    }

    /**
     * Just an expression handler for accessRules()
     * @static
     * @param $user
     * @param $rule
     * @return bool
     */
    public static function allowOwnAccessRule($user, $rule){
        $uid = Yii::app()->request->getParam('uid', Yii::app()->user->id);
        return $uid == $user->id;
    }

    /**
     * Declares access rules for the controller
     * @return array access rules
     */
    public function accessRules() {
        return array(
            array('allow',
                'actions' => array('edit', 'view', 'index'),
                'expression' => array(__CLASS__, 'allowOwnAccessRule'),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array('edit', 'view', 'index', 'list'),
                'roles' => array('admin'),
            ),
            array('deny',
                'actions' => array('list', 'edit', 'view', 'index'),
            ),
        );
    }


/**
 * Login action
 * @param string $service Service, which is being authenticated
 * @param boolean $rememberMe Whether to remember user
 * @throws LException
 */
    public function actionLogin($service = null, $rememberMe = false) {
        $id_prefix = 'LAuthWidget-form-';
        if (isset($_POST['ajax']) && substr($_POST['ajax'], 0, strlen($id_prefix)) == $id_prefix) {
            $model = new LLoginForm;
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        $model_new = false;

        $services = LilyModule::instance()->services;
        if ($service != null) {
            $_services = $services;
            unset($_services['email']);
            $model = new LLoginForm('', array_keys($_services));
            $model->service = $service;
            $model->rememberMe = $rememberMe;
        } else {
            $model = new LLoginForm('', array_keys($services));
            if (isset($_POST['LLoginForm'])) {
                $model->attributes = $_POST['LLoginForm'];
                //Special behaviour for cases, when JS isn't enabled
                if($model->validate() && $model->service != 'email'){
                    $this->redirect(array('', 'service' => $model->service, 'rememberMe' => $model->rememberMe));
                }
            }else
                $model_new = true;
        }

        if (!$model_new && $model->validate() && isset($model->service)) {
            $authIdentity = Yii::app()->eauth->getIdentity($model->service);
            $authIdentity->redirectUrl = Yii::app()->user->returnUrl;
            $authIdentity->cancelUrl = $this->createAbsoluteUrl('user/login');
            if ($model->service == 'email') {
                $authIdentity->email = $model->email;
                $authIdentity->password = $model->password;
            }
            if ($authIdentity->authenticate()) {
                $identity = new LUserIdentity($authIdentity);
                //Authentication succeed
                if ($identity->authenticate()) {
                    $result = Yii::app()->user->login($identity, $model->rememberMe ? LilyModule::instance()->sessionTimeout : 0);

                    if($result) Yii::app()->user->setFlash('lily.login.success', LilyModule::t('You were successfully logged in.'));
                    else throw new LException("login() returned false");

                    //Special redirect to fire popup window closing
                    $authIdentity->redirect();
                } else {/* $identity->authenticate() returns true only when $authIdentity returns true, so this else will never be achieved */}
            }
            //Auth failed, close popup and redirect to $authIdentity->cancelUrl
            if($model->service == 'email'){
                switch($authIdentity->errorCode){
                    case LEmailService::ERROR_ACTIVATION_MAIL_SENT:
                        Yii::app()->user->setFlash('lily.login.success', LilyModule::t("Activation e-mail sent."));
                        break;
                    case LEmailService::ERROR_ACTIVATION_MAIL_FAILED:
                        Yii::app()->user->setFlash('lily.login.fail', LilyModule::t("Failed to send account activation email."));
                        break;
                    case LEmailService::ERROR_AUTH_FAILED:
                        Yii::app()->user->setFlash('lily.login.fail', LilyModule::t("Failed to authenticate (email-password mismatch)."));
                        break;
                }
            }else{
                Yii::app()->user->setFlash('lily.login.fail', LilyModule::t('Failed to authenticate.'));
            }
            $authIdentity->cancel();
        }
        $this->render('login', array('model' => $model, 'services' => $services));
    }
/**
 * Activate action
 * @param $code Activation code
 */
    public function actionActivate($code) {
        $model = LilyModule::instance()->accountManager->performActivation($code);
        /* $errorCode:
         * <ul>
         * <li>1 - failed to find code DB record</li>
         * <li>2 - activation code expired</li>
         * <li>3 - failed to create email account email record</li>
         * <li>4 - failed to send mail</li>
         * <li>0 - everything is OK</li>
         * </ul>
         */
        $errorCode = LilyModule::instance()->accountManager->errorCode;
        $msg = '';
        switch ($errorCode) {
            case 0:
            case 4:
                $msg = LilyModule::t("Your account was successfully activated. Now you can login using your email.");
                break;
            case 1:
            case 2:
                $msg = LilyModule::t("Your code is wrong or have expired. Please request a new one.");
                break;
            case 3:
                $msg = LilyModule::t("Unexpected site error. Please contact site administrtor, if this message repeats.");
                break;
        }
        if (LilyModule::instance()->accountManager->sendMail) {
            if ($errorCode == 0) {
                $msg .= "<br />".LilyModule::t("An email with account details was sent to your email.");
            } else if ($errorCode == 4) {
                $msg .= "<br/ >".LilyModule::t("An email with account details sending failed. Please contact site administrator.");
            }
        }
        Yii::app()->user->setFlash('lily.activate.'.(($errorCode==0 || $errorCode==4)?'success':'fail'), $msg);
        $this->redirect(Yii::app()->homeUrl);
    }
/**
 * Logout action
 */
    public function actionLogout(){
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }

/**
 * List action
 * @param boolean $showDeleted Whether to show deleted users
 */
    public function actionList($showDeleted = false) {
        $params = array(
            'criteria' => array(
                'order' => 'uid ASC',
            ),
            'pagination' => array(
                'pageSize' => 20,
            ),
        );
        if(!$showDeleted) $params['criteria']['condition'] = 'deleted=0';
        $dataProvider = new CActiveDataProvider('LUser', $params);
        $this->render('list', array('dataProvider' => $dataProvider, 'showDeleted' => $showDeleted));
    }
/**
 * View action
 */
    public function actionView() {
        $uid = Yii::app()->request->getParam('uid', Yii::app()->user->id);
        $model = LUser::model()->findByPk($uid);
        $model->setScenario('registered');
        $this->render('view', array('user' => $model));
    }
/**
 * Onetime login action
 * @param string $token onetime login token
 * @param string $redirectUrl Url, to which user will be redirected after login
 */
    public function actionOnetime($token, $redirectUrl = null){
        $result = LilyModule::instance()->accountManager->oneTimeLogin($token);
        if($result) Yii::app()->user->setFlash('lily.onetime.success', LilyModule::t('You were successfully logged in.'));
        else Yii::app()->setFlash('lily.onetime.fail', LilyModule::t('Wrong one-time login token.'));
        $this->redirect(isset($redirectUrl)?$redirectUrl:Yii::app()->homeUrl);
    }

/**
 * Init action
 * @param string $action Init action (start, finish or next)
 * @throws CHttpException
 */
    public function actionInit($action){
        if(!LilyModule::instance()->userIniter->isStarted) throw new CHttpException(404);
        if(($action=='start' && LilyModule::instance()->userIniter->stepId == 0)||($action=='finish' && LilyModule::instance()->userIniter->stepId
            == LilyModule::instance()->userIniter->count-1)){
        $this->render('init', array('action'=>$action));
        }else if($action == 'next'){
            LilyModule::instance()->userIniter->nextStep();
        }else throw new CHttpException(404);
    }
}

