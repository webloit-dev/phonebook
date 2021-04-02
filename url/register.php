<?php
if (!defined("doc")) define("doc", $_SERVER['DOCUMENT_ROOT'].'/hostaway');

require_once(doc."/vendor/autoload.php");

use App\Utility;

use App\HostAway;
//==========================================================================================================================

$utility = new Utility;

$id = $utility->get(["name"=>"id", 'type'=>'get', 'required'=>false]);

$form_action = '/hostaway/controller/add_to_phonebook.php';

//This condition holds for case where user wants to update the database record
if(!empty($id))
{
    $db = new HostAway("pdo");

    ////////////////////////////////////////// The table(s) we'll work with in this script /////////////////////////////////////////////////////
	$phonebook = cred($index='tables', $key='phonebook');;
    ///////////////////////////////////////////////// END OF TABLE(s) /////////////////////////////////////////////////////////////////////////

    $query = "SELECT * FROM $phonebook where id = ? LIMIT 1";

    $param = [$id];

    $result = $db->prepare($query, $param, true);

    $form_action = '/hostaway/controller/edit_phonebook.php';

  //  var_dump($result);
}

require_once(doc."/view/register_view.php"); 
?>