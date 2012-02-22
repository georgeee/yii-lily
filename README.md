[eauth]: https://github.com/Nodge/yii-eauth "Yii EAuth extension"

Lily Module
--------------------------------------

Lily is an Yii module, that provides you user managment funtionalities. But not like others, it allows you to authenticate using various authentivation services (e.g. google or twitter, it uses [EAuth][] extension for this purpose) and, as usual email-password pair.

Out-of-box it provides only a sceleton for managimg user data (through active realtions), it doesn't contain any fields as name or birthday, so you are free to create exactly what you want to.

It supports binding two or more accounts (there are no restrictions, you can bind even a thousand email or google accounts to one user) and also it supports merging two users in one (this action will be suggested if you try to bind an account, that was already bound to another user). Of course, you will be able to set a handler on user merge event in order to update your tables (e.g. change the owner of content).

And, two words about the name - module was called in tribute of one beautiful russian poem, written by Vladimir Mayakovsky, Lilechka (russian: Лилечка). If you speak russian, I really suggest you to read it.

Requirements
--------------------------------------

Lily requires several extensions to be installed:

 * [EOAuth (required by EAuth)](http://www.yiiframework.com/extension/eoauth, "Yii EOAuth extension") 
 * [Loid extension (required by EAuth)](http://www.yiiframework.com/extension/loid "Yii loid extension")
 * [Yii mail (in order to send notification and activation mails for email authentication type)](http://www.yiiframework.com/extension/mail/ "Yii mail extension")
 * [EAuth][]

Basic installation
------------------------------------
 
 1. Download extensions from previous paragraph and put 'em to protected/extensions
 2. Configure required extensions and lily module :
 
```php
 	
 	...
 	
	'aliases' => array(
		'lily' => 'application.modules.lily',
	),
	
 	...
 
	// preloading 'log' component
	'preload'=>array(
		...,
		'lilyModuleLoader',
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
	),
	...
	'modules'=>array(
		'lily' => array(
			'class' => 'lily.LilyModule',
		),
		...,
	),
	...
	// application components
	'components'=>array(
		'user'=>array(
			// enable cookie-based authentication
			'allowAutoLogin'=>true,
            'loginUrl' => array('/lily/user/login'),
		),
        'lilyModuleLoader' => array(
            'class' => 'lily.LilyModuleLoader',
        ),
        'loid' => array(
            'class' => 'ext.lightopenid.loid',
        ),
        'eauth' => array(
            'class' => 'ext.eauth.EAuth',
            'popup' => true, // Use the popup window instead of redirecting.
            'services' => array(// You can change the providers and their classes.
                'onetime' => array(
                    'class' => 'lily.services.LOneTimeService',
                ),
                /*
                'email' => array(
                    'class' => 'lily.services.LEmailService',
                ),
                'google' => array(
                    'class' => 'lily.services.LGoogleService',
                ),
                'yandex' => array(
                    'class' => 'lily.services.LYandexService',
                ),
                'twitter' => array(
                    // регистрация приложения: https://dev.twitter.com/apps/new
                    'class' => 'lily.services.LTwitterService',
                    'key' => '..',
                    'secret' => '..',
                ),
                'vkontakte' => array(
                    // регистрация приложения: http://vkontakte.ru/editapp?act=create&site=1
                    'class' => 'lily.services.LVKontakteService',
                    'client_id' => '..',
                    'client_secret' => '..',
                ),
                'mailru' => array(
                    // регистрация приложения: http://api.mail.ru/sites/my/add
                    'class' => 'lily.services.LMailruService',
                    'client_id' => '..',
                    'client_secret' => '..',
                ),
                */
            ),
        ),
        'mail' => array(
            'class' => 'ext.yii-mail.YiiMail',
        ),
		
        ...,
	),
	...
```
 3. Migrate
 Migrate your DB tables using one of the following ways:
 
 * copy m120131_112629_lily_tables_create.php to your protected/migrations folder and execute "protected/yii migrate" from yii root (take a look at migrations section in Yii tutorial if you are not familar with that)
 * update your DB manually (lily.mysql.sql for mysql and lily.sqlite.sql for sqlite)
 
 4. Configure app menu

Configurations
------------------------------

Lily offers you these configurations:

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
			'enableUserMerge' => true, //Whether to allow user merging
			'enableLogging' => true, //Whether to populate log messages
			'userNameFunction' => null, //callback, that takes LUser object as argument and return user's name
			'allowedRoutes' => array(), //routes, that are allowed during any init step
			'routePrefix' => 'lily', //prefix to module uri (e.g. lily prefix means all actions of the module have uris like 'lily/<controllerId>/<actionId>)
			'relations' => array(), //User table relations, see format in docs
			
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
				'activate' => true, //Whether to activate new account
				'sendMail' => true, //Whether to send mails
				'adminEmail' => 'admin@example.org', //Email to put it in mails (From field)
				'activationTimeout' => 86400, //Timeout, after that activation will be rejected, even if code is clear
			),
			
		),
		...,
	),
```

Example project
------------------------------

<..>

License
-------------------------------

The module was released under the [New BSD License](http://www.opensource.org/licenses/bsd-license.php), so you'll find the latest version on [GitHub](https://github.com/georgeee/yii-lily).
