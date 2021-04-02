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

$errormsg = "<p class='alert alert-danger'>Please enter a phone number</p>";
$phone = $utility->get(["name"=>"phone",  "invalid"=>$errormsg]);
$phone = ltrim($phone, '0');//strip out any leading 0

$errormsg = "<p class='alert alert-danger'>Unknown request</p>";
$id = $utility->get(["name"=>"id",  "invalid"=>$errormsg]);

//Phone numbers, whether int'l or local, have a minimum and maximum length. We do the validation here to ensure this
$tel = $utility->ValidatePhoneNumber($phone, $country);

//This is a well formatted and extracted phone number. It may be the same as the one supplied by the user but in a case where the user had already inputted his/her country code in the number it'll be removed and only the phone number will be extracted.
$phone = $tel['phone'];

//The user may not know his/her country code but we do (#winks
$isd = $tel['isd'];

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

//The phone number must be unique
$db->CheckPhoneNumber($phone, $id);

//Let's validate the timezone
$timezoneName = $utility->ValidateTimezone($timezone);

//The country entered by user could have been "doctored" so we check to see its authenticity.
$country_name = $utility->ValidateCountry($country);

$query = "update $phonebook SET name=?, data=?, updated_on=now() WHERE id = ? LIMIT 1";

$param = [json_encode(["first_name"=>$first_name,"last_name"=>$last_name]), json_encode(["country"=>$country, 'country_name'=>$country_name, "isd_code"=>$isd, "phone"=>$phone, "timezone"=>$timezone,]), $id];

$db->prepare($query, $param);

$utility->ajax("<p class='alert alert-success'>Phone entry was successfully updated.</p>", $return=false);

?>