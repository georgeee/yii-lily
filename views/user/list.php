<?php
/* @var $this Controller */
$this->pageTitle = LilyModule::t('{appName} - Users', array('{appName}' => Yii::app()->name));
$this->breadcrumbs = array(
    LilyModule::t('Users')
);
?><h1><?php echo LilyModule::t('Users'); ?></h1>
<form id="userListOptions" action="">
    <?php if (Yii::app()->user->checkAccess('viewDeletedUser') || Yii::app()->user->checkAccess('viewUser')) { ?>
        <input type="checkbox" id="hideDeletedCheckbox" value="0" name="showDeleted" <?php echo $showDeleted ? "" : "checked"; ?>>
        <label for="hideDeletedCheckbox"><?php echo LilyModule::t("hide deleted users"); ?> </label>
        <br />
    <?php } ?>
    <?php if (Yii::app()->user->checkAccess('viewBannedUser') || Yii::app()->user->checkAccess('viewUser')) { ?>
        <input type="checkbox" id="hideBannedCheckbox" value="0" name="showBanned" <?php echo $showBanned ? "" : "checked"; ?>>
        <label for="hideBannedCheckbox"> <?php echo LilyModule::t("hide banned users"); ?> </label>
        <br />
    <?php } ?>
    <?php if (Yii::app()->user->checkAccess('viewAppendedUser') || Yii::app()->user->checkAccess('viewUser')) { ?>
        <input type="checkbox" id="hideAppendedCheckbox" value="0" name="showAppended" <?php echo $showAppended ? "" : "checked"; ?>>
        <label for="hideAppendedCheckbox"> <?php echo LilyModule::t("hide appended users"); ?></label> 
        <br />
    <?php } ?>
    <?php if (Yii::app()->user->checkAccess('viewActiveUser') || Yii::app()->user->checkAccess('viewUser')) { ?>
        <input type="checkbox" id="hideActiveCheckbox" value="0" name="showActive" <?php echo $showActive ? "" : "checked"; ?>>
        <label for="hideActiveCheckbox"> <?php echo LilyModule::t("hide active users"); ?></label> 
        <br />
    <?php } ?>
    <input type="submit" value="<?php echo LilyModule::t('Reload user list'); ?>">
</form>

<?php

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


$columns[] = array(
    'name' => 'state',
    'type' => 'raw',
    'value' => array('LUser', 'getStateLabel'),
);

$this->widget('zii.widgets.grid.CGridView', array(
    'dataProvider' => $dataProvider,
    'columns' => $columns,
));
?>