<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DefaultControlle
 *
 * @author georgeee
 */
class DefaultController extends Controller {
    public function actionIndex(){
        $this->render('/user/index');
    }
}

?>
