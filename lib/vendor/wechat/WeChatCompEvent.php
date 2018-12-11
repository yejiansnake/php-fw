<?php
/**
 * 微信第三方事件处理
 */

namespace app\lib\vendor\wechat;

class WeChatCompEvent extends WeChatEvent
{
    protected function init(array $options)
    {
        $options['encryptType'] = self::ENCRYPT_TYPE_SAFE;

        parent::init($options);
    }
}