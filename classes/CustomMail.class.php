<?
Class KiinzelMail
{
	public static function sendConfirmation($email, $name, $url)
	{
		$mail = new SendMail($email, 'Kiinzel Confirmation' );

		$mail->setHtml('external/kiinzelConfirm.html');
		$mail->setText('external/kiinzelConfirm.txt');
		
		$mail->replace('/__NAME__/', $name);
		$mail->replace('/__URL__/', $url);
		$mail->replace('/__EMAIL__/', $email);
		
		$mail->send();
	}
	public static function sendFBGreeting($email, $name, $password, $url)
	{
		$mail = new SendMail($email, 'Welcome To Kiinzel!' );

		$mail->setHtml('external/fbWelcome.html');
		$mail->setText('external/fbWelcome.txt');
		
		$mail->replace('/__NAME__/', $name);
		$mail->replace('/__URL__/', $url);
		$mail->replace('/__PASS__/', $url);

		$mail->send();
	}
	public static function sendForgot($email, $name, $url)
	{
		$mail = new SendMail($email, 'Forgot Password' );

		$mail->setHtml('external/forgotPass1.html');
		$mail->setText('external/forgotPass1.txt');
		
		$mail->replace('/__NAME__/', $name);
		$mail->replace('/__URL__/', $url);

		$mail->send();
	}
	public static function sendForgot1($email, $name)
	{
		$mail = new SendMail($email, 'Forgot Email (step 1)' );

		$mail->setHtml('external/forgot1.html');
		$mail->setText('external/forgot1.txt');
		
		$mail->replace('/__NAME__/', $name);
		
		$mail->send();
	}
	public static function sendForgot2($email, $name, $password)
	{
		$mail = new SendMail($email, 'Forgot Email (step 2)' );

		$mail->setHtml('external/forgot2.html');
		$mail->setText('external/forgot3.txt');
		
		$mail->replace('/__NAME__/', $name);
		$mail->replace('/__PASS__/', $password);
		
		$mail->send();
	}
}
?>