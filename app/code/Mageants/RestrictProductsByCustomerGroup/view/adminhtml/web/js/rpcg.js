define([
  'jquery',
], 
function($){ 
	$(document).ready(function(){
		var i = 0;
		$("#add_new_option_button").click(function(){
			i++;
		    var markup ="<tr id='new"+i+"' class='from_to'><td class=''><input type='text' class='input-text validate-url' value='' name='url["+i+"][from]'></td><td class=''><input type='text' class='input-text validate-url' value='' name='url["+i+"][to]'></td><td style='padding-left:20px; padding-top:5px;'><button id='del_new"+i+"' class='btnDelete' type='button' title='Delete'>Delete</button></td></tr>"
		  	$("#rpcg_form_to").append(markup);
		});
		
		$("#rpcg_form_to").on('click', '.btnDelete', function () {
		    $(this).closest('tr').remove();
		});
		$(".field-redirectoption").css('display','none');
		$("#page_response").change(function(){
	        var selectedvalue = $(this).children("option:selected").val();
	        if(selectedvalue == 0){
	        	$(".field-errormessage").css('display','block');
	        	$(".field-redirectoption").css('display','none');
	        }else{
	        	$(".field-redirectoption").css('display','block');
	        	$(".field-errormessage").css('display','none');
	        }
	    });

	});
});