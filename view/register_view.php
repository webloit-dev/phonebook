<!DOCTYPE html>
<html>
<head>
<title>Create Phonebook</title>
<?php require_once(doc."/view/component/css.php"); ?>

</head>

<body>
 
<?php
require_once(doc."/view/component/menu.php");
?>

<div class="container">

<div class="row top-row">


<div class='col-md-12 col-lg-12 col-sm-12 '>
  
        <form action="<?php echo $form_action; ?>" role="form" id="ajax-post-nav-form" method="post" >
            <fieldset>
                <div class="form-group "> 
                    <label>First Name<span style="color:red;">*</span></label>
                        <input type="text" class="form-control form-control-lg signup-field" value="<?php if(!empty($result)){ echo ucwords(json_decode($result[0]['name'])->first_name);} ?>" placeholder="first name" autocomplete id="firstname" name="firstname" required="required" data-name="first name"/>
                </div>

                <div class="form-group "> 
                    <label>Last Name<span style="color:red;">*</span></label>
                        <input type="text" class="form-control form-control-lg signup-field" value="<?php if(!empty($result)){ echo ucwords(json_decode($result[0]['name'])->last_name);}?>" placeholder="last name" autocomplete id="lastname" name="lastname" data-name="last name"/>
                </div>

                <div class="form-group ">
                    <label>Country <span style="color:red;">*</span></label>
                        <select class="form-control form-control-lg" data-name="country" required="required" id="country" name="country">
                            <option value="">Select Country...</option>
                            <?php
                            if(!empty($result))
                            {
                              echo "<option selected value='".json_decode($result[0]['data'])->country."'>".ucwords(json_decode($items[0]['data'])->country_name)."</option>";
                            }
                            ?>
                        </select>
                </div>
                
                <div class="form-group "> 
                    <label>Phone Number<span style="color:red;">*</span></label>
                        <input type="number" class="form-control form-control-lg signup-field" value="<?php if(!empty($result)){ echo json_decode($result[0]['data'])->phone;} ?>" autocomplete id="phone" name="phone" data-name="phone number"/>
                </div>

                <div class="form-group ">
                    <label>Time Zone<span style="color:red;">*</span></label>
                        <select class="form-control form-control-lg" data-name="time zone" required="required" id="timezone" name="timezone">
                            <option value="">Select Time Zone...</option>
                            <?php
                            if(!empty($result))
                            {
                              echo "<option selected value='".json_decode($result[0]['data'])->timezone."'>".json_decode($result[0]['data'])->timezone."</option>";
                            }
                            ?>
                        </select>
                </div>

                <?php
                if(!empty($result))
                {
                    echo "<input type='hidden' name='id' value='$id' required/>";
                }
                ?>
	
                <input class="btn btn-block btn-primary btn-lg"  type="submit" value="<?php if(!empty($result)){ echo "Update Phonebook";}else{ echo "Add To Phonebook";} ?>" id="sub"/>
            </fieldset>
        </form>
 
</div>
</div>
</div>

<?php require_once(doc."/view/component/js.php"); ?>
  
<script src = "/hostaway/asset/js/ajax-country-state.js"></script>
  
<script src = "/hostaway/asset/js/ajax-signup.js"></script>



	
</body>
</html>