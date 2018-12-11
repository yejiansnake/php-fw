<?php

namespace app\lib\vendor\wechat\base;

/**
 * 对公众平台发送给公众账号的消息加解密示例代码.
 *
 * @copyright Copyright (c) 1998-2014 Tencent Inc.
 */
use app\lib\bl\LogMgr;

/**
 * 1.第三方回复加密消息给公众平台；
 * 2.第三方收到公众平台发送的消息，验证消息的安全性，并对消息进行解密。
 */
class WXBizMsgCrypt
{
    /*
     * 将公众平台回复用户的消息加密打包.
     * <ol>
     *    <li>对要发送的消息进行AES-CBC加密</li>
     *    <li>生成安全签名</li>
     *    <li>将消息密文和安全签名打包成xml格式</li>
     * </ol>
     */
	public static function encryptMsg(array $params)
	{
		if (empty($params['token'])
			|| empty($params['aesKey'])
			|| empty($params['appID'])
			|| empty($params['data']))
		{
			self::throwException(ErrorCode::ParamError);
		}

		$token = $params['token'];
		$encodingAesKey = $params['aesKey'];
		$appId = $params['appID'];
		$replyMsg = $params['data'];

		//加密
        $encrypt = Prpcrypt::encrypt($replyMsg, $encodingAesKey, $appId);

        $nonce = Prpcrypt::getRandomStr();
        $timeStamp = time();

		//生成安全签名
        $signature = self::getSHA1($token, $timeStamp, $nonce, $encrypt);

		//生成发送的xml
		return self::generate($encrypt, $signature, $timeStamp, $nonce);
	}

	/*
	 * 检验消息的真实性，并且获取解密后的明文.
	 * <ol>
	 *    <li>利用收到的密文生成安全签名，进行签名验证</li>
	 *    <li>若验证通过，则提取xml中的加密消息</li>
	 *    <li>对消息进行解密</li>
	 * </ol>
	 */
	public static function decryptMsg(array $params)
	{
		if (empty($params['token'])
			|| empty($params['aesKey'])
			|| empty($params['appID'])
			|| empty($params['sign'])
			|| empty($params['timestamp'])
			|| empty($params['nonce'])
			|| empty($params['data']))
		{
			self::throwException(ErrorCode::ParamError);
		}

		$token = $params['token'];
		$encodingAesKey = $params['aesKey'];
		$appId = $params['appID'];
		$msgSignature = $params['sign'];
		$timestamp = $params['timestamp'];
		$nonce = $params['nonce'];
		$encryptData = $params['data'];

		if (strlen($encodingAesKey) != 43)
        {
            self::throwException(ErrorCode::IllegalAesKey);
		}

		//验证安全签名
        $signature = self::getSHA1($token, $timestamp, $nonce, $encryptData);

		if ($signature != $msgSignature)
        {
            self::throwException(ErrorCode::ValidateSignatureError);
		}

        //LogMgr::addEventLog(__METHOD__, LogMgr::LEVEL_INFO, "Prpcrypt::decrypt start");

		$res = Prpcrypt::decrypt($encryptData, $encodingAesKey, $appId);

        //LogMgr::addEventLog(__METHOD__, LogMgr::LEVEL_INFO, "Prpcrypt::decrypt end");

        return $res;
	}

	/*
	 * 生成xml消息
	 * @param string $encrypt 加密后的消息密文
	 * @param string $signature 安全签名
	 * @param string $timestamp 时间戳
	 * @param string $nonce 随机字符串
	 */
	private static function generate($encrypt, $signature, $timestamp, $nonce)
	{
		return "<xml>
<Encrypt><![CDATA[{$encrypt}]]></Encrypt>
<MsgSignature><![CDATA[{$signature}]]></MsgSignature>
<TimeStamp>{$timestamp}</TimeStamp>
<Nonce><![CDATA[{$nonce}]]></Nonce>
</xml>";
	}

    /*
     * 用SHA1算法生成安全签名
     * @param string $token 票据
     * @param string $timestamp 时间戳
     * @param string $nonce 随机字符串
     * @param string $encrypt 密文消息
     */
    private static function getSHA1($token, $timestamp, $nonce, $encrypt_msg)
    {
        if (empty($token) || empty($timestamp) || empty($nonce) || empty($encrypt_msg))
        {
            self::throwException(ErrorCode::ParamError);
        }

        //排序
        $array = [$encrypt_msg, $token, $timestamp, $nonce];
        sort($array, SORT_STRING);
        $str = implode($array);
        return sha1($str);
    }

    private static function throwException($code)
    {
        throw new \Exception("wx msg crypt error, code:{$code}", $code);
    }
}

