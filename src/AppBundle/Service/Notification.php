<?php

namespace AppBundle\Service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Notification
{
    public $mail = null;

    function __construct() {
        $mail = new PHPMailer(true);
        //Server settings
        $mail->isSMTP();                                        // Set mailer to use SMTP
        $mail->Host = 'smtp.gmail.com';                         // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;                                 // Enable SMTP authentication
        $mail->Username = 'cristianperez.bot@gmail.com';         // SMTP username
        $mail->Password = 'tqwadkrliufqiwfu';                    // SMTP password
        $mail->SMTPSecure = 'tls';                              // Enable TLS encryption, `ssl` also accepted
        $mail->Port = 587;                                      // TCP port to connect to
        $mail->setLanguage('es');
        $mail->CharSet = 'UTF-8';
        //Recipients
        $mail->setFrom('cristianperez.bot@gmail.com', 'TODO_BOT');

        $this->mail = $mail;
    }

    public function send_email($body,$subject,$address="cristian.perez.hernandez.96@gmail.com")
    {
        $mail = $this->mail;
        $mail->addAddress($address);     // Add a recipient
        
        //Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = 'Lo sentimos, utilice un cliente de correo que soporte HTML';
        
        try {
            $mail->send();
        } catch (\Throwable $e) {
            return [false,'Mailer Error: ' . $mail->ErrorInfo];
        }
 
        return true;
    }
}