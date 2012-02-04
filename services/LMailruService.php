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
		
		$info = $info[0];
		
		$this->attributes = (array)$info;
                $this->attributes['id'] = $info->uid;
                $this->attributes['url'] = $info->link;
                $this->attributes['name'] = $info->first_name.' '.$info->last_name;
                $this->attributes['displayId'] = $info->email;
                $this->attributes['sex'] = !$info->sex;
                $this->attributes['birthday'] = Yii::app()->dateFormatter->formatDateTime(CDateTimeParser::parse($info->birthday, 'dd.MM.yyyy'), 'medium', NULL);
                
	}
	
}
