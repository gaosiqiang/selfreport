<?php
class Mail
{
    public static function send($to, $subject, $content, $type)
    {
        Yii::app()->mailer->IsSMTP();
//        Yii::app()->mailer->SMTPSecure = 'tls';
        Yii::app()->mailer->SMTPAuth = TRUE;
        Yii::app()->mailer->Host = Yii::app()->params['smtp']['host'];
        Yii::app()->mailer->Port = Yii::app()->params['smtp']['port'];
        Yii::app()->mailer->Username = Yii::app()->params['smtp']['username'];
        Yii::app()->mailer->Password = Yii::app()->params['smtp']['password'];
        Yii::app()->mailer->From = Yii::app()->params['smtp']['from'];
        Yii::app()->mailer->FromName = Yii::app()->params['smtp']['fromName'];
        Yii::app()->mailer->AddReplyTo(Yii::app()->params['smtp']['replyTo']);
        Yii::app()->mailer->CharSet = Yii::app()->params['smtp']['charset'];
        Yii::app()->mailer->ContentType = Yii::app()->params['smtp']['contentType'];
        
        $email = '';
        if (is_array($to))
        {
            foreach ($to as $e)
            {
                Yii::app()->mailer->AddAddress($e);
            }
            $email = implode(',', $to);
        }
        else
        {
            Yii::app()->mailer->AddAddress($to);
            $email = $to;
        }
        
        Yii::app()->mailer->Subject = $subject;
        Yii::app()->mailer->Body = iconv('utf-8','utf-8//IGNORE',$content);
        Yii::app()->mailer->IsHTML(true);
        $status = Yii::app()->mailer->Send();
        
        MailLog::createLog($email, $status, $type, $subject);
    }
}
