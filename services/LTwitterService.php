<?php
/**
 * LTwitterService class file.
 *
 * @author George Agapov <george.agapov@gmail.com>
 * @link https://github.com/georgeee/yii-lily
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

/**
 * LTwitterService is a eauth service class.
 * It provides properties and fetching method for eauth extension to authenticate through Twitter OAuth service.
 *
 * @package application.modules.lily.services
 */

class LTwitterService extends TwitterOAuthService
{

    /**
     * Fetch attributes array.
     * @return boolean whether the attributes was successfully fetched.
     */
    protected function fetchAttributes()
    {
        $info = $this->makeSignedRequest('https://api.twitter.com/1/account/verify_credentials.json');
        $this->attributes = (array)$info;
        $this->attributes['url'] = 'http://twitter.com/account/redirect_by_id?id=' . $info->id_str;
        $this->attributes['displayId'] = $this->attributes['username'] = $info->screen_name;
        $this->attributes['language'] = $info->lang;
        $this->attributes['timezone'] = timezone_name_from_abbr('', $info->utc_offset, date('I'));
        $this->attributes['photo'] = $info->profile_image_url;
    }


}