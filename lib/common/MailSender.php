<?php
/**
 * 邮件发送者
 * User: yejian
 * Date: 2016/3/30
 * Time: 20:45
 */

namespace app\lib\common;

use Yii;

class MailSender
{
    public static function send($email, $subTitle, $template, $content)
    {
        return static::sendImp($email, $subTitle, $template, ['content' => $content]);
    }

    protected static function sendImp($email, $subTitle, $template, $params)
    {
        try
        {
            $mail = Yii::$app->mailer->compose($template, $params)
                ->setTo($email)
                ->setSubject($subTitle);

            if (!$mail->send())
            {
                return false;
            }
        } catch (\Exception $ex)
        {
            return false;
        }

        return true;
    }
}