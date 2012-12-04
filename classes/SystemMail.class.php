<?
Class SystemMail
{
	public static function CreateGuild($playerId, $guildName, $guildId, $shortName)
	{
		$mail = new MailApi();
		$vararray = array( '/__GUILD__/' => $guildName,'/__GUILDID__/' => $guildId, '/__GUILDSMALLCAPS__/'=> $shortName);
		$mail->sendSystemMail($playerId, "New Guild Created ".$guildName, "newGuild.html", $vararray);
		
	}
	public static function InviteMember($playerId, $guildName, $guildId, $shortName, $emailContent=null)
	{
		$mail = new MailApi();
		$vararray = array( '/__GUILD__/' => $guildName, '/__GUILDSMALLCAPS__/'=> $shortName);
		if(!$emailContent)
		{
			$mail->sendSystemMail($playerId, "Guild Invitation from ".$guildName, "guildInvite.html", $vararray);
		}
		else
		{
			$mail->sendSystemMail($playerId, "Guild Invitation from ".$guildName, "guildInvite.html", $vararray, $emailContent);
		}		
	}
	public static function NewPlayer($playerId, $username)
	{
		$mail = new MailApi();
		$vararray = array( '/__USERNAME__/' => $username);
		$mail->sendSystemMail($playerId, "Welcome to Pantheon", "welcome.html", $vararray);
	}
}
		