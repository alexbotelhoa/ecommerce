<?php

namespace Hcode;

use Rain\Tpl;

class Mailer
{

    const USERNAME = "alexbotelho1@hotmail.com";
    const PASSWORD = "-";
    const NAME_FROM = "ABA ecommerce";

    private $mail;

    public function __construct($toAddress, $toName, $subject, $tplName, $data = array())
    {
        $config = array(
            "tpl_dir" => $_SERVER["DOCUMENT_ROOT"] . "/ecommercer/app/views/email/",
            "cache_dir" => $_SERVER["DOCUMENT_ROOT"] . "/ecommercer/app/views/cache/",
            "debug" => false
        );

        Tpl::configure($config);

        $tpl = new Tpl();

        foreach ($data as $key => $value) {
            $tpl->assign($key, $value);
        }

        $html = $tpl->draw($tplName, true);

        $this->mail = new PHPMailer;

        //Tell PHPMailer to use SMTP
        $this->mail->isSMTP();

        //Enable SMTP debugging
        $this->mail->SMTPDebug = 0;

        $this->mail->Debugoutput = 'html';

        //Set the hostname of the mail server
        $this->mail->Host = 'smtp.office365.com';

        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $this->mail->Port = 587;

        //Set the encryption mechanism to use - STARTTLS or SMTPS
        $this->mail->SMTPSecure = 'tls';

        //Whether to use SMTP authentication
        $this->mail->SMTPAuth = true;

        //Username to use for SMTP authentication - use full email address for gmail
        $this->mail->Username = Mailer::USERNAME;

        //Password to use for SMTP authentication
        $this->mail->Password = Mailer::PASSWORD;

        //Set who the message is to be sent from
        $this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

        //Set an alternative reply-to address
        //$mail->addReplyTo('replyto@hotmail.com.br', 'Email Fake');

        //Set who the message is to be sent to
        $this->mail->addAddress($toAddress, $toName);

        //Set the subject line
        $this->mail->Subject = $subject;

        //Read an HTML message body from an external file, convert referenced images to embedded,
        //convert HTML into a basic plain-text alternative body
        $this->mail->msgHTML($html);

        //Replace the plain text body with one created manually
        $this->mail->AltBody = 'Teste de envio de email do curso de PHP 7';

        //Attach an image file
        //$mail->addAttachment('images/phpmailer_mini.png');
    }

    public function send()
    {
        return $this->mail->send();
    }

}

?>