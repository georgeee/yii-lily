<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DefaultControlle
 *
 * @author georgeee
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
    public function actionLogin() {
        $id_prefix = 'LAuthWidget-form-';
        if (isset($_POST['ajax']) && substr($_POST['ajax'], 0, strlen($id_prefix)) == $id_prefix) {
            $model = new LLoginForm;
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
        $model_new = false;

        $services = Yii::app()->eauth->getServices();
        if (Yii::app()->getRequest()->getQuery('service') != null) {
            $_services = $services;
            unset($_services['email']);
            $model = new LLoginForm('', array_keys($_services));
            $model->attributes = array('service' => Yii::app()->getRequest()->getQuery('service'), 'rememberMe' => Yii::app()->getRequest()->getQuery('rememberMe'));
        } else {
            $model = new LLoginForm('', array_keys($services));
            // if it is ajax validation request
            // collect user input data
            if (isset($_POST['LLoginForm'])) {
                $model->attributes = $_POST['LLoginForm'];
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
                // успешная авторизация
                if ($identity->authenticate()) {
                    Yii::app()->user->login($identity, $model->rememberMe ? LilyModule::instance()->sessionTimeout : 0);

                    // специальное перенаправления для корректного закрытия всплывающего окна
                    $authIdentity->redirect();
                } else {
                    // закрытие всплывающего окна и перенаправление на cancelUrl
                    $authIdentity->cancel();
                }
            }
            $authIdentity->cancel();
            // авторизация не удалась, перенаправляем на страницу входа
            //$this->redirect(array('user/login'));
            // display the login form
        }
        $this->render('login', array('model' => $model, 'services' => $services));
    }

    public function actionActivate() {
        $code = Yii::app()->getRequest()->getParam('code');
        $model = LilyModule::instance()->accountManager->performActivation($code);
        $this->render('activate', array('code' => $model, 'errorCode' => LilyModule::instance()->accountManager->errorCode));
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

}

?>
