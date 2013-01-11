<?php

/* @var $data LUser */

$attrs = array('uid', 'name');
$current_user = Yii::app()->user;
/* @var $current_user CWebUser */

if (true /* @TODO only admin access */) {
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
}

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
if ($data->state<=0 && true /* @TODO only admin access (or user, in case of deletion) */) {
    $value = '';
    if ($data->state != 0)
        $value.= '<li>' . CHtml::link(LilyModule::t("Activate user"), $this->createUrl("user/switch_state", array('uid' => $data->uid, 'mode' => 0))) . '</li>';
    if ($data->state != -1)
        $value .= '<li>' . CHtml::link(LilyModule::t("Delete user"), $this->createUrl("user/switch_state", array('uid' => $data->uid, 'mode' => -1))) . '</li>';
    if ($data->state != -2)
        $value.= '<li>' . CHtml::link(LilyModule::t("Ban user"), $this->createUrl("user/switch_state", array('uid' => $data->uid, 'mode' => -2))) . '</li>';
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