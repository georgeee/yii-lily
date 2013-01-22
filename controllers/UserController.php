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
    public $defaultAction = 'view';

    /**
     * Declares filters for the controller
     * @return array filters
     */
    public function filters() {
        return array(
            'accessControl'
        );
    }

    /**
     * Declares access rules for the controller
     * @return array access rules
     */
    public function accessRules() {
        return array(
            array('deny',
                'actions' => array('logout'),
                'users' => array('?'),
            ),
            array('deny',
                'actions' => array('login', 'onetime'),
                'users' => array('@'),
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
                if ($model->validate() && $model->service != 'email') {
                    $this->redirect(array('', 'service' => $model->service, 'rememberMe' => $model->rememberMe));
                }
            }else
                $model_new = true;
        }

        if (!$model_new && $model->validate() && isset($model->service)) {
            $authIdentity = Yii::app()->eauth->getIdentity($model->service);
            /* @var $authIdentity LEmailService */
            $authIdentity->redirectUrl = Yii::app()->user->returnUrl;
            $authIdentity->cancelUrl = $this->createAbsoluteUrl('user/login');
            if ($model->service == 'email') {
                $authIdentity->email = $model->email;
                $authIdentity->password = $model->password;
                $authIdentity->rememberMe = $model->rememberMe;
            }
            if ($authIdentity->authenticate()) {
                if ($model->service == 'email' && $authIdentity->errorCode == LEmailService::ERROR_INFORMATION_MAIL_FAILED)
                    Yii::app()->user->setFlash('lily.login.error', LilyModule::t("Your account was created, but it failed to send you email with account information."));

                $identity = new LUserIdentity($authIdentity);
                //Authentication succeed
                if ($identity->authenticate()) {
                    /* @var $user LUser */
                    $user = (isset($identity->user) ? $identity->user : $identity->session->user);
                    if ($user->state == LUser::BANNED_STATE && Yii::app()->authManager->checkAccess('unbanUser', $user->uid, array('uid' => $user->uid))) {
                        $user->state = LUser::ACTIVE_STATE;
                        if (!$user->save())
                            throw new CDbException("failed to save user");
                    }
                    if ($user->state == LUser::BANNED_STATE) {
                        Yii::app()->user->setFlash('lily.login.error', LilyModule::t("Your account was put under ban. Please contact to site administrator for details."));
                    } else {
                        $result = Yii::app()->user->login($identity, $model->rememberMe ? LilyModule::instance()->sessionTimeout : 0);
                        if ($result)
                            Yii::app()->user->setFlash('lily.login.success', LilyModule::t('You were successfully logged in.'));
                        else
                            throw new LException("login() returned false");
                    }
                    //Special redirect to fire popup window closing
                    $authIdentity->redirect();
                } else {/* $identity->authenticate() returns true only when $authIdentity returns true, so this else will never be achieved */
                }
            }
            //Auth failed, close popup and redirect to $authIdentity->cancelUrl
            if ($model->service == 'email') {
                switch ($authIdentity->errorCode) {
                    case LEmailService::ERROR_ACTIVATION_MAIL_SENT:
                        Yii::app()->user->setFlash('lily.login.success', LilyModule::t("Activation e-mail sent."));
                        break;
                    case LEmailService::ERROR_ACTIVATION_MAIL_FAILED:
                        Yii::app()->user->setFlash('lily.login.error', LilyModule::t("Failed to send account activation email."));
                        break;
                    case LEmailService::ERROR_AUTH_FAILED:
                        Yii::app()->user->setFlash('lily.login.error', LilyModule::t("Failed to authenticate (email-password mismatch)."));
                        break;
                    case LEmailService::ERROR_NOT_REGISTERED:
                        Yii::app()->user->setFlash('lily.login.error', LilyModule::t("Account with given e-mail is not registered. You have to pass registration."));
                        $authIdentity->cancelUrl = $this->createUrl('register', array('email' => $authIdentity->email));
                        break;
                }
            } else {
                Yii::app()->user->setFlash('lily.login.error', LilyModule::t('Failed to authenticate.'));
            }
            $authIdentity->cancel();
        }
        $this->render('login', array('model' => $model, 'services' => $services));
    }

    /**
     * Register action 
     */
    public function actionRegister() {
        if (isset($_POST['ajax']) && $_POST['ajax'] == 'LRegisterForm-form') {
            $model = new LRegisterForm;
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
        $model = new LRegisterForm;
        if (isset($_POST['LRegisterForm'])) {
            $model->attributes = $_POST['LRegisterForm'];
            if ($model->validate()) {
                $email = $model->email;
                $password = $model->password;
                $authIdentity = new LEmailService;
                $authIdentity->email = $email;
                $authIdentity->password = $password;
                $authIdentity->user = Yii::app()->user->isGuest ? null : LilyModule::instance()->user;
                $authIdentity->rememberMe = $model->rememberMe;
                if ($authIdentity->authenticate(true)) {
                    if (LilyModule::instance()->accountManager->loginAfterRegistration && Yii::app()->user->isGuest) {
                        $identity = new LUserIdentity($authIdentity);
                        $identity->authenticate();
                        $result = Yii::app()->user->login($identity, $model->rememberMe ? LilyModule::instance()->sessionTimeout : 0);

                        if ($result)
                            Yii::app()->user->setFlash('lily.login.success', LilyModule::t('You were successfully logged in.'));
                        else
                            throw new LException("login() returned false");
                    }
                    $authIdentity->redirect(Yii::app()->user->isGuest ? Yii::app()->homeUrl : $this->createUrl('view'));
                } else {
                    switch ($authIdentity->errorCode) {
                        case LEmailService::ERROR_ACTIVATION_MAIL_SENT:
                            Yii::app()->user->setFlash('lily.login.success', LilyModule::t("Activation e-mail sent."));
                            break;
                        case LEmailService::ERROR_ACTIVATION_MAIL_FAILED:
                            Yii::app()->user->setFlash('lily.login.error', LilyModule::t("Failed to send account activation email."));
                            break;
                        case LEmailService::ERROR_INFORMATION_MAIL_FAILED:
                            Yii::app()->user->setFlash('lily.login.error', LilyModule::t("Your account was created, but it failed to send you email with account information."));
                            break;
                    }
                }
                //Special redirect to fire popup window closing
                $authIdentity->cancel();
            }
        } else if (isset($_GET['email']))
            $model->email = $_GET['email'];
        $this->render('register', array('model' => $model));
    }

    /**
     * Activate action
     * @param $code Activation code
     */
    public function actionActivate($code) {
        $_code = LEmailAccountActivation::model()->findByAttributes(array('code' => $code));
        if (isset($_code)) {
            $rememberMe = $_code->rememberMe;
        }
        $account = LilyModule::instance()->accountManager->performActivation($code);
        /* $errorCode:
         * <ul>
         * <li>1 - failed to find code DB record</li>
         * <li>2 - activation code expired</li>
         * <li>3 - failed to send mail</li>
         * <li>0 - everything is OK</li>
         * </ul>
         */
        $errorCode = LilyModule::instance()->accountManager->errorCode;
        $msg = '';
        switch ($errorCode) {
            case 0:
            case 3:
                $msg = LilyModule::t("Your account was successfully activated. Now you can login using your email.");
                break;
            case 1:
            case 2:
                $msg = LilyModule::t("Your code is wrong or have expired. Please request a new one.");
                break;
        }
        if (LilyModule::instance()->accountManager->sendMail) {
            if ($errorCode == 0) {
                $msg .= "<br />" . LilyModule::t("An email with account details was sent to your email.");
            } else if ($errorCode == 3) {
                $msg .= "<br/ >" . LilyModule::t("An email with account details sending failed. Please contact site administrator.");
            }
        }
        Yii::app()->user->setFlash('lily.activate.' . (($errorCode == 0 || $errorCode == 4) ? 'success' : 'fail'), $msg);
        if (($errorCode == 0 || $errorCode == 3) && Yii::app()->user->isGuest && LilyModule::instance()->accountManager->loginAfterRegistration) {
            //Now comes login
            $authIdentity = new LEmailService;
            $authIdentity->email = $account->id;
            $authIdentity->user = $account->user;
            $authIdentity->authenticate(false, false);
            $identity = new LUserIdentity($authIdentity);
            $identity->authenticate();
            $result = Yii::app()->user->login($identity, $rememberMe ? LilyModule::instance()->sessionTimeout : 0);
            if ($result) {
                Yii::app()->user->setFlash('lily.login.success', LilyModule::t('You were successfully logged in.'));
                $authIdentity->redirect($this->createUrl('view'));
            }else
                throw new LException("login() returned false");
        }else
            $this->redirect(Yii::app()->homeUrl);
    }

    /**
     * Logout action
     */
    public function actionLogout() {
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }

    /**
     * List action
     * @param type $showDeleted
     * @param type $showAppended
     * @param type $showBanned
     * @param type $showActive
     * @throws CHttpException 
     */
    public function actionList($showDeleted = true, $showAppended = true, $showBanned = true, $showActive = true) {
        if (!Yii::app()->user->checkAccess('listUser'))
            throw new CHttpException(403);
        $params = array(
            'criteria' => array(
                'order' => 'uid ASC',
            ),
            'pagination' => array(
                'pageSize' => 20,
            ),
        );
        $condition = '';
        if ($showActive && (Yii::app()->user->checkAccess('viewActiveUser') || Yii::app()->user->checkAccess('viewUser') ))
            $condition.=(!empty($condition) ? ' OR ' : '') . '(state=' . LUser::ACTIVE_STATE . ')';
        if ($showDeleted && (Yii::app()->user->checkAccess('viewDeletedUser') || Yii::app()->user->checkAccess('viewUser') ))
            $condition.=(!empty($condition) ? ' OR ' : '') . '(state=' . LUser::DELETED_STATE . ')';
        if ($showBanned && (Yii::app()->user->checkAccess('viewBannedUser') || Yii::app()->user->checkAccess('viewUser') ))
            $condition.=(!empty($condition) ? ' OR ' : '') . '(state=' . LUser::BANNED_STATE . ')';
        if ($showAppended && (Yii::app()->user->checkAccess('viewAppendedUser') || Yii::app()->user->checkAccess('viewUser') ))
            $condition.=(!empty($condition) ? ' OR ' : '') . '(state>' . LUser::ACTIVE_STATE . ')';
        if (!empty($condition))
            $params['criteria']['condition'] = $condition;
        $dataProvider = new CActiveDataProvider('LUser', $params);
        $this->render('list', array('dataProvider' => $dataProvider, "showDeleted" => $showDeleted, "showAppended" => $showAppended, "showBanned" => $showBanned, "showActive" => $showActive));
    }

    /**
     * Switch user state action
     * @param int $uid Id of user
     * @param int $mode Id of user
     */
    public function actionSwitch_state($mode = LUser::DELETED_STATE, $uid = null) {
        $approved = (int) Yii::app()->request->getPost('approved', 0);
        if ($uid == null)
            $uid = Yii::app()->user->id;
        /* @var $user LUser */
        $user = LUser::model()->findByPk($uid);
        if ($mode > LUser::ACTIVE_STATE)
            throw new CHttpException(404);
        if (($mode == LUser::DELETED_STATE && ($user->state == LUser::DELETED_STATE || !Yii::app()->user->checkAccess('deleteUser', array('user' => $user))))
                || ($mode == LUser::BANNED_STATE && ($user->state == LUser::BANNED_STATE || !Yii::app()->user->checkAccess('banUser', array('user' => $user))))
                || ($mode == LUser::ACTIVE_STATE && ($user->state == LUser::ACTIVE_STATE
                || ($user->state == LUser::DELETED_STATE && !Yii::app()->user->checkAccess('restoreUser', array('user' => $user)))
                || ($user->state == LUser::BANNED_STATE && !Yii::app()->user->checkAccess('unbanUser', array('user' => $user)))
                )))
            throw new CHttpException(403);
        if ($approved) {
            $user->state = $mode;
            if (!$user->save())
                throw new CDbException("failed to save user");
            if ($mode < LUser::ACTIVE_STATE && $uid == Yii::app()->user->id) {
                Yii::app()->user->logout();
                $this->redirect(Yii::app()->homeUrl);
            } else {
                switch ($mode) {
                    case LUser::ACTIVE_STATE: $msg = LilyModule::t('User {user} was successfully activated', array('{user}' => $user->name));
                        break;
                    case LUser::DELETED_STATE: $msg = LilyModule::t('User {user} was successfully deleted', array('{user}' => $user->name));
                        break;
                    case LUser::BANNED_STATE: $msg = LilyModule::t('User {user} was successfully banned', array('{user}' => $user->name));
                        break;
                }
                Yii::app()->user->setFlash('lily.switch_state.success', CHtml::encode($msg));
                $this->redirect(array('view', 'uid' => $uid));
            }
        }else
            $this->render('switch_state', array('mode' => $mode, 'uid' => $uid, 'user' => $user, 'self' => $uid == Yii::app()->user->id));
    }

    /**
     * View action
     */
    public function actionView() {
        $uid = Yii::app()->request->getParam('uid', Yii::app()->user->id);
        $model = LUser::model()->findByPk($uid);
        if (!isset($model))
            throw new CHttpException(404);
        if (!Yii::app()->user->checkAccess('viewUser', array('uid' => $uid)))
            throw new CHttpException(403);
        $model->setScenario('registered');
        $this->render('view', array('user' => $model));
    }

    /**
     * Onetime login action
     * @param string $token onetime login token
     * @param string $redirectUrl Url, to which user will be redirected after login
     */
    public function actionOnetime($token, $redirectUrl = null) {
        $result = LilyModule::instance()->accountManager->oneTimeLogin($token);
        if ($result)
            Yii::app()->user->setFlash('lily.onetime.success', LilyModule::t('You were successfully logged in.'));
        else
            Yii::app()->setFlash('lily.onetime.error', LilyModule::t('Wrong one-time login token.'));
        $this->redirect(isset($redirectUrl) ? $redirectUrl : Yii::app()->homeUrl);
    }

    /**
     * Init action
     * @param string $action Init action (start, finish or next)
     * @throws CHttpException
     */
    public function actionInit($action) {
        if (!LilyModule::instance()->userIniter->isStarted)
            throw new CHttpException(403);
        if (($action == 'start' && LilyModule::instance()->userIniter->stepId == 0) || ($action == 'finish' && LilyModule::instance()->userIniter->stepId
                == LilyModule::instance()->userIniter->count - 1)) {
            $this->render('init', array('action' => $action));
        } else if ($action == 'next') {
            LilyModule::instance()->userIniter->nextStep();
        }else
            throw new CHttpException(403);
    }

}

