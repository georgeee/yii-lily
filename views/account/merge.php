<h1>Merge user accounts</h1>

<div>
    <form action="" method='post'  >
        <p class="note">Do you really want to merge your user account with <?php echo CHtml::link($user->nameId, $this->createUrl('user/view', array('uid' => $user->uid))); ?>?</p>
        <br>
        <?php if ($banWarning) { ?>
            <p class="warning"><?php echo LilyModule::t("Warning! User, you're merging with is banned. If you'll submit this form, your account would be banned also."); ?></p>
        <?php } ?>
        <?php if ($deleteWarning) { ?>
            <p class="warning"><?php echo LilyModule::t("Warning! User, you're merging with is deleted. If you'll submit this form, your account would be deleted also."); ?></p>
        <?php } ?>
        <input type="submit" name="accept" value="Yes"/>
        <?php echo CHtml::link('Cancel', $this->createUrl('account/bind')); ?>
    </form>
</div>