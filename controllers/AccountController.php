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
class AccountController extends Controller {

    public function filters() {
        return array(
            'accessControl',
        );
    }

    public function accessRules() {
        return array(
            array('allow',
                'actions' => array('bind', 'merge', 'index'),
                'users' => array('@'),
            ),
            
            array('allow',
                'actions' => array('list'),
                'expression' => function($user, $rule) {
                    $uid = Yii::app()->request->getParam('uid', Yii::app()->user->id);
                    return $uid == $user->id;
                },
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array('delete'),
                'expression' => function($user, $rule) {
                    $aid = Yii::app()->request->getParam('aid');
                    if($aid==null) return false;
                    return LAccount::model()->findByPk($aid)->uid == $user->id;
                },
            ),
            array('allow',
                'actions' => array('list', 'delete', 'index'),
                'roles' => array('admin'),
            ),
            array('deny',
                'actions' => array('bind', 'delete', 'index', 'list', 'merge'),
            ),
        );
    }

    public function actionIndex() {
        $this->redirect($this->createUrl('list'));
    }

    public function actionBind() {
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
            $authIdentity->cancelUrl = $this->createAbsoluteUrl('account/bind');
            $user = LilyModule::instance()->user;
            $aids = $user->accountIds;
            if ($model->service == 'email') {
                $authIdentity->email = $model->email;
                $authIdentity->password = $model->password;
                $authIdentity->user = $user;
            }
            if ($authIdentity->authenticate()) {
                $identity = new LUserIdentity($authIdentity);
                $identity->user = $user;
                // успешная авторизация
                if ($identity->authenticate()) {
                    if ($identity->account->uid == $user->uid) {
                        if (!in_array($identity->account->aid, $aids))
                            Yii::app()->user->setFlash('lily.account.bind.success', LilyModule::t('Account was successfully binded.'));
                        else
                            Yii::app()->user->setFlash('lily.account.bind.already', LilyModule::t('Account is already binded to current user.'));
                        $authIdentity->redirect();
                    } else {
                        if (LilyModule::instance()->enableUserMerge) {
                            $merge_id = LilyModule::instance()->generateRandomString();
                            if (!isset(LilyModule::instance()->sessionData->merge))
                                LilyModule::instance()->sessionData->merge = array();
                            LilyModule::instance()->sessionData->merge[$merge_id] = $identity->account->uid;
                            LilyModule::instance()->session->save();
                            Yii::app()->user->setFlash('lily.account.merge', LilyModule::t('You\'ve tried to bind an account, that\'s already binded to {user}.', array('{user}' => CHtml::link($identity->account->user->nameId, $this->createUrl('user/view', array('uid' => $identity->account->uid))))));

                            $authIdentity->redirect($this->createUrl('account/merge', array('merge_id' => $merge_id)));
                        }else {
                            Yii::app()->user->setFlash('lily.account.bound', LilyModule::t('Account is already bound to another user.'));
                            $authIdentity->cancel();
                        }
                    }
                } else {
                    Yii::app()->user->setFlash('lily.account.fail', LilyModule::t('Failed to authenticate account.'));
                    // закрытие всплывающего окна и перенаправление на cancelUrl
                    $authIdentity->cancel();
                }
            } else {
                Yii::app()->user->setFlash('lily.account.fail', LilyModule::t('Failed to authenticate account.'));
                // закрытие всплывающего окна и перенаправление на cancelUrl
                $authIdentity->cancel();
            }
        }
        $this->render('bind', array('model' => $model, 'services' => $services));
    }

    public function actionMerge() {
        $merge_id = Yii::app()->request->getQuery('merge_id');
        if (!isset($merge_id) || !isset(LilyModule::instance()->sessionData->merge[$merge_id]))
            throw new CHttpException(404, Yii::t('lily.account.merge', 'Incorrect merge id specified!'));
        $accept = Yii::app()->request->getPost('accept');
        if (isset($accept)) {
            $result = LilyModule::instance()->accountManager->merge(LilyModule::instance()->sessionData->merge[$merge_id]);
            unset(LilyModule::instance()->sessionData->merge[$merge_id]);
            if ($result)
                $this->redirect('list');
            else {
                Yii::app()->user->setFlash('lily.merge.fail', LilyModule::t('Failed to merge user accounts.'));
                $this->redirect('bind');
            }
        }
        $this->render('merge', array('user' => LUser::model()->findByPk(LilyModule::instance()->sessionData->merge[$merge_id])));
    }

    public function actionList() {
        $uid = Yii::app()->request->getQuery('uid', Yii::app()->user->id);
        $dataProvider = new CActiveDataProvider('LAccount', array(
                    'criteria' => array(
                        'condition' => 'uid=:uid',
                        'params' => array(':uid' => $uid),
                        'order' => 'uid ASC',
                    ),
                    'pagination' => array(
                        'pageSize' => 20,
                    ),
                ));
        $this->render('list', array('accountProvider' => $dataProvider, 'user' => LUser::model()->findByPk($uid)));
    }

    public function actionDelete() {
        $aid = Yii::app()->request->getQuery('aid');
        if (!isset($aid))
            throw new CHttpException(404, Yii::t('lily.account.delete', 'Account id not specified!'));
        $account = LAccount::model()->findByPk($aid);
        if (!isset($account))
            throw new CHttpException(404, Yii::t('lily.account.delete', 'Incorrect account id specified!'));
        $accept = Yii::app()->request->getPost('accept');
        if (isset($accept)) {
            $account->delete();
            $this->redirect('list');
        }
        $this->render('delete', array('account' => $account));
    }

}

?>
