<?php

Class UploadApi
{
	private $debugQuery = true;
	private $uploadsDirectory;
	private $shortPath = "";
	public $error;
	public $errors = array(1 => 'php.ini max file size exceeded',
	                2 => 'html form max file size exceeded',
	                3 => 'file upload was only partial',
	                4 => 'no file was attached');
					
	function __construct($gentype, $uploadsDirectory)
	{
		
		$this->queries = parse_ini_file(RequestHandler::GetBaseDirectory() . "/ini/upload.ini");
		
		$this->uploadsDirectory = $uploadsDirectory.$gentype;
		
		// get short path
		$tmp = preg_split('[/]', $uploadsDirectory);
		
		$found = false;
		foreach($tmp as $index => $path)
		{
			if($path == "images" && $found == false)
				$found = true;
			if($found)
			{
				$this->shortPath .= '/'.$path;
			}
		}
		$this->shortPath .= $gentype;
	}
	
	function UploadError($msg)
	{
		trigger_error($error);
		$this->error = $error;
		
	}
	public function getPathInfo($str)
	{
		$md5 = md5($str);
		$level1 = substr($md5, 2, 6);
		$level2 = substr($md5, 7, 6);
		$level3 = substr($md5, 13, 6);
		
		return array($level1, $level2, $level3);
	}
	function moveFile($htmlFieldname, $maxfileSize)
	{
		
		if($_FILES[$htmlFieldname]['size'] <= $maxfileSize )
		{
			$lvlArray = $this->getPathInfo($_FILES[$htmlFieldname]['name']);
			
			$structure = $this->uploadsDirectory."/".$lvlArray[0]."/".$lvlArray[1]."/".$lvlArray[2];
			
			$this->shortPath .= "/".$lvlArray[0]."/".$lvlArray[1]."/".$lvlArray[2];
			
			@mkdir($this->uploadsDirectory."/".$lvlArray[0], 0777, true)
			or $this->error('Error Making Directory Structure:'.$structure);
			
			chmod($this->uploadsDirectory."/".$lvlArray[0], 0777);
			
			@mkdir($this->uploadsDirectory."/".$lvlArray[0]."/".$lvlArray[1], 0777)
			or $this->error('Error Making Directory Structure:'.$structure);
			
			chmod($this->uploadsDirectory."/".$lvlArray[0]."/".$lvlArray[1], 0777);
			
			@mkdir($this->uploadsDirectory."/".$lvlArray[0]."/".$lvlArray[1]."/".$lvlArray[2], 0777)
			or $this->error('Error Making Directory Structure:'.$structure);
			
			chmod($this->uploadsDirectory."/".$lvlArray[0]."/".$lvlArray[1]."/".$lvlArray[2], 0777);
			
			
			$now = time();
			while(file_exists($uploadFilename = $structure.'/'.$now.'-'.$_FILES[$htmlFieldname]['name']))
			{
			    $now++;
			}
			$this->shortPath .= '/'.$now.'-'.$_FILES[$htmlFieldname]['name'];
			
			//trigger_error($_FILES[$htmlFieldname]['tmp_name']);
			//trigger_error($uploadFilename);
			
			@move_uploaded_file($_FILES[$htmlFieldname]['tmp_name'], $uploadFilename)
		    or $this->error('receiving directory insuffiecient permission');
			
			
			return $this->shortPath;
		}
		else
		{
			$this->error('Max file Size Exceeded(max: '.$maxfileSize.', your file: '.$_FILES[$htmlFieldname]['size'].')');
		}
	}
	
	function error($str)
	{
		trigger_error($str);
	}
	
	
	    
	
	

}
?>