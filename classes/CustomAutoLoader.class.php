<?php

class CustomAutoLoader
{
	private static $BaseDirectory;

	private static $PathSpec = array(
			'api/%s.api.php',
			'classes/%s.class.php',
			'objects/%s.object.php',
            'classes/%s.interface.php'
		);

	public static function AutoLoad($class)
	{
		if (!isset(self::$BaseDirectory))
			self::$BaseDirectory = dirname(__FILE__) . '/../';

		foreach (self::$PathSpec as $pathSpec)
		{
			
			$includeFile = self::$BaseDirectory . '/' . sprintf($pathSpec, $class);
			if (file_exists($includeFile))
			{
				require_once ($includeFile);
				return;
			}
		}
	}
}

// register the autoloader
spl_autoload_register('CustomAutoLoader::AutoLoad');

?>
