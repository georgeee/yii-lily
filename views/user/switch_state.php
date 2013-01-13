
<h1>Switch user account state</h1>
<form action="" method="POST">
    <p>
        <?php
        /* @var $this UserController */
        switch ($mode) {
            case LUser::ACTIVE_STATE: $txt = LilyModule::t(!$self ? "Do you really want to activate user {user} account?" : "Do you really want to activate your account?", array('{user}' => $user->name));
                break;
            case LUser::DELETED_STATE: $txt = LilyModule::t(!$self ? "Do you really want to delete user {user} account?" : "Do you really want to delete your account?", array('{user}' => $user->name));
                break;
            case LUser::BANNED_STATE: $txt = LilyModule::t("Do you really want to ban user {user} account?", array('{user}' => $user->name));
                break;
        }
        echo CHtml::encode($txt);
        ?>
    </p>
    <input type="hidden" name="approved" value="1" />
    <div>
        <a href="<?php
        echo $this->createUrl(($user->id==Yii::app()->user->id&&$user->state==LUser::DELETED_STATE)?'logout':'view', array('uid' => $user->uid));
        ?>"><?php echo CHtml::encode(LilyModule::t("Cancel")); ?></a>
        <input type="submit" value=" <?php
           switch ($mode) {
               case LUser::ACTIVE_STATE: $txt = LilyModule::t(!$self ? "Activate user" : "Activate my user account");
                   break;
               case LUser::DELETED_STATE: $txt = LilyModule::t(!$self ? "Delete user" : "Delete my user account");
                   break;
               case LUser::BANNED_STATE: $txt = LilyModule::t("Ban user");
                   break;
           }
           echo CHtml::encode($txt);
        ?> " />
    </div>
</form>