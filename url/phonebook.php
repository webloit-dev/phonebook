<?php
if (!defined("doc")) define("doc", $_SERVER['DOCUMENT_ROOT'].'/hostaway');

require_once(doc."/vendor/autoload.php");

use App\Utility;

use App\HostAway;
//==========================================================================================================================

$db = new HostAway("mysqli");

 ////////////////////////////////////////// The table(s) we'll work with in this script /////////////////////////////////////////////////////
 $phonebook = cred($index='tables', $key='phonebook');//$db->get_value($table_name="phonebook");
 ///////////////////////////////////////////////// END OF TABLE(s) /////////////////////////////////////////////////////////////////////////
 
 $currentpage = $db->get_page();
		
 //Page 1 will be 0-9;
 //Page 2 will be 10-19;
 //:. Offset = (current_page - 1) * limit.
 $limit = 15;
 $offset = ($currentpage - 1) * $limit;

$query = "SELECT * FROM $phonebook GROUP BY id ORDER BY id DESC";

$items = $db->register($query, true);
//var_dump($items[1]);exit;	
//For the purpose of pagination
$count_query = "select count(distinct id) as count from $phonebook ";

$rows = $db->register($count_query, true);
	
$num_of_rows = $rows[0]["count"];

 require_once(doc."/view/phonebook_view.php"); 
?>