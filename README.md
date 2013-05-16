[eauth]: https://github.com/Nodge/yii-eauth "Yii EAuth extension"

Lily Module
--------------------------------------

Lily is an Yii module that provides user managment funtionality. It allows you to authenticate using various services e.g. Google or Twitter. Module uses [EAuth][] extension in order to achieve it. Also it supports simple authentication via email-password pair.

Out of the box it only provides only a skeleton for user data management through AR realtions. It doesn't contain any fields such as name or birthday. You're free to create exactly what you want to.

Module support simple user managment functionalities, such as delete and ban. It's written with use of Yii's authentication manager, so it's easy to manage user's permissions on module's actions.

When using this module user can have multiple ways to authenticate (e.g. twitter + email-password). Also you can merge two user accounts into a single one. This action will be suggested if you're trying to use credentials that already were used by another user. You will be able to set a handler on the user merge event in order to update your tables (for example, to change the owner of content).

Module name is a tribute to one beautiful Russian poem written by Vladimir Mayakovsky and called Lilechka (Лилечка). If you speak Russian, I really suggest you to read it.

Requirements
--------------------------------------

Lily requires several extensions to be installed:

 * [EOAuth (required by EAuth)](http://www.yiiframework.com/extension/eoauth "Yii EOAuth extension") 
 * [Loid extension (required by EAuth)](http://www.yiiframework.com/extension/loid "Yii loid extension")
 * [Yii mail (in order to send notification and activation mails for email authentication type)](http://www.yiiframework.com/extension/mail/ "Yii mail extension")
 * [EAuth][]

Basic installation
------------------------------------
 0. Download and put lily module to `protected/extensions/lily` directory
 1. Download extensions listed above and put them to `protected/extensions`.
 2. Edit index.php:
```php

 	Yii::createWebApplication($config);
	Yii::app()->onBeginRequest = array('LilyModule', 'initModule');
	Yii::app()->run();
```

 3. Configure required extensions and lily module:
```php
 	
 	...
 	
	'aliases' => array(
		'lily' => 'ext.lily',
	),
	
 	...
	// autoloading model and component classes
	'import'=>array(
		...,
            'ext.eoauth.*',
            'ext.eoauth.lib.*',
            'ext.lightopenid.*',
            'ext.eauth.*',
            'ext.eauth.services.*',
            'ext.yii-mail.YiiMailMessage',
            'lily.LilyModule',
	),
	...
	'modules'=>array(
            'lily' => array(
                'class' => 'lily.LilyModule',
                ..., //Lily configurations
            ),
            ...,
	),
	...
	// application components
	'components'=>array(
            'urlManager' => array(
                'urlFormat' => 'path',
                'showScriptName' => true,
                'rules' => array(
                    //The rule is necessary to use, otherwise "Too many redirects" error may occure during user initialization
                    '<module:\w+>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
                ),
            ),
            'authManager' => array(
                'class' => 'CDbAuthManager',
                'connectionID' => 'db', //Type in your db connection name instead of db
                'assignmentTable' => '{{rbac_assignment}}',
                'itemChildTable' => '{{rbac_item_child}}',
                'itemTable' => '{{rbac_item}}',
                'defaultRoles'=>array('userAuthenticated'),
            ),
            'loid' => array(
                'class' => 'ext.lightopenid.loid',
            ),
            'eauth' => array(
                'class' => 'ext.eauth.EAuth',
                'popup' => true,
                'services' => array(
                    'email' => array(
                        'class' => 'lily.services.LEmailService', 
                    ),
                    'onetime' => array(
                        'class' => 'lily.services.LOneTimeService', //service, required by Lily
                    ),
                    'google' => array(
                        'class' => 'lily.services.LGoogleService',
                    ),
                    'yandex' => array(
                        'class' => 'lily.services.LYandexService',
                    ),
                    'twitter' => array(
                        'class' => 'lily.services.LTwitterService',
                        'key' => '',
                        'secret' => '',
                    ),
                    'vkontakte' => array(
                        'class' => 'lily.services.LVKontakteService',
                        'client_id' => '',
                        'client_secret' => '',
                    ),
                    'mailru' => array(
                        'class' => 'lily.services.LMailruService',
                        'client_id' => '',
                        'client_secret' => '',
                    ),
                ),
            ),
            'user' => array(
                // enable cookie-based authentication
                'allowAutoLogin' => true,
                'loginUrl' => array('/lily/user/login'),
                'autoUpdateFlash' => false,
            ),
            //Mail component is configured for use with gmail,
            //read corresponding docs to get acquinted with full set of parameters
            'mail' => array(
                'class' => 'ext.yii-mail.YiiMail',
                'transportType' => 'smtp',
                'viewPath' => 'application.views.mail',
                'logging' => true,
                'dryRun' => false,
                'transportOptions' => array(
                    'host' => 'smtp.gmail.com',
                    'username' => 'example@gmail.com',
                    'password' => 'Example password',
                    'port' => 465,
                    'encryption' => 'ssl',
                ),
            ),
            ...,
            //Logging component is optional, but recommended
            'log' => array(
                'class' => 'CLogRouter',
                'routes' => array(
                    //Route for collecting Lily's trace and info messages
                    array(
                        'class' => 'CDbLogRoute',
                        'logTableName' => 'ls_log_linfo',
                        'categories' => 'lily',
                        'levels' => 'trace, info',
                        'connectionID' => 'db',
                    ),
                    //Route for collecting Lily's warning and error messages
                    array(
                        'class' => 'CDbLogRoute',
                        'logTableName' => 'ls_log_lwe',
                        'categories' => 'lily',
                        'levels' => 'error, warning',
                        'connectionID' => 'db',
                    ),
                    //Route for collecting exceptions
                    array(
                        'class' => 'CDbLogRoute',
                        'logTableName' => 'ls_log_exc',
                        'categories' => 'exception.*',
                        'connectionID' => 'db',
                    ),
                ),
            ),
            ...,
        ),
	...
```
 4. Edit console.php:
```php 
    'components' => array(
        'db' => ..., //Your db, just as in main config
        'authManager' => ..., //Your authManager settings, just as in main config
    ),
    'commandMap' => array(
        'migrate' => array(
            'class' => 'system.cli.commands.MigrateCommand',
            'migrationPath' => 'application.migrations',
            'migrationTable' => 'ls_migration',
            'connectionID' => 'db',
        ),
        'lily_rbac' => array(
            'class' => 'lily.commands.LAuthInstaller'
        ),
    ),
```
 5. Deploy the DB tables:
     1. `./yiic migrate --migrationPath=ext.lily.migrations --migrationTable=lily_migration up`
     2. Create authManager tables into your DB, if you haven't done it yet
 
 6. Initialize RBAC structure for Lily:
    1. Run `./yiic lily_rbac`, it will install structure'
    2. Run `./yiic lily_rbac assign --user={uid} --role={role, default userAdmin}` to assign role to a user

 7. Edit views, refered to main menu. Check out the [example](https://github.com/georgeee/yii-lily-sample) to understand, what is fine to be there

Configuration
------------------------------

Lily offers you these configuration options:

```php
	'modules'=>array(
		'lily' => array(
			'class' => 'lily.LilyModule',
			
			//LilyModule properties
			'hashFunction' => 'md5', //hash function name
			'hashSalt' => 'any abracadbra you want to use as salt', //hash Salt, string that will be appended to hashing value before hashing 
			'randomKeyLength' => 20, //lengths of random keys, generated by application (e.g. activation key)
			'passwordRegexp' => '~^[a-zA-Z0-9\\-\\_\\|\\.\\,\\;\\=\\+\\~/\\\\\\[\\]\\{\\}\\!\\@\\#\\$\\%\\^\\*\\&\\(\\)\\ ]{8,32}$~',//regular expression for password checking
			'sessionTimeout => 604800, //timeout, after that session will be classified as expired
			'enableUserMerge' => true, //whether to allow user merging
			'userNameFunction' => null, //callback, that takes LUser object as argument and return user's name
			'allowedRoutes' => array(), //routes, that are allowed during any init step
			'routePrefix' => 'lily', //prefix to module uri (e.g. lily prefix means all actions of the module have uris like 'lily/<controllerId>/<actionId>)
			'relations' => array(), //user table relations
			
			//LUserIniter component properties
			'userIniter' => array(
				'showStartStep' => true, //Whether to show initial step page with common information about next actions
				'showFinishStep' => true, //Whether to show finish step page with common information about site using, registration results or etc.
				'finishRedirectUrl' => '', //Url, to which user will be redirected after initing process (last step) step
			),
			
			//LAccountManager component properties
			'accountManager' => array(
				'informationMailView' => null, //path to view of information letter (null - use the default content)
				'activationMailView' => null, //path to view of activation letter (null - use the default content)
				'restoreMailView' => null, //path to view of restore letter (null - use the default content)
				'informationMailSubjectCallback' => null, //callback for email subject of information letter
				'activationMailSubjectCallback' => null, //callback for email subject of activation letter
				'restoreMailSubjectCallback' => null, //callback for email subject of restoration letter
                                'registerEmail' => true, //should we register new e-mail account on the fly, or it's necessary to do it on registration page
                                'loginAfterRegistration' => true, //should we automaticaly log user in after e-mail registration
				'activate' => true, //Whether to activate new account
				'sendMail' => true, //Whether to send mails
				'adminEmail' => 'admin@example.org', //Email to put it in mails (From field)
				'activationTimeout' => 86400, //Timeout, after that activation will be rejected, even if code is clear
			),
			
		),
		...,
	),
```

I think, that these configurations are quite understandable (see comments on the right, if not - sources or write to me), I will only describe 'relations' property:

Property is array with such elements:

```php
$relationName => array(
	'relation' => $relation,
	'onUserMerge' => $onUserMerge,
	'onRegister' =>  $onRegister,
	'callback' => $callback,
),
```

$relationName - name of relations, as in ActiveRecord relations()

$relation - ActiveRecord relation in format, specified by  relations()

$onUserMerge - behavoiur on user merging. Can be: auto (just updates indexes from old uid to new one), event (raises an event accross the relation model object (or objects)), callback (executes the callback, specified by 'callback' property of relation)

$callback - see above

Also, I highly recommend you to take a look at sample project below. 

Sample project
------------------------------

I highly recommend you to look at [Lily sample project] (https://github.com/georgeee/yii-lily-sample), it will make using of module a bit more clear.
Demo is [here](http://georgeee.ru/lily-sample/).

Plans on future (@TODO)
----------------------

1. Make interface more beautiful
    1. Flash messages (Everywhere, where they should be)
    2. Message templates - make cool views to use by default
    3. Bootstrap themes (as an option)
2. Interface additions
    1. Session managment
    2. Simple role managment
3. Labels, texts and messages
    1. A lot of text doesn't sound good, my English isn't perfect.
    2. Translate all the stuff to russian

I'm not sure, when I'll finish all the work, so it would be really great if you'll help me with some stuff. Especcially with #3. 

License
-------------------------------

The module is being developed under the [New BSD License](http://www.opensource.org/licenses/bsd-license.php), so you'll find the latest version on [GitHub](https://github.com/georgeee/yii-lily).
