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

    public function actionIndex() {
        $this->actionView();
    }


    public function actionEdit() {
        
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
