<?php

namespace app\lib\vendor\wechat\base;

/**
 * PKCS7Encoder class
 *
 * 提供基于PKCS7算法的加解密接口.
 */
class PKCS7Encoder
{
	const block_size = 32;

	/**
	 * 对需要加密的明文进行填充补位
	 * @param string $text 需要进行填充补位操作的明文
	 * @return string 补齐明文字符串
	 */
	public static function encode($text)
	{
		$text_length = strlen($text);

		//计算需要填充的位数
		$amount_to_pad = self::block_size - ($text_length % self::block_size);

		if ($amount_to_pad == 0)
        {
			$amount_to_pad = self::block_size;
		}

		//获得补位所用的字符
		$pad_chr = chr($amount_to_pad);
		$tmp = "";

		for ($index = 0; $index < $amount_to_pad; $index++)
        {
			$tmp .= $pad_chr;
		}

		return $text . $tmp;
	}

	/**
	 * 对解密后的明文进行补位删除
	 * @param string $text 解密后的明文
	 * @return string'删除填充补位后的明文
	 */
    public static function decode($text)
	{
		$pad = ord(substr($text, -1));

		if ($pad < 1 || $pad > 32)
        {
			$pad = 0;
		}

		return substr($text, 0, (strlen($text) - $pad));
	}

}

/**
 * Prpcrypt class
 *
 * 提供接收和推送给公众平台消息的加解密接口.
 */
class Prpcrypt
{
	private static function createKey($key)
	{
		return base64_decode($key . "=");
	}

	/**
	 * 对明文进行加密
	 * @param string $text 需要加密的明文
     * @param string $appID 公众平台的 appID
	 * @return string 加密后的密文
	 */
	public static function encrypt($data, $aesKey, $appID)
	{
        if (empty($data) || empty($aesKey) || empty($appID))
        {
            self::throwException(ErrorCode::EncryptAESError);
        }

        $key = self::createKey($aesKey);

        //获得16位随机字符串，填充到明文之前
        $random = self::getRandomStr();
        $text = $random . pack("N", strlen($data)) . $data . $appID;

        // 网络字节序
        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');
        $iv = substr($key, 0, 16);

        //使用自定义的填充方式对明文进行补位填充
        $text = PKCS7Encoder::encode($text);
        mcrypt_generic_init($module, $key, $iv);

        //加密
        $encrypted = mcrypt_generic($module, $text);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);

        //print(base64_encode($encrypted));
        //使用BASE64对加密后的字符串进行编码
        return base64_encode($encrypted);
	}

	/**
	 * 对密文进行解密
	 * @param string $encrypted 需要解密的密文
	 * @return string 解密得到的明文
	 */
	public static function decrypt($encrypted, $aesKey, $appID)
	{
        if (empty($encrypted) || empty($aesKey) || empty($appID))
        {
            self::throwException(ErrorCode::DecryptAESError);
        }

        $key = self::createKey($aesKey);

        //使用BASE64对需要解密的字符串进行解码
        $cipherText_dec = base64_decode($encrypted);

        $module = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_CBC, '');

        //LogMgr::addEventLog(__METHOD__, LogMgr::LEVEL_INFO, "Prpcrypt::decrypt 1 start");

        $iv = substr($key, 0, 16);

        mcrypt_generic_init($module, $key, $iv);

        //LogMgr::addEventLog(__METHOD__, LogMgr::LEVEL_INFO, "Prpcrypt::decrypt 1 end");

        //解密
        $decrypted = mdecrypt_generic($module, $cipherText_dec);
        mcrypt_generic_deinit($module);
        mcrypt_module_close($module);

        //去除补位字符
        $result = PKCS7Encoder::decode($decrypted);

        //去除16位随机字符串,网络字节序和AppId
        if (strlen($result) < 16)
        {
            self::throwException(ErrorCode::IllegalBuffer);
        }

        $content = substr($result, 16, strlen($result));
        $len_list = unpack("N", substr($content, 0, 4));
        $xml_len = $len_list[1];
        $xml_content = substr($content, 4, $xml_len);
        $from_appID = substr($content, $xml_len + 4);

		if ($from_appID != $appID)
        {
            self::throwException(ErrorCode::ValidateAppidError);
        }

		return $xml_content;
	}

	/**
	 * 随机生成16位字符串
	 * @return string 生成的字符串
	 */
    public static function getRandomStr()
	{
		$str = "";
		$str_pol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($str_pol) - 1;

		for ($i = 0; $i < 16; $i++)
        {
			$str .= $str_pol[mt_rand(0, $max)];
		}

		return $str;
	}

    private static function throwException($code)
    {
        throw new \Exception("prp crypt error, code:{$code}", $code);
    }
}

?>