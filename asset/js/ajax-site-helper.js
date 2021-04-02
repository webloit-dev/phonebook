var CookieUtil = {
	              get: function (name){
		               var cookieName = encodeURIComponent(name) + "=",            
		               cookieStart = document.cookie.indexOf(cookieName),            
			           cookieValue = null;                    
			           if (cookieStart  >  -1){            
			           var cookieEnd = document.cookie.indexOf(";", cookieStart)            
			           if (cookieEnd == -1){                
			           cookieEnd = document.cookie.length;            
			           }            
			           cookieValue = decodeURIComponent(document.cookie.substring(cookieStart + cookieName.length, cookieEnd));        
			           } 
			           return cookieValue;    
			        },
					
					 set: function (name, value, expires, path, domain, secure) {        
					      var cookieText = encodeURIComponent(name) + "=" + encodeURIComponent(value);    
						  if (expires instanceof Date){
							  cookieText += "; expires=" + expires.toGMTString();        
							  }
							  if (path){
								  cookieText += "; path=" + path;
								  }
								  if (domain){
									  cookieText += "; domain=" + domain;
									  }            
									  if (secure){
										  cookieText += "; secure";        
										  }            
										  document.cookie = cookieText;    
					},        
					
					unset: function (name, path, domain, secure){        
					       this.set(name, "", new Date(0), path, domain, secure);    
						   }
                };

function calculate_time_zone() {
	var rightNow = new Date();
	var jan1 = new Date(rightNow.getFullYear(), 0, 1, 0, 0, 0, 0);  // jan 1st
	var june1 = new Date(rightNow.getFullYear(), 6, 1, 0, 0, 0, 0); // june 1st
	var temp = jan1.toGMTString();
	var jan2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
	temp = june1.toGMTString();
	var june2 = new Date(temp.substring(0, temp.lastIndexOf(" ")-1));
	var std_time_offset = (jan1 - jan2) / (1000 * 60 * 60);
	var daylight_time_offset = (june1 - june2) / (1000 * 60 * 60);
	var dst;
	if (std_time_offset == daylight_time_offset) {
		dst = "0"; // daylight savings time is NOT observed
	} else {
		// positive is southern, negative is northern hemisphere
		var hemisphere = std_time_offset - daylight_time_offset;
		if (hemisphere >= 0)
			std_time_offset = daylight_time_offset;
		dst = "1"; // daylight savings time is observed
	}
	var i;
	
	return std_time_offset;
	
	//document.cookie = "timezone = "+encodeURIComponent(convert(std_time_offset)); 
	
}

//After fetching the timezone(that has been calculated on the fly) the script that made the operation will then convert it.
function convert(value) {
	var hours = parseInt(value);
   	value -= parseInt(value);
	value *= 60;
	var mins = parseInt(value);
   	value -= parseInt(value);
	value *= 60;
	var secs = parseInt(value);
	var display_hours = hours;
	// handle GMT case (00:00)
	if (hours == 0) {
		display_hours = "00";
	} else if (hours > 0) {
		// add a plus sign and perhaps an extra 0
		display_hours = (hours < 10) ? "+0"+hours : "+"+hours;
	} else {
		// add an extra 0 if needed 
		display_hours = (hours > -10) ? "-0"+Math.abs(hours) : hours;
	}
	
	mins = (mins < 10) ? "0"+mins : mins;
	return display_hours+":"+mins;
}


function getQueryStringArgs()
{
	//get query string without the initial ?    
	var qs = (location.search.length  >  0 ? location.search.substring(1) : "");        
	//object to hold data    
	var args = {};
	//get individual items    
	var items = qs.split("&");    
	var item = null,
	    name = null,
		value = null;        
	//assign each item onto the args object    
	for (var i=0; i  <  items.length; i++)
	{
		item = items[i].split("=");        
		name = decodeURIComponent(item[0]);        
		value = decodeURIComponent(item[1]);        
		args[name] = value;    
		
	}
	return args; 
} 


if(CookieUtil.get("timezone") == null)
{
	CookieUtil.set("timezone", convert(calculate_time_zone()),  "/", "/"); 
}


function getFullDate()
{
	var fullDate = new Date();
		
    var month = fullDate.getMonth() + 1;
    if(month < 10)
	{
		month = "0"+month;
	};
		
    var date = fullDate.getDate();
		
    if(date < 10)
	{
		date = "0" + date;
	};
		
    //The final and well-formatted today's date.
    var today_date = fullDate.getFullYear()+"-"+month+"-"+date;
    return today_date;
}




function empty(val)
{
	if(val == undefined || val == "")
	{
		return true;
	}
	else
	{
		return false;
	}
}
	
function CustomNetworkError(msg=" A network error occured.")
{
	$(document).ajaxError(function(){
	    $(document).find('.modal-ctrl').remove();
        $(document).find('#myModal').remove();
   
        var row = $(document).find("div.row").eq(1);
		
	    $("<button class='btn modal-ctrl btn-primary btn-lg' data-toggle='modal' datatarget='#myModal'>Launch demo modal </button>").css({"display":"none"}).appendTo(row);
					  
        $("<div class='modal fade' id='myModal' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'> <p>"+msg+"</p> </div> </div></div></div>").appendTo(row);
					  
	    $(document).find('.modal-ctrl').trigger("click");
    });
};

function dragstart_handler(ev) {
	
    // Add the target element's id to the data transfer object
    ev.dataTransfer.setData("text/plain", ev.target.id);
  }


