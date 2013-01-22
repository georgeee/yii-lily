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
class LAccountManager extends CApplicationComponent {

    /**
     * @var string path to view of information letter (null - use the default content)
     */
    public $informationMailView = null;

    /**
     * @var string path to view of activation letter (null - use the default content)
     */
    public $activationMailView = null;

    /**
     * @var string path to view of restore letter (null - use the default content)
     */
    public $restoreMailView = null;

    /**
     * @var mixed callback for email subject of information letter
     */
    public $informationMailSubjectCallback = null;

    /**
     * @var mixed callback for email subject of activation letter
     */
    public $activationMailSubjectCallback = null;

    /**
     * @var mixed callback for email subject of restoration letter
     */
    public $restoreMailSubjectCallback = null;

    /**
     * @var boolean should we register new e-mail account on the fly, or it's necessary to do it on registration page
     */
    public $registerEmail = true;

    /**
     * @var boolean should we automaticaly log user in after e-mail registration
     */
    public $loginAfterRegistration = true;

    /**
     * @var boolean Whether to activate new account
     */
    public $activate = true;

    /**
     * @var boolean Whether to send mails
     */
    public $sendMail = true;

    /**
     * @var string Email to put it in mails (From field)
     */
    public $adminEmail = 'admin@example.org';

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
     * @param LAccount $account Account model instance
     * @return boolean true, if mail was sent, false otherwise
     */
    public function sendInformationMail(LAccount $account) {
        $this->errorCode = 0;
        $message = new YiiMailMessage();
        if (isset($this->informationMailSubjectCallback))
            $subject = call_user_func($this->informationMailSubjectCallback, $account);
        if (!isset($subject) || !is_string($subject))
            $subject = LilyModule::t('E-mail registration on {siteName}', array('{siteName}' => Yii::app()->name));
        $message->setSubject($subject);
        $message->view = $this->informationMailView;
        if (isset($this->informationMailView))
            $message->setBody(array('account' => $account), 'text/html');
        else
            $message->setBody(LilyModule::t('Your email was successfully registered on <a href="{siteUrl}">{siteName}</a>!', array('{siteUrl}' => Yii::app()->createAbsoluteUrl(Yii::app()->homeUrl), '{siteName}' => Yii::app()->name)), 'text/html');
        $message->addTo($account->id);
        $message->from = $this->adminEmail;
        $recipient_count = Yii::app()->mail->send($message);
        if ($recipient_count > 0)
            Yii::log('E-mail to ' . $account->id . ' was sent.', CLogger::LEVEL_INFO, 'lily');
        else
            Yii::log('Failed to send e-mail to ' . $account->id . '.', CLogger::LEVEL_WARNING, 'lily');
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
     * @param LUser $user model instance of the user account
     * (for purpose of using it in mail message) or NULL $code was provided for new account registration
     * @return boolean true, if mail was sent, false otherwise
     */
    public function sendActivationMail(LEmailAccountActivation $code, LUser $user = null) {
        $this->errorCode = 0;
        $message = new YiiMailMessage();
        if (isset($this->activationMailSubjectCallback))
            $subject = call_user_func($this->activationMailSubjectCallback, $code, $user);
        if (!isset($subject) || !is_string($subject))
            $subject = LilyModule::t('E-mail registration on {siteName}', array('{siteName}' => Yii::app()->name));
        $message->setSubject($subject);
        $message->view = $this->activationMailView;
        if (isset($this->activationMailView))
            $message->setBody(array('code' => $code, 'user' => $user), 'text/html');
        else
            $message->setBody(LilyModule::t('Your email was used in registration on <a href="{siteUrl}">{siteName}</a>.<br />
To activate it you have to go by this <a href="{activationUrl}">link</a></li> in your browser. <br />
If you haven\'t entered this email on {siteName}, than just ignore this message.<br />
<br />
Yours respectfully,<br />
administration of {siteName}.', array('{siteUrl}' => Yii::app()->createAbsoluteUrl(Yii::app()->homeUrl), '{siteName}' => Yii::app()->name,
                        '{activationUrl}' => Yii::app()->createAbsoluteUrl('/'.LilyModule::route('user/activate'), array('code' => $code->code)))), 'text/html');
        $message->addTo($code->email);
        $message->from = $this->adminEmail;
        $recipient_count = Yii::app()->mail->send($message);
        if ($recipient_count > 0)
            Yii::log('E-mail to ' . $code->email . ' was sent.', CLogger::LEVEL_INFO, 'lily');
        else
            Yii::log('Failed sending e-mail to ' . $code->email . '.', CLogger::LEVEL_WARNING, 'lily');
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
     * <li>1 - failed to send email</li>
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
     * @param boolean $rememberMe Whether to remember user, authenticating him after activation
     * @return LEmailAccountActivation|LAccount created activation code or account instance if activation set to off
     * @throws CDbException
     */
    public function performRegistration($email, $password, $activate = null, $send_mail = null, LUser $user_account = null, $rememberMe = false) {
        $this->errorCode = 0;
        if (!isset($activate))
            $activate = $this->activate;
        if (!isset($send_mail))
            $send_mail = $this->sendMail;

        if (!$activate) {
            $account = LAccount::create('email', $email, (object) array('password' => LilyModule::instance()->hash($password)), $user_account);
            if (isset($account)) {
                if ($send_mail) {
                    $result = self::sendInformationMail($account);
                    if (!$result) {
                        $this->errorCode = 1;
                    }
                }
                Yii::log("performRegistration: created new account ($email, $password, " . ($user_account == null ? 'null' : $user_account->uid) . ").", CLogger::LEVEL_INFO, 'lily');
                return $account;
            } else {
                throw new CDbException("failed to create account");
            }
        }

        $code = LEmailAccountActivation::create($email, $password, $user_account, $rememberMe);
        if (!isset($code)) {
            throw new CDbException("failed to create LEmailAccountActivation DB record ($email, $password, " . ($user_account == null ? 'null' : $user_account->uid) . ").");
        }
        if ($send_mail) {
            $result = self::sendActivationMail($code, $user_account);
            if (!$result) {
                $this->errorCode = 1;
            }
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
     * <li>3 - failed to send mail</li>
     * <li>0 - everything is OK</li>
     * </ul>
     *
     * If DB insertion succeed, it returns an instance of Model, refered to
     * created row (LAccount)
     * @param string $_code Activation code, sent by email
     * @param bool $send_mail Whether to send an email to the user
     * (activation email if $activate is true, or information email if it's false)
     * @return LAccount null if DB record was not created or Model instance of the created row, if DB insertion succeed
     */
    public function performActivation($_code, $send_mail = null) {
        $this->errorCode = 0;
        if (!isset($send_mail))
            $send_mail = $this->sendMail;
        $code = LEmailAccountActivation::model()->findByAttributes(array('code' => $_code));
        if (!isset($code)) {
            $this->errorCode = 1;
            Yii::log("performActivation: failed to find code record code $_code.", CLogger::LEVEL_INFO, 'lily');
            return null;
        }
        if (time() > $code->created + $this->activationTimeout) {
            if (!$code->delete())
                throw new CDbException("can't delete code");
            $this->errorCode = 2;
            Yii::log("performActivation: activation code $_code expired.", CLogger::LEVEL_INFO, 'lily');
            return null;
        }
        $account = LAccount::create('email', $code->email, (object) array('password' => $code->password), $code->uid);
        if (!isset($account)) {
            throw new CDbException("performActivation: failed to create LAccount record (code $_code).");
        }
        if (LEmailAccountActivation::model()->deleteAllByAttributes(array('email' => $code->email)) < 1) {
            throw new CDbException("performActivation: failed to delete all codes of $code->email.");
        }
        Yii::log("performActivation: new account by code $_code was created (aid $account->aid).", CLogger::LEVEL_INFO, 'lily');
        if ($send_mail) {
            $result = $this->sendInformationMail($account);
            if (!$result) {
                $this->errorCode = 3;
            }
        }
        return $account;
    }

    /**
     * Merges two users. Unlike the others methods of this class,
     * it doesn't provide any errorCode, It just throws an Exception =)
     *
     * @param integer $oldUid Old user id
     * @param integer $newUid New user id
     * @param integer $aid Account id (through which this merging is performed)
     * @return boolean whether the operation succeed
     * @throws LException, CDbException
     */
    public function merge($oldUid, $newUid, $aid = null) {
        $this->errorCode = 0;
        if (!isset($oldUid))
            throw new LException("oldUid argument is not set");
        if (!isset($newUid))
            throw new LException("newUid argument is not set");
        $oldUser = LUser::model()->findByPk($oldUid);
        $newUser = LUser::model()->findByPk($newUid);

        if (!isset($oldUser))
            throw new LException("no user found with uid $oldUid");
        if (!isset($newUser))
            throw new LException("no user found with uid $newUid");

        LilyModule::instance()->onUserMerge(new LMergeEvent($oldUid, $newUid, $aid));

        $newUser->state = $oldUser->state;
        $oldUser->state = $newUid;

        if (!$oldUser->save()) {
            throw new CDbException("Failed to save old user instance");
        }
        if (!$newUser->save()) {
            throw new CDbException("Failed to save new user instance");
        }

        $auth = Yii::app()->authManager;
        $items = $auth->getAuthAssignments($oldUid);
        foreach ($items as $itemName => $assignment) {
            $auth->assign($itemName, $newUid);
        }
        $auth->save();

        Yii::app()->db->createCommand()
                ->update(LUser::model()->tableName(), array('state' => $newUid), 'state=:oldUid', array(':oldUid' => $oldUid));
        Yii::log("Merge: successfully appended $oldUid to $newUid.", CLogger::LEVEL_INFO, 'lily');
    }

    /**
     * Perform one-time login by one-time login token (see LOneTime::create())
     * @param string $token One time login token
     * @return bool Was user logged in or not
     */
    public function oneTimeLogin($token) {
        $authIdentity = Yii::app()->eauth->getIdentity('onetime');
        $authIdentity->token = $token;
        if ($authIdentity->authenticate()) {
            $identity = new LUserIdentity($authIdentity);
            //Authentication succeed
            if ($identity->authenticate()) {
                $result = Yii::app()->user->login($identity, 0);
                if ($result)
                    return true;
            }
        }
        return false;
    }

    /**
     * This function just sends a restoration email
     *
     *
     * After the execution, you can take a look at errorCode property of accountManager
     * It can take following values:
     * <ul>
     * <li>1 - failed to send email</li>
     * <li>0 - everything is OK</li>
     * </ul>
     *
     * @param LAccount $account Account (service - email) - account, to which's mail we will send mail.
     * @return boolean true, if mail was sent, false otherwise
     */
    public function sendRestoreMail(LAccount $account) {
        $this->errorCode = 0;
        $onetime = LOneTime::create($account->uid);
        $message = new YiiMailMessage();
        if (isset($this->restoreMailSubjectCallback))
            $subject = call_user_func($this->restoreMailSubjectCallback, $account);
        if (!isset($subject) || !is_string($subject))
            $subject = LilyModule::t('Password restoration on {siteName}', array('{siteName}' => Yii::app()->name));
        $message->setSubject($subject);
        $message->view = $this->restoreMailView;
        if (isset($this->restoreMailView))
            $message->setBody(array('account' => $account), 'text/html');
        else
            $message->setBody(LilyModule::t('Your email is registered on <a href="{siteUrl}">{siteName}</a>.<br />
And someone (possibly you) requested password restoration. <br />
If it was you, open <a href="{restoreUrl}">link</a></li> in your browser in order to login on website and then change the password in account settings. <br />
<br />
Yours respectfully,<br />
administration of {siteName}.', array('{siteUrl}' => Yii::app()->createAbsoluteUrl(Yii::app()->homeUrl), '{siteName}' => Yii::app()->name,
                        '{restoreUrl}' => Yii::app()->createAbsoluteUrl('/'.LilyModule::route('user/onetime'), array('token' => $onetime->token)))), 'text/html');
        $message->addTo($account->id);
        $message->from = $this->adminEmail;
        $recipient_count = Yii::app()->mail->send($message);
        if ($recipient_count > 0)
            Yii::log('E-mail to ' . $account->id . ' was sent.', CLogger::LEVEL_INFO, 'lily');
        else
            Yii::log('Failed sending e-mail to ' . $account->id . '.', CLogger::LEVEL_WARNING, 'lily');
        $this->errorCode = $recipient_count == 0;
        return $recipient_count > 0;
    }

}

