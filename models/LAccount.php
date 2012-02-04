<?php

/**
 * This is the model class for table "{{account}}".
 *
 * The followings are the available columns in table '{{account}}':
 * @property integer $uid
 * @property string $service
 * @property string $id
 * @property string $data
 * @property string $created
 */
class LAccount extends CActiveRecord {

    protected $unserialized = false;

    
    public function getServiceName(){
        $services = Yii::app()->eauth->getServices();
        return $services[$this->service]->title;
    }
    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return LAccount the static model class
     */
    
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return '{{lily_account}}';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
//			array('service_id, user_id, created', 'required'),
//			array('uid', 'numerical', 'integerOnly'=>true),
//			array('service_id, user_id', 'length', 'max'=>255),
//			array('service_data', 'safe'),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('uid, service, id, created', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user' => array(self::BELONGS_TO, 'LUser', 'uid'),
            'sessions' => array(self::HAS_MANY, 'LSession', 'aid'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels() {
        return array(
            'uid' => 'Uid',
            'service' => 'Service',
            'id' => 'User',
            'data' => 'Service Data',
            'created' => 'Created',
        );
    }

    public function checkUnserialized() {
        if (!$this->unserialized) {
            $this->data = unserialize($this->data);
            $this->unserialized = true;
        }
    }

    public function checkSerialized() {
        if ($this->unserialized) {
            $this->data = serialize($this->data);
            $this->unserialized = false;
        }
    }

    protected function afterFind() {
        parent::afterFind();
        $this->checkUnserialized();
    }

    protected function afterSave() {
        parent::afterSave();
        $this->checkUnserialized();
    }

    protected function beforeSave() {
        parent::beforeSave();
        $this->checkSerialized();
        return true;
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
        $criteria->compare('service', $this->service, true);
        $criteria->compare('id', $this->id, true);
        $criteria->compare('created', $this->created, true);

        return new CActiveDataProvider($this, array(
                    'criteria' => $criteria,
                ));
    }

    /**
     * Perform the creation of new account
     * Return created account or null if creation failed
     * @param string $service Service id
     * @param string $id User's id in the service
     * @param object $data Optional service data
     * @param integer $uid User id (account instance), null if it's new account
     * @return LAccount
     */
    public static function create($service, $id, $data = null, $uid = null) {
        if (!isset($uid)) {
            $uid = new LUser;
            if (!$uid->save()) {
                Yii::log("LAccount::create($service, $id,..) failed on new User creation", 'error', 'lily.LAccount.fail');
                return null;
            }
        }
        if (is_object($uid))
            $uid = $uid->uid;
        $account = new LAccount;
        $account->uid = $uid;
        $account->service = $service;
        $account->id = $id;
        $account->unserialized = true;
        $account->data =$data;
        $account->created = time();
        if ($account->save()) {
            Yii::log("LAccount::create($service, $id,..) successfully created new account aid={$account->aid}", 'info', 'lily.LAccount.success');
            return $account;
        } else {
            Yii::log("LAccount::create($service, $id,..) failed on account saving:\n\$account:\n" . print_r($account->getAttributes(), 1) . "\nerrors:\n" . print_r($account->getErrors(), 1), 'warning', 'lily.LAccount.fail');
            return null;
        }
    }

}