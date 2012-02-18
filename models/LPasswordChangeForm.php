<?php
/**
 * LPasswordChangeForm class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LPasswordChangeForm is a model class for changing email password implemention.
 * @package application.modules.lily.models
 */
class LPasswordChangeForm extends CFormModel
{
    /**
     * @var string password field
     */
    public $password;
    /**
     * @var string repeat password field
     */
    public $password_repeat;


/**
* Declares the validation rules.
* @return array validation rules
*/
    public function rules()
    {
        return array(
            array('password, password_repeat', 'required'),
            array('password', 'match', 'pattern' => LilyModule::instance()->passwordRegexp),
            array('password_repeat', 'compare', 'compareAttribute' => 'password'),
        );
    }
/**
 * Declares the attribute labels
 * @return array attribute labels
 */
    public function attributeLabels()
    {
        return array(
            'password_repeat' => LilyModule::t("Repeat password"),
            'password' => LilyModule::t("Password"),
        );
    }


}
