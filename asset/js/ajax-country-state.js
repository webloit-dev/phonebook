$(document).ready(function(){

let submitBtn;

if($(document).find(":submit").length > 1)
{
	submitBtn = $(document).find(":submit").eq(1);
}
else
{
	submitBtn = $(document).find(":submit").eq(0);
}

let submitBtnVal = submitBtn.val();

//We fetch countries in the background "magically" from which user(s) will select from.	
	        $.ajax({url:"https://api.hostaway.com/countries", 
		           type:"GET",
				   //dataType:"JSON",
				   beforeSend:function(){
					   $(".ajax-indicator").show(); 
					   submitBtn.attr("disabled", "disabled");
					   submitBtn.val("Please Wait...");
                       //$("body").addClass("opaque-body-during-ajax-call"); 
					   },
				   complete:function(xhr, status){
					   $(".ajax-indicator").hide();  
					   submitBtn.removeAttr("disabled");
					   submitBtn.val(submitBtnVal);
                       //$("body").removeClass("opaque-body-during-ajax-call"); 
				   },
				   success:function(data){
					if(data == false)//that means nothing was echoed back. No result was found.
					{ 
						$(document).find('.modal-ctrl').remove();
                        $(document).find('#myModal').remove();
						
                    }
					else
					{
						var select_menu = $("#country");

						var country = select_menu.val() || "none";
						
						select_menu.children("option:not(:first)").remove();

	                    var countries = data['result'];

						    for(country_code in countries)
						    {
							    if(country.toLowerCase() == country_code.toLowerCase())
							    {
								    $("<option />", {value:country_code, text:countries[country_code]}).attr({"selected":"selected"}).appendTo(select_menu).insert;
			                    }
							    else
							    {
								    $("<option />", {value:country_code, text:countries[country_code]}).appendTo(select_menu).insert;
			                    }
                            }
	                };
				   },//success function ends there.
				  error:function(){
					  //alert("A network error occured. Please try again");
				  },
		        });



	//WE FETCH THE TIMEZONES HERE FROM WHICH USER(S) WILL SELECT FROM
				$.ajax({url:"https://api.hostaway.com/timezones", 
				type:"GET",
				//dataType:"JSON",
				beforeSend:function(){
					$(".ajax-indicator").show(); 
					submitBtn.attr("disabled", "disabled");
					submitBtn.val("Please Wait...");
					//$("body").addClass("opaque-body-during-ajax-call"); 
					},
				complete:function(xhr, status){
					$(".ajax-indicator").hide();  
					submitBtn.removeAttr("disabled");
					submitBtn.val(submitBtnVal);
					//$("body").removeClass("opaque-body-during-ajax-call"); 
				},
				success:function(data){
				    if(data == false)//that means nothing was echoed back. No result was found.
				    { 
						$(document).find('.modal-ctrl').remove();
					    $(document).find('#myModal').remove();
				    }
				    else
				    {
						var timezone_select = $("#timezone");

					    var current_timezone = timezone_select.val() || "none";
						
					    timezone_select.children("option:not(:first)").remove();

					    var time_zones = data['result'];

						for(region in time_zones)
						{
							if(current_timezone == time_zones[region]['diff'])
							{
								$("<option />", {value:time_zones[region]['diff'], text:time_zones[region]['value']}).attr({"selected":"selected"}).appendTo(timezone_select).insert;
							}
							else
							{
								$("<option />", {value:time_zones[region]['diff'], text:time_zones[region]['value']}).appendTo(timezone_select).insert;
							} 
						 }
				 };
				},//success function ends there.
			   error:function(){
				   //alert("A network error occured. Please try again");
			   },
			 });
					
				

});