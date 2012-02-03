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
    //lowercase and uppercase latin letters, characters (excluding brackets) "-.,;=+~/\[]{}!@#$%^*&()_|" and simple whitespace
    public $passwordRegexp = '~^[a-zA-Z0-9\\-\\_\\|\\.\\,\\;\\=\\+\\~/\\\\\\[\\]\\{\\}\\!\\@\\#\\$\\%\\^\\*\\&\\(\\)\\ ]{8,32}$~';
    //LAccountManager configurations
    public $activate = true;
    public $sendMail = true;
    public $informationMailView = null; //'registrationFollowup';
    public $activationMailView = null; //'activationFollowup';
    public $adminEmail = 'admin@example.org';
    public $activationUrl = 'lily/user/activate';
    public $activationTimeout = 86400;//24h
    public $sessionTimeout = 604800;//Week
    
    /**
     * account manager component
     * @var LAccountManager 
     */
    public $accountManager;

    public function init() {
        parent::init();
        $this->setImport(array(
            'lily.*',
            'lily.components.*',
            'lily.models.*',
        ));
        $this->setComponents(
                array(
                    'accountManager' => array(
                        'class' => 'LAccountManager',
                        'activate' => $this->activate,
                        'sendMail' => $this->sendMail,
                        'informationMailView' => $this->informationMailView,
                        'activationMailView' => $this->activationMailView,
                        'adminEmail' => $this->adminEmail,
                        'activationUrl' => $this->activationUrl,
                        'activationTimeout' => $this->activationTimeout,
                    ),
                )
        );
    }

    /**
     * email account manager component instance
     * @return LEmailAccountManager 
     */
    public function getAccountManager() {
        return $this->getComponent('accountManager');
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

    public function getAssetsUrl() {
        $assets_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        return Yii::app()->assetManager->publish($assets_path, false, -1, YII_DEBUG);
    }

    public function registerCss($css) {
        Yii::app()->getClientScript()->registerCssFile($this->getAssetsUrl() . "/css/$css.css");
    }

    public function registerJs($js) {
        Yii::app()->clientScript->registerCoreScript('jquery');
        Yii::app()->getClientScript()->registerScriptFile($this->getAssetsUrl() . "/js/$js.js");
    }

}

?>
