<?php
/* $errorCode:
 * <ul>
 * <li>1 - failed to find code DB record</li>
 * <li>2 - activation code expired</li>
 * <li>3 - failed to create email account email record</li>
 * <li>4 - failed to send mail</li>
 * <li>0 - everything is OK</li>
 * </ul>
 */
switch ($errorCode) {
    case 0:
    case 4:
        ?>
        Your account was successfully activated. Now you can login using your email.
        <?php
        break;
    case 1:
    case 2:
        ?>
        Your code is wrong or have expired. Please request a new one.
        <?php
        break;
    case 3:
        ?>
        Unexpected site error. Please contact site administrtor, if this message repeats.
        <?php
        break;
}
if (Yii::app()->getModule('lily')->accountManager->sendMail) {
    if ($errorCode == 0) {
        ?>
        An email with account details was sent to your email.
        <?php
    } else if ($errorCode == 4) {
        ?>
        An email with account details sending failed. Please contact site administrator.
        <?php
    }
}