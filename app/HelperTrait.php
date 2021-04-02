<?php 

namespace App;

trait HelperTrait
{
	public $cookie_name = "skuskesku";
	
	//public $create_session_cookie = true;
	
	private $data = [];
	
	public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
	
	public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);
        return null;
    }
	
	public function delete_cookie($cookie_name)
	{
		setcookie($cookie_name, "",  time() - 3600, "/");
	}
	
	
	function get_country_code($country)
	{
		$country_table = $this->get_value($table_name="county_table");
		
		//It should not be empty though. We wanna fetch the ISO country code of this Seller
		$query = "select iso from $country_table WHERE local_name = '$country' limit 1";
    
	//echo $query;exit;
    
	    $result = $this->register($query, true);
		 
		if(empty($result))
		{
			echo "<p class='alert alert-danger'>Please try again.</p>";
	        exit;
		}
		else
		{
			$country_code = $result[0]["iso"];
		}
		
		return $country_code;
	}
	
	
	//Helps us confirm if a given country exists and was fixed by us
    function verify_country($country_id)
	{
		$country_table = $this->get_value($table_name="county_table");
		
		$query = "select local_name from ".$country_table." where id = '".$country_id."' and type = 'co' ";
	    
		$country = $this->register($query, true);
	    //var_dump($query);
        if(empty($country))
        {
			return false;
		}
		else
		{
			return trim(strtolower($country[0]["local_name"]));
		}
	}
	
	//Confirms if the given state exists (in the selected country) or was fixed
	 function verify_state($country_id, $state_id)
	{
		$country_table = $this->get_value($table_name="county_table");
		
		$query = "select id, local_name from ".$country_table." where in_location = '".$country_id."' and id = '".$state_id."' ";
	    
		$state = $this->register($query, true);
	
        if(empty($state))
        {
			return false;
		}
		else
		{
			return trim(strtolower($state[0]["local_name"]));
		}
	}
	
	//This is only called when the specified "town" is one in Nigeria, in this case we wanna confirm if the town truly exists in the selected state
	function verify_town($state_id, $town)
	{
		$lga = $this->get_value($table_name="lga");
		
		$query = "select name from $lga where state_id = '$state_id' and LCASE(name) = '$town'";
	    
		$result = $this->register($query, true);
	
        if(empty($result))
        {
			return false;
		}
		else
		{
			return $result;
		}
	}
	
	// Function to check if a supplied data exists before before performing a processing. Returns boolean 
    // Basically this method checks against results that may have multiple rows. It specifically wants just a single
    // result set.
	function check_if_exists($query, $errmsg="", $exit=false)
	{
		$errormail = "<p class='alert alert-danger'>ID/Address/Email already associated with an account. No two people can have same id/address/email.</p>";
		
	    $result = $this->register($query, true);
		if(!empty($result))
		{
			if(count($result) > 0)
			{
				//If USer provides a custom error message we'll display that...
				if((isset($errmsg)) and (!empty($errmsg)))
				{
					$msg_to_send = $errmsg;
				}
				//... else we'll display our own Custom msg.
				else
				{
					$msg_to_send = $errormail;
				}
					
				$utility = new utility();
				$utility->ajax($msg_to_send, $return=false);
            }
			else
			{
				return;//return the queried data(boolean).
			}
		}
	}
	

    public function get_page()
	{
		if(isset($_GET["page"]) && is_numeric($_GET["page"]))
	    {
		    $currentpage = (int)$_GET["page"];
		
		    //It must always be set for all pages that need pagination 
		    if(isset($_SESSION["totalpages"]))
		    {
			    //If the requested page is equal to the total number of page(s) it means we've gotten to the end of the page. If it is greater it means it has been manually fixed by User cause it ought not to be over so we reset it to the last page.
		        if($currentpage >= $_SESSION["totalpages"])
		        {
			        $currentpage = $_SESSION["totalpages"];
		        }
		    
		        //If it is less than 1 it means it has been manually fixed by User cause it ought not to be lower than 1 so we reset it to the first page. This is the same thing as fetching goods when the page is first loaded.
	            if($currentpage < 1)
		        {
		            $currentpage = 1;
                }
		    }
	    }
	    else
	    {
		    $currentpage = 1;
        }
		
		return $currentpage;
	}
	
}
?>