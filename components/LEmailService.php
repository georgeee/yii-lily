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

    public $emailPostField = 'email';
    public $passwordPostField = 'password';

    public function autheniticate() {
        $email = Yii::app()->request->getPost($this->emailPostField);
        $password = Yii::app()->request->getPost($this->passwordPostField);

        if (!isset($email) || !isset($password))
            return false;
        
        $email_account = LEmailAccount::model()->findByAttributes('email=:email', array(':email' => $email));
        if (!isset($email_account)) { //Performing the registration
            $error_code = -1;
            LEmailAccountManager::performRegistration($email, $password, true, true, true, null, $error_code);
            $this->authenticated = false;
            $flash_id = $flash_msg = '';
            if($error_code == 0){
                $flash_msg = t('ee', 'На ваш e-mail {email} отправлено письмо с инструкциями по активации аккаунта.', array('{email}'=>$email));
                $flash_id = 'activationMailSent';
            }else{
                $flash_msg = t('ee', 'Произошла ошибка при регистрации {email}. Пожалуйста, проверьте введенные данный. <br \> В случае повторения ошибки, обратитесь к администрации сайта.', array('{email}'=>$email));
                $flash_id = 'activationMailSendingError';
            }
            
            Yii::app()->user->setFlash($flash_id, $flash_msg);
        }else{
            $password_hash = Yii::app()->hashGenerator->hash($password);
            if($password_hash == $email_account->password){
                $this->authenticated = true;
            }else{
                $this->authenticated = false;
            }
        }
        return $this->authenticated;
    }

}

?>
