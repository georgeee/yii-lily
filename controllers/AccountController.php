<?php

/**
 * AccountController class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * AccountController is a controller class, which manages with account bind/list/delete actions.
 *
 * @package application.modules.lily.controllers
 */
class AccountController extends Controller {
    /**
     * @var string the name of the default action
     */
    public $defaultAction='list';

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
    public static function allowListOwnAccessRule($user, $rule) {
        $uid = Yii::app()->request->getParam('uid', Yii::app()->user->id);
        return $uid == $user->id;
    }

    /**
     * Just an expression handler for accessRules()
     * @static
     * @param $user
     * @param $rule
     * @return bool
     */
    public static function allowModifyOwnAccessRule($user, $rule) {
        $aid = Yii::app()->request->getParam('aid');
        $account = LAccount::model()->findByPk($aid);
        if($account==null) return false;
        return $account->uid == $user->id;
    }

/**
 * Declares access rules for the controller
 * @return array access rules
 */
    public function accessRules() {
        return array(
            array('allow',
                'actions' => array('bind', 'merge', 'index'),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array('list'),
                'expression' => array(__CLASS__, 'allowListOwnAccessRule'),
                'users' => array('@'),
            ),
            array('allow',
                'actions' => array('delete', 'edit'),
                'expression' => array(__CLASS__, 'allowModifyOwnAccessRule'),
            ),
            array('allow',
                'actions' => array('list', 'delete', 'index', 'edit'),
                'roles' => array('admin'),
                'users' => array('@'),
            ),
            array('deny',
                'actions' => array('bind', 'delete', 'index', 'list', 'merge'),
            ),
        );
    }
/**
 * Bind action
 * @param string $service Service, which is being authenticated
 * @param boolean $rememberMe Whether to remember user
 */
    public function actionBind($service = null, $rememberMe = false) {
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
            $model->attributes = array('service' => $service, 'rememberMe' => $rememberMe);
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
/**
 * Merge action
 * @param string $merge_id Merge id string (randomly generated token)
 * @throws CHttpException 404 if merge_id is wrong
 */
    public function actionMerge($merge_id) {
        if (!isset(LilyModule::instance()->sessionData->merge[$merge_id]))
            throw new CHttpException(404, LilyModule::t('Incorrect merge id specified!'));
        $accept = Yii::app()->request->getPost('accept');
        if (isset($accept)) {
            LilyModule::instance()->accountManager->merge(LilyModule::instance()->sessionData->merge[$merge_id], Yii::app()->user->id);
            unset(LilyModule::instance()->sessionData->merge[$merge_id]);
            $this->redirect('list');
        }
        $this->render('merge', array('user' => LUser::model()->findByPk(LilyModule::instance()->sessionData->merge[$merge_id])));
    }
/**
 * List action
 */
    public function actionList() {
        $uid = Yii::app()->request->getQuery('uid', Yii::app()->user->id);
        $dataProvider = new CActiveDataProvider('LAccount', array(
                    'criteria' => array(
                        'condition' => 'uid=:uid AND hidden=0',
                        'params' => array(':uid' => $uid),
                        'order' => 'uid ASC',
                    ),
                    'pagination' => array(
                        'pageSize' => 20,
                    ),
                ));
        $this->render('list', array('accountProvider' => $dataProvider, 'user' => LUser::model()->findByPk($uid)));
    }
/**
 * Delete action
 * @param integer $aid Account Id
 * @param string $accept If $accept is set, we will act the deletion of account
 */
    public function actionDelete($aid, $accept = null) {
        $account = LAccount::model()->findByPk($aid);
        if (isset($accept)) {
            $account->delete();
            $this->redirect('list');
        }
        $this->render('delete', array('account' => $account));
    }
/**
 * Edit action
 * @param integer $aid Account Id
 * @throws CHttpException 404 if service of the account is 'email'
 */
    public function actionEdit($aid){
        $account = LAccount::model()->findByPk($aid);
        if($account->service == 'email'){
            $model = new LPasswordChangeForm;
            if (isset($_POST['ajax']) && $_POST['ajax'] === 'password-form') {
                echo CActiveForm::validate($model);
                Yii::app()->end();
            }
            if(isset($_POST['LPasswordChangeForm'])){
                $model->attributes = $_POST['LPasswordChangeForm'];
                if($model->validate()){
                    $account->data->password = LilyModule::instance()->hash($model->password);
                    $account->save();
                    $this->redirect('list');
                }
            }
            $this->render('edit', array('model' => $model, 'account'=>$account));
        }else throw new CHttpException(404);
    }
/**
 * Restore action
 */
    public function actionRestore(){
        $model = new LRestoreForm;
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'restore-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
        if(isset($_POST['LRestoreForm'])){
            $model->attributes = $_POST['LRestoreForm'];
            if($model->validate()){
                $result = LilyModule::instance()->accountManager->sendRestoreMail($model->account);
                if($result){
                    Yii::app()->user->setFlash('lily.restore.success', LilyModule::t('Message with restoration instructions was sent to your e-mail.'));
                }else{
                    Yii::app()->user->setFlash('lily.restore.fail', LilyModule::t('Failed to send e-mail with restoration instructions.'));
                }
                $this->redirect(array('user/login'));
            }
        }
        $this->render('restore', array('model' => $model));
    }
}

?>
