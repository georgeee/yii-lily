<?php

/**
 * Helper component for email account managment
 *
 * @author georgeee
 */
class LAccountManager extends CApplicationComponent {

    public $informationMailView = null; //'registrationFollowup';
    public $activationMailView = null; //'activationFollowup';
    public $activate = true;
    public $sendMail = true;
    public $adminEmail = 'admin@example.org';
    public $activationUrl = 'lily/user/activate';
    public $activationTimeout = 86400;

    /**
     * This function just sends an email account registration information email (message with some information about result of activation, registration itself)
     * @param LAccount $email_account Email account model instance
     * @return boolean true, if mail was sent, false otherwise
     */
    public function sendInformationMail(LAccount $account) {
        $message = new YiiMailMessage();
        $message->setSubject(Yii::t('ee', 'E-mail registration on {siteName}', array('{siteName}' => Yii::app()->name)));
        $message->view = $this->informationMailView;
        if (isset($this->informationMailView))
            $message->setBody(array('account' => $account), 'text/html');
        else
            $message->setBody(LilyModule::t('Your email was successfully registered on <a href="{siteUrl}">{siteName}</a>!', array('{siteUrl}' => Yii::app()->createAbsoluteUrl(''), '{siteName}' => Yii::app()->name)), 'text/html');
        $message->addTo($account->id);
        $message->from = $this->adminEmail;
        $recipient_count = Yii::app()->mail->send($message);
        if ($recipient_count > 0)
            Yii::log('E-mail to ' + $account->id + ' was sent.', 'info', 'lily.mail.success');
        else
            Yii::log('Failed sending e-mail to ' + $account->id + '.', 'info', 'lily.mail.fail');
        return $recipient_count > 0;
    }

