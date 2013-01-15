<?php

/**
 * LSession class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LSession is a model class, that serves to manage with user sessions.
 *
 * Two words about handling data:
 * Data is contained in unserialized way. After retrieving from database or saving, it gets unserialized and before saving it gets serialized back.
 * Maybe later it should be rewrited to minimize count of serialize/unserialize actions
 *
 * @property integer $sid Session id
 * @property integer $aid Account id
 * @property LAccount $account Account instance
 * @property LUser $user User instance
 * @property object $data Session data object. You can put there some common information, refered to this session
 * (as default it contains information, retrieved from service). Don't forget to save session after editing it (call save() method)
 * @property string $ssid Secure session id. It's a random generated string, which is used for avoiding session data substitution.
 * @property integer $created Timestamp of the moment, this session was created
 *
 * @package application.modules.lily.models
 */
class LSession extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return LSession the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{lily_session}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        return array(
            array('aid, uid, created', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        return array(
            'account' => array(self::BELONGS_TO, 'LAccount', 'aid'),
            'user' => array(self::BELONGS_TO, 'LUser', 'uid'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'sid' => LilyModule::t('Session id'),
            'aid' => LilyModule::t('Account id'),
            'uid' => LilyModule::t('User id'),
            'created' => LilyModule::t('Created'),
            'ssid' => LilyModule::t('Secure session id'),
        );
    }

    /**
     * This method simply unserializes data attribute
     */
    protected function unserializeData() {
        $this->data = unserialize($this->data);
    }

    /**
     * This method simply serializes data attribute
     */
    protected function serializeData() {
        $this->data = serialize($this->data);
    }

    /**
     * After find handler, gets executed after model instance being retrieved from database
     */
    protected function afterFind() {
        parent::afterFind();
        $this->unserializeData();
    }

    /**
     * After save handler, gets executed after model instance being saved to database
     */
    protected function afterSave() {
        parent::afterSave();
        $this->unserializeData();
    }

    /**
     * Before save handler, gets executed before model instance being saved to database
     * @return bool true, we haven't to disallow saving action
     */
    protected function beforeSave() {
        parent::beforeSave();
        $this->serializeData();
        return true;
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        $criteria = new CDbCriteria;
        $criteria->compare('aid', $this->aid);
        $criteria->compare('uid', $this->uid);
        $criteria->compare('created', $this->created);
        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

    /**
     * Launches new session
     * @param integer|LAccount $account Account id (or instance)
     * @param object $data Session data
     * @return LSession created session or null if failed
     */
    public static function create($account, $data = null) {
        if (!isset($account)) {
            throw new LException("account argument must be set");
        }
        if (!is_object($account))
            $account = LAccount::model()->findByPk($account);
        if (!isset($account))
            throw new LException("account argument must be set");
        $session = new LSession;
        $session->aid = $account->aid;
        $session->uid = $account->uid;
        $session->created = time();
        $session->data = $data;
        $session->ssid = LilyModule::instance()->generateRandomString();
        if (!$session->save())
            throw new CDbException("failed to create session");
        return $session;
    }

}