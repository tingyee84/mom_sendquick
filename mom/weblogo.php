<?php
	$page_mode = '45';
	$page_title = 'Web Interface Logo';
	include('header.php');
	include('checkAccess.php');
	$x = GetLanguage("sysconfig",$lang);
?>
		<div class="page-header">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<li class="breadcrumb-item"><?php echo $xml->system_config;?></li>
					<li class="breadcrumb-item active" aria-current="page"><?php echo $xml->web_logo;?></li>
				</ol>
			</nav>
		</div>
	
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="row">
							<div class="col-lg-3">
								<label class="control-label"><?php echo $x->latest_image;?></label>
							</div>
							<div class="col-lg-5">
								<form id="delImg" name="delImg">
								<p id="img_prev"></p>
								<p id="img_detail"></p>
								<hr><input type="hidden" name="mode" value="delete">
								<p><input class="btn btn-warning" type="submit" id="delBtn" value="<?php echo $x->delete_image;?>"></p>
								</form>
							</div>
						</div>
						<hr>
						<div class="row">
							<div class="col-lg-3">
								<label class="control-label"><?php echo $x->upload_image;?></label>
							</div>
							<div class="col-lg-5">
								<form id="uploadImg" name="uploadImg" enctype="multipart/form-data">
								<p><label class="btn btn-default" for="imgfile">
									<input id="imgfile" name="imgfile" type="file" class="file" required>
								</label></p>
								<hr><input type="hidden" name="mode" value="upload">
								<p><input class="btn btn-primary" type="submit" value="<?php echo $xml_common->upload;?>"></p>
								</form>
							</div>
							<div class="col-lg-4 help-block">
								<?php echo $x->image_allow_1; ?><br><?php echo $x->image_allow_2; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php include('footnote.php'); ?>
	</div>
	<script nonce="<?php echo session_id();?>">
	$.post('weblogo_lib.php',{mode:'view'},function(val) {
		if (val.image) {
			$('#img_prev').html(val.image);
			$('#img_detail').html(val.detail);
			$('#delBtn').prop('disabled', false);
		} else {
			$('#img_prev').html('<i>No image found</i>');
			$('#delBtn').prop('disabled', true);
		}
	},'json');
	$('#delImg').on('submit', function(e) {
		if(confirm('<?php echo $x->alert_8;?>')) {
			$.post('weblogo_lib.php',$('#delImg').serialize(),function(res) {
				if(res){ alert(res); }
				location.reload(true);
			});
		}
		e.preventDefault();
	});	
	$('form#uploadImg').submit(function(){
		var formData = new FormData($(this)[0]);
		$.ajax({
			url: 'weblogo_lib.php',
			type: 'POST',
			data: formData,
			contentType: false,
			processData: false,
			success: function(res){
				if(res){ alert(res); }
				location.reload(true);
			}
		});
		return false;
	});
	</script>
</body>
</html>
