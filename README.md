[eauth]: https://github.com/Nodge/yii-eauth "Yii EAuth extension"

Lily Module
--------------------------------------

Lily is an Yii module, that provides you user managment funtionalities. But not like others, it allows you to authenticate using various authentivation services (e.g. google or twitter, it uses [EAuth][] extension for this purpose) and, as usual email-password pair.

Out-of-box it provides only a sceleton for managimg user data (through active realtions), it doesn't contain any fields as name or birthday, so you are free to create exactly what you want to.

It supports binding two or more accounts (there are no restrictions, you can bind even a thousand email or google accounts to one user) and also it supports merging two users in one (this action will be suggested if you try to bind an account, that was already bound to another user). Of course, you will be able to set a handler on user merge event in order to update your tables (e.g. change the owner of content).

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
 2. Configure required extensions:
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
