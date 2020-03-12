function contentpulse(urlpage,container){
	//document.getElementById(container).innerHTML='<div style="text-align:center;padding-top:200px"><img src="js/load.gif" /><br><span class="btext_small_l">Loading, Please 	Wait......</span></div>';

	$.ajax({url : urlpage,success : function(data){$("#" + container).html(data);}});
}

function AjaxFunction(cat_id,url,divcontent,data){

var httpxml;
try{
  // Firefox, Opera 8.0+, Safari
	httpxml=new XMLHttpRequest();
}
catch (e){
  // Internet Explorer
	try
   		{
   			httpxml=new ActiveXObject("Msxml2.XMLHTTP");
    	}
  	catch (e)
    {
    	try
      		{
      		httpxml=new ActiveXObject("Microsoft.XMLHTTP");
     	}
    	catch (e)
      	{
      		alert("Your browser does not support AJAX!");
      		return false;
      	}
    }
}

function stateck(){
	if(httpxml.readyState==4)
    {
		document.getElementById(divcontent).innerHTML="Please wait(Kwatililamu katono).......";
		document.getElementById(divcontent).innerHTML=httpxml.responseText
	}
}
	
//url=url+"?cat_id="+cat_id;
url=url+"?cat_id="+cat_id;

//url=url+"&sid="+Math.random();
httpxml.onreadystatechange=stateck;
httpxml.open("GET",url,true);
httpxml.send(null);
}

function calculate_billed_total(amount_id,value_id){
	document.getElementById('billed_total').value = document.getElementById(amount_id).value * document.getElementById(value_id).value;
}