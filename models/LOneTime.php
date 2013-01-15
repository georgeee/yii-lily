<?php

/**
 * LOneTime class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LOneTime is a model class, that serves to manage with one-time logins tokens.
 *
 * @property integer $tid Token id
 * @property string $token Token, just a random generated string, that will be passed to the user
 * @property integer $uid Id of the user, to which this token refers
 * @property integer $created Timestamp of token creation
 * @property LUser $user User, to which this token refers
 *
 * @package application.modules.lily.models
 */
class LOneTime extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return LOneTime the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{lily_onetime}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return array(
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
            'tid' => LilyModule::t("Token id"),
            'uid' => LilyModule::t("User id"),
            'token' => LilyModule::t("Token"),
            'created' => LilyModule::t("Created"),
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        $criteria = new CDbCriteria;
        $criteria->compare('uid', $this->uid);
        $criteria->compare('token', $this->token, true);
        $criteria->compare('created', $this->created, true);
        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

    /**
     * Perform the creation of new one-time login token
     * @param integer|LUser $uid User id (or LUser instance)
     * @return LOneTime created token instance
     */
    public static function create($uid) {
        if (!isset($uid))
            throw new LException("uid argument must be set");
        if (is_object($uid))
            $uid = $uid->uid;
        $token = new LOneTime;
        $token->token = LilyModule::instance()->generateRandomString();
        $token->uid = $uid;
        $token->created = time();
        if ($token->save()) {
            Yii::log("LOneTime::create($uid) successfully created new one-time login token tid={$token->tid}", CLogger::LEVEL_INFO, 'lily');
            return $token;
        } else {
            throw new CDbException("Failed to save new onetime-login model instance");
        }
    }

}
