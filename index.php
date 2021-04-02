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
 $limit = 3;
 $offset = ($currentpage - 1) * $limit;

 $param = null;

 //If a search was triggered this will be set and not empty.
 $value = (new Utility())->get(["name"=>"value", 'type'=>'get', 'required'=>false]);
 
 if(!empty($value))
 {
    $query = "SELECT * FROM $phonebook WHERE id = ? OR JSON_EXTRACT(name, '$.first_name') LIKE ? OR JSON_EXTRACT(name, '$.last_name') LIKE ? OR JSON_EXTRACT(data, '$.country_name') LIKE ? GROUP BY id ORDER BY id DESC limit $limit offset $offset";

    $count_query = "select count(distinct id) as count from $phonebook  WHERE id = ? OR JSON_EXTRACT(name, '$.first_name') LIKE ? OR JSON_EXTRACT(name, '$.last_name') LIKE ? OR JSON_EXTRACT(data, '$.country_name') LIKE ?";

    $param = [$value, "%$value%", "%$value%", "%$value%"];
 }
 else
 {
    $query = "SELECT * FROM $phonebook GROUP BY id ORDER BY id DESC limit $limit offset $offset";

    $count_query = "select count(distinct id) as count from $phonebook ";
 }

$items = $db->prepare($query, $param, true);
	
//For the purpose of pagination
$rows = $db->prepare($count_query, $param, true);
	
$num_of_rows = $rows[0]["count"];

$url = urlForPagination($htacess=false);

 require_once(doc."/view/phonebook_view.php"); 
?>