[eauth]: https://github.com/Nodge/yii-eauth "Yii EAuth extension"

Lily Module
--------------------------------------

Lily is an Yii module that provides user managment funtionality. It allows you to authenticate using various services e.g. Google or Twitter. Module uses [EAuth][] extension in order to achieve it. Also it supports simple authentication via email-password pair.

Out of the box it only provides only a skeleton for user data management through AR realtions. It doesn't contain any fields such as name or birthday. You're free to create exactly what you want to.

When using this module user can have multiple ways to authenticate (e.g. twitter + email-password). Also you can merge two user accounts into a single one. This action will be suggested if you're trying to use credentials that already were used by another user. You will be able to set a handler on the user merge event in order to update your tables (for example, to change the owner of content).

Module name is a tribute to one beautiful Russian poem written by Vladimir Mayakovsky and called Lilechka (Лилечка). If you speak Russian, I really suggest you to read it.

Requirements
--------------------------------------

Lily requires several extensions to be installed:

 * [EOAuth (required by EAuth)](http://www.yiiframework.com/extension/eoauth, "Yii EOAuth extension") 
 * [Loid extension (required by EAuth)](http://www.yiiframework.com/extension/loid "Yii loid extension")
 * [Yii mail (in order to send notification and activation mails for email authentication type)](http://www.yiiframework.com/extension/mail/ "Yii mail extension")
 * [EAuth][]

Basic installation
------------------------------------
 
 1. Download extensions listed above and put them to `protected/extensions`.
 2. Configure required extensions and lily module:
 
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
 3. Migrate your DB tables using one of the following ways:
 
     * copy `data/m120131_112629_lily_tables_create.php` to your `protected/migrations` folder and execute `protected/yii migrate` from application root (take a look at migrations section in Yii tutorial if you are not familar with that)
     * update your DB manually (`data/lily.mysql.sql` for mysql and `data/lily.sqlite.sql` for sqlite)
 
 4. Configure app menu (see sample project for example).

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

Also, I hardly recommend you to take a look at sample project below. 

Sample project
------------------------------

[Lily sample project] (https://github.com/georgeee/yii-lily-sample)

License
-------------------------------

The module was released under the [New BSD License](http://www.opensource.org/licenses/bsd-license.php), so you'll find the latest version on [GitHub](https://github.com/georgeee/yii-lily).
