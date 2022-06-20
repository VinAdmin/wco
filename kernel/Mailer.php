<?php

namespace vadc\kernel;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private static $config = null;
    
    function __construct() {
        try{
            $mail = new PHPMailer(true);
            $mail->SMTPOptions = ['ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )];
            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                    //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->CharSet = "UTF-8";
            $mail->Host       = \vadc::$config['email']['host'];               //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            
            $mail->Username   = \vadc::$config['email']['username'];           //SMTP username
            $mail->Password   = \vadc::$config['email']['password'];           //SMTP password
            //$mail->SMTPSecure = 'ssl';                                //Enable implicit TLS encryption
            $mail->Port       = \vadc::$config['email']['port'];

            self::$config = $mail;
        } catch (Exception $ex) {

        }
    }
    
    static public function sendEmail($to,$subject,$text) {
        global $config;
        try{
            self::$config->setFrom($config['email']['username'], 'Informer');
            self::$config->addAddress($to);     //Add a recipient
            self::$config->Subject = $subject;
            self::$config->Body    = $text;
            self::$config->AltBody = 'This is the body in plain text for non-HTML mail clients';
            self::$config->send();
        } catch (Exception $ex) {

        }
    }
}
