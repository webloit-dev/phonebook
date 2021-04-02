<?php

// This function formats and sanitize all parameters retrieved from user_input
function sanitize($var)
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

//In an HTML document we will only allow links that point to Webloit especially when making bulk emails which can be easily changed from client side.
function confirm_link($html, $tag="a")
{
	switch($tag)
	{
		case "img":
		    $tag="img";
			$attr = "src";
			
			break;
			
		default:
		    $tag="a";
			$attr = "href";
	}
	
	$dom = new DomDocument();
    $dom->loadHTML($html);
	
	$tags = $dom->getElementsByTagName($tag);
	
    $output = [];
    
	foreach ($tags as $item) 
	{
		$output[] = $item->getAttribute($attr);
	/*
   $output[] = array (
      'str' => $dom->saveHTML($item),
      'href' => $item->getAttribute('href'),
      'anchorText' => $item->nodeValue
   );
   */
    }
	
    $output = array_unique($output);
	
	for($i=0;$i<count($output);$i++)
	{
		if(isset($output[$i]))
		{
			$url = parse_url($output[$i], PHP_URL_HOST);
		
		    if((empty($url)) or ($url != $_SERVER["HTTP_HOST"]))
		    {
				echo "<p class='alert alert-danger'>A link pointing to an external domain/website was detected. You can only use links to {$_SERVER["HTTP_HOST"]}</p>";
			    exit;
		    }
		}
	}
}


function count_img_tags($html)
{
	confirm_link($html, $tag="img");
	
	//Let's make sure the Merchant doesn't upload more than the allowed number of images. We do so by searching for the "<img>" tag. If user tries to be wise by trying to circumvent the screening by providing masked content then the images won't even render which is worthless.
	$noOfImage = preg_split("/<img/", $html);
	
	if(count($noOfImage) > 10)
	{
		echo "<p class='alert alert-danger'>You've exceeded the number of images (10) you can send. Reduce it and try again.</p>";
	    exit;
	}
}
	

/* This function attempts to separate a string that has numbers inside to
* bring out just the number portion in the string that can be converted to integer. 
* It assumes that in the  string the only portion to remove to be able to convert 
* it to a real number is the first(0) item. It converts the string to an array,
* slice out from the second(1) part to the end, uses iteration to concat the sliced parts
* which now contains only the real number then returns it to controller.
* Example: #9038 will be converted to 9038, leaving out the # before it.
***********************************************************************************************/
function str_to_int($str)
{
	$ro = str_split($str);
    $r = array_slice($ro, 1);
    $p = "";
	for ($i=0;$i<count($r);$i++)
	{
		$p .= $r[$i];
	}
    return $p;
}


// splits an array(even multidimensional) into chunks with indexes ONLY so that in_array can do its job.
// The first argument is the array, the second argument is the string/value you want to search for if it exists in $search
// This is my custom version of "in_array".
// The return value is boolean(true or false).
function search_string($search, $needle)
{
	$v = [];//initialises an empty array.
	for($i=0;$i<count($search);$i++)//loops through the array to be searched on
	{
		//The argument has a key(2nd one) that is constant(always 0). the 3rd key is the actual association
		foreach($search[$i][0] as $b=>$t)//iterates and brings out only the main key=>value
		{
			$v []= $t;	//stores it in the initialised variable above
		}
	}
	$c = in_array($needle,$v);//if the second argument of this function is found in this newly created array.
	return $c;
}

	

