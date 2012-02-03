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

    public function actionIndex() {
        $this->render('/user/index');
    }

    public function actionLogin() {
        $id_prefix = 'LAuthWidget-form-';
        if(isset($_POST['ajax'])) Yii::log ("Ajax post: {$_POST['ajax']}", 'info', 'lily.UserController');
        if (isset($_POST['ajax']) && substr($_POST['ajax'], 0, strlen($id_prefix))==$id_prefix) {
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
            }else $model_new = true;
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
                    Yii::app()->user->login($identity, $model->rememberMe?Yii::app()->getModule('lily')->sessionTimeout:0);

                    // специальное перенаправления для корректного закрытия всплывающего окна
                    $authIdentity->redirect();
                } else {
                    // закрытие всплывающего окна и перенаправление на cancelUrl
                    $authIdentity->cancel();
                }
            }

            // авторизация не удалась, перенаправляем на страницу входа
            //$this->redirect(array('user/login'));
            // display the login form
        }
        $this->render('login', array('model' => $model, 'services' => $services));
    }
    
    public function actionActivate(){
        $code = Yii::app()->getRequest()->getParam('code');
        $errorCode = -1;
        $model = Yii::app()->getModule('lily')->accountManager->performActivation($code, null, $errorCode);
        $this->render('activate', array('code' => $model, 'errorCode' => $errorCode));
    }
    
}

?>
