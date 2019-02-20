$(document).ready(function() {


	$('.chk_disable').on('change', function() {
		if($(this).prop('checked')) { 
			 $('#'+$(this).data('target')).attr('disabled', false);
		}else{
			 $('#'+$(this).data('target')).attr('disabled', 'disabled');
		}
	});
 
	$('#btn_send_sell').click(function(){
		console.log('send sell');
		var url = $(this).data('url');
		var order_id = $(this).data('order');
		var message = $(this).data('message');
		$('#btn_send_sell').hide();
		$.post(url, { order_id:order_id },
		
			
		    function( data ) {	
				console.log(data);
				$('#'+message).removeClass('alert-danger');
				$('#'+message).removeClass('alert-success');
				$('#'+message).addClass('alert');
			
				if(data.success){
					$('#'+message).show();
					$('#'+message).addClass('alert-success');
					$('#'+message).text(data.message );
				}else{
					$('#'+message).show();
					$('#'+message).addClass('alert-danger');
					$('#'+message).text(data.message );
				}
			}, 'json');
	
	
	});
	
	$('#btn_send_sell_refund').click(function(){
		console.log('send sell');
		var url = $(this).data('url');
		var order_id = $(this).data('order');
		var message = $(this).data('message');
		$('#btn_send_sell_refund').hide();
		$.post(url, { order_id:order_id },
		
			
		    function( data ) {	
				console.log(data);
				$('#'+message).removeClass('alert-danger');
				$('#'+message).removeClass('alert-success');
				$('#'+message).addClass('alert');
				
			
				if(data.success){
					$('#'+message).show();
					$('#'+message).addClass('alert-success');
					$('#'+message).text(data.message );
				}else{
					$('#'+message).show();
					$('#'+message).addClass('alert-danger');
					$('#'+message).text(data.message );
				}
			}, 'json');
	
	
	});
	
	/*
	$('#btn_send_sell_correction').click(function(){
		console.log('send sell');
		var url = $(this).data('url');
		var order_id = $(this).data('order');
		var message = $(this).data('message');
		$('#btn_send_sell_correction').hide();
		$.post(url, { order_id:order_id },
		
			
		    function( data ) {	
				console.log(data);
				$('#'+message).removeClass('alert-danger');
				$('#'+message).removeClass('alert-success');
				
			
				if(data.error  == null){
					$('#'+message).show();
					$('#'+message).addClass('alert-success');
					$('#'+message).text('Проверка успешна ' );
				}else{
					$('#'+message).show();
					$('#'+message).addClass('alert-danger');
					$('#'+message).text('Ошибка '+ data.code + ' ' + data.text );
				}
			}, 'json');
	
	
	});
	*/
});
 
 