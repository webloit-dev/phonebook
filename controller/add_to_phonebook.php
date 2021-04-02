<?php
if (!defined("doc")) define("doc", $_SERVER['DOCUMENT_ROOT'].'/hostaway');

require_once(doc."/vendor/autoload.php");

use App\Utility;

use App\HostAway;
//==========================================================================================================================

$utility = new Utility();

$errormsg = "<p class='alert alert-danger'>Please enter your first name</p>";
$first_name = $utility->get(["name"=>"firstname",  "invalid"=>$errormsg]);

$errormsg = "<p class='alert alert-danger'>No last name?</p>";
$last_name = $utility->get(["name"=>"lastname", "required"=>false,  "invalid"=>$errormsg]);

$errormsg = "<p class='alert alert-danger'>Select a country, please</p>";
$country = $utility->get(["name"=>"country",  "invalid"=>$errormsg]);

$errormsg = "<p class='alert alert-danger'>Select a timezone, please.</p>";
$timezone = $utility->get(["name"=>"timezone",  "invalid"=>$errormsg]);

$errormsg = "<p class='alert alert-danger'>Enter your country's ISD code, please</p>";
$isd = $utility->get(["name"=>"isd",  "invalid"=>$errormsg]);

$errormsg = "<p class='alert alert-danger'>Please enter a phone number</p>";
$phone = $utility->get(["name"=>"phone",  "invalid"=>$errormsg]);


$db = new HostAway("mysqli");

 ////////////////////////////////////////// The table(s) we'll work with in this script /////////////////////////////////////////////////////
 $phonebook = cred($index='tables', $key='phonebook');//$db->get_value($table_name="phonebook");
 ///////////////////////////////////////////////// END OF TABLE(s) /////////////////////////////////////////////////////////////////////////

//Phone Numbers must be unique. If we encounter a number that already exists we stop execution of this script.
$query = "SELECT JSON_EXTRACT(data, '$.phone') AS phone FROM $phonebook WHERE JSON_EXTRACT(data, '$.phone') = ?";

$result = $db->prepare($query, [$phone], true);

if(!empty($result))
{
    $utility->ajax("<p class='alert alert-danger'>Phone number already exists.</p>", $return=false);
}

//Let's validate the timezone
$timezoneName = $utility->ValidateTimezone($timezone);


//The country entered by user could have been "doctored" so we check to see its authenticity.
$country_name = $utility->ValidateCountry($country);

$query = "insert into $phonebook(name, data, inserted_on) values(?, ?, now())";

$param = [json_encode(["first_name"=>$first_name,"last_name"=>$last_name]), json_encode(["country"=>$country, 'country_name'=>$country_name, "isd_code"=>$isd, "phone"=>$phone, "timezone"=>$timezone,])];

$db->prepare($query, $param);

$utility->ajax("<p class='alert alert-success'>+{$isd}{$phone} was sucesfully added to the phonebook.</p>", $return=false);

?>