<?php
/**
 * LMailruService class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LMailruService is a eauth service class.
 * It provides properties and fetching method for eauth extension to authenticate through Mail.ru OAuth service.
 *
 * @package application.modules.lily.services
 */


class LMailruService extends MailruOAuthService
{

    /**
     * Fetch attributes array.
     * @return boolean whether the attributes was successfully fetched.
     */
    protected function fetchAttributes()
    {
        $info = (array)$this->makeSignedRequest('http://www.appsmail.ru/platform/api', array(
            'query' => array(
                'uids' => $this->uid,
                'method' => 'users.getInfo',
                'app_id' => $this->client_id,
            ),
        ));

        $info = $info[0];

        //$this->attributes = (array)$info;
        $this->attributes['id'] = $info->uid;
        $this->attributes['url'] = $info->link;
        $this->attributes['name'] = $info->first_name . ' ' . $info->last_name;
        $this->attributes['displayId'] = $info->email;
        $this->attributes['sex'] = !$info->sex;
        $this->attributes['firstname'] = $info->first_name;
        $this->attributes['lastname'] = $info->last_name;
        $this->attributes['username'] = $info->nick;
        $this->attributes['email'] = $info->email;
        $this->attributes['city'] = $info->location->city->name;
        $this->attributes['country'] = $info->location->country->name;
        $this->attributes['birthday'] = CDateTimeParser::parse($info->birthday, 'dd.MM.yyyy');

    }

}
