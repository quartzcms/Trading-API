<?php

namespace Core\Trading;

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class NotificationService
{
	public static function sendDailyResult($result, $smtp_host, $smtp_username, $smtp_password, $smtp_port, $from_email, $to_email)
	{
		if (intval($result) === 0 || $smtp_host == '' || $smtp_username == '' || $smtp_password == ''
			|| $smtp_port == '' || $from_email == '' || $to_email == '') {
			return;
		}

		$mail_body = ((int) $result > 0 ? 'Today, you gained ' : 'Today, you lost ') . $result . '$';
		$mail = new PHPMailer(true);

		try {
			$mail->isSMTP();
			$mail->Host = $smtp_host;
			$mail->SMTPAuth = true;
			$mail->Username = $smtp_username;
			$mail->Password = $smtp_password;
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
			$mail->Port = $smtp_port;
			$mail->setFrom($from_email, 'Trading-API');
			$mail->addAddress($to_email);
			$mail->isHTML(true);
			$mail->Subject = 'Trading Results';
			$mail->Body = $mail_body;
			$mail->AltBody = $mail_body;
			$mail->send();
		} catch (Exception $exception) {
			error_log('Failed to send trading results email: ' . $exception->getMessage());
		}
	}
}
