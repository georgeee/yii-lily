<?php
/**
 * LUserModel class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LUserModel is a model class template.
 * If you want to create a LUser relation model, it's recommended to inherit it from this class.
 *
 * @package application.modules.lily.models
 */
class LUserModel extends CActiveRecord
{
    /**
     * Raises an UserMerge event accross the model instance
     * @param LMergeEvent $event
     */
    public function onUserMerge(LMergeEvent $event)
    {
        $this->raiseEvent('onUserMerge', $event);
    }

}

?>
