$(document).ready(function(){

//These are fields that we check against on time to avoid stress on the User.
//These specially check against emails and phone number entered by the User to avoid duplicate record(s).
$('.prevent-duplicate-record').on("blur", (function(event){
	if($(this).val() == "")
	{
		return;
    }
    event.preventDefault();
	
	var key = $(this).attr("id");
	
	var value = $(this).val();
	
	var notificationBox = $(this).next();
	
	var dataURL = $(this).data("validatoraddr");
	
	if(dataURL == undefined || dataURL == "")
	{
		var validation_url = "/controller/ajax_control/ajax_reg_validation.php?"+key+"="+value;
	}
	else
	{
		var validation_url = "/controller/ajax_control/"+dataURL+"?"+key+"="+value;
	}
	
    var requestData = {key: value};    
    $.post(validation_url, function(data)
	{
		if(data == false)//that means nothing was echoed back
		{
			notificationBox.html("");//clear the class area
        }
        else
		{
			notificationBox.html(data);
        };
    }).fail(function(jqXHR){
		notificationBox.html('An error occurred: ' + jqXHR.status).append(jqXHR.responseText);    
        });

}));



//Password one
$('#password').blur(function(event){ 
if($("#password2").val() != "" && $(this).val() != $("#password2").val())
{
	$("span.pass").html("<p class='alert alert-danger'>Your Passwords Do Not Match.</p>");
	
	$(this).attr("required","required");
	$("#password2").attr("required","required");
	return;
}
else{
	$("span.pass").html("");
};


});


//For The Password Field 2 when it loses focus.
$('#password2').blur(function(event){ 
if($("#password").val() !=  $(this).val() && $("#password").val() != "" )
{
	$("span.pass").html("<p class='alert alert-danger'>Your Passwords Do Not Match.</p>");
	
	$(this).attr("required","required");
	$("#password").attr("required","required");
	return;
}
else{
	$("span.pass").html("");
}

});


//checkbox
$(document).on("click", ".check", function(event)
{
	//event.preventDefault();
	let response = $(this).prop("checked");
	console.log(response);
	let submitButton = $(document).find("#button");
	let submitButtonParent = $(document).find("#button").parent();
	
	if($(document).find("div.notification-box").length < 1)
	{
		$("<div class='notification-box'></div>").prependTo(submitButtonParent);
	}
	
	let div = $(document).find("div.notification-box");
	
	if(response == false)
	{
		submitButton.attr("disabled","disabled");
		div.html("<p class='alert alert-info'>You need to agree to the T&C</p>");
		return;
	}
	else
	{
		div.html("");
		submitButton.removeAttr("disabled");
	};

});


$(document).on("click", "#gen_password", function(event){
	event.preventDefault();

    var target = $(this).data("target");

    var strength = $(this).data("strength");

    var passPhrase = getKey(strength);
	
	var id = $("#"+target);

    $(document).find("#"+target).val(passPhrase);

    if($(document).find("#"+target+"2").length >= 1)
	{
		$(document).find("#"+target+"2").val(passPhrase);
    }
	
	if(id.parent().find("#seePassword").length >= 1)
	{
		if(id.attr("type") == "password")
		{
			id.parent().find("#seePassword").trigger("click");
	    }
	}

});
  
  
$(".signupTab").on("click", function(event){
	  event.preventDefault();
	  var parentOfThisLink = $(this).parent().parent().parent();
	  
	  parentOfThisLink.fadeToggle("slow").addClass("d-none");
	  
	  var targetTab = $(this).data("id");
	  
	  switch(targetTab)
	  {
		  case "step1":
		      $("#step1").fadeToggle("slow").removeClass("d-none");
			  break;
			  
		  case "step2":
		      $("#step2").fadeToggle("slow").removeClass("d-none");
			  break;
			  
		  case "step3":
		      $("#step3").fadeToggle("slow").removeClass("d-none");
			  break;
	  };
	  
      //$("."+targetTab).removeclass("d-none")
  })

}); 