function limit_text($content,$link)
{
	// checks if the content we're receiving isn't empty, to avoid the warning
         if (empty( $content ) ) {
                 return;
             }
			 
	if(strlen($content) > 800)
	{

             // converts all special characters to utf-8
             $content = mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8');
			 
			 $shortened = substr($content,0,609);

             // creating new document
             $doc = new DOMDocument('1.0', 'utf-8');

             //turning off some errors
             libxml_use_internal_errors(true);

             // it loads the content without adding enclosing html/body tags and also the doctype declaration
             $doc->LoadHTML($shortened, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

             // do whatever you want to do with this code now
			 $text = $doc->saveHTML()."<a href='$link'>...see more</a>";
			 return $text;
	}
	else
	{
		return $content;
	}
}


//$num_rows = total number of rows fetched from db. (int)
//$limit = the limit clause required to calculate the total number of pages we'll likely have.(int)

//$session_total_pages = This variable is the name of the key in the $_SESSION variable where we'll store the total count of the goods we fetched from db even via the first query. This is important to avoid collision or overwriting of the total count of goods thus giving false result which is typical of AJAX request(s). I get: I don't even understand what i just explained but I'm right anyway.
function calculate_pages($num_of_rows, $limit, $nameOfSessionIndex="totalpages")
{
	/*We first calculate the total number of pages we have here. 
	//Formula: total_number_of_rows_fetched / our_limit_of_goods_to_display_per_page.
	//We use the ceil() function to round up result. For instance, if our limit is 3 and we have $num_rows of 4, when we divide(4/3) we'll
	//get 1.133. Ceil() will help us round it to an integer of 2(unlike the normal approximation in maths that'll round it up to 0). If //we've a remainder of something like 5.6 ceil() will round it up to 6. If the result(after rounding up) is 1 it means there are no more goods to be displayed but if it's greater it means we've more result set. For instance, if our limit is 3 and we've a $num_row of 4 it obviously means there is one more good to be displayed, ceil() will round it up to 2 in this case.
	===========================================================================*/
	
	//"$_SESSION[$total_pages]" is made global so that we won't have to be passing it via URL's and it will easily be collected by the "controller" scripts when sorting result. We collect it because User may change the name, in any case we can still work with it.
	$index_name = $nameOfSessionIndex;
	
	if(isset($_SESSION[$index_name]))
	{
		unset($_SESSION[$index_name]);
		$_SESSION[$index_name] = ceil((int)$num_of_rows / (int)$limit);
		$total_pages = ceil((int)$num_of_rows / (int)$limit);
	}
	
	else
	{
		$_SESSION[$index_name] = ceil((int)$num_of_rows / (int)$limit);
		$total_pages = ceil((int)$num_of_rows / (int)$limit);
	}
	return $total_pages;
}


//$num_rows = total number of rows fetched from db. (int)
//$limit = the limit clause required to calculate the total number of pages we'll likely have.(int)

//$session_total_pages = This variable is the name of the key in the $_SESSION variable where we'll store the total count of the goods we fetched from db even via the first query. This is important to avoid collision or overwriting of the total count of goods thus giving false result which is typical of AJAX request(s). I get; I don't even understand what i just explained but I'm right anyway.
function infinite_scrolling($num_of_rows, $limit, $nameOfSessionIndex="totalpages")
{
	/*We first calculate the total number of pages we have here. 
	//Formula: total_number_of_rows_fetched / our_limit_of_goods_to_display_per_page.
	//We use the ceil() function to round up result. For instance, if our limit is 3 and we have $num_rows of 4, when we divide(4/3) we'll
	//get 1.133. Ceil() will help us round it to an integer of 2(unlike the normal approximation in maths that'll round it up to 0). If //we've a remainder of something like 5.6 ceil() will round it up to 6. If the result(after rounding up) is 1 it means there are no more goods to be displayed but if it's greater it means we've more result set. For instance, if our limit is 3 and we've a $num_row of 4 it obviously means there is one more good to be displayed, ceil() will round it up to 2 in this case.
	===========================================================================*/
	
	//"$_SESSION[$total_pages]" is made global so that we won't have to be passing it via URL's and it will easily be collected by the "controller" scripts when sorting result. We collect it because User may change the name, in any case we can still work with it.
	$index_name = $nameOfSessionIndex;
	
	if(isset($_SESSION[$index_name]))
	{
		unset($_SESSION[$index_name]);
		$_SESSION[$index_name] = ceil((int)$num_of_rows / (int)$limit);
	}
	
	else
	{
		$_SESSION[$index_name] = ceil((int)$num_of_rows / (int)$limit);
	}
	
	//echo $_SESSION[$index_name];
	
    //$_SESSION[$index_name] is set for all queries anyways - it is set in this function.
	if(isset($_SESSION[$index_name]))
	{
		//If total page is greater than 1 it means we've other result(s) to be displayed.
		if($_SESSION[$index_name] > 1)
		{
			//If this is set it means a pagination has been started. This could also have been initialised by the User manually though.
			//$_SESSION is a global variable and thus can be detected even in a function but the index/key("page") must be set.
			if(isset($_GET["page"]) && is_numeric($_GET["page"]))
		    {
				//We get the current page number that was set by the User and force it to integer for "just incase" scenarios.
				$current_page = (int)$_GET["page"];
				
				//unset($_SESSION["page"]);
			
	            //If the requested page is greater than the total number of page(s) it means we've gotten to the end of the page. If it is //greater it means it has been manually fixed by User cause it ought not to be over so we reset it to the last page.
			    if($current_page >= $_SESSION[$index_name])
			    {
					$current_page = $_SESSION[$index_name];
			    }
		         //If it is less than 1 it means it has been manually fixed by User cause it ought not to be lower than 1 so we reset it to the first page. This is the same thing as fetching goods when the page is first loaded.
		        if($current_page < 1)
				{
					$current_page = 1;
                }
				
                //It means we're fetching the first result like we just first landed on this page cause the offset will be 0 based on our //calculation below. We just show the "next" page link and that of the last page.
				//Computer program starts its count from 0.
				//Page 1 will be 0-9;
				//Page 2 will be 10-19;
                //:. Offset = (current_page - 1) * limit.		
				if($current_page == 1)
				{
					//setcookie("page", 2, time()+60*60*24*30, "/" );
					//$_SESSION["page"] = 2;
					$next_page = 2;
					//return 2;
				}
				
				//If it's not equal to 1 then it is definitely more than one. This could mean that we're either in the 2nd, 3rd, etc page. 
				//It could also mean that we're in the last page.
				else
				{
					 $next_page = $current_page + 1;
					 //setcookie("page", $next_page, time()+60*60*24*30, "/" );
					 //$_SESSION["page"] = $next_page;
					 //return $next_page;
				}
			}
			
			//If there's no pagination, but since it runs under the "> 1" block it means there are more page(s) to be loaded anyway.
			else
			{
				//$_SESSION["page"] = 2;
				$next_page = 2;
				//return 2;
			}
			
			if(!empty($next_page))
		    {
				$data["current_page"] = $next_page - 1;
			    $data["next_page"] = $next_page;
		    }
		    else
		    {
			    $data["next_page"] = $_SESSION[$index_name];
			    $data["current_page"] = $_SESSION[$index_name];
		    }
		        $data["total"] = $_SESSION[$index_name];//total_pages
				$data["total_to_fetch"] = $num_of_rows;//total_records
				return $data;
		}
		else
		{
			$data["next_page"] = 1;
			$data["current_page"] = 1;
			$data["total"] = 1;//total_pages
			$data["total_to_fetch"] = 1;//total_records
			return $data;
		}
	}
}








//$num_rows = total number of rows fetched from db. (int)
//$limit = the limit clause required to calculate the total number of pages we'll likely have.(int)
//$url = the link we'll use for the pagination.(string)
//$url will be in the form, e.g, "/mysite.com/category/category_name/"(if a clean url was implemented) or 
//"/mysite.com/search/?search=searched_term$page=". The internally calculated page will be appended to the end of the link for the pagination. It is compulsory that the id of the $_GET variable be named "page".
//$nameOfSessionIndex = This variable is the name of the key in the $_SESSION variable where we'll store the total count of the goods we fetched from db even via the first query. This is important to avoid collision or overwriting of the total count of goods thus giving false result which is typical of AJAX request(s).
function pagination($num_of_rows, $limit, $url, $nameOfSessionIndex="totalpages")
{
	//$url = preg_replace("([\d]+)", "", $url);
	/*We first calculate the total number of pages we have here. 
	//Formula: total_number_of_rows_fetched / our_limit_of_goods_to_display_per_page.
	//We use the ceil() function to round up result. For instance, if our limit is 3 and we have $num_rows of 4, when we divide(4/3) we'll
	//get 1.133. Ceil() will help us round it to an integer of 2(unlike the normal approximation in maths that'll round it up to 0). If //we've a remainder of something like 5.6 ceil() will round it up to 6. If the result(after rounding up) is 1 it means there are no more goods to be displayed but if it's greater it means we've more result set. For instance, if our limit is 3 and we've a $num_row of 4 it obviously means there is one more good to be displayed, ceil() will round it up to 2 in this case.
	===========================================================================*/
	
	//"$_SESSION[$total_pages]" is made global so that we won't have to be passing it via URL's and it will easily be collected by the "controller" scripts when sorting result.
	
	$total_pages = $nameOfSessionIndex;
	
	if(isset($_SESSION[$total_pages]))
	{
		unset($_SESSION[$total_pages]);
		$_SESSION[$total_pages] = ceil((int)$num_of_rows / (int)$limit);
	}
	else
	{
		$_SESSION[$total_pages] = ceil((int)$num_of_rows / (int)$limit);
	}
	
	
    //$_SESSION["totalpages"] is set for all queries anyways.
	if(isset($_SESSION[$total_pages]))
	{
		//If total page is greater than 1 it means we've other result(s) to be displayed.
		if($_SESSION[$total_pages] > 1)
		{
			//our range of links to display.
			$range = 3;
			
			//If this is set it means a pagination has been started. This could also have been initialised by the User manually though.
			//$_GET is a global variable and thus can be detected even in a function but the index/key("page") must be set. Its value is the current "page" that was viewed. On first page load this won't be started because we're on the first page. This will only be started when there aretruly more pages to be viewed and User hs clicked on the "next" page.
			if(isset($_GET["page"]) && is_numeric($_GET["page"]))
		    {
				//We get the current page number that was set by the User and force it to integer for "just incase" scenarios.
				$current_page = (int)$_GET["page"];
			
	            //If the requested page is greater than the total number of page(s) it means we've gotten to the end of the page. If it is //greater it means it has been manually fixed by User cause it ought not to be over so we reset it to the last page.
			    if($current_page >= $_SESSION[$total_pages])
			    {
					$current_page = $_SESSION[$total_pages];
			    }
		         //If it is less than 1 it means it has been manually fixed by User cause it ought not to be lower than 1 so we reset it to the first page. This is the same thing as fetching goods when the page is first loaded.
		        if($current_page < 1)
				{
					$current_page = 1;
                }
				
                //It means we're fetching the first result like we just first landed on this page cause the offset will be 0 based on our //calculation below. No need to show the "Prev" link,we just show the "next" page link and that of the last page.
				//Computer program starts its count from 0.
				//Page 1 will be 0-9;
				//Page 2 will be 10-19;
                //:. Offset = (current_page - 1) * limit.		
				if($current_page == 1)
				{
					echo "<nav aria-label='page navigation bg-dark'>
					       <ul class='pagination pagination-lg justify-content-center'>
						
				              <li class='next page-item'><a class='page-link' href='".$url."2'>Next</a></li>
						      
				              <li class=' next page-item'><a class='page-link ' href='$url".$_SESSION[$total_pages]."'>&raquo;</a></li>
						
						   </ul>
						   </nav>";
				}
				
				//If it's not equal to 1 then it is definitely more than one. This could mean that we're either in the 2nd, 3rd, etc page. 
				//It could also mean that we're in the last page, in this case a different logic will be applied. Since we're not in the //first page we can show the two necessary links: "<" which indicates the previous page(current_page - 1) and the "<<" link //that will take us straight to the first page.
				else
				{
					$prev_page = $current_page - 1;
				    $next_page = $current_page + 1;
				    echo "<nav aria-label='page navigation'>
					      <ul class='pagination pagination-lg justify-content-center'>
				             <li class='next page-item'><a class='page-link' href='".$url."1'>&laquo;</a></li>
				             <li class='next page-item'><a class='page-link' href='".$url."".$prev_page."'>&lt;</a></li>";
				    
					/*Here, we wanna show only links within range 3. FOr instance, if we're on page 8, since our range of links to be //displayed is 3 our displayed link(reprenting pages) will look like(after viewing the first page):
					 << < 5 6 7 [8] 9 10 11 > >>.
					
					We show the last three link/pages(must be greater than 0) and the next 3 pages/links(must be less than the total pages.
					 =============================================================================================================*/
					for($i=($current_page - 3);$i <= (($current_page + $range) + 1);$i++)
					{
						//The loop must be greater than 0 cause we don't have links like -2, -1, 0 and must be less than or equal to the //total number of pages we've for this result.
						if(($i > 0) and ($i < $_SESSION[$total_pages]))
						{
							//"<<" will link to the starting page while "<" will link us to the previous page(7). If the loop number we're on is equal to the one supplied to us we'll highlight like we did above.
							if($i == $current_page)
							{
								echo "<li class='next page-item'><a class='page-link' href='#'>[$i]</a></li>";
								continue;
							}
							
							echo "<li class='next page-item'><a class='page-link' href='".$url."".$i."'>$i</a></li>";
						}
				    }
					
					
					//It means we're not yet on the last page so we show the last link(indicating the next page) and the ">>" link indicating the last page.
					if(($current_page > 0) and ($current_page < $_SESSION[$total_pages]))
					{
						//echo "<li><a href='#'>...</a></li>";
						echo "<li class='next page-item'><a class='page-link' href='".$url."".$_SESSION[$total_pages]."'>".$_SESSION[$total_pages]."</a></li>";
						echo "<li class='next page-item'><a class='page-link' href='".$url."".$next_page."'>&gt;</a></li>";
						echo "<li class='next page-item'><a class='page-link' href='".$url."".$_SESSION[$total_pages]."'>&raquo;</a></li>";
				    }
					echo "</ul> 
					       </nav>";
				 }
				// echo $_SESSION[$total_pages];
		    }
			  
			  //But if pagination  hasn't been triggered this is our default paging. We definitely must show the link to the next page because there are more goods. Remember that this block of code is executing because the total number of pages is greater than one.
			  else
			  {
				  echo "<nav aria-label='page navigation '>
				        <ul class='pagination pagination-lg justify-content-center'>
				          <li class='next page-item'><a class='page-link' href='".$url."2'>Next</a></li>
				          <li class='next page-item'><a class='page-link' href='$url".$_SESSION[$total_pages]."'>&raquo;</a></li>
						</ul>
						</nav>";
			  }
		}
		   //echo $_SESSION[$total_pages];
		   //But if the page is just equal to one or kess it means we don't have anymore row or we don't have any result. 
	}
}

//Controls naviagtion for pagination such that "Page=" will always be appended
function urlForPagination($htacess=false)
{
	//It means pagination has started already so we remove any previous "page" value so that a new one will be appended. This is typical of pages that don't use clean URL and URL be like webloit.com?id=$id&page=*
	if(preg_match("/page=(\d+)/", $_SERVER["REQUEST_URI"])) 
	{
		$url = preg_replace('/page=(\d+)/', 'page=', $_SERVER["REQUEST_URI"]);
    }
	
	//At this stage it is believed that a query was triggered and as thus must have a query string. This is typical of pages that use clean URL and URL be like webloit.com/id/$id/page/*
	else if(preg_match("/page\/(\d+)/", $_SERVER["REQUEST_URI"])) 
	{
		$url = preg_replace('/page\/(\d+)/', 'page/', $_SERVER["REQUEST_URI"]);
    }
	
	//This could be that the user is just fetching result for the first time.
	else
	{
		if($htacess == true)
		{
			$url = $_SERVER["REQUEST_URI"]."/page/";
		}
		else
		{
			$url = parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY);
			
			if(empty($url))
			{
				$url = $_SERVER["REQUEST_URI"]."?page=";
			}
			else
			{
				$url = $_SERVER["REQUEST_URI"]."&page=";
			}
			
			//$url = $_SERVER["REQUEST_URI"]."&page=";
		}
	}
	
	return $url;
}

