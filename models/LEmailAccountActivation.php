<?php

/**
 * LEmailAccountActivation class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LEmailAccountActivation is a model class, that works with email account activation codes.
 * So, when user registers on the site, using email service, it's a good idea to make sure, his email is OK.
 * And for this option we send an activation code to his address. And this table contains these codes and some
 * common data, refered to activating and email account registering.
 *
 * @property integer $code_id Id of activation code
 * @property integer $uid User id, if it's not registration of new user, but a bind operation
 * @property string $email Mail address, which we should activate
 * @property string $password Password hash of account, that will be created after activation
 * @property string $code Activation code, which was sent to mail and serves as the key to activation operation
 * @property integer $created Timestamp of the moment, when code was created
 * @property boolean $rememberMe Whether to remember user, authenticating him after activation
 *
 * @package application.modules.lily.models
 */
class LEmailAccountActivation extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return LEmailAccountActivation the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{lily_email_account_activation}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return array(
            array('code, created, email, password, uid, rememberMe', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        return array(
            'user' => array(self::BELONGS_TO, 'LUser', 'uid'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'code_id' => LilyModule::t('Code id'),
            'code' => LilyModule::t('Code'),
            'created' => LilyModule::t('Created'),
            'uid' => LilyModule::t('User id'),
            'email' => LilyModule::t('E-mail'),
            'rememberMe' => LilyModule::t('Remember me after registration'),
        );
    }

    /**
     * Perform the creation of new email account activation code
     * by email and password
     * @param string $email
     * @param string $password
     * @param integer|LUser $uid User id or LUser model instance, null if it's new user account
     * @param boolean $rememberMe Whether to remember user, authenticating him after activation
     * @return LEmailAccountActivation
     */
    public static function create($email, $password, $uid = null, $rememberMe = false) {
        $password = LilyModule::instance()->hash($password);
        if (isset($uid) && is_object($uid))
            $uid = $uid->uid;
        $code = new LEmailAccountActivation();
        $code->code = LilyModule::instance()->generateRandomString();
        $code->email = $email;
        $code->password = $password;
        $code->created = time();
        $code->uid = $uid;
        $code->rememberMe = $rememberMe;
        if (!$code->save())
            throw new CDbException("failed to create new activation code");
        return $code;
    }

}