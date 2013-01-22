<?php
/**
 * LMergeEvent class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LMergeEvent is a event class. An instance of this class is used as the argument on onUserMerge event raising.
 * @package application.modules.lily.components
 */
class LMergeEvent extends CModelEvent
{
    /**
     * @var integer old Uid, an uid of the user, that should be appended to another
     */
    public $oldUid;
    /**
     * @var integer new Uid, an uid of the user, to which another user will be appended
     */
    public $newUid;
    
    /**
     * @var integer Account id (through which this merging is performed)
     */
    public $aid;
    
    /**
     * This function constructs new LMergeEvent
     * @param $oldUid
     * @param $newUid
     */
    public function __construct($oldUid, $newUid, $aid = null)
    {
        parent::__construct(null, null);
        $this->newUid = $newUid;
        $this->oldUid = $oldUid;
        $this->aid = $aid;
    }
}

?>