function dragover_handler(ev) {
 ev.preventDefault();
 
 ev.dataTransfer.dropEffect = "move";
}


function drop_handler(ev) {
	ev.preventDefault();
 
    const drop_target_id = ev.target.parentElement.id;
 
    const drop_target_order = $(document).find("#"+drop_target_id).find(".order").val();
 
    // Get the id of the origin/src
    const srcID = ev.dataTransfer.getData("text/plain");
 
    const srcOrder = $(document).find("#"+srcID).find(".order").val();
 
    if(srcOrder > drop_target_order)
    {
		$("#"+srcID).insertBefore("#"+drop_target_id);
    }
    else
    { 
	    $("#"+srcID).insertAfter("#"+drop_target_id);
    }
 
    var i = 0;
 
    $(document).find("tbody tr").each(function(){
	    i += 1;
	    $(this).find(".order").val(i);
	});
	
    $(document).find(".save").removeClass("d-none");
}


const capitalLetters = (s) => {
    return s.trim().split(" ").map(i => i[0].toUpperCase() + i.substr(1)).reduce((ac, i) => `${ac} ${i}`);
}






/************************************************************************************************************************************************************
**
**
******************************************************* THIS BLOCKS MARKS THE BEGINNING OF EVENT LISTENERS **************************************************
**
**
**************************************************************************************************************************************************************/
$(document).ready(function(){
 
    $(document).on("keyup keypress", ".text-box-write", function(event){
	 var inputBox = $(this);
	 
	 var input = $(this).val();
	 
	 if(input != "")
	 {
		 inputBox.next().removeClass("d-none");
	 }
	 else
	 {
		 inputBox.next().addClass("d-none");
	 }
 
    })
	
	//function to run when user attempts a removal operation. This works on all GET request 
$(document).on("click", ".remove", function(event){
	event.preventDefault();
	
	$(document).find("#myModal").modal('hide');

		var this_class = $(this);
		
		var control = this_class.attr("href");//The ful "Get" URL that was clicked on. We intercept it.
		
		var row = $(".row").eq(0);
		
		var div;
		
		if($(document).find(".container-fluid").length >= 1)
	    {
			div = $(document).find(".container-fluid").eq(0);
        }
	    else
	    {
			div = $(document).find(".container").eq(0);
	    }
		
		$.ajax({url:control, 
		        type:"GET",
				 beforeSend:function(){
					   this_class.addClass("disabled-link");
					   
					   $("<div class='d-flex justify-content-center spinner-div'><div class='spinner-grow position-fixed text-warning' role='status' style='left: 50%; top: 50%; height:60px; width:60px; margin:0px auto; position: absolute; z-index:1000;'><span class='sr-only'>Loading...</span></div").prependTo(div);
					   
					   },
		        complete:function(xhr, status){
					   this_class.removeClass("disabled-link");
					   div.find(".spinner-div").remove();
				   },
				success:function(data){
					if(data != false)
			        {
						try
						{
							var response = $.parseJSON(data);
			            }
			            catch(e)
			            {
					        $("<div class='modal fade' id='myModal' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'><p>"+data+"</p> </div> </div></div></div>").prependTo(row);
					  
					        $(document).find('#myModal').modal('show');
							
				            return;
			            }
			
						   //var response = $.parseJSON(data);
						   //console.log(response);
				        if(response["status"] == true)
				        {
							if(this_class.data("clear") == undefined)
							{
								this_class.parent().parent().empty();
							}
					  
					        $("<div class='modal fade' id='myModal' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'><p>"+response["msg"]+"</p> </div> </div></div></div>").prependTo(row);
							
							$(document).find('#myModal').modal('show');
							   
							//That means an action should be carried out on the Merchant's side. This is an administrative event
							if(response["socket_event"] != undefined)
							{
								var event_data = {"type":"action", "payload":response["payload"]};
							
                                socket.send(JSON.stringify(event_data));
							}
							   
							if(response["reload"] == true)
							{
								setTimeout(function(){
							                location.reload();
							       }, 1000);
							    };
						    }
						    else
						    {
					            $("<div class='modal fade' id='myModal' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'><p>"+response["msg"]+"</p> </div> </div></div></div>").prependTo(row);
								
								$(document).find('#myModal').modal('show');
						    };
			            };
				    },
	            });
});
 
/* ******************************************* START DASHBOARD-RELATED SETUP HERE ******************************************************* */
//function to run when user attempts to run any feature that first needs coonfirmation. This replaces the native "alert" prompt of browsers.
$(document).on("click", "a.pre-run", function(event){ 
    event.preventDefault(); 
	
	var clickedLink = $(this);
	
	var href = clickedLink.attr("href");
	
	var classToUse = clickedLink.data("classname") || "remove";
	
	var textWord = clickedLink.text() || "Continue";
	
	var caption = clickedLink.data("caption") || "Shall we?";
	//
	var row = $(".row").eq(0);

	$("<div class='modal fade' id='myModal' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'><div class='card'> <div class='card-body'><h5 class='card-title d-flex justify-content-center'>"+caption+"</h5></div><div class='card-footer'> <div class='btn-group d-flex justify-content-center' data-toggle='buttons'> <button type='button' class='close btn btn-default btn-lg' data-dismiss='modal' aria-hidden='true'>Cancel</button><a href='"+href+"' class='"+classToUse+" btn btn-danger btn-lg'>"+capitalLetters(textWord)+"</a></div></div> <div id='responseArea'></div></div></div> </div></div></div>").prependTo(row);
	
	$(document).find('#myModal').modal('show');

});


if(localStorage.background_color != undefined)
{
	$("body").css({"background-color":localStorage.background_color});
}

$(document).on("click", ".change_background", function(event){
	event.preventDefault();		
    var color = $(this).data("color");

   localStorage.background_color = color;
   $("body").css({"background-color":color});
});
	
	
$(document).on("click", ".check_all_link", function(event){ 
    event.preventDefault();
	
	var trigger = $(this).data("trigger");
	
	$("#"+trigger).trigger("click");
});


$(document).on("click", ".add-selected", function(event){ 
    event.preventDefault();
    
	var clickedLink = $(this);
	
	var linkUrl = clickedLink.attr("href");
	
	var altText = clickedLink.data("altText");
	
	var sub_value = clickedLink.text();
	
	var parentDiv = clickedLink.parent().parent().parent().parent();
	
    var products = [];
	
	var checkedBoxes = $(".select-me:checked");
	
	if(checkedBoxes.length <= 0)
	{
		$("<div class='alert alert-info alert-dismissable'> <button type='button' class='close' data-dismiss='alert' aria-hidden='true'> &times; </button>Make at least one selection.</div>").prependTo(parentDiv);
		
		return;
	}
		
	checkedBoxes.each(function(){
		products.push($(this).val().toString());
	});
	
	console.log("me"+products);
	
	$.ajax({url:linkUrl,  
		    type:"POST",
			data: {"id":products},
			beforeSend:function(){
					   clickedLink.text(altText);
					   clickedLink.addClass("disabled-link");
					   },
			complete:function(xhr, status){
					   clickedLink.text(sub_value);
					   clickedLink.removeClass("disabled-link");
				   },
		    cache: true,
			//data: {"add_gid":products},
			success:function(data){
					try
					{
						var result = $.parseJSON(data);
						if(result["status"] == true)
						{
							$(document).find('.modal-ctrl').remove();
                            $(document).find('#myModal').remove();
					   
					        var row = $(document).find("div.row");
					  
					        $("<div id='myModal' class='modal fade' tabindex='-1' role='dialog' aria-labelledby='myModal1' aria-hidden='true'><div class='modal-dialog' role='document'><div class='modal-content'><div class='modal-header'><h5 class='modal-title' id='exampleModalLiveLabel1'>Happy Shopping, Buddy!</h5><button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div><div class='modal-body'>"+result["msg"]+"</div></div></div></div>").appendTo(row);
							
							$(document).find('#myModal').modal('show');
					 
						}
						else
						{
							return;
						}
					}
					catch(e)
					{
						console.warn("Couldn't fetch anything");
					};
				},
			error:function(){
					   
				   },
	});
	
});

	
$(document).on("click", "#check_all", function(event){ 

    if($(this).data("target") == undefined)
	{
		$(".customer").trigger("click");
	}
	else
	{
		$("."+$(this).data("target")).trigger("click");
	}
});



$(document).on("click", ".remove-area", function(event){ 
    event.preventDefault();
	$(this).parent().parent().remove();
});


$(document).on("click", ".add-area", function(event){ 
    event.preventDefault();
	var name = $(".potential-name").data("name") || $(this).data("name");
	var placeholder = $(".potential-name").data("placeholder") || $(this).data("placeholder");
	
	$("<div class='input-group'><div class='input-group-prepend'><a class='remove-area btn btn-secondary' href='#'><i class='fas fa-minus'></i></a></div><input type='text' name='"+name+"' class='form-control form-control-lg' placeholder='"+placeholder+"'/><hr/></div>").appendTo(".dynamic-input-div");
	
	$(this).parent().find("input").last().trigger("focus");
});



//URL's with these hashes should reload normally without AJAX-system. This is triggered when User presses the "back" button
//let keywords = ["index", "upload", "bulk", "create_page"];
let keywords = ["upload", "bulk", "create_page"];
$(document).on("click", "a.nextPageViaAjaxOnly", function(event){
	
	event.preventDefault();
	
	if(document.documentElement.clientWidth < 1300)
	{
		var container = $(".main-menu-for-dashboard");
		
		if(container.is(":visible") == true)
		{
			container.toggle("slow");
	    }
	}
	
	var url = $(this).attr("href");
	console.log(url);
	//There should always be an "href" attribute to URL's like this anyway.
	if(url == "")
	{
		return;
	}
		
	//By default all the URL must have a hash so we look for it here
	if(url.search("#") > 0)
	{
		//We extract the word ff the hash to search it against the values in the array above.
		var keyword = url.substr(url.search("#")+1);
			
		//There must always be an hash for URL's that are to be loaded via AJAX anyway.
		if(keyword == "")
		{
			return;
		}
	
	    
		if($(document).find("#TopDivForAJAXOnly").length < 1)
	    {
			$(document).find(".pap").wrapAll('<div class="col-sm-10 col-lg-10 col-md-10" style="height:800px; overflow:scroll;" id="TopDivForAJAXOnly"></div>');
	    }
	
	    var TopDiv = $(document).find("#TopDivForAJAXOnly");
	
	    TopDiv.empty();
	
	    $("<div class='dual-ring-rotate'></div>").appendTo(TopDiv);
		
			
		//This is among those URL's that should be reloaded.
		if(keywords.includes(keyword)) 
		{
			location.replace(url);
		}
		
		$(TopDiv).load(url+" #pap", function(data){
			if(keyword == "index")
			{
				fetchProducts();
			}
			
			arrangeDashboard();
		})
			
		history.pushState(location.pathname+location.hash, "Webloit", url);
		
		document.title = keyword;
		
		
	}
	else
	{
		location.replace(url);
	}
	
})


window.addEventListener("popstate", function(e){
	e.preventDefault();
	console.log("Triggered");
	console.log(location.hash);
	
	setTimeout(function(){
	//This must have been triggered via Fancy Box cause that is mainly the time we assign a "Hash" URL value of "galler-*";
	if(location.hash.search("gallery") >= 1)
	{
		console.log("found it");
		
		return;
		
	}
	
	
	
	if(history.state != null)
	{
		var url = history.state;
			
		console.log(url);
		
		//We extract the word ff the hash to search it against the values in the "keywords" array.
		var keyword = url.substr(url.search("#")+1);
			
		console.log("Prev Page is: "+url+"\nKeyword is: "+keyword);
		
		if(keyword == "")
		{
			console.log("not found");
			return;
		}
		
		//This is among those URL's that should be reloaded.
		if(keywords.includes(keyword))
		{
			console.log("Yes, included");
			location.replace(url);
		}
		
		if($(document).find("#TopDivForAJAXOnly").length < 1)
	    {
			$(document).find(".pap").wrapAll('<div id="TopDivForAJAXOnly">');
	    }

	    var TopDiv = $(document).find("#TopDivForAJAXOnly");
	
	    TopDiv.empty();
	
	    $("<div class='dual-ring-rotate'></div>").appendTo(TopDiv);
	        
		console.log("I fetched: "+url);
	    
		
		$(TopDiv).load(url+" #pap", function(data){
			if(keyword == "index")
			{
				fetchProducts();
			}
			
			arrangeDashboard();
		});
		
			
		history.replaceState(location.pathname+location.hash, "Webloit", url);
			
		arrangeDashboard();
	}
	else
	{
		console.log("no history");
		
		/*
		var url = "/office/dashboard/";
		location.replace(url);
		*/
	}
	}, 5);
})




/* ************************************************ DASHBOARD-RELATED SETUP HERE ******************************************************* */
$(document).on("click", ".copy", function(event){  
		event.preventDefault();
		var id = $(this).data("command");
		var IdOfWhatToCopy = $("#"+id);
		var copy = IdOfWhatToCopy.select(); 
		var p = document.execCommand("copy");
	});
	


$(document).on("click", ".modal-ctrl", (function(event){
		event.preventDefault();
		$(document).find("#myModal").modal({ 
      keyboard: true 
   });
}));


//This brings up any linked page as a modal.
$(document).on("click", "a.ajax-link-no-direct", (function(event){
		 event.preventDefault();
		  var clickedLink = $(this);
		 
		  var expandOrNot = clickedLink.data("expand");
		
		  var backdrop = clickedLink.data("backdrop");
	
		 var addr = clickedLink.attr("href");
		 
		 if($(document).find(".container-fluid").length >= 1)
		 {
            div = $(document).find(".container-fluid").eq(0);
		 }
		 else
		 {
			div = $(document).find(".container").eq(0);
		 }
		 
		 $.ajax({url:addr,  
		           type:"GET",
				   cache: true,
				   beforeSend:function(){
					   //All links like this shouldn't be clickable so that we don't have mulitple modals which will destroy our UI
					   $("a.ajax-link-no-direct").addClass("disabled-link");
					   
					   $("<div class='d-flex justify-content-center spinner-div'><div class='spinner-grow position-fixed text-primary' role='status' style='left: 50%; top: 50%; height:60px; width:60px; margin:0px auto; position: absolute; z-index:10000;'><span class='sr-only'>Loading...</span></div").prependTo(div);
					   
					   },
				  complete:function(xhr, status){
					   $("a.ajax-link-no-direct").removeClass("disabled-link");
					   div.find(".spinner-div").remove();
					   
				   },
				   success:function(data){
					   
					   $(document).find('.modal-close').trigger("click");
					    $(document).find('.modal-ctrls').remove();
                        $(document).find('#myModals').remove();
						
						var row = $(document).find("div.row").eq(0);
						
						if(backdrop == "static")
						{
							var dataBackdrop = "data-backdrop='static'";
						}
						else
						{
							var dataBackdrop = "data-doNothing='nothing'";
						}
					   
					     //Some dialogs should expand on bigger screens but remain the way they are on smaller devices/screen
						 if(expandOrNot == true)
						 {
							 if(document.documentElement.clientWidth > 1300)
							 {
								 $("<div class='modal fade' "+dataBackdrop+" id='myModals' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog  modal-xl'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'>"+data+" </div> </div></div></div>").appendTo(row);
							 }
							 else
							 {
								 $("<div class='modal fade' id='myModals' "+dataBackdrop+" tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'>"+data+" </div> </div></div></div>").appendTo(row);
							 }
						 }
						 else
						 {
						
                        $("<div class='modal fade' "+dataBackdrop+" id='myModals' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'>"+data+" </div> </div></div></div>").appendTo(row);
						 }
						
						$(document).find('#myModals').modal('show');
	                     //$(document).find('.modal-ctrls').trigger("click");
				   },
				   error:function(){
					   
				   },
	              });
	 }));
	 

//================================================ FOR OUR CALCULATOR APP =======================================================
//When you click on the numbers for calculation. It takes the value and displays it on the "screen".
  $(document).on("click", "#input", function(event){
	  var pressed = $(this).val();//The value
	  //"screen" is the screen where is visible
	  var screen = $("#screen");
	  screen.val(screen.val() + pressed);
  });
  
  $(document).on("click", "#calculate", function(event){
	  event.preventDefault();
	  var screen = $("#screen");
	  
	  var pattern = /[a-z]/; 
	  
	  var result;
	  
	  if(pattern.test(screen.val()) == false)
	  {
		  result = eval(screen.val());
	  }
	  else
	  {
		  result = 0;
	  }
	  
	  screen.val(result);
	  
  });
  
  //This clears value(s) just like when erasing.
  $(document).on("click", "#clear", function(event){
	  event.preventDefault();
	  var screen = $("#screen");
	 var removeOneItemFromScreen = screen.val().length - 1;//This reduces the length of content on screen by 1
	 var newContent = screen.val().substr(0,removeOneItemFromScreen);
	  $("#screen").val(newContent);
  });
//============================================= END OF OUR CALCULATOR ===========================================================

  

//The search form in the homepage should be triggered by AJAX so that we can create a clean URL for it.  
$(document).on("submit", ".search-form", function(event){  
event.preventDefault();
   
   var this_form = $(this);
   
   var newUrl = $(this)[0]["action"];
   
   var inputValue = $(this).find(".search-box").val();
   console.log(inputValue);
   location.assign(newUrl+inputValue);
});
  
  

$(document).on("click", ".search-specific", function(event){  
event.preventDefault();
   var clickedLink = $(this);
   
   var expandOrNot = $(this).data("expand");
   
   var search_product = $("#search_product").val();
   
   var url = clickedLink.attr("href");
   
   var addr = url+"?search="+search_product;
   
   $.ajax({url:addr,  
		           type:"GET",
				   cache: true,
				   beforeSend:function(){
					   clickedLink.addClass("disabled-link");
					   },
				  complete:function(xhr, status){
					   clickedLink.removeClass("disabled-link");
				   },
				   success:function(data){
					   
					   $(document).find('.modal-close').trigger("click");
					    $(document).find('.modal-ctrls').remove();
                        $(document).find('#myModals').remove();
						
						var row = $(document).find("div.row").eq(0);
					   
						 if(expandOrNot == true)
						 {
							 if(document.documentElement.clientWidth > 1300)
							 {
								 $("<div class='modal fade' id='myModals' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog modal-xl'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'>"+data+" </div> </div></div></div>").appendTo(row);
							 }
							 else
							 {
								 $("<div class='modal fade' id='myModals' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'>"+data+" </div> </div></div></div>").appendTo(row);
							 }
						 }
						 else
						 {
							 $("<div class='modal fade' id='myModals' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'>"+data+" </div> </div></div></div>").appendTo(row);
						 }
						
						$(document).find('#myModals').modal('show');
	                     //$(document).find('.modal-ctrls').trigger("click");
				   },
				   error:function(){
					   
				   },
	              });
   
})
  
  
//The variant of auto-submit for those forms that use GET method
$(document).on("submit", "#getForm", function(event){  
event.preventDefault();
   
   var this_form = $(this);
	
	//In case there are more than 2 submit buttons in a form.
    var sub = this_form.find(":submit").last();
	
	var query = "?";
	
	this_form.find("textarea, input:not(:submit)").each(function(){
		//console.log($(this).val());
		
		 query += $(this).attr("name")+"="+$(this).val()+"&";
		});
		
		
	//In case there are more than 2 submit buttons in a form.
    //var sub = this_form.find(":submit").last();

    if(this_form.find("div").hasClass("success") == false)
    {
		$("<div class='success'></div>").insertBefore(sub);
	
	    var responseArea = this_form.find(".success");
    }
    else
    {				   
	    var responseArea = this_form.find(".success");
    }
	
	var notFilled = false;
	
	//We make sure those fields that are required are filled incase the user mistakenly skips any.
	this_form.find("input").each(function(){
		
		if($(this).data("name") != undefined || $(this).attr("required") != undefined)
		{
			if($(this).val() == "")
			{
				notFilled = true;
				
				var name = $(this).data("name") || $(this).attr("name");
			
			    responseArea.html("<p class='alert alert-danger'>You should fill in the "+capitalLetters(name)+" field before you proceed</p>");
			
			    return false;
			}
		}
	});
	
	if(notFilled == true)
	{
		return false;
	}
	
	var sub_value = sub.val();

		var action = $(this)[0]["action"];
		
		var method = $(this)[0]["method"];
		
		 $.ajax({url:action+query, 
		           type:method,
				   beforeSend:function(){
					   if(this_form.find("div").hasClass("upload-progress-div") == false)
					   {
						   $("<div class='form-group upload-progress-div'><div class='progress'><div class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' style='width: 0%' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'></div></div></div>").insertBefore(sub);
                       };
					       sub.val("...in progress");
				           //$("body").addClass("opaque-body-during-ajax-call"); //blur the body
			       },
				   xhr: function(){
					   var xhr = new window.XMLHttpRequest();
					   xhr.upload.addEventListener("progress", function(evt){
						   if(evt.lengthComputable){
							   var percentComplete = evt.loaded / evt.total;
							   percentComplete = parseInt(percentComplete * 100);
					           var progressDiv = this_form.find(".progress-bar");
					           progressDiv.width(percentComplete+"%");
						   };
					   }, false);
					   return xhr;
				   },
			       complete:function(xhr){
					  console.log("Completed: "+xhr.status);
					       sub.val(sub_value);
				           $("body").removeClass("opaque-body-during-ajax-call");
						   this_form.find(".progress-bar").parent().parent().remove();
			       },
				   success:function(data){
					   responseArea.html(data);
				   },
				   error:function(){
					   responseArea.html("<p class='alert alert-danger'>There was a problem in submission. Please try again.</p>");
				   }
		   });
});
  
  
  //General for all pages that use a POST submit method especially.
$(document).on("submit", "#form", function(event){  
event.preventDefault();

    var this_form = $(this);
	
	//In case there are more than 2 submit buttons in a form.
    var sub = this_form.find(":submit").last();

    if(this_form.find("div").hasClass("success") == false)
    {
		$("<div class='success'></div>").insertBefore(sub);
	
	    var responseArea = this_form.find(".success");
    }
    else
    {				   
	    var responseArea = this_form.find(".success");
    }
	
	if($(document).find("#hidden_content").length >= 1)
	{
		$(document).find("#hidden_content").val(frames['richedit'].document.body.innerHTML);
	}
	
	var notFilled = false;
	
	//We make sure those fields that are required are filled incase the user mistakenly skips any.
	this_form.find("input").each(function(){
		
		if($(this).data("name") != undefined || $(this).attr("required") != undefined)
		{
			if($(this).val() == "")
			{
				
				var notFilled = true;
				
				var name = $(this).data("name") || $(this).attr("name");
				
				$(this).removeClass("is-valid").addClass("is-invalid");
			
			    responseArea.html("<p class='alert alert-danger'>You should fill in the "+capitalLetters(name)+" field before you proceed</p>");
			
			    return false;
				
			}
			
			//Anyone it loops through without any error is considered valid
			$(this).removeClass("is-invalid").addClass("is-valid");
			
		}
	});
	
	// This means there are 1/2 fields that weren't filled and they're necessary
	if(notFilled == true)
	{
		return false;
	}
	
	
		var sub_value = sub.val();

		var action = $(this)[0]["action"];
		
		var method = $(this)[0]["method"];
		
		data_to_send = new FormData(this);
		
		 $.ajax({url:action, 
		           type:method,
				   beforeSend:function(){
					   if(this_form.find("div").hasClass("upload-progress-div") == false)
					   {
						   $("<div class='form-group upload-progress-div'><div class='progress'><div class='progress-bar progress-bar-striped progress-bar-animated' role='progressbar' style='width: 0%' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'></div></div></div>").insertBefore(sub);
                       };
					       sub.val("...in progress");
						   sub.attr("disabled", "disabled");
				           $("body").addClass("opaque-body-during-ajax-call"); //blur the body
			       },
				   xhr: function(){
					   var xhr = new window.XMLHttpRequest();
					   xhr.upload.addEventListener("progress", function(evt){
						   if(evt.lengthComputable){
							   var percentComplete = evt.loaded / evt.total;
							   percentComplete = parseInt(percentComplete * 100);
					           var progressDiv = this_form.find(".progress-bar");
					           progressDiv.width(percentComplete+"%");
						   };
					   }, false);
					   return xhr;
				   },
			       complete:function(xhr){
					  console.log("Completed: "+xhr.status);
					       sub.val(sub_value);
				           $("body").removeClass("opaque-body-during-ajax-call");
						   sub.removeAttr("disabled");
						   this_form.find(".progress-bar").parent().parent().remove();
			       },
				   data: new FormData(this),
				   contentType: false,
				   cache: false,
				   processData:false,
				   success:function(data){
					   responseArea.html(data);
				   },
				   error:function(){
					   responseArea.html("<p class='alert alert-danger'>There was a problem in submission. Please try again.</p>");
				   }
		   });
});


if($("a#dashboard-menu-toggle").length == 1)
{
	if(CookieUtil.get("push_checked") == null)
	{
		if(typeof(messaging) != "undefined")
		{
			messaging.getToken().then((currentToken) => {
				console.log(currentToken);
				fetch('/controller/background_call/push_subscription.php', {
					method: 'PUT',
                    headers: {
						'Content-Type': 'application/json'
                     },
                    body: JSON.stringify(currentToken)
                }).then(function(response) {
					if (!response.ok) {
					throw new Error('Bad status code from server.');
                    }
                return response.json();})
			}).catch((e) => console.log("found nothing"));
        };
    };

	//This style is applied on smaller devices/screen. This is called immediately the page is loaded.
    if(document.documentElement.clientWidth < 1300) 
    {
        //var breadcrumb_div = $(document).find(".breadcrumb-for-small-screens");
		 
		var toggle_side_menu = $(document).find("a#dashboard-menu-toggle");
		 
		var dashboard_menu = $(document).find("div.main-menu-for-dashboard");
		 
		var dummy_dashboard_div = $(document).find("div.pap");
		
		dummy_dashboard_div.parent().parent().removeClass("col-sm-10 col-lg-10 col-md-10").addClass("col-lg-12 col-md-12 col-sm-12 col-xs-12");
	  
		dashboard_menu.removeClass("col-lg-2 col-sm-2 col-md-2 d-none d-sm-block").css({"position":"absolute", "width":"200px",  "z-index":"10000"}).hide();
	    
	    toggle_side_menu.on("click",function(event){
			//when you click on the breadcrumb
		    event.preventDefault();
			console.log("i was clicked");
			event.stopPropagation();
		 
		    dashboard_menu.toggle("slow");
	    });
	 
	   $(document).find("table").addClass("table-responsive");
	   
	   $(document).find("div.preview-container").removeClass("preview-style-on-bigger-screens").css({"height":"100%","width":"100%"});
    }
	else
	{
		console.log("negative");
	};
};


$(".container-fluid").on('swiperight', function(e) { 

	if(document.documentElement.clientWidth < 1300) 
    {
		if($(document).find(".main-menu-for-dashboard").length >= 1)
		{
			var container = $(document).find(".main-menu-for-dashboard");

            // if the target of the click isn't the container nor a descendant of the container
            if (!container.is(e.target) && container.has(e.target).length === 0) 
            {
				if( container.is(":hidden") == true)
				{
					container.toggle("slow");
		        }
            }
		}
	}
})
	
	
$(document).on("click", (function(e) 
{
	if(document.documentElement.clientWidth < 1300)
	{
		var container = $(".main-menu-for-dashboard");

        // if the target of the click isn't the container nor a descendant of the container
        if (!container.is(e.target) && container.has(e.target).length === 0) 
        {
			if( container.is(":visible") == true)
			{
				container.toggle("slow");
		    }
        }
	
	}
   
}));
	
	
	$(document).find("[data-toggle='tooltip']").tooltip();

    $(document).on("click", ".hint", function(event){
	    $("[data-toggle='tooltip']").tooltip();
    });

//This is for the profile area. It toggles between sliding up and down the hidden menu in the 'profile menu' area 
//That is the menu of "change password", etc. It is also inherited by certain categories in the dashboard category area and can also 
//be inherited by whatsoever script.
    $(document).on("click", "a.container-for-hidden-menus", function(event){
	//$("a.container-for-hidden-menus").click(function(event){
		event.preventDefault();
		
		var parentContainer = $(this);
		var profile_menu_area = parentContainer.parent().next();
		
		//This closes any previously opened tag.
		$(document).find(".sub-lists-of-menu-profile").hide("slow").prev().find(".container-for-hidden-menus").find(".caret-ctrl").removeClass("fa-minus-circle").addClass("fa-arrow-alt-circle-down");
		
		if(profile_menu_area.is(":hidden"))
		{
			parentContainer.parent().find("i.fa-arrow-alt-circle-down").removeClass("fa-minus-circle").addClass("fa-arrow-alt-circle-down");
			
			profile_menu_area.show("slow");
			parentContainer.parent().find("i.fa-arrow-alt-circle-down").removeClass("fa-fa-arrow-alt-circle-down").addClass("fa-minus-circle");
		}
		else
		{
			profile_menu_area.hide("slow");
			parentContainer.parent().find("i.fa-arrow-alt-circle-down").removeClass("fa-minus-circle").addClass("fa-arrow-alt-circle-down");
		};
		
     });
	 
	$(document).on("change", ".range_slider", function(event){  
		event.preventDefault();
		
		var rangeDisplay, value, displayCurrency;
		
		rangeDisplay = $(this).attr("data-rangeDisplay");
		
		value = $(this).val();
		
		displayCurrency = $(this).attr("data-displayCurrency");
		
		if(displayCurrency == "yes")
		{
			if($(document).find("#currency").length >= 1)
			{
				//If the user proceeds with the search the products will be displayed in this currency
				CookieUtil.set("currency", $(document).find("#currency").val(), 0);
				
				value = money_format(value, $(document).find("#currency").val())
			}
		}
		
		$("."+rangeDisplay).text(value);
	});
	
	
//Some forms require that the navigation be done via js. These forms will receive the Redirect URl as part of a successful authentication which the js will redirect to instead of server-side validation
$(document).on("submit", "#ajax-post-nav-form", (function(event){
	event.preventDefault();
	var this_form = $(this);

	
	//In case there are more than 2 submit buttons in a form.
    var sub = this_form.find(":submit").last();

    if(this_form.find("div").hasClass("success") == false)
    {
		$("<div class='success'></div>").insertBefore(sub);
	
	    var responseArea = this_form.find(".success");
    }
    else
    {				   
	    var responseArea = this_form.find(".success");
    };
	
	var sub_value = sub.val();

	var action = this_form[0]["action"];
		
	var method = this_form[0]["method"];
		
	data_to_send = new FormData(this);
		
		
	if($(document).find(".container-fluid").length >= 1)
	{
        div = $(document).find(".container-fluid").eq(0);
    }
	else
	{
		div = $(document).find(".container").eq(0);
	}
		 
		 
	nav = $(".navbar").eq(0);
	
    $.ajax({url:action,  
		    type:method,
		    beforeSend:function(){
					   sub.val("...in progress");
					   sub.attr("disabled", "disabled");
					   
					   $("<div class='d-flex justify-content-center spinner-div'><div class='spinner-grow position-fixed text-primary' role='status' style='left: 50%; top: 50%; height:60px; width:60px; margin:0px auto; position: absolute; z-index:1000;'><span class='sr-only'>Loading...</span></div").prependTo(div);
					   
					   },
		    complete:function(xhr, status){
					   sub.val(sub_value);
					   sub.removeAttr("disabled");
					   div.find(".spinner-div").remove();
					   
					   
				   },
		    data: new FormData(this),
			contentType: false,
			cache: false,
			processData:false,
			success:function(data){
				    try
					{
						var response = $.parseJSON(data);
						
						if(response["status"] == true)
						{
							$("<div class='progress fixed-top'><div class='progress-bar progress-bar-striped bg-info' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100' style='width: 75%'></div></div>").appendTo(nav);
							sub.val("redirecting...");
							
							var replace = response["replace"] || false;
							
							if(replace == false)
							{
								window.location.href = response["redirect_url"];
							}
							else
							{
								window.location.replace(response["redirect_url"]);
							}
						}
					}
					catch(e)
					{
						responseArea.html(data);
					}
				   },
			error:function(){
					   responseArea.html("<p class='alert alert-danger'>A network error occured.</p>");
				   },
	              });
}));



//Called for links that should redirect after successful parsing/authentication. We redirect client side instead.
$(document).on("click", ".ajax-redirect-link", (function(event){
	event.preventDefault();
	$(document).find("#myModal").modal('hide');
	var this_link = $(this);
		
	if($(document).find(".container-fluid").length >= 1)
	{
        div = $(document).find(".container-fluid").eq(0);
    }
	else
	{
		div = $(document).find(".container").eq(0);
	}
	
	var row = $(document).find("div.row").eq(0);
	
	var urlLink = this_link.attr("href");
		 
	nav = $(".navbar").eq(0);
	
    $.ajax({url:urlLink,  
		    type:"GET",
		    beforeSend:function(){
					   this_link.addClass("disabled-link");
					   
					   $("<div class='d-flex justify-content-center spinner-div'><div class='spinner-grow position-fixed text-primary' role='status' style='left: 50%; top: 50%; height:60px; width:60px; margin:0px auto; position: absolute; z-index:1000;'><span class='sr-only'>Loading...</span></div").prependTo(div);
					   
					   },
		    complete:function(xhr, status){
					   this_link.removeClass("disabled-link");
					   div.find(".spinner-div").remove();
				   },
			success:function(data){
				    try
					{
						var response = $.parseJSON(data);
						
						if(response["status"] == true)
						{
							$("<div class='progress fixed-top'><div class='progress-bar progress-bar-striped bg-info' role='progressbar' aria-valuenow='75' aria-valuemin='0' aria-valuemax='100' style='width: 75%'></div></div>").appendTo(nav);
							
							var replace = response["replace"] || false;
							
							if(replace == false)
							{
								window.location.href = response["redirect_url"];
							}
							else
							{
								window.location.replace(response["redirect_url"]);
							}
							
							//location.replace(response["redirect_url"]);
						}
					}
					catch(e)
					{
					    if($(document).find("#responseArea").length >= 1)
					    {
							responseArea = $(document).find("#responseArea").eq(0);
							
							responseArea.html(data);
					    }
						else
						{
							$("<div class='modal fade' id='myModals' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'>"+data+" </div> </div></div></div>").appendTo(row);
						 
						    $(document).find('#myModals').modal('show');
						
						}
					}
				   },
			error:function(){
				        if($(document).find("#responseArea").length >= 1)
					    {
							responseArea = $(document).find("#responseArea").eq(0);
							
							responseArea.html("<p class='alert alert-danger'>A network error occured.</p>");
					    }
						else
						{
							$("<div class='modal fade' id='myModals' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'><p class='alert alert-danger'>A network error occured.</p></div> </div></div></div>").appendTo(row);
						 
						    $(document).find('#myModals').modal('show');
						
						}
					   
				   },
	              });
}))


//function to run when user attempts to delete.
$(document).on("click", "a.del", function(event){ 
    event.preventDefault();
	
    var ask = confirm("Are you sure you want to do this?");
	if(ask == false)
	{
		return;
	}
    else
	{
		$(document).find("#myModal").modal('hide');
		
		var this_button = $(this);
		var url = this_button.attr("href");
		var parent_element = this_button.parent().parent();
		
		var original_text = this_button.text();
		
		var loading_text = $(this).data("text") || "deleting";
		
		var parentid = this_button.data("parentid");
		
		var cmd = $(this).data("clear");
		
		$.ajax({url:url, 
		           type:"GET",
				   beforeSend:function(){
					    this_button.text(loading_text);

				   },
				   complete:function(){
					   this_button.text(original_text);
				   },
				   success:function(data){
					   if(data != false)
			           {
						   try
						   {
							   var response = $.parseJSON(data);
						       $(document).find('.close').trigger("click");
						   
							   var row = $(document).find("div.row").eq(0);
						   
					           $("<div class='modal fade' id='myModal' tabindex='-1' role='dialog' aria-labelledby='myModalLabel' aria-hidden='true'> <div class='modal-dialog'> <div class='modal-content'> <div class='modal-header'> <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>× </button> </div> <div class='modal-body'><p>"+response["msg"]+"</p> </div> </div></div></div>").prependTo(row);
							   
							   $(document).find('#myModal').modal('show');
							   
				               if(response["status"] == true)
				               {
							       console.log(cmd);
							   
							       if(cmd !== "no")
							       {
									   if(parentid != "")
									   {
										   $(document).find("#"+parentid).empty();
									   }
									   else
									   {
										   parent_element.remove();	
									   }
							       }   
				   
								   
								   if(response["reload"] == true)
								   {
									   setTimeout(function(){
										   location.reload();
									   }, 1000);
								   };
						       };
						   }
						   catch(e)
						   {
							   console.log("nothing sha");
						   };
			           };
				   },
	              });
	};
});
	 
});

