<?php

namespace App;

use App\HelperTrait;

use GuzzleHttp\Psr7;

class Utility
{
	use HelperTrait;

	//AJAX function Checker to be run if a page was accessed via an ajax call
	//if AJAX we echo the passed parameter else we just return control to the controller to do whatever
	function ajax($error_msg="", $return=true, $redirect_url="")
	{
		$ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
        
		if ($ajax)
		{
			if(is_array($error_msg))
			{
				print_r(json_encode($error_msg));
			}
			else
			{
				echo $error_msg;
			}
	        exit;
	    }
	    else
	    {
			if($return == true)
			{
				return;
			}
			
			//We kill the script.
			else
			{
				$_SESSION["error"] = $error_msg;
				
				if(!empty($redirect_url))
				{
					header("Location: $redirect_url");
			    }
			    else
			    {
					if(isset($_SERVER["HTTP_REFERER"]))
					{
						header("Location: ".$_SERVER["HTTP_REFERER"]."");
			        }
					else
					{
						header("Location: /");
                    }
			    }
				
				exit;
			}
	    }
	}
	
	// This function formats and sanitize all parameters retrieved from user_input
    public function sanitize($var)
    {
		if(!is_array($var))
		{
			$str = mb_strtolower($var);
	        $trim = trim($str);
	        $fil = filter_var($trim, FILTER_SANITIZE_STRING,FILTER_FLAG_ENCODE_HIGH);
	 
	        if(!get_magic_quotes_gpc())
	        {
				$add = addslashes($fil);
	        }
		    else
		    {
			   $add = $fil;
		    }
		}
		else
		{
			$add = [];
			
			for($i=0;$i<count($var);$i++)
			{
				$str = mb_strtolower($var[$i]);
	            $trim = trim($str);
	            $fil = filter_var($trim, FILTER_SANITIZE_STRING,FILTER_FLAG_ENCODE_HIGH);
	            
				if(empty($fil))
				{
					continue;
				}
				
	            if(!get_magic_quotes_gpc())
	            {
					$add []= addslashes($fil);
	            }
		        else
		        {
					$add []= $fil;
		        }
			}
		}
		
		return $add;
    }


    // This function cleans inputs then maintain new lines and capitalisations. Used mainly in textareas.
    function new_lines($var)
    {
		$trim = trim($var);
	    $fil = filter_var($trim, FILTER_SANITIZE_STRING,FILTER_FLAG_NO_ENCODE_QUOTES);
	 
	    if(!get_magic_quotes_gpc())
	    {
			$add = addslashes($fil);
	    }
		else
		{
			$add = $fil;
		}
		
		$new_line = nl2br(htmlspecialchars($add));
		
		return $new_line;
    }
	
	
	function generate_id($length=5)
	{
		$random_generator = openssl_random_pseudo_bytes($length);
        $id = bin2hex($random_generator);// Actual identifier.
	    return $id;
    }
	
	
	function randomNumbers($length=10) 
	{
		$result = "";

        for($i = 0; $i < $length; $i++) 
		{
			$result .= mt_rand(0, 9);
        }

        return $result;
    }

	
	function add_date($days="")
    {
		$date = new DateTime();
		
		if(!empty($days))
		{
			$date->add(new DateInterval('P'.$days.'D'));
		}
		
		//+1 day.
		else
		{
			$date->add(new DateInterval('P0D'));
		}
		
        return $date->format('Y-m-d');
    }
	
	//The first parameter = lower date
	//Second parameter = Higher date
	public function date_difference($date1, $date2)
	{
		$datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);
        $interval = $datetime1->diff($datetime2);
        
