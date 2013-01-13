<div class="authMethod <?php echo $data->service; ?>">
    <div class="authMethodIcon"></div>
    <div class="authMethodDesc"><?php
if (isset($data->data->url)) {
    echo CHtml::link($data->displayId, $data->data->url);
} else
    echo $data->displayId;
?>
    </div>
</div>
<div class="authMethodActions">
    <b><?php echo LilyModule::t("Actions:"); ?></b>
    <ul>
        <?php if ($data->service == 'email' && Yii::app()->user->checkAccess('editEmailAccount', array('uid' => $data->uid))) { ?>
            <li><a href="<?php echo $this->createUrl('edit', array('aid' => $data->aid)); ?>">
                    <?php echo LilyModule::t('Edit this account'); ?>
                </a></li>
            <?php
        }
        if (Yii::app()->user->checkAccess('editEmailAccount', array('uid' => $data->uid))) {
            ?>
            <li><a href="<?php echo $this->createUrl('delete', array('aid' => $data->aid)); ?>">
                    <?php echo LilyModule::t('Delete this account'); ?>
            </a></li>
            <?php
        }
        ?>
    </ul>
</div>