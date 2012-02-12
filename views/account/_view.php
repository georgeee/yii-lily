<div class="authMethod <?php echo $data->service; ?>">
    <div class="authMethodIcon"></div>
    <div class="authMethodDesc"><?php
        if (isset($data->data->url)) {
            echo CHtml::link($data->displayId, $data->data->url);
        } else echo $data->displayId;
        ?></div>
    <a href="<?php echo $this->createUrl('delete', array('aid' => $data->aid));?>">
        <div class="authMethodDelete"></div>
    </a>
    <?php if ($data->service == 'email') { ?>
    <a href="<?php echo $this->createUrl('edit', array('aid' => $data->aid));?>">
        <div class="authMethodEdit"></div>
    </a>
    <?php } ?>
</div>