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
 * @property LAccountManager $accountManager
 * @property LilyModule $instance
 */
class LilyModule extends CWebModule {

    //General configurations
    public $hashFunction = 'md5';
    public $hashSalt = "ePGFxh7JeNL1AlaWCDfv";
    public $randomKeyLength = 20;
    //lowercase and uppercase latin letters, characters (excluding brackets) "-.,;=+~/\[]{}!@#$%^*&()_|" and simple whitespace
    public $passwordRegexp = '~^[a-zA-Z0-9\\-\\_\\|\\.\\,\\;\\=\\+\\~/\\\\\\[\\]\\{\\}\\!\\@\\#\\$\\%\\^\\*\\&\\(\\)\\ ]{8,32}$~';
    public $sessionTimeout = 604800; //Week
    public $enableUserMerge = true;
    public $userNameFunction = null;
    
    public $_relations = array();
    public $_userRelations = array();
    public $_session = null;
    
    protected static $_instance;
    public static function instance(){
        return self::$_instance;
    }
    
    public function getRelations(){
        return $this->_relations;
    }
    public function getUserRelations(){
        return $this->_userRelations;
    }
    
    public function setRelations($relations){
        $this->_relations = $relations;
        $userRelations = array();
        foreach($relations as $name => $relation){
            $userRelations[$name] = $relation['relation'];
        }
        $this->_userRelations = $userRelations;
    }
    
    public function onUserMerge($event){
        $this->raiseEvent('onUserMerge', $event);
    }
    public function onAfterLilyLoad($event){
        $this->raiseEvent('onAfterLilyLoad', $event);
    }
    public function onBeforeLilyLoad($event){
        $this->raiseEvent('onBeforeLilyLoad', $event);
    }
    
    public function getSession() {
        return $this->_session;
    }

    public function getAccount() {
        return isset($this->_session->account) ? $this->_session->account : null;
    }

    public function getUser() {
        return isset($this->_session->account->user) ? $this->_session->account->user : null;
    }

    public function getSessionData() {
        return isset($this->_session->data) ? $this->_session->data : new stdClass;
    }

    public function init() {
        parent::init();
        self::$_instance = $this;
        $this->setImport(array(
            'lily.*',
            'lily.components.*',
            'lily.services.*',
            'lily.models.*',
        ));
        $this->onBeforeLilyLoad(new CEvent($this));
        if(!$this->hasComponent('accountManager')) $this->accountManager = array();
        if (!Yii::app()->user->isGuest) {
            $logout = true;
            $sid = Yii::app()->user->getState('sid');
            $ssid = Yii::app()->user->hasState('ssid');
            if (isset($sid) && isset($ssid)) {
                $session = LSession::model()->findByPk($sid);
                if (isset($session) && $session->ssid == $ssid) {
                    if ($session->created + $this->sessionTimeout >= time()) {
                        $this->_session = $session;
                        Yii::app()->user->name = $this->_session->account->user->getName($this->userNameFunction);
                        $this->_session->account->user->setScenario('registered');
//                        if (!isset($this->_session->account->user->name)
//                                && !in_array(Yii::app()->urlManager->parseUrl(Yii::app()->getRequest()), array('lily/user/edit', 'lily/user/logout', 'site/logout'))) {
//                            Yii::app()->user->setFlash('lily_incompleteUserData', self::t('Your user data is incomplete! Please fill in the suggested form in order to continue site exploring.'));
//                            Yii::app()->request->redirect(Yii::app()->createUrl('lily/user/edit', array('returnUrl' => Yii::app()->request->getUrl())));
//                        }

                        $logout = false;
                    }else
                        $session->delete();
                }
            }
            if ($logout)
                Yii::app()->user->logout();
        }
        $this->onAfterLilyLoad(new CEvent($this));        
    }

    public function setAccountManager($settings) {

        $this->setComponents(
                array(
                    'accountManager' => array_merge(array('class' => 'LAccountManager'), $settings),
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
            $length = $this->randomKeyLength;
        $result = '';
        $possible_chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $pc_length = strlen($possible_chars);
        for ($i = 0; $i < $length; $i++) {
            $result .= $possible_chars[mt_rand(0, $pc_length - 1)];
        }
        return $result;
    }

    public static function t($str = '', $params = array(), $dic = 'default') {
        return Yii::t("LilyModule." . $dic, $str, $params);
    }

    public function getAssetsUrl() {
        $assets_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'assets';
        return Yii::app()->assetManager->publish($assets_path, false, -1, YII_DEBUG);
    }
}

?>
