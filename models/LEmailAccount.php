<?php

/**
 * This is the model class for table "{{email_account}}".
 *
 * The followings are the available columns in table '{{email_account}}':
 * @property integer $email_id
 * @property string $email
 * @property string $password
 */
class LEmailAccount extends CActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return LEmailAccount the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{lily_email_account}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('email, password', 'required'),
            array('email, password', 'length', 'max' => 255),
            array('email', 'email'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('email_id, email, password', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'email_id' => 'Email',
            'email' => 'Email',
            'password' => 'Password',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search() {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('email_id', $this->email_id);
        $criteria->compare('email', $this->email, true);
        $criteria->compare('password', $this->password, true);

        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }
    /**
     * Perform the creation of new email account by email and password
     * @param string $email
     * @param string $password
     * @param boolean $hash_password - whether to hash $password value 
     * or not (defaults to false)
     * @return LEmailAccount 
     */
    public static function create($email, $password, $hash_password = false) {
        if($hash_password){
            $password = Yii::app()->getModule('lily')->hash($password);
        }
        $account = new LEmailAccount;
        $account->attributes = array('email' => $email, 'password' => $password);
        return $account->save()?$account:null;
    }
    
}