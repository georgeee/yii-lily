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
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

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
            }
        }
        if ($model->validate() && isset($model->service)) {
            $authIdentity = Yii::app()->eauth->getIdentity($model->service);
            $authIdentity->redirectUrl = Yii::app()->user->returnUrl;
            $authIdentity->cancelUrl = $this->createAbsoluteUrl('user/login');

                $authIdentity->email = $model->email;
                $authIdentity->password = $model->password;
        Yii::trace(print_r($authIdentity,1));
            if ($authIdentity->authenticate()) {
                $identity = new EAuthUserIdentity($authIdentity);
                // успешная авторизация
                if ($identity->authenticate()) {
                    Yii::app()->user->login($identity, 7 * 24 * 60 * 60);

                    // специальное перенаправления для корректного закрытия всплывающего окна
                    $authIdentity->redirect();
                } else {
                    // закрытие всплывающего окна и перенаправление на cancelUrl
                    $authIdentity->cancel();
                }
            }else Yii::app()->user->setFlash('ghgh', 'Fuck');

            // авторизация не удалась, перенаправляем на страницу входа
            //$this->redirect(array('user/login'));
            // display the login form
        }
        $this->render('login', array('model' => $model, 'services' => $services));
    }

}

?>
