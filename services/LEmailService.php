<?php

/**
 * LEmailService class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LEmailService is a eauth service class.
 * It provides email authentication functionality to application.
 *
 * @package application.modules.lily.services
 */
class LEmailService extends EAuthServiceBase implements IAuthService {
    /**
     * Everything is OK.
     */

    const ERROR_NONE = 0;
    /**
     * Failed to authenticate authentication identity (email-password mismatch).
     */
    const ERROR_AUTH_FAILED = 1;
    /**
     * No errors occured. Activation mail to user was sent and user will be registered
     * as soon as he will click the activation url in the email message body.
     */
    const ERROR_ACTIVATION_MAIL_SENT = 2;
    /**
     * Error occured while trying to send activation mail.
     */
    const ERROR_ACTIVATION_MAIL_FAILED = 3;
    /**
     * Error occured while trying to send information mail.
     */
    const ERROR_INFORMATION_MAIL_FAILED = 5;
    /**
     * E-mail is not bound to any account and "on-fly" registration set to off
     * (LilyModule::instance()->accountManager->registerEmail == false)
     */
    const ERROR_NOT_REGISTERED = 4;

    public function getServiceType() {
        return 'email';
    }

    public function getServiceTitle() {
        return 'E-mail';
    }

    public function getServiceName() {
        return 'email';
    }

    /**
     * @var string email field, put here email address, you want to authenticate
     */
    public $email;

    /**
     * @var string password field, put here password, with which you want to authenticate
     */
    public $password;

    /**
     * @var integer code of the error, that occured, ERROR_NONE will be set if no errors occured
     */
    public $errorCode;

    /**
     * @var LUser user-owner of the email account, specified on bind process
     * (null means that we're trying to authenticate, not bind an account to already existing user)
     */
    public $user = null;
    
    /**
     * @var boolean $rememberMe Whether to remember user, authenticating him after activation 
     */    
    public $rememberMe = false;

    /**
     * This function simply tries to authenticate auth identity
     * @param type $forceRegisterEmail Should we auto-register e-mail even if LilyModule::instance()->accountManager->registerEmail is set to false
     * @param type $checkPassword Should we check password matching if account already exist
     * @return boolean is user identity authenticated or authentication failed
     */
    public function authenticate($forceRegisterEmail = false, $checkPassword = true) {
        $email = $this->email;
        $this->authenticated = false;

        if (!isset($email)) {
            return false;
        }
        $account = LAccount::model()->findByAttributes(array('service' => 'email', 'id' => $email));
        if (!(isset($account) && !$checkPassword) && !isset($this->password))
            return false;
        if (!(isset($account) && !$checkPassword))
            $password = $this->password;
        if (!isset($account)) {
            if (LilyModule::instance()->accountManager->registerEmail || $forceRegisterEmail) {
                //Performing the registration
                LilyModule::instance()->accountManager->performRegistration($email, $password, null, null, $this->user, $this->rememberMe);
                if (LilyModule::instance()->accountManager->activate) {
                    if (LilyModule::instance()->accountManager->errorCode == 0) {
                        $this->errorCode = self::ERROR_ACTIVATION_MAIL_SENT;
                    } else {
                        $this->errorCode = self::ERROR_ACTIVATION_MAIL_FAILED;
                    }
                } else {
                    $this->errorCode = (LilyModule::instance()->accountManager->sendMail &&
                            LilyModule::instance()->accountManager->errorCode == 1) ? self::ERROR_INFORMATION_MAIL_FAILED : self::ERROR_NONE;
                    $this->attributes['id'] = $this->attributes['email'] = $this->attributes['displayId'] = $email;
                    $this->authenticated = true;
                }
            } else {
                $this->errorCode = self::ERROR_NOT_REGISTERED;
            }
        } else {
            if (!$checkPassword || ($password_hash = LilyModule::instance()->hash($password)) == $account->data->password) {
                $this->attributes['id'] = $this->attributes['email'] = $this->attributes['displayId'] = $email;
                $this->authenticated = true;
                $this->errorCode = self::ERROR_NONE;
            } else {
                $this->errorCode = self::ERROR_AUTH_FAILED;
            }
        }
        Yii::log("LEmailService auth resulted with code $this->errorCode.", CLogger::LEVEL_INFO, 'lily');
        return $this->authenticated;
    }

    /**
     * Simply redirects user to the specified url (if not specified, redirectUrl property will be used instead)
     *
     * We have to override this function, because email authentication doesn't require popup.
     * @param string $url
     */
    public function redirect($url = null) {
        Yii::app()->request->redirect(isset($url) ? $url : $this->getRedirectUrl(), true);
    }

    /**
     * Simply redirects user to the specified url (if not specified, cancelUrl property will be used instead)
     *
     * We have to override this function, because email authentication doesn't require popup.
     * @param string $url
     */
    public function cancel($url = null) {
        Yii::app()->request->redirect(isset($url) ? $url : $this->getCancelUrl(), false);
    }

}

?>
