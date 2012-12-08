<?php
/**
 * LLoginFormValidator class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LLoginFormValidator is a validator class. It's used in login form and it just uses required, email and match validators to check the content.
 * Why not use them directly? Just because we need to check email and password fields in login form only when service is set to email,
 * so this validator a bit alters pointed-above valdators behaviour in order to sutisfy our wishes =)
 * @package application.modules.lily.components
 */
class LLoginFormValidator extends CValidator
{

    /**
     * This function validates attribute (see validator docs of yii)
     * @param object $object
     * @param string $attribute
     */
    protected function validateAttribute($object, $attribute)
    {
        if (!empty($object->service) && $object->service != 'email')
            return;
        $v = ($attribute == 'email' ? CValidator::createValidator('email', $object, $attribute) : CValidator::createValidator('match', $object, $attribute, array('pattern' => LilyModule::instance()->passwordRegexp)));
        $v->validate($object);
        $v = CValidator::createValidator('required', $object, $attribute);
        $v->validate($object);
    }

    /**
     * This function returns js code to validate attribute (see validator docs of yii)
     * @param $object
     * @param $attribute
     * @return string JS code
     */
    public function clientValidateAttribute($object, $attribute)
    {
        $v1 = ($attribute == 'email' ? CValidator::createValidator('email', $object, $attribute) : CValidator::createValidator('match', $object, $attribute, array('pattern' => LilyModule::instance()->passwordRegexp)));
        $v2 = CValidator::createValidator('required', $object, $attribute);
        $result = $v1->clientValidateAttribute($object, $attribute)
            . $v2->clientValidateAttribute($object, $attribute);
        if (isset($object->id)) $result = "if ( $('#$object->id .authMethodSelect').val() == 'email' ) {" . $result . "}";
        return $result;
    }

}

?>
