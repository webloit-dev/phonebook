<!DOCTYPE html>
<html>
<head>
<title>Phonebook</title>
<?php require_once(doc."/view/component/css.php"); ?>

</head>

<body>

<?php
require_once(doc."/view/component/menu.php");
?>
 
<div class="container">



<div class="row top-row">



<div class='col-md-12 col-lg-12 col-sm-12 '>

<nav class="navbar navbar-dark bg-dark sticky-top">
  <form class="form-inline" method="get" action="<?php echo $_SERVER["PHP_SELF"]; ?>" role="search">
    <input class="form-control mr-sm-2" name='value'
    <?php
    if(!empty($value))
        echo "value='$value'";
    ?>
     type="search" placeholder="Search" aria-label="Search">
  </form>

  <li class="nav-item ml-4">
  <a title="Create new contact" href="/hostaway/url/register.php"><i style="float:right;" class="fa fa-edit bg-light btn fa-lg"></i></a>
      </li>
</nav>

<?php
if(!empty($items))
{
 for($i=0;$i<count($items);$i++)
 {
	 $sn = $offset + ($i + 1);
	 
	 echo "<div class='card' id='card-$sn'>
  
<div class='list-group list-group-flush'>

  <div class='list-group-item d-flex w-100 justify-content-between'>
      <p class='mb-1 font-weight-bold'>Name:</p>
      <p>".ucwords(json_decode($items[$i]['name'])->first_name)." ". ucwords(json_decode($items[$i]['name'])->last_name)."</p>
  </div>
  
  <div class='list-group-item d-flex w-100 justify-content-between'>
      <p class='mb-1 font-weight-bold'>Country:</p>
      <p>".ucwords(json_decode($items[$i]['data'])->country_name)."</p>
  </div>
  
  <div class='list-group-item d-flex w-100 justify-content-between'>
      <p class='mb-1 font-weight-bold'>Phone Number:</p>
      <p><a  class='text-primary' href='tel:07083218536'>".formatPhoneNumber(json_decode($items[$i]['data'])->phone, json_decode($items[$i]['data'])->country)."</a></p>
  </div>
  
  <div class='list-group-item d-flex w-100 justify-content-between'>
      <p class='mb-1 font-weight-bold'>Time Zone:</p>
      <p>".json_decode($items[$i]['data'])->timezone."</p>
  </div>
  
  <div class='list-group-item d-flex w-100 justify-content-between'>
      <p class='mb-1 font-weight-bold'>Created On:</p>
      <p>{$items[$i]['inserted_on']}</p>
  </div>
  
  <div class='card-footer'>
      <div class='btn-group d-flex justify-content-center'>
          <a class='btn btn-lg btn-primary' href='/hostaway/url/register.php?id={$items[$i]["id"]}'>Update</a>
		  <a class='btn btn-danger del' data-clear='yes' data-parentid='card-$sn' href='/hostaway/controller/delete_phonebook.php?id={$items[$i]["id"]}'>Remove</a>
      </div>
  </div>
  
  
 </div>
</div><hr/>";
	 
 }
 
 pagination($num_of_rows, $limit, $url); 

}
else
{
	 echo "<div class='card'>
<h5 class='card-header d-flex justify-content-center'>
    Nothing Yet!
  </h5>
  <div class='card-body'>
  <p>You're yet to create a phonebook. Let's get started <a class='nextPageViaAjaxOnly' href='/hostaway/url/register.php'>here</a></p>
  </div>
  </div>";
  
}

require_once(doc."/view/component/js.php"); 

?>
  	
</body>
</html>