		return (int)$interval->format('%R%a days');
	}
	
	//Takes an array as arg
	public function get($param)
	{
		$name = $param["name"];
		
		//Whether or not we should exit the script if the specified post/get param isn't defined
		$required = $param["required"] ?? true;
		
		//Whether this is a "Get" or "Post" form
		$type = $param["type"] ?? "post";
		
		//Whether to filter/sanitize the input
		$filter = $param["filter"] ?? true;
		
		//The message to echo to the user if the specified form field isn't set or is empty
		$invalid = $param["invalid"] ?? "Please enter a valid value";
		
		//The type of filter we should use - whether the "sanitize" that transforms everything to lowercase or "new_line" that maintains the case as supplied.
		$keep_case = $param["keep_case"] ?? false;
		
		//In a situation whereby the key isn't set, it tells us whether to proceed (and return a null value) or exit it.
		$nullable = $param["nullable"] ?? false;
		
		switch($type)
		{
			case "post":
			    if(!isset($_POST[$name]))
			    {
					if($nullable == false)
					{
						echo $invalid;
				        exit;
					}
					else
					{
						return null;
					}
			    }
			    else
			    {
					if($filter == true)
					{
						if($keep_case == false)
						{
							$value = $this->sanitize($_POST[$name]);
						}
						else
						{
							$value = $this->new_lines($_POST[$name]);
						}
					}
					//Typical of passwords which may have combination of cases.
					else
					{
						$value = @filter_var($_POST[$name], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH);//$_POST[$name];
					}
				}
				
				break;
				
			case "get":
			    if(!isset($_GET[$name]))
			    {
					if($required != false)
					{
						echo $invalid;
				        exit;
					}
					else
					{
						return;
					}
			    }
			    else
			    {
					if($filter == true)
					{
						if($keep_case == false)
						{
							$value = $this->sanitize($_GET[$name]);
						}
						else
						{
							$value = $this->new_lines($_GET[$name]);
						}
					}
					else
					{
						$value = @filter_var($_GET[$name], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH);//$_GET[$name];
					}
				}
				
				break;
				
			default:
			    //do nothing
				
		}
		
		if($required == true)
		{
			if(empty($value))
			{
				echo $invalid;
				exit;
			}
		}
		return $value;
	}
	
	//We check to make sure the currency being supplied to us is valid and among those we support.
	public function validate_currency($currency, $die_it=true)
	{
		$rate = $this->rate;
		
		//we iterate through the currencies we have from the result gotten from the database.
		//We open our exchange rate file to bring out the supported currencies and their values in EUR
		$fp = fopen($rate, "rb");
		$contents = fread($fp, filesize($rate));
		fclose($fp);
		$rate = (array)json_decode($contents);
		
		//If it exists it means it is supported and has its value here else we'll just exit the script in this function.
        if(!array_key_exists(strtoupper($currency), $rate))
		{
			if($die_it != true)
			{
				return false;
			}
			else
			{
				echo "<p class='alert alert-danger'>Your selected currency is not valid/supported</p";
				exit;
			}
        }
		else
		{
			return true;
		}
	}
	
	
	//Takes a JSON-encoded value and unwraps it into the given "to" instruction. If an array is passed, it is first encoded and then decoded - for the culture.
	public function unwrap($prop, $to="string", $capitalize=false)
	{
		//If an array is passed in, for convenience reasons we will encode it and then decode it. This means we can convert an array to string.
		if(is_array($prop))
		{
			//Give it back to the caller. Him no serious
			if($to == "array")
			{
				return $prop;
			}
			else
			{
				$prop = json_encode($prop);
			}
		}
			
		$array = json_decode($prop);
		
		switch($to)
		{
			case "string":
			
			    $value = "";
				
				for($i=0;$i<count($array);$i++)
				{
					if($capitalize == true)
					{
						$array[$i] = ucwords($array[$i]);
					}
					
					if(end($array) == $array[$i])
					{
						$value .= $array[$i];
					}
					else
					{
						$value .= $array[$i].", ";
					}
				}
				
				break;
			
			//Unpacking into an array.
			default:
			
			    $value = [];
				
				for($i=0;$i<count($array);$i++)
				{
					if($capitalize == true)
					{
						$array[$i] = ucwords($array[$i]);
					}
					
					$value []= $array[$i];
				}
		}
		//print_r($value);exit;
		return $value;
	}
	
	public function get_time_zone()
	{
		$user_timezone = (isset($_COOKIE["timezone"])) ? sanitize($_COOKIE["timezone"]) : "+01:00";
		
		return $user_timezone;
	}
	
	public function restrict_length($variable, $field_name="", $allowed_length=100)
	{
		if(strlen($variable) > $allowed_length)
		{
			$this->ajax("<p class='alert alert-danger'> The ".ucwords($field_name)." can not be more than $allowed_length characters.</p>", $return=false);
		}
	}

	//We supply the country code for validation. Upon validation we return the "full" country name
	public function ValidateCountry($country_code)
	{
		// Create a client with a base URI
		$client = new \GuzzleHttp\Client(['base_uri' => 'https://api.hostaway.com/']);

		try
		{
			$response = $client->request('GET', 'countries');

            if($response->getStatusCode() == 200)
			{
				$body = json_decode($response->getBody())->result;

				$object = json_decode(json_encode($body), true);

				$country_codes = array_keys($object);

				if(in_array(strtoupper($country_code), $country_codes))
				{
					$country = $object[strtoupper($country_code)];
				}
				else
				{
					$this->ajax("<p class='alert alert-danger'>This is an unknown country.</p>", $return=false);
				}

				return strtolower($country);
			}
		}

		catch (\GuzzleHttp\Exception\RequestException $e) 
		{
			$this->ajax(Psr7\Message::toString($e->getRequest()));
			if ($e->hasResponse()) 
			{
				$this->ajax(Psr7\Message::toString($e->getResponse()));
			}
			exit;
		}

		catch (\GuzzleHttp\Exception\ConnectException  $e) 
		{
			$this->ajax("<p class='alert alert-danger'>Network error. Please refresh the page.</p>");
			//echo Psr7\Message::toString($e->getRequest());
			exit;
		}
	}


	//We check the timezone here. The name of the timezone selected doesn't matter; what matters is the timezone itself.
	public function ValidateTimezone($timezone)
	{
		// Create a client with a base URI
		$client = new \GuzzleHttp\Client(['base_uri' => 'https://api.hostaway.com/']);

		try
		{
			$response = $client->request('GET', 'timezones');

            if($response->getStatusCode() == 200)
			{
				$found = false;

				$body = json_decode($response->getBody())->result;

				$object = json_decode(json_encode($body), true);
//echo $timezone;
				foreach($object as $obj)
				{
				    if($obj['diff'] == $timezone)
					{
					    $found = true;
					    $timezoneName = $obj['value'];

						break;
					}
					
				}

				if($found == true)
				    return $timezoneName;
				else
				    $this->ajax("<p class='alert alert-danger'>This is an unknown country.</p>", $return=false);
				
			}
		}

		catch (\GuzzleHttp\Exception\RequestException $e) 
		{
			$this->ajax(Psr7\Message::toString($e->getRequest()));
			if ($e->hasResponse()) 
			{
				$this->ajax(Psr7\Message::toString($e->getResponse()));
			}
			exit;
		}

		catch (\GuzzleHttp\Exception\ConnectException  $e) 
		{
			$this->ajax("<p class='alert alert-danger'>Network error. Please refresh the page.</p>");
			//echo Psr7\Message::toString($e->getRequest());
			exit;
		}
	}
		
}
?>