function generate_id($length=5)
{
	$random_generator = openssl_random_pseudo_bytes($length);
    $id = bin2hex($random_generator);// Actual identifier.
	return $id;
}
			
/***************************************************** 
Adds a specific number of days to the current date
*****************************************************/
function add_date($num)
{
	$date = new DateTime();
   $date->add(new DateInterval('P'.$num.'D'));
    return $date->format('Y-m-d');
}

function cred($index='', $key='')
{
	if(!defined("doc")) define("doc", $_SERVER['DOCUMENT_ROOT'].'/hostaway');

	$content = file_get_contents(doc.'/engine/settings.json');
	
	$formatted = json_decode($content, true);

	//$formatted[$index][0][$key];

	if(!empty($index) and !empty($key))
	{
		$content = $formatted[$index][0][$key];
	}
	else if(!empty($index))
	{
		$content = $formatted[$index][0];
	}
	else
	{
		$content = $formatted;
	}

	return $content;
}

function formatPhoneNumber($phone, $country_code="NGN") 
{
    $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
    try 
	{
		$phoneNumber = $phoneUtil->parse($phone, strtoupper($country_code));

			
		$formattedPhoneNumber =  $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::INTERNATIONAL);

		return $formattedPhoneNumber;
			//echo $phoneUtil->format($phoneNumber, \libphonenumber\PhoneNumberFormat::E164);
			//var_dump($phoneUtil->isValidNumber($phoneNumber));
            //var_dump($phoneNumber);
			//exit;
    } 
	catch (\libphonenumber\NumberParseException $e) 
	{
		var_dump($e);
	}

    
}

function accessProtected($obj, $prop) {
	$reflection = new ReflectionClass($obj);
	$property = $reflection->getProperty($prop);
	$property->setAccessible(true);
	return $property->getValue($obj);
  }

?>
		 