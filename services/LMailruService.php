<?php
/**
 * An example of extending the provider class.
 *
 * @author ChooJoy <choojoy.work@gmail.com>
 * @link http://code.google.com/p/yii-eauth/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
 

class LMailruService extends MailruOAuthService {	

	protected function fetchAttributes() {
		$info = (array)$this->makeSignedRequest('http://www.appsmail.ru/platform/api', array(
			'query' => array(
				'uids' => $this->uid,
				'method' => 'users.getInfo',
				'app_id' => $this->client_id,
			),
		));
		
                Yii::log(print_r($info,1), 'info', 'lily.LMailruService');
		$info = $info[0];
		
		$this->attributes['id'] = $info->uid;
		$this->attributes['first_name'] = $info->first_name;
		$this->attributes['photo'] = $info->pic;
	}
	
}
