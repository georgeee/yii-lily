<?php

/* @var $data LUser */

$attrs = array('uid', 'name');

$attrs[] = array(
    'name' => 'state',
    'type' => 'raw',
    'value' => LUser::getStateLabel($data),
);

$attrs[] = array(
    'name' => 'inited',
    'type' => 'raw',
    'value' => LilyModule::t($data->inited ? "Inited" : "Not inited"),
);
if (Yii::app()->user->checkAccess('listAccounts', array('uid' => $data->uid))) {
    $attrs[] = array(
        'label' => LilyModule::t('Accounts'),
        'type' => 'raw',
        'value' => CHtml::link(LilyModule::t("Go to account list"), $this->createUrl("account/list", array('uid' => $data->uid))),
    );
    $attrs[] = array(
        'type' => 'raw',
        'value' => $this->widget('zii.widgets.grid.CGridView', array(
            'dataProvider' => new CActiveDataProvider('LAccount', array(
                'criteria' => array(
                    'condition' => 'uid=:uid AND hidden=0',
                    'params' => array(':uid' => $data->uid),
                    'order' => 'created ASC',
                ),
            )),
            'enablePagination' => false,
            'summaryText' => '',
            'columns' => array(
                array(
                    'name' => 'service',
                    'value' => '$data->serviceName',
                ),
                array(
                    'name' => 'id',
                    'value' => '$data->displayId',
                ),
                array(
                    'name' => 'created',
                    'value' => 'Yii::app()->dateFormatter->formatDateTime($data->created)',
                ),
            ),
                ), true)
            ,
    );
}
if ($data->state <= LUser::ACTIVE_STATE) {
    $value = '';
    if ($data->state != LUser::ACTIVE_STATE && (
            ($data->state == LUser::DELETED_STATE && Yii::app()->user->checkAccess('restoreUser', array('uid' => $data->uid)))
            || ($data->state == LUser::BANNED_STATE && Yii::app()->user->checkAccess('unbanUser', array('uid' => $data->uid)))
            ))
        $value.= '<li>' . CHtml::link(LilyModule::t("Activate user"), $this->createUrl("user/switch_state", array('uid' => $data->uid, 'mode' => LUser::ACTIVE_STATE))) . '</li>';
    if ($data->state != LUser::DELETED_STATE && (
            ($data->state == LUser::ACTIVE_STATE && Yii::app()->user->checkAccess('deleteUser', array('uid' => $data->uid)))
            || ($data->state == LUser::BANNED_STATE && Yii::app()->user->checkAccess('unbanUser', array('uid' => $data->uid)) && Yii::app()->user->checkAccess('deleteUser', array('uid' => $data->uid)))
            ))
        $value .= '<li>' . CHtml::link(LilyModule::t("Delete user"), $this->createUrl("user/switch_state", array('uid' => $data->uid, 'mode' => LUser::DELETED_STATE))) . '</li>';
    if ($data->state != LUser::BANNED_STATE && !Yii::app()->authManager->checkAccess('unbanUser', $data->uid, array('uid' => $data->uid)) && (
            ($data->state == LUser::ACTIVE_STATE && Yii::app()->user->checkAccess('banUser', array('uid' => $data->uid)))
            || ($data->state == LUser::DELETED_STATE && Yii::app()->user->checkAccess('restoreUser', array('uid' => $data->uid)) && Yii::app()->user->checkAccess('banUser', array('uid' => $data->uid)))
            ))
        $value.= '<li>' . CHtml::link(LilyModule::t("Ban user"), $this->createUrl("user/switch_state", array('uid' => $data->uid, 'mode' => LUser::BANNED_STATE))) . '</li>';
    if(!empty($value))
    $attrs[] = array(
        'type' => 'raw',
        'label' => LilyModule::t('Actions'),
        'value' => '<ul>' . $value . '</ul>'
    );
}
$this->widget('zii.widgets.CDetailView', array(
    'data' => $data,
    'itemCssClass' => array(),
    'attributes' => $attrs,
));