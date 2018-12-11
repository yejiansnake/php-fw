<?php
/**
 * 微信网页授权
 */

namespace app\lib\vendor\wechat;

class CorpWeChat extends BaseWeChat
{
    //---------------------------------------------------------------------------------------------------
    //参数名称

    private $corpID = '';
    private $accessToken = '';

    //---------------------------------------------------------------------------------------------------

    protected function init(array $options)
    {
        if (empty($options['corpID'])
            || empty($options['accessToken'])
        )
        {
            throw new \InvalidArgumentException();
        }

        $this->corpID = $options['corpID'];
        $this->accessToken = $options['accessToken'];
    }

    //---------------------------------------------------------------------------------------------------

    //获取 ACCESS_TOKEN
    const API_URL_GET_ACCESS_TOKEN = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken';

    //获取 JS_API_TICKET
    const API_URL_GET_JS_API_TICKET = 'https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket';

    /**
     * @param $corpID
     * @param $corpSecret
     * @return mixed
     * @throws \Exception
     */
    public static function getAccessToken($corpID, $corpSecret)
    {
        $params = [
            'corpid' => $corpID,
            'corpsecret' => $corpSecret,
        ];

        return self::callApi(self::API_URL_GET_ACCESS_TOKEN, $params);
    }

    /**
     * @param $accessToken
     * @return mixed|null
     * @throws \Exception
     */
    public static function getJsApiTicket($accessToken)
    {
        if (empty($accessToken))
        {
            return null;
        }

        $params = [
            'access_token' => $accessToken,
        ];

        return self::callApi(self::API_URL_GET_JS_API_TICKET, $params);
    }

    //---------------------------------------------------------------------------------------------------
    //接口

    //读取成员
    const API_URL_GET_USER = 'https://qyapi.weixin.qq.com/cgi-bin/user/get';

    //获取部门成员详情
    const API_URL_GET_DEP_USER_LIST = 'https://qyapi.weixin.qq.com/cgi-bin/user/list';

    //获取部门列表
    const API_URL_GET_DEP_LIST = 'https://qyapi.weixin.qq.com/cgi-bin/department/list';

    //获取标签列表
    const API_URL_GET_TAG_LIST = 'https://qyapi.weixin.qq.com/cgi-bin/tag/list';

    //获取标签成员
    const API_URL_GET_TAG_USER_LIST = 'https://qyapi.weixin.qq.com/cgi-bin/tag/get';

    //获取部门成员简明信息
    const API_URL_GET_DEP_USER_SIMPLE_LIST = 'https://qyapi.weixin.qq.com/cgi-bin/user/simplelist';

    //获取临时素材
    const API_URL_MEDIA_GET = 'https://qyapi.weixin.qq.com/cgi-bin/media/get';

    //发送消息
    const API_URL_MESSAGE_SEND = 'https://qyapi.weixin.qq.com/cgi-bin/message/send';


    /**
     * @param $userID
     * @return mixed
     * @throws \Exception
     */
    public function getUser($userID)
    {
        $get = [
            'access_token' => $this->accessToken,
            'userid' => $userID,
        ];

        return parent::callApi(self::API_URL_GET_USER, $get);
    }

    /**
     * @param int $departmentID
     * @param bool $fetchChild
     * @return mixed
     * @throws \Exception
     */
    public function getDepUserSimpleList($departmentID = 1, $fetchChild = false)
    {
        $get = [
            'access_token' => $this->accessToken,
            'department_id' => $departmentID,
            'fetch_child' => empty($fetchChild) ? 0 : 1,
        ];

        return parent::callApi(self::API_URL_GET_DEP_USER_SIMPLE_LIST, $get);
    }

    /**
     * @param int $departmentID
     * @param bool $fetchChild
     * @return mixed
     * @throws \Exception
     */
    public function getDepUserList($departmentID = 1, $fetchChild = false)
    {
        $get = [
            'access_token' => $this->accessToken,
            'department_id' => $departmentID,
            'fetch_child' => empty($fetchChild) ? 0 : 1,
        ];

        return parent::callApi(self::API_URL_GET_DEP_USER_LIST, $get);
    }

    /**
     * @param null $id
     * @return mixed
     * @throws \Exception
     */
    public function getDeList($id = null)
    {
        $get = [
            'access_token' => $this->accessToken,
        ];

        if (!empty($id))
        {
            $get['id'] = $id;
        }

        return parent::callApi(self::API_URL_GET_DEP_LIST, $get);
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function getTagList()
    {
        $get = [
            'access_token' => $this->accessToken,
        ];

        return parent::callApi(self::API_URL_GET_TAG_LIST, $get);
    }

    /**
     * @param int $tagId
     * @return mixed
     * @throws \Exception
     */
    public function getTagUserList($tagId = 1)
    {
        $get = [
            'access_token' => $this->accessToken,
            'tagid' => $tagId,
        ];

        return parent::callApi(self::API_URL_GET_TAG_USER_LIST, $get);
    }

    public function getMedia($mediaID)
    {
        $params = [
            'access_token' => $this->accessToken,
            'media_id' => $mediaID
        ];

        return self::callApiRaw(self::API_URL_MEDIA_GET, $params, null, true);
    }

    /**
     * @param $wxID
     * @param $content
     * @param $agentID
     * @return mixed
     * @throws \Exception
     */
    public function sendMessage($wxID, $content, $agentID)
    {
        $get = [
            'access_token' => $this->accessToken,
        ];

        $post = [
            'touser' => $wxID,
            'msgtype' => 'text',
            'agentid' => $agentID,
            'text' => ['content' => $content]
        ];

        return parent::callApiPostJson(self::API_URL_MESSAGE_SEND, $post, $get);
    }
}