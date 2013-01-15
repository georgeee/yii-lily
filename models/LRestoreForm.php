<?php
/**
 * LRestoreForm class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LRestoreForm is a model class for email password restoring implemention.
 * @package application.modules.lily.models
 */
class LRestoreForm extends CFormModel
{
    /**
     * @var string email field
     */
    public $email;
    /**
     * @var LAccount account of the email if it was successfully validated
     */
    public $account;

    /**
     * Declares the validation rules.
     * @return array validation rules
     */
    public function rules()
    {
        return array(
            array('email', 'email'),
            array('email', 'required'),
            array('email', 'inDB'),
        );
    }

    /**
     * Validator for email field, that checks if this email refers to existing account
     * @param $attribute
     * @param $params
     */
    public function inDB($attribute, $params)
    {
        $this->account = LAccount::model()->findByAttributes(array('service' => 'email', 'id' => $this->$attribute));
        if (!isset($this->account))
            $this->addError($attribute, LilyModule::t("Account with such email doesn't exist."));
    }


    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'email' => LilyModule::t("E-mail address"),
        );
    }

}
