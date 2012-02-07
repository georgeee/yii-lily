<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CMergeEvent
 *
 * @author georgeee
 */
class LMergeEvent extends CModelEvent{
    
    public $oldUid, $newUid;
    
    public function __construct($oldUid, $newUid) {
        parent::__construct(null, null);
        $this->newUid = $newUid;
        $this->oldUid = $oldUid;
    }
}

?>
