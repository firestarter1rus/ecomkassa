<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-latest" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
		<div class="alert alert-info"><i class="fa fa-info-circle"></i>  <?php echo $ecom_cron;?>&emsp;<b>* * * * * wget -q --spider <?php echo$cron;?></b></div>
	  
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-latest" class="form-horizontal">
		<ul class="nav nav-tabs">
			<li class="active">
				<a href="#tab_sale" data-toggle="tab"><?php echo $ecom_settings; ?></a>
			</li>
			<li>
				<a href="#tab_about" data-toggle="tab"><?php echo $ecom_about; ?></a>
			</li>
			 
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="tab_sale">
			
				
				
				<br>
				<h3 class="panel-title"><?php echo $ecom_connection; ?></h3>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="input-url"><?php echo $entry_url; ?></label>
					<div class="col-sm-10">
					  <input type="text" name="ecomkassa_url" value="<?php echo $url; ?>" placeholder="<?php echo $entry_url; ?>" id="input-url" class="form-control" />
					  <?php if ($error_url) { ?>
					  <div class="text-danger"><?php echo $error_url; ?></div>
					  <?php } ?>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-sm-2 control-label" for="input-login"><?php echo $entry_login; ?></label>
					<div class="col-sm-10">
					  <input type="text" name="ecomkassa_login" value="<?php echo $login; ?>" placeholder="<?php echo $entry_login; ?>" id="input-login" class="form-control" />
					  <?php if ($error_login) { ?>
					  <div class="text-danger"><?php echo $error_login; ?></div>
					  <?php } ?>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-sm-2 control-label" for="input-password"><?php echo $entry_password; ?></label>
					<div class="col-sm-10">
					  <input type="text" name="ecomkassa_password" value="<?php echo $password; ?>" placeholder="<?php echo $entry_password; ?>" id="input-password" class="form-control" />
					  <?php if ($error_password) { ?>
					  <div class="text-danger"><?php echo $error_password; ?></div>
					  <?php } ?>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-sm-2 control-label" for="input-shopid"><?php echo $entry_shopid; ?></label>
					<div class="col-sm-10">
					  <input type="text" name="ecomkassa_shopid" value="<?php echo $shopid; ?>" placeholder="<?php echo $entry_shopid; ?>" id="input-shopid" class="form-control" />
					  <?php if ($error_shopid) { ?>
					  <div class="text-danger"><?php echo $error_shopid; ?></div>
					  <?php } ?>
					</div>
				</div>
				
				<div class="form-group">
					<label class="col-sm-2 control-label" for="input-shopid"><?php echo $ecom_entry_check;?></label>
					<div class="col-sm-10">
					   <span class="btn btn-default" role="button" id="btn_check" data-url="<?php echo $check_url;?>"><?php echo $ecom_btn_check;?></span>
						<div class="text-info" id="msg_check"></div>
					</div>
				</div>

				<br>
				<br>
				<h3 class="panel-title"><?php echo $ecom_shop; ?></h3>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="input-inn"><?php echo $entry_inn; ?>*</label>
					<div class="col-sm-10">
					  <input type="text" name="ecomkassa_inn" required="required" value="<?php echo $inn; ?>" placeholder="<?php echo $entry_inn; ?>" id="input-inn" class="form-control" />
					  <?php if ($error_inn) { ?>
					  <div class="text-danger"><?php echo $error_inn; ?></div>
					  <?php } ?>
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label" for="input-sno"><?php echo $ecom_tax; ?></label>
					<div class="col-sm-10">
			 
					  <select  name="ecomkassa_sno" class="form-control"  id="input-sno"/>
							<option value=""  <?php if($sno == ''){echo 'selected="selected"';}?>><?php echo $ecom_tax_none; ?></option>
							<option value="osn" <?php if($sno == 'osn'){echo 'selected="selected"';}?>><?php echo $ecom_tax_osn; ?></option>
							<option value="usn_income" <?php if($sno == 'usn_income'){echo 'selected="selected"';}?>><?php echo $ecom_tax_usn_income; ?></option>
							<option value="usn_income_outcome" <?php if($sno == 'usn_income_outcome'){echo 'selected="selected"';}?>><?php echo $ecom_tax_usn_income_outcome; ?></option>
							<option value="envd" <?php if($sno == 'envd'){echo 'selected="selected"';}?>><?php echo $ecom_tax_envd; ?></option>
							<option value="esn" <?php if($sno == 'esn'){echo 'selected="selected"';}?>><?php echo $ecom_tax_esn; ?></option>
							<option value="patent" <?php if($sno == 'patent'){echo 'selected="selected"';}?>><?php echo $ecom_tax_patent; ?></option>
							 
					  </select>
				 
					</div>
				  </div>	
				 <div class="form-group">
					<label class="col-sm-2 control-label" for="input-vat"><?php echo $ecom_vat;?></label>
					<div class="col-sm-10">
				 
					  <select  name="ecomkassa_vat" class="form-control" id="input-vat"  />
							<option value="none"  <?php if($vat == 'none'){echo 'selected="selected"';}?>><?php echo $ecom_vat_none;?></option>
							<option value="vat0" <?php if($vat == 'vat0'){echo 'selected="selected"';}?>><?php echo $ecom_vat_vat0;?></option>
							<option value="vat10" <?php if($vat == 'vat10'){echo 'selected="selected"';}?>><?php echo $ecom_vat_vat10;?></option>
							<option value="vat18" <?php if($vat == 'vat18'){echo 'selected="selected"';}?>><?php echo $ecom_vat_vat18;?></option>
							<option value="vat20" <?php if($vat == 'vat20'){echo 'selected="selected"';}?>><?php echo $ecom_vat_vat20;?></option>
							<option value="vat110" <?php if($vat == 'vat110'){echo 'selected="selected"';}?>><?php echo $ecom_vat_vat110;?></option>
							<option value="vat118" <?php if($vat == 'vat118'){echo 'selected="selected"';}?>><?php echo $ecom_vat_vat118;?></option>
				 
							 
					  </select>
				 
					</div>
				  </div> 
 
				<br>
				<br>
				<h3 class="panel-title"><?php echo $ecom_payment_systems;?></h3>
				
		 
					<?php
				 
		 
						foreach($payment_systems as $payment_system){
						 
							
							?>
							
								<div class="form-group">
									<label class="col-sm-2 control-label" for="input-name"><?php echo $payment_system['name']; ?></label>
									<div class="col-sm-10">
									  
									  <select  name="ecomkassa_payment[<?php echo $payment_system['code']; ?>]" class="form-control" />
											<option value="-1"><?php echo $ecom_dont_use; ?></option>
											<?php
												foreach($order_statuses as $order_status){
													echo '<option ';
													if(isset($payment[$payment_system['code']])){
													 if($payment[$payment_system['code']] == $order_status['order_status_id']){
														echo 'selected="selected"';
													 }
													}
													echo 'value="'.$order_status['order_status_id'].'">'.$order_status['name'].'</option>';
												}
											
											?>
									  </select>
								 
									</div>
								  </div>
							
							
							
							
							
							<?php
						
						}
					
					
					?>
		 
				<br>
				<br>
					<h3 class="panel-title"><?php echo $ecom_status_heading; ?></h3>
				  
				  <div class="form-group">
					<label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
					<div class="col-sm-10">
					  <select name="ecomkassa_status" id="input-status" class="form-control">
						<?php if ($status) { ?>
						<option value="1" selected="selected"><?php echo $text_enabled; ?></option>
						<option value="0"><?php echo $text_disabled; ?></option>
						<?php } else { ?>
						<option value="1"><?php echo $text_enabled; ?></option>
						<option value="0" selected="selected"><?php echo $text_disabled; ?></option>
						<?php } ?>
					  </select>
					</div>
				  </div>
				
			
			</div><!-- tab-pane -->
			
			<div class="tab-pane" id="tab_about">
				<div class="col-sm-offset-2 col-sm-8">
					<h3>Онлайн-касса E-COM kassa Opencart</h3>
					
					
					<div class="row" style="margin-top:50px;">
						<div class="col-sm-3">
						<a href="www.ecom.ru"><img src="view/image/ecomkassa/ecom.png" class="img-responsive" style="margin:25px;margin-top:0px;max-width:150px;"></a>
						</div>
						<div class="col-sm-9">
						<h4>Разработан по заказу компании E-COM</h4>
						<p>Онлайн-касса для интернет магазинов</p>
						<p><a href="https://www.ecomkassa.ru">www.ecomkassa.ru</a></p>
						<p><a href="mailto:www.ecom.ru">sales@ecomkassa.ru</a></p>
						</div>
					</div>
					
					<div class="row"  style="margin-top:50px;">
						<div class="col-sm-3">
						<a href="mad-studio.ru"><img src="view/image/ecomkassa/mad.png"  class="img-responsive"  style="margin:20px;max-width:150px;"></a>
						</div>
						<div class="col-sm-9">
						<h4>Разработан при поддержке компании MAD-Studio</h4>
						<p>Разработка и поддержка Opencart</p>
						<p><a href="https://mad-studio.ru">mad-studio.ru</a></p>
						<p><a href="mailto:mail@mad-studio.ru">mail@mad-studio.ru</a></p>
						</div>
					</div>
					
					
					
				</div>
			</div><!-- tab-pane -->
			
	 
		</div><!-- tab-content -->
		  
		  
        </form>
      </div>
    </div>
  </div>
</div>
<script>
	$('#btn_check').click(function(){
		
		var url = $(this).data('url');

		$.post(url, { login: $('input[name=ecomkassa_login]').val(), pass: $('input[name=ecomkassa_password]').val(), url: $('input[name=ecomkassa_url]').val() },
		
		
		    function( data ) {	
				$('#msg_check').removeClass('alert-danger');
				$('#msg_check').removeClass('alert-success');
				
			
				if(data.code>=2){
					$('#msg_check').addClass('alert-danger');
					$('#msg_check').text('Ошибка '+ data.code + ' ' + data.text );
				}else{
					$('#msg_check').addClass('alert-success');
					$('#msg_check').text('Проверка успешна ' );
				}
			
			
			
			}, 'json');
	
	
	});


</script>
<?php echo $footer; ?>