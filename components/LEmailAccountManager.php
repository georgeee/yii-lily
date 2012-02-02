<?php

/**
 * Helper component for email account managment
 *
 * @author georgeee
 */
class LEmailAccountManager extends CApplicationComponent {

    public $informationMailView = null; //'registrationFollowup';
    public $activationMailView = null; //'activationFollowup';
    public $activate = true;
    public $sendMail = true;
    public $adminEmail = 'admin@example.org';
    public $activationUrl = 'lily/email/activation';
    public $activationTimeout = 86400;

    /**
     * This function just sends an email account registration information email (message with some information about result of activation, registration itself)
     * @param LEmailAccount $email_account Email account model instance
     * @param boolean $new_account if it's registration of new user account, or just binding email to existing one
     * @param LUser $user_account if $new_account is false, here should be put model instance of the user account (for purpose of using it in mail message)
     * @return boolean true, if mail was sent, false otherwise
     */
    public function sendInformationMail(LEmailAccount $email_account, $new_account = true, LUser $user_account = null) {
        $message = new YiiMailMessage();
        $message->setSubject(Yii::t('ee', 'E-mail registration on {siteName}', array('{siteName}' => Yii::app()->name)));
        $message->view = $this->informationMailView;
        if (isset($this->informationMailView))
            $message->setBody(array('email_account' => $email_account, 'new_account' => $new_account, 'user_account' => $user_account), 'text/html');
        else
            $message->setBody(LilyModule::t('Your email was successfully registered on <a href="{siteUrl}">{siteName}</a>!', array('{siteUrl}' => Yii::app()->createAbsoluteUrl(''), '{siteName}' => Yii::app()->name)), 'text/html');
        $message->addTo($email_account->email);
        $message->from = $this->adminEmail;
        $recipient_count = Yii::app()->mail->send($message);
        return $recipient_count > 0;
    }

    /**
     * This function just sends an activation email
     * @param LEmailAccountActivation $code Activation model instance
     * @param boolean $new_account if it's registration of new user account, or just binding email to existing one
     * @param LUser $user_account if $new_account is false, here should be put model instance of the user account (for purpose of using it in mail message)
     * @return boolean true, if mail was sent, false otherwise
     */
    public function sendActivationMail(LEmailAccountActivation $code, $new_account = true, LUser $user_account = null) {
        $message = new YiiMailMessage();
        $message->setSubject(Yii::t('ee', 'E-mail registration on {siteName}', array('{siteName}' => Yii::app()->name)));
        $message->view = $this->activationMailView;
        if (isset($this->activationMailView))
            $message->setBody(array('code' => $code, 'new_account' => $new_account, 'user_account' => $user_account), 'text/html');
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
        return $recipient_count > 0;
    }

    /**
     * This function performs an email account registration (see arguments)
     * 
     * If DB insertion succeed, it returns an instance of Model, refered to
     * created row (if $activate argument set to true - {@link EEEmailAccountActivation}, otherwise - {@link EEEmailAccount})
     * @param string $email Email
     * @param string $password Password
     * @param boolean $activate Whether to use activation procedure or just skip it
     * @param boolean $send_mail Whether to send an email to the user
     * (activation email if $activate is true, or information email if it's false)
     * @param boolean $new_account if it's registration of new user account, or just binding email to existing one
     * @param LUser $user_account if $new_account is false, here should be put model instance of the user account (for purpose of using it in mail message)
     * @param integer $error_code Reference to the variable, in which error code will be stored. It can take following values:
     * <ul>
     * <li>1 - failed to create DB record</li>
     * <li>2 - failed to send email</li>
     * <li>0 - everything is OK</li>
     * </ul>
     * @return mixed null if DB record was not created or Model instance of the created row, if DB insertion succeed
     */
    public function performRegistration($email, $password, $activate = null, $send_mail = null, $new_account = true, LUser $user_account = null, &$error_code = null) {
        if (!isset($activate))
            $activate = $this->activate;
        if (!isset($send_mail))
            $send_mail = $this->sendMail;
        if ($error_code !== null)
            $error_code = 0;

        if (!$activate) {
            $account = LEmailAccount::create($email, $password, true);
            if (isset($account)) {
                if ($send_mail) {
                    $result = self::sendInformationMail($account, $new_account, $user_account);
                    if (!$result) {
                        if ($error_code !== null)
                            $error_code = 2;
                    }
                }
                return $account;
            }
            else {
                if ($error_code !== null)
                    $error_code = 1;
                return null;
            }
        }

        $code = LEmailAccountActivation::create($email, $password);
        if (!isset($code)) {
            if ($error_code !== null)
                $error_code = 1;
            return null;
        }
        if ($send_mail) {
            $result = self::sendActivationMail($code, $new_account, $user_account);
            if (!$result) {
                if ($error_code !== null)
                    $error_code = 2;
            }
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
     * @param boolean $new_account if it's registration of new user account, or just binding email to existing one
     * @param LUser $user_account if $new_account is false, here should be put model instance of the user account (for purpose of using it in mail message)
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
    public function performActivation($code, $send_mail = true, $new_account = true, LUser $user_account = null, &$error_code = null) {
        if (!isset($send_mail))
            $send_mail = $this->sendMail;
        if ($error_code !== null)
            $error_code = 0;
        $code = LEmailAccountActivation::model()->findByAttributes(array('code' => $code));
        if (!isset($code)) {
            if ($error_code !== null)
                $error_code = 1;
            return null;
        }
        if (time() > $code->created + $this->activationTimeout) {
            $code->delete();
            if ($error_code !== null)
                $error_code = 2;
            return null;
        }
        $account = LEmailAccount::create($code->email, $code->password);
        if (!isset($account)) {
            if ($error_code !== null)
                $error_code = 3;
            return null;
        }
        $code->delete();
        if ($send_mail) {
            $result = self::sendInformationMail($account, $new_account, $user_account);
            if (!$result) {
                if ($error_code !== null)
                    $error_code = 4;
            }
        }
        return $account;
    }

}

?>
