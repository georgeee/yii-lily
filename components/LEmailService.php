<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EEEmailService
 *
 * @author georgeee
 */
class LEmailService extends EAuthServiceBase implements IAuthService {
    
    protected $name = 'email';
    protected $title = 'E-mail';
    protected $type = 'email';
    
    public $email = '';
    public $password = '';

    public function authenticate() {
            Yii::trace ('LEmailService->autheniticate()');
        $email = $this->email;
        $password = $this->password;

        if (!isset($email) || !isset($password)){
            return false;
        }
        $email_account = LEmailAccount::model()->findByAttributes(array('email' => $email));
        if (!isset($email_account)) { //Performing the registration
            $error_code = -1;
            Yii::app()->getModule('lily')->getEmailAccountManager()->performRegistration($email, $password, null, null, true, null, $error_code);
            $this->authenticated = false;
            $flash_id = $flash_msg = '';
            if($error_code == 0){
                $flash_msg = LilyModule::t('На ваш e-mail {email} отправлено письмо с инструкциями по активации аккаунта.', array('{email}'=>$email));
                $flash_id = 'activationMailSent';
            }else{
                $flash_msg = LilyModule::t('Произошла ошибка при регистрации {email}. Пожалуйста, проверьте введенные данный. <br \> В случае повторения ошибки, обратитесь к администрации сайта.', array('{email}'=>$email));
                $flash_id = 'activationMailSendingError';
            }
            
            Yii::app()->user->setFlash($flash_id, $flash_msg);
        }else{
            $password_hash = Yii::app()->hashGenerator->hash($password);
            if($password_hash == $email_account->password){
                $this->id = $email;
                $this->authenticated = true;
            }else{
                $this->authenticated = false;
            }
        }
        return $this->authenticated;
    }

}

?>
