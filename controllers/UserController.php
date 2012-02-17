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

    public function filters() {
        return array(
            'accessControl',
        );
    }

    public function accessRules() {
        return array(
            array('allow',
                'actions' => array('edit', 'view', 'index'),
                'expression' => function($user, $rule) {
                    $uid = Yii::app()->request->getParam('uid', Yii::app()->user->id);
                    return $uid == $user->id;
                },
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

    public function actionIndex() {
        $this->redirect($this->createUrl('view'));
    }

//TODO normal behaviour after auth
//TODO make flashes work with redirects
    public function actionLogin() {
        $id_prefix = 'LAuthWidget-form-';
        if (isset($_POST['ajax']) && substr($_POST['ajax'], 0, strlen($id_prefix)) == $id_prefix) {
            $model = new LLoginForm;
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        $model_new = false;

        $services = LilyModule::instance()->services;
        if (Yii::app()->getRequest()->getQuery('service') != null) {
            $_services = $services;
            unset($_services['email']);
            $model = new LLoginForm('', array_keys($_services));
            $model->service = Yii::app()->getRequest()->getQuery('service');
            $model->rememberMe = Yii::app()->getRequest()->getQuery('rememberMe');
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

    public function actionActivate() {
        $code = Yii::app()->getRequest()->getParam('code');
        $model = LilyModule::instance()->accountManager->performActivation($code);
        $this->render('activate', array('code' => $model, 'errorCode' => LilyModule::instance()->accountManager->errorCode));
    }

    public function actionLogout(){
        Yii::app()->user->logout();
        $this->redirect(Yii::app()->homeUrl);
    }

    public function actionEdit() {
        $uid = Yii::app()->request->getParam('uid', Yii::app()->user->id);
        $model = LUser::model()->findByPk($uid);
        $model->setScenario('registered');
        if (isset($_POST['ajax']) && $_POST['ajax'] == 'user-edit-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
        foreach (array('name', 'sex', 'birthday') as $param)
            if (!isset($model->$param) && isset(LilyModule::instance()->session->data->$param))
                $model->$param = LilyModule::instance()->session->data->$param;
        if (isset($_POST['LUser'])) {
            $model->attributes = $_POST['LUser'];
            if ($model->validate()) {
                if ($model->save()) {
                    $returnUrl = Yii::app()->request->getQuery('returnUrl');
                    $this->redirect(isset($returnUrl) ? $returnUrl : "index");
                }
            }
        }
        $this->render('edit', array('user' => $model));
    }

    public function actionList() {
        $dataProvider = new CActiveDataProvider('LUser', array(
                    'criteria' => array(
                        'order' => 'uid ASC',
                    ),
                    'pagination' => array(
                        'pageSize' => 20,
                    ),
                ));
        $this->render('list', array('dataProvider' => $dataProvider));
    }

    public function actionView() {
        $uid = Yii::app()->request->getParam('uid', Yii::app()->user->id);
        $model = LUser::model()->findByPk($uid);
        $model->setScenario('registered');
        $this->render('view', array('user' => $model));
    }

    public function actionOnetime($token, $redirectUrl = null){
        $result = LilyModule::instance()->accountManager->oneTimeLogin($token);
        if($result) Yii::app()->user->setFlash('lily.onetime.success', LilyModule::t('You were successfully logged in.'));
        else Yii::app()->setFlash('lily.onetime.fail', LilyModule::t('Wrong one-time login token.'));
        $this->redirect(isset($redirectUrl)?$redirectUrl:Yii::app()->homeUrl);
    }


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