    /**
     * This function just sends an activation email
     * @param LEmailAccountActivation $code Activation model instance
     * @param LUser $user_account model instance of the user account 
     * (for purpose of using it in mail message) or NULL $code was provided for new account registration
     * @return boolean true, if mail was sent, false otherwise
     */
    public function sendActivationMail(LEmailAccountActivation $code, LUser $user_account = null) {
        $message = new YiiMailMessage();
        $message->setSubject(Yii::t('ee', 'E-mail registration on {siteName}', array('{siteName}' => Yii::app()->name)));
        $message->view = $this->activationMailView;
        if (isset($this->activationMailView))
            $message->setBody(array('code' => $code, 'user_account' => $user_account), 'text/html');
        else
            $message->setBody(LilyModule::t('Your email was used in registration on <a href="{siteUrl}">{siteName}</a>.<br />
To activate it you have to go by this <a href="{activationUrl}">link</a></li> in your browser. <br />
If you haven\'t entered this email on {siteName}, than just ignore this message.<br />
<br />
Yours respectfully,<br />
administration of {siteName}.', array('{siteUrl}' => Yii::app()->createAbsoluteUrl(''), '{siteName}' => Yii::app()->name,
                        '{activationUrl}' => Yii::app()->createAbsoluteUrl($this->activationUrl, array('code' => $code->code)))), 'text/html');
        $message->addTo($code->email);
        $message->from = Yii::app()->params['adminEmail'];
        $recipient_count = Yii::app()->mail->send($message);
        if ($recipient_count > 0)
            Yii::log('E-mail to ' + $code->email + ' was sent.', 'info', 'lily.mail.success');
        else
            Yii::log('Failed sending e-mail to ' + $code->email + '.', 'info', 'lily.mail.fail');
        return $recipient_count > 0;
    }

    /**
     * This function performs an email account registration (see arguments)
     * 
     * If DB insertion succeed, it returns an instance of Model, refered to
     * created row (if $activate argument set to true - {@link LEmailAccountActivation}, otherwise - {@link LAccount})
     * @param string $email Email
     * @param string $password Password
     * @param boolean $activate Whether to use activation procedure or just skip it
     * @param boolean $send_mail Whether to send an email to the user
     * (activation email if $activate is true, or information email if it's false)
     * @param LUser $user_account model instance of the user account 
     * (for purpose of using it in mail message) or NULL $code was provided for new account registration
     * @param integer $error_code Reference to the variable, in which error code will be stored. It can take following values:
     * <ul>
     * <li>1 - failed to create DB record</li>
     * <li>2 - failed to send email</li>
     * <li>0 - everything is OK</li>
     * </ul>
     * @return mixed null if DB record was not created or Model instance of the created row, if DB insertion succeed
     */
    public function performRegistration($email, $password, $activate = null, $send_mail = null, LUser $user_account = null, &$error_code = null) {
        if (!isset($activate))
            $activate = $this->activate;
        if (!isset($send_mail))
            $send_mail = $this->sendMail;
        if ($error_code !== null)
            $error_code = 0;

        if (!$activate) {
            $account = LAccount::create('email', $email, (object) array('password' => Yii::app()->getModule('lily')->hash($password)), $user_account);
            if (isset($account)) {
                if ($send_mail) {
                    $result = self::sendInformationMail($account);
                    if (!$result) {
                        if ($error_code !== null)
                            $error_code = 2;
                    }
                }
                Yii::log("performRegistration: created new account ($email, $password, " . ($user_account == null ? 'null' : $user_account->uid) . ").", 'info', 'lily.performRegistration.success');
                return $account;
            }else {
                if ($error_code !== null)
                    $error_code = 1;
                Yii::log("performRegistration: failed to create LAccount DB record ($email, $password, " . ($user_account == null ? 'null' : $user_account->uid) . ").", 'info', 'lily.performRegistration.fail');
                return null;
            }
        }

        $code = LEmailAccountActivation::create($email, $password, $user_account);
        if (!isset($code)) {
            if ($error_code !== null)
                $error_code = 1;
            Yii::log("performRegistration: failed to create LEmailAccountActivation DB record ($email, $password, " . ($user_account == null ? 'null' : $user_account->uid) . ").", 'info', 'lily.performRegistration.fail');
            return null;
        }
        if ($send_mail) {
            $result = self::sendActivationMail($code, $user_account);
            if (!$result) {
                if ($error_code !== null)
                    $error_code = 2;
            }
            Yii::log("performRegistration: created new activation code $code->code ($email, $password, " . ($user_account == null ? 'null' : $user_account->uid) . ").", 'info', 'lily.performRegistration.success');
        }
        return $code;
    }

    /**
     * This function performs an email account activation (see arguments)
     * 
     * If DB insertion succeed, it returns an instance of Model, refered to
     * created row (EEEmailAccount)
     * @param string $code Activation code, sent by email
     * @param type $send_mail Whether to send an email to the user
     * (activation email if $activate is true, or information email if it's false)
     * @param integer $error_code Reference to the variable, in which error code will be stored. It can take following values:
     * <ul>
     * <li>1 - failed to find code DB record</li>
     * <li>2 - activation code expired</li>
     * <li>3 - failed to create email account email record</li>
     * <li>4 - failed to send mail</li>
     * <li>0 - everything is OK</li>
     * </ul>
     * @return LEmailAccount null if DB record was not created or Model instance of the created row, if DB insertion succeed
     */
    public function performActivation($_code, $send_mail = null, &$error_code = null) {
        if (!isset($send_mail))
            $send_mail = $this->sendMail;
        if ($error_code !== null)
            $error_code = 0;
        $code = LEmailAccountActivation::model()->findByAttributes(array('code' => $_code));
        if (!isset($code)) {
            if ($error_code !== null)
                $error_code = 1;
            Yii::log("performActivation: failed to find code record code $_code.", 'info', 'lily.performActivation.info');
            return null;
        }
        if (time() > $code->created + $this->activationTimeout) {
            $code->delete();
            if ($error_code !== null)
                $error_code = 2;
            Yii::log("performActivation: activation code $_code expired.", 'info', 'lily.performActivation.info');
            return null;
        }
        $account = LAccount::create('email', $code->email, (object) array('password' => $code->password), $code->uid);
        if (!isset($account)) {
            if ($error_code !== null)
                $error_code = 3;
            Yii::log("performActivation: failed to create LAccount record (code $_code).", 'info', 'lily.performActivation.fail');
            return null;
        }
        $code->delete();
        Yii::log("performActivation: new account by code $_code was created (aid $account->aid).", 'info', 'lily.performActivation.success');
        if ($send_mail) {
            $result = $this->sendInformationMail($account);
            if (!$result) {
                if ($error_code !== null)
                    $error_code = 4;
            }
        }
        return $account;
    }

    public function merge($with_uid, $uid = null) {
        if (!isset($with_uid))
            return false;
        if (is_object($with_uid))
            $with_uid = $with_uid->uid;

        if (!isset($uid))
            $uid = Yii::app()->getModule('lily')->user;
        if (!isset($uid))
            return false;
        if (!is_object($uid))
            $uid = LUser::model()->findByPk($uid);

        if (!$uid->appendAccountsFromUid($with_uid))
            return false;
        return LUser::model()->findByPk($with_uid)->delete();
    }

}

?>
