<?php

require_once "class.phpmailer.php";

Class SendMail 
{
	private $toEmail;
	private $fromEmail;
	private $subject;
	private $html;
	private $text;
	private $replyTo;
	private $random_hash;
	private $host = 'smtp.gmail.com';
	private $port = 465; 
	private $auth = true;
	private $username = 'Tracy@kiinzel.com'; 
	private $password = 'kiinzel2013';
	private $firstlast = "Tracy Lauren";
	
	public function __construct($toEmail,  $subject, $fromEmail="Tracy@kiinzel.com",$replyTo="Tracy@kiinzel.com")
    {
		$this->toEmail = $toEmail;
		$this->fromEmail = $fromEmail;		
		$this->subject = $subject;
		$this->replyTo = $replyTo;
		$this->random_hash = md5(date('r', time()))."@kiinzel.com";
	}
	public function setUser($user)
	{
		$this->username = $user;
	}
	public function setPass($pass)
	{
		$this->password = $pass;
	}
	public function setFirstLast($fl)
	{
		$this->firstlast = $fl;
	}
	public function setHtml($path)
	{
		$this->html = file_get_contents(dirname(__FILE__) ."/../templates/email/$path");
	}
	
	public function setText($path)
	{
		$this->html = file_get_contents(dirname(__FILE__) ."/../templates/email/$path");
	}
	
	public function replace($pattern, $string)
	{
		$this->html = preg_replace($pattern, $string, $this->html);
		$this->text = preg_replace($pattern, $string, $this->text);
	}
	
	private function buildmail()
	{
	
		$output = "--PHP-alt-".$this->random_hash."\r\n";
		$output .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";
		$output .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	
		$output .= $this->text."\r\n";
	
		$output .= "--PHP-alt-".$this->random_hash."\r\n";
		$output .= "Content-Type: text/html; charset=\"iso-8859-1\"\r\n";
		$output .= "Content-Transfer-Encoding: 7bit\r\n";
		$output .= $this->html."\r\n";
	
		$output .= "--PHP-alt-".$this->random_hash."--\r\n";
	
		return $output;
	}
	
	public function send()
	{
		$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
		$mail->IsSMTP();
		
		try {
			$mail->Host       = $this->host; // SMTP server
			$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
			$mail->SMTPAuth   = true;                  // enable SMTP authentication
			$mail->SMTPSecure = 'ssl';
			$mail->Port       = $this->port;                    // set the SMTP port for the GMAIL server
			$mail->Username   = $this->username; // SMTP account username
			$mail->Password   = $this->password;        // SMTP account password
			$mail->AddReplyTo($this->replyTo, $this->firstlast);
			$mail->AddAddress($this->toEmail, "");
			$mail->SetFrom($this->fromEmail, $this->firstlast);
			$mail->Subject = $this->subject;
			$mail->AltBody = $this->text; // optional - MsgHTML will create an alternate automatically
			$mail->MsgHTML($this->html);
			//$mail->AddAttachment('images/phpmailer.gif');      // attachment
			//$mail->AddAttachment('images/phpmailer_mini.gif'); // attachment
			$mail->Send();
			return 1;
		} catch (phpmailerException $e) {
  			trigger_error( $e->errorMessage() );
			//echo $e->errorMessage();
			return 0; //Pretty error messages from PHPMailer
		} catch (Exception $e) {
			trigger_error( $e->getMessage() );
			//echo $e->getMessage();
  			return -1; //Boring error messages from anything else!
		}
	}
}
?>