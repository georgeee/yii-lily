<div>
    <form action="" method='post'  >
        <p class="note">Do you really want to merge your account with <?php echo CHtml::link($user->nameId, $this->createUrl('user/view', array('uid' => $user->uid))); ?>?</p>
        <br>
        <input type="submit" name="accept" value="Yes"/>
        <?php echo CHtml::link('Cancel', $this->createUrl('account/bind')); ?>
    </form>
</div>