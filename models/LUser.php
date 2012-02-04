<?php

/**
 * This is the model class for table "{{user}}".
 *
 * The followings are the available columns in table '{{user}}':
 * @property integer $uid
 * @property string $name
 * @property string $birthday
 * @property integer $sex
 */
class LUser extends CActiveRecord {

    public $pattern = 'yyyy-MM-dd';
    public $_sex_options = null;

    public function getSexOptions() {
        if (!isset($this->_sex_options))
            $this->_sex_options = array(
                1 => LilyModule::t('Male'),
                0 => LilyModule::t('Female')
            );
        return $this->_sex_options;
    }

    public function getSexOption($sex = null) {
        if(!isset($sex)) $sex = $this->sex;
        return $this->sexOptions[(int)$sex];
    }
    

    public function convertBirthdayToUser() {
        $timestamp = CDateTimeParser::parse($this->birthday, $this->pattern);
        if ($timestamp === false)
            $this->birthday = null;
        else
            $this->birthday = Yii::app()->dateFormatter->formatDateTime($timestamp, 'medium', NULL);
    }

    public function convertBirthdayToDB() {
        $timestamp = CDateTimeParser::parse($this->birthday, Yii::app()->locale->getDateFormat('medium'));
        if ($timestamp === false)
            $this->birthday = null;
        else
            $this->birthday = Yii::app()->dateFormatter->format($this->pattern, $timestamp);
    }

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return LUser the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{lily_user}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name,birthday', 'required', 'on' => 'registered'),
            array('sex', 'boolean', 'on' => 'registered'),
            array('name', 'length', 'on' => 'registered', 'max' => 255),
            //array('birthday', 'date', 'on' => 'registered', 'format' => $this->pattern),
            array('birthday', 'date', 'on' => 'registered', 'format' => Yii::app()->locale->getDateFormat('medium')),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('uid, name, birthday, sex', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'accounts' => array(self::HAS_MANY, 'LAccount', 'uid'),
            'emailActivations' => array(self::HAS_MANY, 'LAccount', 'uid'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'uid' => 'User id',
            'name' => 'Name',
            'birthday' => 'Birthday',
            'sex' => 'Sex',
        );
    }

    protected function beforeSave() {
        if (!parent::beforeSave())
            return false;
        $this->convertBirthdayToDB();
        return true;
    }

    protected function afterFind() {
        parent::afterFind();
        $this->convertBirthdayToUser();
    }

    protected function afterSave() {
        parent::afterSave();
        $this->convertBirthdayToUser();
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('uid', $this->uid);
        $criteria->compare('name', $this->name, true);
        $criteria->compare('birthday', $this->birthday, true);
        $criteria->compare('sex', $this->sex);

        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

}