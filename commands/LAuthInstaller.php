<?php
/**
 * LAuthInstaller class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LAuthInstaller is a console command class, which installs operation-task-role infrastructure for Lily
 *
 * @package application.commands
 */

class LAuthInstaller extends CConsoleCommand
{


    public function actionIndex(){
        $auth=Yii::app()->authManager;
        $auth->createOperation('listUser', 'view user list');
        $auth->createOperation('deleteUser', 'delete user');
        $auth->createOperation('restoreUser', 'restore deleted user');
        $auth->createOperation('banUser', 'ban user');
        $auth->createOperation('unbanUser', 'set banned user to active');
        
        
        $auth->createOperation('viewUser', 'view user page (also affects on the list)');
        $auth->createOperation('viewDeletedUser', 'view deleted user page (also affects on the list)',
                'return isset($params["user"])?$params["user"]->state==LUser::DELETED_STATE:false;')->addChild('viewUser');
        $auth->createOperation('viewBannedUser', 'view banned user page (also affects on the list)',
                'return isset($params["user"])?$params["user"]->state==LUser::BANNED_STATE:false;')->addChild('viewUser');
        $auth->createOperation('viewAppendedUser', 'view appended user page (also affects on the list)',
                'return isset($params["user"])?$params["user"]->state>LUser::ACTIVE_STATE:false;')->addChild('viewUser');
        $auth->createOperation('viewActiveUser', 'view active user page (also affects on the list)',
                'return isset($params["user"])?$params["user"]->state==LUser::ACTIVE_STATE:false;')->addChild('viewUser');
        
        $auth->createOperation('listAccounts', 'view account list');
        $auth->createOperation('deleteAccount', 'delete account');
        $auth->createOperation('editEmailAccount', 'edit e-mail account\'s password');
        
        $ownBizRule = 'if(isset($params["user"]))$params["uid"] = $params["user"]->uid;return isset($params["uid"])?$params["uid"]==$params["userId"]:false;';
        $auth->createTask('viewOwnUser', 'view own user page', $ownBizRule)->addChild('viewUser');
        $auth->createTask('deleteOwnUser', 'delete own user', $ownBizRule)->addChild('deleteUser');
        $auth->createTask('restoreOwnUser', 'restore own user', $ownBizRule)->addChild('restoreUser');
        $auth->createTask('listOwnAccounts', 'view own user account list', $ownBizRule)->addChild('listAccounts');
        $auth->createTask('deleteOwnAccount', 'delete own account', $ownBizRule)->addChild('deleteAccount');
        $auth->createTask('editOwnEmailAccount', 'edit own e-mail account\'s password', $ownBizRule)->addChild('editEmailAccount');
        
        $authenticated = $auth->createRole('userAuthenticated', 'authenticated user', 'return !Yii::app()->user->isGuest;');
        $authenticated->addChild('viewOwnUser');
        $authenticated->addChild('deleteOwnUser');
        $authenticated->addChild('restoreOwnUser');
        $authenticated->addChild('listOwnAccounts');
        $authenticated->addChild('deleteOwnAccount');
        $authenticated->addChild('editOwnEmailAccount');
        
        $auth->createRole('userGuest', 'not (strictly) authenticated user', 'return Yii::app()->user->isGuest;');
        
        $moderator = $auth->createRole('userModerator', 'user moderator');
        $moderator->addChild('listUser');
        $moderator->addChild('viewUser');
        $moderator->addChild('banUser');
        $moderator->addChild('unbanUser');
        
        $admin = $auth->createRole('userAdmin', 'user administrator');
        $admin->addChild('listUser');
        $admin->addChild('viewUser');
        $admin->addChild('deleteUser');
        $admin->addChild('restoreUser');
        $admin->addChild('banUser');
        $admin->addChild('unbanUser');
        $admin->addChild('listAccounts');
        $admin->addChild('deleteAccount');
        $admin->addChild('editEmailAccount');
        
        $auth->save();
    }
    
    public function actionAssign($user, $role = 'userAdmin'){
        $auth=Yii::app()->authManager;
        
        if ($auth->isAssigned($role, $user)) {
            echo $role . ' role already assigned for user ' . $user . PHP_EOL;
            return;
        }
        
        $auth->assign($role, $user);
        $auth->save();
    }
}
