<?php

/**
 * Lily Module
 * This module was started in february 2012 by George Agapov aka georgeee and
 * provides functionalities of user managment, but not like other yii modules.
 * It uses eauth extension by Maxim Zemskov (https://github.com/Nodge/yii-eauth)
 * and provides user auth by openID|oauth|oauth2 protocols (see module description)
 * or just by email-password pair.
 * 
 * And, two words about the name - module was called in tribute of one beautiful
 * russian poem, written by Vladimir Mayakovsky, Lilechka (russian: Лилечка). If
 * you speak russian, I really suggest you to read it.
 * 
 *
 * @author georgeee
 */
class LilyModule extends CWebModule {

    //General configurations
    public $hashFunction = 'md5';
    public $hashSalt = "ePGFxh7JeNL1AlaWCDfv";
    public $activationKeyLength = 20;
    
    //LEmailAccountManager configurations
    public $activate = true;
    public $sendMail = true;
    public $informationMailView = null; //'registrationFollowup';
    public $activationMailView = null; //'activationFollowup';
    public $adminEmail = 'admin@example.org';
    public $activationUrl = 'lily/email/activation';
    public $activationTimeout = 86400;

    public function init() {
        parent::init();
        $this->setImport(array(
            'lily.components.*',
            'lily.models.*',
        ));
        $this->setComponents(
                array(
                    'emailAccountManager' => array(
                        'activate' => $this->activate,
                        'sendMail' => $this->sendMail,
                        'informationMailView' => $this->informatioMailView,
                        'activationMailView' => $this->activationMailView,
                        'adminEmail' => $this->adminEmail,
                        'activationUrl' => $this->acivationUrl,
                        'activationTimeout' => $this->activaionTimeout,
                    ),
                )
        );
    }

    public function hash($str) {
        $hashFunction = $this->hashFunction;
        return $hashFunction($str . $this->hashSalt);
    }

    public function generateRandomString($length = -1) {
        if ($length == -1)
            $length = $this->activationKeyLength;
        $result = '';
        $possible_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $pc_length = strlen($possible_chars);
        for ($i = 0; $i < $length; $i++) {
            $result .= $possible_chars[mt_rand(0, $pc_length - 1)];
        }
        return $result;
    }

//    public function init() {
//        parent::init();
//    }

    public static function t($str = '', $params = array(), $dic = 'user') {
        return Yii::t("UserModule." . $dic, $str, $params);
    }

}

?>
