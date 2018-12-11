<?php

namespace app\lib\vendor\SendCloud;

use app\lib\common\WebRequest;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Yii;
use yii\web\BadRequestHttpException;

class MailClient
{
    const SEND_CLOUD_URL = 'http://api.sendcloud.net/apiv2/mail/sendtemplate';

    public static function send(array $params, array $config, $fun)
    {
        if (empty($params) || empty($fun) || empty($config))
        {
            throw new BadRequestHttpException('invalid params');
        }

        return self::$fun($params, $config);
    }

    private function smtp(&$params, &$config)
    {
        if (empty($params['subject'])
            || empty($params['toUser'])
            || empty($params['content'])
            || empty($config['host'])
            || empty($config['port'])
            || empty($config['username'])
            || empty($config['password'])
            || empty($config['from'])
        )
        {
            throw new BadRequestHttpException('params invalid');
        }

        $transport = Swift_SmtpTransport::newInstance($config['host'], $config['port'])
            ->setUsername($config['username'])
            ->setPassword($config['password']);
        $mailer = Swift_Mailer::newInstance($transport);
        $fromName = (!empty($config['fromName'])) ? $config['fromName'] : '';
        $message = Swift_Message::newInstance($params['subject'])
            ->setFrom([$config['from'] => $fromName])
            ->setTo($params['toUser'])
            ->setBody($params['content'])
            ->setContentType('text/html');

        return $mailer->send($message);
    }


    private function sendCloud(&$params, &$config)
    {
        if (empty($params['templateInvokeName'])
            || empty($params['xsmtpApi'])
            || empty($config['apiUser'])
            || empty($config['apiKey'])
            || empty($config['from'])
        )
        {
            throw new BadRequestHttpException('params invalid');
        }

        $fromName = !empty($config['fromName']) ? $config['fromName'] : null;
        $postData = [
            'apiUser' => $config['apiUser'],
            'apiKey' => $config['apiKey'],
            'from' => $config['from'],
            'xsmtpapi' => json_encode($params['xsmtpApi'], JSON_UNESCAPED_UNICODE),
            'templateInvokeName' => $params['templateInvokeName'],
            'fromName' => $fromName
        ];

        return WebRequest::getResponse(self::SEND_CLOUD_URL,
            [
                'post' => $postData,
            ]
        );
    }
}