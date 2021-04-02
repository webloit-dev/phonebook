<!DOCTYPE html>
<html>
<head>
<title>Create Phonebook</title>
<?php require_once(doc."/view/component/css.php"); ?>

</head>

<body>
 
<div class="container">


<?php
require_once(doc."/view/component/menu.php");
?>

<div class="row">


<div class='col-md-12 col-lg-12 col-sm-12 '>
<div class="homepage-search-form-parent align-middle " >
      <div class="input-group form-group input-lg mb-3">
        <div class="input-group-append">
          <a class=" btn btn-secondary go-back-from-searchbox" href="#"><i class="fas fa-arrow-left "></i></a>
        </div>
		    
		    <form class="form-inline my-2 my-md-0 row search-form" id="search-form" method="get" action="/search/" role="search">
            <input data-auto_complete="/controller/ajax_control/ajax_search_suggestions.php" autocomplete="off" type="search" name="search" class="form-control col-lg-12 search-box" <?php if(isset($_GET["search"])){ echo "value=".sanitize($_GET["search"])."";}?> id="search-box-lg" placeholder="Search Webloit"/>
        </form>	
				
			</div>
		</div>

<a title="Give Coupon" class="nextPageViaAjaxOnly" href="/hostaway/url/register.php"><i style="float:right;" class="fas fa-pen bg-dark text-light btn fa-lg"></i></a>

<?php
if(!empty($items))
{
 for($i=0;$i<count($items);$i++)
 {
	 $sn = $offset + ($i + 1);
	 
	 echo "<div class='card'>
  
<div class='list-group list-group-flush'>

  <div class='list-group-item d-flex w-100 justify-content-between'>
      <p class='mb-1 font-weight-bold'>Name:</p>
      <p>".ucwords(json_decode($items[0]['name'])->first_name)." ". ucwords(json_decode($items[0]['name'])->last_name)."</p>
  </div>
  
  <div class='list-group-item d-flex w-100 justify-content-between'>
      <p class='mb-1 font-weight-bold'>Country:</p>
      <p>".ucwords(json_decode($items[0]['data'])->country_name)."</p>
  </div>
  
  <div class='list-group-item d-flex w-100 justify-content-between'>
      <p class='mb-1 font-weight-bold'>Phone:</p>
      <p>+".json_decode($items[0]['data'])->isd_code.json_decode($items[0]['data'])->phone."</p>
  </div>
  
  <div class='list-group-item d-flex w-100 justify-content-between'>
      <p class='mb-1 font-weight-bold'>Time Zone:</p>
      <p>".json_decode($items[0]['data'])->timezone."</p>
  </div>
  
  <div class='list-group-item d-flex w-100 justify-content-between'>
      <p class='mb-1 font-weight-bold'>Created On:</p>
      <p>{$items[0]['inserted_on']}</p>
  </div>
  
  <div class='card-footer'>
      <div class='btn-group d-flex justify-content-center'>
          <a class='btn btn-lg btn-primary' href='/hostaway/url/register.php?id={$items[$i]["id"]}#runBounty'>Update</a>
		  <a class='btn btn-danger del' href='/controller/delete/delete_coupon.php?gid={$items[$i]["id"]}'>Remove</a>
      </div>
  </div>
  
  
 </div>
</div>";
	 
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