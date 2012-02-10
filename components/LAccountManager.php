<?php
/**
 * LAccountManager class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LAccountManager is a component class, containing several common methods for account managment.
 * @package application.modules.lily.components
 */
class LAccountManager extends CApplicationComponent
{

    /**
     * @var string path to view of information letter (null - use the default content)
     */
    public $informationMailView = null;

    /**
     * @var string path to view of activation letter (null - use the default content)
     */
    public $activationMailView = null;

    /**
     * @var mixed callback for email subject of information letter
     */
    public $informationMailSubjectCallback = null;

    /**
     * @var mixed callback for email subject of activation letter
     */
    public $activationMailSubjectCallback = null;

    /**
     * @var boolean Whether to activate new account
     */
    public $activate = true;

    /**
     * @var boolean Whether to send mails
     */
    public $sendMail = true;

    /**
     * @var string Email to put it in mails (From subject)
     */
    public $adminEmail = 'admin@example.org';

    /**
     * @var string Route to activate action
     */
    public $activationUrl = 'lily/user/activate';

    /**
     * @var integer Timeout, after that activation will be rejected, even if code is clear
     */
    public $activationTimeout = 86400;


    /**
     * @var integer here will be put errorCode after executing some method (see method details)
     */
    public $errorCode;

    /**
     * This function just sends an email account registration information email (message with some information about result of activation, registration itself)
     *
     *
     * After the execution, you can take a look at errorCode property of accountManager
     * It can take following values:
     * <ul>
     * <li>1 - failed to send email</li>
     * <li>0 - everything is OK</li>
     * </ul>
     *
     * @param LAccount $email_account Email account model instance
     * @return boolean true, if mail was sent, false otherwise
     */
    public function sendInformationMail(LAccount $account)
    {
        $this->errorCode = 0;
        $message = new YiiMailMessage();
        if (isset($this->informationMailSubjectCallback))
            $subject = call_user_func($this->informationMailSubjectCallback, $account);
        if (!isset($subject) || !is_string($subject))
            $subject = Yii::t('ee', 'E-mail registration on {siteName}', array('{siteName}' => Yii::app()->name));
        $message->setSubject($subject);
        $message->view = $this->informationMailView;
        if (isset($this->informationMailView))
            $message->setBody(array('account' => $account), 'text/html');
        else
            $message->setBody(LilyModule::t('Your email was successfully registered on <a href="{siteUrl}">{siteName}</a>!', array('{siteUrl}' => Yii::app()->createAbsoluteUrl(''), '{siteName}' => Yii::app()->name)), 'text/html');
        $message->addTo($account->id);
        $message->from = $this->adminEmail;
        $recipient_count = Yii::app()->mail->send($message);
        if (LilyModule::instance()->enableLogging) {
            if ($recipient_count > 0)
                Yii::log('E-mail to ' . $account->id . ' was sent.', 'info', 'lily.mail.success');
            else
                Yii::log('Failed to send e-mail to ' . $account->id . '.', 'info', 'lily.mail.fail');
        }
        $this->errorCode = $recipient_count == 0;
        return $recipient_count > 0;
    }

    /**
     * This function just sends an activation email
     *
     *
     * After the execution, you can take a look at errorCode property of accountManager
     * It can take following values:
     * <ul>
     * <li>1 - failed to send email</li>
     * <li>0 - everything is OK</li>
     * </ul>
     *
     * @param LEmailAccountActivation $code Activation model instance
     * @param LUser $user_account model instance of the user account
     * (for purpose of using it in mail message) or NULL $code was provided for new account registration
     * @return boolean true, if mail was sent, false otherwise
     */
    public function sendActivationMail(LEmailAccountActivation $code, LUser $user_account = null)
    {
        $this->errorCode = 0;
        $message = new YiiMailMessage();
        if (isset($this->activationMailSubjectCallback))
            $subject = call_user_func($this->activationMailSubjectCallback, $code, $user_account);
        if (!isset($subject) || !is_string($subject))
            $subject = Yii::t('ee', 'E-mail registration on {siteName}', array('{siteName}' => Yii::app()->name));
        $message->setSubject($subject);
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
        $message->from = $this->adminEmail;
        $recipient_count = Yii::app()->mail->send($message);
        if (LilyModule::instance()->enableLogging) {
            if ($recipient_count > 0)
                Yii::log('E-mail to ' . $code->email . ' was sent.', CLogger::LEVEL_INFO, 'lily');
            else
                Yii::log('Failed sending e-mail to ' . $code->email . '.', CLogger::LEVEL_INFO, 'lily');
        }
        $this->errorCode = $recipient_count == 0;
        return $recipient_count > 0;
    }

