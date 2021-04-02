<?php
namespace App;

/**
 * Define a custom exception class
 */
class UploadFiles
{
	public $doc;
    public $root;
	public $full_path;
	public $sub_folder;
	
	//This is the folder from the public folder where 
	public $docParentFolder = "uploads";
	public $width = 550;
	public $height = 505;
	public $valid_mimes = ["image/jpeg", "image/png", "image/gif"];
	public $max_file_upload_size = 5242880;//5MB
	public $percent = 0.5;
	public $max_no_of_file_to_upload = 5;
	public $name_of_file;
	
	//These are configurations for outside the web root where errors will be logged
	public $parentFolder = "modal";
	public $log_file_name = "admin_logs";
	public $log_file_ext = "txt";
	public $use_root = true;
	public $log_full_path;
	
	// construction function
	//It is assumed we're uploading products for display or sale but can be changed to anything else. This (folder) is actually a sub folder of $docParentFolder located in the public html folder.
	public function __construct($sub_folder="products", $user_defined_valid_mimes="", $override_mimes = false)
	{
		if(!defined("doc")) define("doc", $_SERVER['DOCUMENT_ROOT']);
		$this->doc = doc."/";
		
		if (!defined("root")) define("root",__DIR__);
		$this->root = root."/../../";
		
		if(!empty($sub_folder))
		{
			$this->sub_folder = $sub_folder;
		
		    //This shall be strictly for storing images and photos.
		    $this->full_path = $this->doc.$this->docParentFolder."/".$sub_folder."/";
		}
		else
		{
			$this->full_path = $this->doc.$this->docParentFolder."/";
		}
		
		//By default administrative logging should be done outside the webroot but if User chooses otherwise then it'll be done there.
		if($this->use_root == false)
		{
			$this->log_full_path = $this->doc.$this->parentFolder."/".$this->log_file_name.".".$this->log_file_ext."";//for logging
		}
		else
		{
			$this->log_full_path = $this->root.$this->parentFolder."/".$this->log_file_name.".".$this->log_file_ext."";//for logging
		}
		
		//We've a list of predefined allowed meme types, User can choose to either add to it or override and create custom
		if($override_mimes == true)
		{
			$this->valid_mimes = $user_defined_valid_mimes;
		}
		else
		{
			if(!empty($user_defined_valid_mimes))
			{
				for($i=0;$i<count($user_defined_valid_mimes);$i++)
				{
					$this->valid_mimes []= $user_defined_valid_mimes[$i];
				}
			}
		}
	}
	
	
    function unique_name($lenght = 13) 
	{
		$prefix = "webloit_".date("Ymd");
		// uniqid gives 13 chars, but you could adjust it to your needs.
        if (function_exists("random_bytes"))
		{
			$bytes = random_bytes(ceil($lenght / 2));
        }   
		elseif(function_exists("openssl_random_pseudo_bytes")) 
		{
			$bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
        } 
		else 
		{
			throw new Exception("no cryptographically secure random function available");
        }
        return substr($prefix.bin2hex($bytes), 0, $lenght);
    }
	
	public function file_size_convert($bytes)
	{
		$bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach($arBytes as $arItem)
        {
			if($bytes >= $arItem["VALUE"])
            {
				$result = $bytes / $arItem["VALUE"];
			    $result = strval(round($result, 2))." ".$arItem["UNIT"];
                break;
            }
        }
        return $result;
    }

	
	
	function read_file($filename="settings.json", $reason="auth")
	{
		if(empty($filename))
		{
			$filename = "settings.json";
		}
		
		switch($reason)
		{
			//Anything authentication must happen outside the web root folder.
			case "auth":
			    $src = $this->root.$this->parentFolder."/storehouse/$filename";
				
				break;
			
			//This is within the web root folder.
			default:
			    if(!empty($this->sub_folder))
				{
					$src = $this->doc.$this->docParentFolder."/".$this->sub_folder."/".$filename;
				}
				else
				{
					$src = $this->doc.$this->docParentFolder."/$filename";
				}
		}
		
		$content = file_get_contents($src);
		//echo $content;
		return $content;
		
	}



    function file_delete($file)
	{
		//var_dump($file);exit;
		if(is_array($file))
		{
			for($i=0;$i<count($file);$i++)
			{
				if(!@unlink($this->full_path.$file[$i]))//if file couldn't be deleted then exit and do nothing which also means it won't be deleted from database.
				{
					//We log it. We'll manually delete it ourselves.
			        $content = "Failed to delete ".$this->full_path."/{$file[$i]} ' on ".date("Y-m-d H:i:s").". Please delete it manually.\n";
			
			        $this->writeToFile($content);
	            }
			}
		}
		else
		{
			if(!@unlink($this->full_path."$file"))//if file couldn't be deleted then exit and do nothing which also means it won't be deleted from database.
	        {
				//We log it. We'll manually delete it ourselves.
			    $content = "Failed to delete ".$this->full_path."/$file, ' on ".date("Y-m-d H:i:s").". Please delete it manually.\n";
			
			    $this->writeToFile($content);
	        }
		}
		return;
    }
	
	
	function writeToFile($content)
	{
		$fp = fopen($this->log_full_path, 'a');
        
		fwrite($fp, $content);
        fclose($fp);
	    return;
	}
		
}
?>