<?php
namespace app\lib\vendor\QCloud;

use app\lib\common\HttpHelper;

class SmsClient
{
    const API_URL_SINGLE = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms';
    const API_URL_VOICE_VERIFY_CODE = 'https://yun.tim.qq.com/v5/tlsvoicesvr/sendvoice';
    const API_URL_VOICE_PROMPT = 'https://yun.tim.qq.com/v5/tlsvoicesvr/sendvoiceprompt';

    private $appId;
    private $appKey;

    function __construct(array $config)
    {
        $this->appId = $config['appId'];
        $this->appKey = $config['appKey'];
    }

    /**
     * 普通单发，明确指定内容，如果有多个签名，请在内容中以【】的方式添加到信息内容中，否则系统将使用默认签名
     * @param string $nationCode 国家码，如 86 为中国
     * @param string $mobile 不带国家码的手机号
     * @param string $msg 信息内容，必须与申请的模板格式一致，否则将返回错误
     * @param int $type 短信类型，0 为普通短信，1 营销短信
     * @param string $extend 扩展码，可填空串
     * @param string $ext 服务端原样返回的参数，可填空串
     * @return array json { "result": xxxxx, "errmsg": "xxxxxx" ... }，被省略的内容参见协议文档
     */
    function sendSingle($nationCode, $mobile, $msg, $type = 0, $extend = '', $ext = '')
    {
        /* 请求包体
        {
            "tel": {
                "nationcode": "86",
                "mobile": "13788888888"
            },
            "type": 0,
            "msg": "你的验证码是1234",
            "sig": "fdba654e05bc0d15796713a1a1a2318c",
            "time": 1479888540,
            "extend": "",
            "ext": ""
        }

        应答包体
        {
            "result": 0,
            "errmsg": "OK",
            "ext": "",
            "sid": "xxxxxxx",
            "fee": 1
        } */

        $req = self::buildReq([
            'api' => self::API_URL_SINGLE,
            'nationCode' => $nationCode,
            'mobile' => $mobile,
            'ext' => $ext,
        ]);

        $req['data'] += [
            'type' => (int)$type,
            'msg' => $msg,
            'extend' => empty($extend) ? '' : $extend,
        ];

        return self::send($req['url'], $req['data']);
    }

    /**
     * 指定模板单发
     * @param string $nationCode 国家码，如 86 为中国
     * @param string $mobile 不带国家码的手机号
     * @param int $tplId 模板 id
     * @param array $tplParams 模板参数列表，如模板 {1}...{2}...{3}，那么需要带三个参数
     * @param string $sign 签名，如果填空串，系统会使用默认签名
     * @param string $extend 扩展码，可填空串
     * @param string $ext 服务端原样返回的参数，可填空串
     * @return array json { "result": xxxxx, "errmsg": "xxxxxx"  ... }，被省略的内容参见协议文档
     */
    function sendSingleTemplate($nationCode, $mobile,
        $tplId, array $tplParams, $sign = "", $extend = "", $ext = "")
    {
        /* 请求包体
        {
            "tel": {
                "nationcode": "86",
                "mobile": "13788888888"
            },
            "sign": "腾讯云",
            "tpl_id": 19,
            "params": [
                "验证码",
                "1234",
                "4"
            ],
            "sig": "fdba654e05bc0d15796713a1a1a2318c",
            "time": 1479888540,
            "extend": "",
            "ext": ""
        }
        应答包体
        {
            "result": 0,
            "errmsg": "OK",
            "ext": "",
            "sid": "xxxxxxx",
            "fee": 1
        } */

        $req = self::buildReq([
            'api' => self::API_URL_SINGLE,
            'nationCode' => $nationCode,
            'mobile' => $mobile,
            'ext' => $ext,
        ]);

        $req['data'] += [
            'tpl_id' => $tplId,
            'params' => $tplParams,
            'extend' => empty($extend) ? '' : $extend,
        ];

        if (!empty($sign) && is_string($sign))
        {
            $req['data']['sign'] = $sign;
        }

        return self::send($req['url'], $req['data']);
    }

    /**
     * 语言验证码发送
     * @param string $nationCode 国家码，如 86 为中国
     * @param string $mobile 不带国家码的手机号
     * @param string $msg 信息内容，必须与申请的模板格式一致，否则将返回错误
     * @param int $playtimes 信息内容，必须与申请的模板格式一致，否则将返回错误
     * @param string $ext 服务端原样返回的参数，可填空串
     * @return array json { "result": xxxxx, "errmsg": "xxxxxx" ... }，被省略的内容参见协议文档
     */
    function sendVoiceVerifyCode($nationCode, $mobile, $msg, $playtimes = 2, $ext = "")
    {
        $req = self::buildReq([
            'api' => self::API_URL_VOICE_VERIFY_CODE,
            'nationCode' => $nationCode,
            'mobile' => $mobile,
            'ext' => $ext,
        ]);

        $req['data'] += [
            'msg' => $msg,
            'playtimes' => $playtimes,
        ];

        return self::send($req['url'], $req['data']);
    }

    /**
     * 语言验证码发送
     * @param string $nationCode 国家码，如 86 为中国
     * @param string $mobile 不带国家码的手机号
     * @param string $prompttype 语音类型目前固定值，2
     * @param string $msg 信息内容，必须与申请的模板格式一致，否则将返回错误
     * @param string $playtimes 播放次数
     * @param string $ext 服务端原样返回的参数，可填空串
     * @return array json { "result": xxxxx, "errmsg": "xxxxxx" ... }，被省略的内容参见协议文档
     */
    function sendVoicePrompt($nationCode, $mobile, $msg, $playtimes = 2, $ext = "")
    {
        $req = self::buildReq([
            'api' => self::API_URL_VOICE_PROMPT,
            'nationCode' => $nationCode,
            'mobile' => $mobile,
            'ext' => $ext,
        ]);

        $req['data'] += [
            'promptfile' => $msg,
            'prompttype' => 2,
            'playtimes' => $playtimes,
        ];

        return self::send($req['url'], $req['data']);
    }

    private static function rand()
    {
        return rand(100000, 999999);
    }

    private static function createSig($appKey, $random, $time, $phones)
    {
        $mobile = '';

        if (is_array($phones))
        {
            $mobile = implode(',', $phones);
        }
        else if (is_string($phones))
        {
            $mobile = $phones;
        }

        return hash("sha256",
            "appkey={$appKey}&random={$random}&time={$time}&mobile={$mobile}");
    }

    private function buildReq(array $params)
    {
        $random = self::rand();
        $time = time();
        $url = HttpHelper::wrapUrl($params['api'], [
            'sdkappid' => $this->appId,
            'random' => $random
        ]);

        return [
            'url' => $url,
            'data' => [
                'tel' => [
                    'nationcode' => "{$params['nationCode']}",
                    'mobile' => "{$params['mobile']}",
                ],
                'sig' => self::createSig($this->appKey, $random, $time, $params['mobile']),
                'time' => $time,
                'ext' => empty($params['ext']) ? '' : $params['ext'],
            ],
        ];
    }

    private static function send($url, $dataObj)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($dataObj));
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        $res = [
            'result' => 0,
        ];

        if (false === $ret) {
            $res = [
                'result' => -2,
                'errmsg' => $error,
            ];
        }
        else
        {
            $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if (200 != $statusCode) {
                $res = [
                    'result' => -1,
                    'errmsg' => "code:{$statusCode}, info:{$error}",
                ];
            } else {
                $res = json_decode($ret, true);
            }
        }

        return $res;
    }
}