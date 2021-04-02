<?php
if (!defined("doc")) define("doc", $_SERVER['DOCUMENT_ROOT'].'/hostaway');

require_once(doc."/vendor/autoload.php");

use App\Utility;

use App\HostAway;
//==========================================================================================================================

$utility = new Utility();

$errormsg = "<p class='alert alert-danger'>Unknown request</p>";
$id = $utility->get(["name"=>"id", 'type'=>'get', "invalid"=>$errormsg]);

$db = new HostAway("mysqli");

 ////////////////////////////////////////// The table(s) we'll work with in this script /////////////////////////////////////////////////////
 $phonebook = cred($index='tables', $key='phonebook');//$db->get_value($table_name="phonebook");
 ///////////////////////////////////////////////// END OF TABLE(s) /////////////////////////////////////////////////////////////////////////

//FIrst of all, we check if a record exists since this is an "update" request
$query = "SELECT data FROM $phonebook WHERE id = ?";

$result = $db->prepare($query, [$id], true);

if(empty($result))
{
    $utility->ajax("<p class='alert alert-danger'>No phone entry found.</p>", $return=false);
}
else
{
    $query = "DELETE FROM $phonebook WHERE id = ?";

    $db->prepare($query, [$id]);
}

$data["status"] = true;
$data["reload"] = false;
$data["msg"] = "Phone entry was successfully deleted";

$utility->ajax($data);

?>