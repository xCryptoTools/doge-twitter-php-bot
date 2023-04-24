<?php

function send_mail($mail, $message) {
	
	// Mailer
	$mailer = new PHPMailer\PHPMailer\PHPMailer;

	if ($mail['smtp'] == true) {
		$mailer->isSMTP();
		$mailer->SMTPAuth = true;
		$mailer->SMTPSecure = $mail['smtp_secure'];
		$mailer->Host = $mail['smtp_host'];
		$mailer->Port = $mail['smtp_port'];
		$mailer->Username = $mail['smtp_username'];
		$mailer->Password = $mail['smtp_password'];
	}
	$mailer->Timeout = 10;

	$mailer->From = $mail['from'];
	$mailer->addAddress($mail['to']);

	$mailer->WordWrap = 70;
	$mailer->Subject = $message;
	$mailer->Body = $message . '

---
This is an automated e-mail from your Twitter bot';

	// send mail
	if ($mailer->send()) {
		echo '<br /><br />E-mail sent succesfully.';
	} else {
		echo '<br /><br />Error sending e-mail! Please check your configuration.';
	}
}
