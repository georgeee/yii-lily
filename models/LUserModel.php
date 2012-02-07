<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LUserModel
 *
 * @author georgeee
 */
class LUserModel extends CActiveRecord{
    
    public function onUserMerge(LMergeEvent $event){
        $this->raiseEvent('onUserMerge', $event);
    }
    
}

?>
