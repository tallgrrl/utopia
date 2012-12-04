<?php

class RequestHandler
{
	static $base;
	static $selector;

	public function RequestHandler()
	{
		throw new Exception("RequestHandler is a static class");
	}

	public static function GetBaseDirectory()
	{
		static $baseDirectory;

		if (!isset($baseDirectory))
			$baseDirectory = dirname(__FILE__) . '/../';

		return $baseDirectory;
	}

	public static function GetSelectorName()
	{
		if (!isset($selector))
			self::$selector = $_REQUEST['__select'];

		if (empty(self::$selector))
			self::$selector = 'index';

		return self::$selector;
	}

	public static function GetSelectorFile($selector)
	{
		return self::GetBaseDirectory() . '/handlers/' . ($selector === false ? self::GetSelectorName() : $selector). '.php';
	}

	public static function SetSelector($newSelector)
	{
		self::$selector = $newSelector;
	}

	public static function GetParams()
	{
		return $_REQUEST['__param'];
	}

	public static function ExecuteRequest($selector = false)
	{
		if ($selector === false)
			$selector = self::GetSelectorName();

		$inc_file = self::GetSelectorFile($selector);

		ob_end_clean();
		ob_start();

		try
		{
			if (file_exists($inc_file))
			{
				header('Cache-Control: no-cache');
				header('Pragma: no-cache');

				include ($inc_file);
			}
			else
				throw new FileNotFoundException("$selector");
		}
		catch (BadRequestException $brex)
		{
			trigger_error("Invalid request: " . $brex->getMessage(), E_USER_WARNING);

			if ($selector != '400')
			{
				header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
				self::ExecuteRequest('400');
				exit;
			}
		}
		catch (FileNotFoundException $fofex)
		{
			//trigger_error("Invalid selector: " . $fofex->getMessage(), E_USER_WARNING);

			if ($selector != '404')
			{
				header($_SERVER['SERVER_PROTOCOL'] . ' 404 File Not Found');
				self::ExecuteRequest('404');
				exit;
			}
		}
		catch (FoundElsewhereRedirectException $ferex)
		{
			// exception message contains the URL to be redirected to
			header($_SERVER['SERVER_PROTOCOL'] . ' 302 Found Elsewhere');
			header('Location: ' . $ferex->getMessage());
			exit;
		}
		catch (RedirectException $rex)
		{
			// exception message contains the URL to be redirected to
			header($_SERVER['SERVER_PROTOCOL'] . ' 301 Moved Permanently');
			header('Location: ' . $rex->getMessage());
			exit;
		}
		catch (TemporaryRedirectException $trex)
		{
			// exception message contains the URL to be redirected to
			header($_SERVER['SERVER_PROTOCOL'] . ' 307 Moved Temporarily');
			header('Location: ' . $trex->getMessage());
			exit;
		}
		catch (Exception $ex)
		{
			trigger_error("Exception: " . $ex->getMessage(), E_USER_WARNING);

			if ($selector != 'error')
			{
				self::ExecuteRequest('error');
				exit;
			}

			print "An error has occurred, please try again later.";
		}

		while(ob_get_level() > 0)
			ob_end_flush();
	}
}

class BadRequestException extends Exception {}
class FileNotFoundException extends Exception {}
class RedirectException extends Exception {}
class FoundElsewhereRedirectException extends Exception {}
class TemporaryRedirectException extends Exception {}

?>
