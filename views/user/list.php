<h1>Users</h1>

<?php
if ($showState) {
    echo CHtml::link(LilyModule::t('Hide deleted (banned) users'), array(''));
} else {
    echo CHtml::link(LilyModule::t('Show deleted (banned) users'), array('', 'showState' => 1));
}

$columns = array(
    array('name' => 'uid'),
    array(
        'name' => 'name',
        'type' => 'raw',
        'value' => 'CHtml::link(CHtml::encode($data->name),array("user/view","uid"=>$data->uid))',
    ),
    array(
        'header' => LilyModule::t('Accounts'),
        'label' => LilyModule::t('Account list'),
        'class' => 'CLinkColumn',

        'urlExpression' => 'Yii::app()->urlManager->createUrl("' . LilyModule::instance()->route('account/list') . '", array(\'uid\'=>$data->uid))',
    ),
);

if ($showState) {

    $columns[] = array(
        'name' => 'state',
        'type' => 'raw',
        'value' => array('LUser', 'getStateLabel'),
    );
}

$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider' => $dataProvider,
    'columns' => $columns,
)); ?>