    /**
     * This function performs an email account registration (see arguments)
     *
     *
     * After the execution, you can take a look at errorCode property of accountManager
     * It can take following values:
     * <ul>
     * <li>1 - failed to create DB record</li>
     * <li>2 - failed to send email</li>
     * <li>0 - everything is OK</li>
     * </ul>
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
     * @return mixed null if DB record was not created or Model instance of the created row, if DB insertion succeed
     */
    public function performRegistration($email, $password, $activate = null, $send_mail = null, LUser $user_account = null)
    {
        $this->errorCode = 0;
        if (!isset($activate))
            $activate = $this->activate;
        if (!isset($send_mail))
            $send_mail = $this->sendMail;

        if (!$activate) {
            $account = LAccount::create('email', $email, (object)array('password' => LilyModule::instance()->hash($password)), $user_account);
            if (isset($account)) {
                if ($send_mail) {
                    $result = self::sendInformationMail($account);
                    if (!$result) {
                        $this->errorCode = 2;
                    }
                }
                if (LilyModule::instance()->enableLogging)
                    Yii::log("performRegistration: created new account ($email, $password, " . ($user_account == null ? 'null' : $user_account->uid) . ").", CLogger::LEVEL_INFO, 'lily');
                return $account;
            } else {
                $this->errorCode = 1;
                if (LilyModule::instance()->enableLogging)
                    Yii::log("performRegistration: failed to create LAccount DB record ($email, $password, " . ($user_account == null ? 'null' : $user_account->uid) . ").", CLogger::LEVEL_INFO, 'lily');
                return null;
            }
        }

        $code = LEmailAccountActivation::create($email, $password, $user_account);
        if (!isset($code)) {
            $this->errorCode = 1;
            if (LilyModule::instance()->enableLogging)
                Yii::log("performRegistration: failed to create LEmailAccountActivation DB record ($email, $password, " . ($user_account == null ? 'null' : $user_account->uid) . ").", CLogger::LEVEL_INFO, 'lily');
            return null;
        }
        if ($send_mail) {
            $result = self::sendActivationMail($code, $user_account);
            if (!$result) {
                $this->errorCode = 2;
            }
            if (LilyModule::instance()->enableLogging)
                Yii::log("performRegistration: created new activation code $code->code ($email, $password, " . ($user_account == null ? 'null' : $user_account->uid) . ").", CLogger::LEVEL_INFO, 'lily');
        }
        return $code;
    }

    /**
     * This function performs an email account activation (see arguments)
     *
     *
     * After the execution, you can take a look at errorCode property of accountManager
     * It can take following values:
     * <ul>
     * <li>1 - failed to find code DB record</li>
     * <li>2 - activation code expired</li>
     * <li>3 - failed to create email account email record</li>
     * <li>4 - failed to send mail</li>
     * <li>0 - everything is OK</li>
     * </ul>
     *
     * If DB insertion succeed, it returns an instance of Model, refered to
     * created row (EEEmailAccount)
     * @param string $code Activation code, sent by email
     * @param bool $send_mail Whether to send an email to the user
     * (activation email if $activate is true, or information email if it's false)
     * @return LAccount null if DB record was not created or Model instance of the created row, if DB insertion succeed
     */
    public function performActivation($_code, $send_mail = null)
    {
        $this->errorCode = 0;
        if (!isset($send_mail))
            $send_mail = $this->sendMail;
        $code = LEmailAccountActivation::model()->findByAttributes(array('code' => $_code));
        if (!isset($code)) {
            $this->errorCode = 1;
            if (LilyModule::instance()->enableLogging)
                Yii::log("performActivation: failed to find code record code $_code.", CLogger::LEVEL_INFO, 'lily');
            return null;
        }
        if (time() > $code->created + $this->activationTimeout) {
            $code->delete();
            $this->errorCode = 2;
            if (LilyModule::instance()->enableLogging)
                Yii::log("performActivation: activation code $_code expired.", CLogger::LEVEL_INFO, 'lily');
            return null;
        }
        $account = LAccount::create('email', $code->email, (object)array('password' => $code->password), $code->uid);
        if (!isset($account)) {
            $this->errorCode = 3;
            if (LilyModule::instance()->enableLogging)
                Yii::log("performActivation: failed to create LAccount record (code $_code).", CLogger::LEVEL_INFO, 'lily');
            return null;
        }
        $code->delete();
        if (LilyModule::instance()->enableLogging)
            Yii::log("performActivation: new account by code $_code was created (aid $account->aid).", CLogger::LEVEL_INFO, 'lily');
        if ($send_mail) {
            $result = $this->sendInformationMail($account);
            if (!$result) {
                $this->errorCode = 4;
            }
        }
        return $account;
    }

    /**
     * Merges two users. Unlike to others methods of this class,
     * it doesn't provide any errorCode, It just throws an Exception =)
     *
     * @param mixed $oldUid Old user id
     * @param mixed $newUid New user id
     * @return boolean whether the operation succeed
     * @throws LException
     */
    public function merge($oldUid, $newUid)
    {
        $this->errorCode = 0;
        if (!isset($oldUid) || !isset($newUid))
            throw new LException("Both uids on merging must be set!");
        $oldUser = LUser::model()->findByPk($oldUid);
        $newUser = LUser::model()->findByPk($newUid);

        if (!isset($oldUser) || !isset($newUser))
            throw new LException("Both uids on merging must be set!");

        LilyModule::instance()->onUserMerge(new LMergeEvent($oldUid, $newUid));

        $oldUser->deleted = $newUid;

        if (!$oldUser->save()) {
            throw new LException("Failed to set old user instance to state=deleted");
        }

        Yii::app()->db->createCommand()
            ->update(LUser::model()->tableName(), array('deleted' => $newUid), 'deleted=:oldUid', array(':oldUid' => $oldUid));

        if (LilyModule::instance()->enableLogging)
            Yii::log("Merge: successfully appended $oldUid to $newUid.", CLogger::LEVEL_INFO, 'lily');
    }

}

