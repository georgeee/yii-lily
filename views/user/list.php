<h1>Users</h1>

<?php
if ($showDeleted) {
    echo CHtml::link(LilyModule::t('Hide deleted users'), array(''));
} else {
    echo CHtml::link(LilyModule::t('Show deleted users'), array('', 'showDeleted' => 1));
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
        'label' => LilyModule::t('Accounts list'),
        'class' => 'CLinkColumn',

        'urlExpression' => 'Yii::app()->urlManager->createUrl("' . LilyModule::instance()->route('account/list') . '", array(\'uid\'=>$data->uid))',
    ),
);

if ($showDeleted) {

    $columns[] = array(
        'name' => 'deleted',
        'type' => 'raw',
        'value' => function($data, $row, $obj)
        {
            switch ($data->deleted) {
                case 0:
                    return LilyModule::t("Not deleted");
                    break;
                case -1:
                    return LilyModule::t("Totally deleted");
                    break;
                default:
                    return LilyModule::t("Appended to user {user}",
                        array("{user}" => CHtml::link(CHtml::encode($data->reciever->name),
                            array("user/view", "uid" => $data->deleted)))
                    );
                    break;
            }
        },
    );
}

$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider' => $dataProvider,
    'columns' => $columns,
)); ?>