<?php
session_start();
$fieldname = 'file';
define('MAX_FILE_SIZE', 3000000);
$core = new Core();
$data = array();
$tmp = preg_split('[/]', $_SERVER['REQUEST_URI']);
	
	if(isset($tmp[2]))
		$tmpl = $tmp[2];
	if(isset($tmp[3]))
		$index = $tmp[3];
	if(isset($tmp[4]))
		$userId = $tmp[4];
	if(isset($tmp[5]))
		$listId = $tmp[5];
	
	
	if(!isset($userId))
		$userId = $_SESSION['kinzel']->id;
if(isset($_FILES) && count($_FILES)>0 && $_FILES[$fieldname]['size'] > 0)
{

	$api = new UploadApi($tmpl, $core->sitevars['UPLOAD_DIR_USER']);
	
	$imgPath = $api->moveFile($fieldname, MAX_FILE_SIZE); 
	switch($tmpl)
	{
		case 'profile':
			$uapi = new UserApi();
			$_SESSION['kinzel'] = $uapi->updateUser($_SESSION['kinzel']->id, 'profile_pic', $imgPath);
		break;
		case 'dreams':
			$data['dream_pic'] = $imgPath;
		break;
		case 'listing':
			$data['listId'] = $listId;

			$data['list_pic'] = $imgPath;
		break;
		
	}
	
	//update record
	
}

	
	
	
	
	$data['core'] = $core;
	switch($tmpl)
	{
		case 'profile':
			$uapi = new UserApi();
			
			$u = $uapi->getUser($userId);
			$data['user'] = $u;
			if($u->id == $_SESSION['kinzel']->id)
			{
				$data['mine'] = true;
			}
			else {
				$data['mine'] = false;
			}
			$data['profile_pic'] = $u->profile_pic;
			
				
		$x = preg_split("/\./", $data['profile_pic']);
		$ext = $x[1];
		$fileName = $x[0] . ":t,100,100." . $x[1];
		$data['profile_pic'] = $fileName;

			$core->display($data, 'uploadProfile.tpl');
		break;
		case 'dreams':
			$data['index'] = $index;
			
			if(!isset($data['dream_pic']))
			{
				//check to see if there is a dream:
				$dapi = new DreamApi();
				$iapi = new ImagesApi();
				$ThisDream = $dapi->getMyDreams($userId);
				
				if($ThisDream)
				{
					$images = $iapi->getImages($ThisDream->id, 'dream');
					if(isset($images[$index]))
						$data['dream_pic'] = $images[$index]->image;
				}	
			}

			$core->display($data, 'uploadDream.html');
		break;
		case 'listing':
			$data['index'] = $index;
			//print "LISTING";
			if(!isset($data['list_pic']))
			{
				//print "NO LISTPIC";
				//check to see if there is any listings:
				if(isset($listId) && $listId != '')
				{
					//print "listId is ".$listId;
					$lapi = new ListingsApi();
					$iapi = new ImagesApi();
					$Listings = $lapi->getListings($userId, $listId);
					
					if($Listings)
					{
						//print "FOUND LISTING";
						$images = $iapi->getImages($Listings[0]->id, 'listing');
						if(isset($images[$index]))
							$data['list_pic'] = $images[$index]->image;
					}
				}	
			}

			$core->display($data, 'uploadList.html');
		break;
	}

?>