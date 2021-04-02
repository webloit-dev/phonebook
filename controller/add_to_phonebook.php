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


//Let's make sure this number is unique and doesn't exist in the database
$db->CheckPhoneNumber($phone);

//Let's validate the timezone to make sure it was selected from the select menu we presented to the user.
$timezoneName = $utility->ValidateTimezone($timezone);

//The country entered by user could have been "doctored" so we check to see its authenticity.
$country_name = $utility->ValidateCountry($country);

$query = "insert into $phonebook(name, data, inserted_on) values(?, ?, now())";

$param = [json_encode(["first_name"=>$first_name,"last_name"=>$last_name]), json_encode(["country"=>$country, 'country_name'=>$country_name, "isd_code"=>$isd, "phone"=>$phone, "timezone"=>$timezone,])];

$db->prepare($query, $param);

$utility->ajax("<p class='alert alert-success'>".formatPhoneNumber($phone, $country)." was successfully added to the phonebook.</p>", $return=false);

?>