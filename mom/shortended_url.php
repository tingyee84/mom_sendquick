<?php
	$page_mode = '7';
	$page_title = 'Shortened URL';
	include('header.php');
	include('checkAccess.php');

	if( $_POST ){
		
		/*
		$max_len = rand(7,10);
		$full_url = $_POST['full_url'];
		$main_url = "http://mmstv.tv/";
		
		//echo "max_len: " . $max_len;
		
		if( $full_url != "" ){
			
			//check have exiting
			$sql0 = "select short_url from shortened_url where url = '".pg_escape_string($full_url)."'";
			$row0 = getSQLresult($dbconn, $sql0);
			
			if( $row0[0]['short_url'] != '' ){
				
				$short_url = $row0[0]['short_url'];
				
			}else{
				
				$permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				// Output: video-g6swmAP8X5VG4jCi.mp4
				//echo 'video-'.substr(str_shuffle($permitted_chars), 0, 16).'.mp4';
				//echo substr(str_shuffle($permitted_chars), 0, 6);
				$short_url = $main_url . substr(str_shuffle($permitted_chars), 0, $max_len);
				
				//echo $short_url;
				//die;
				
				$sql1 = "insert into shortened_url ( url, short_url ) values ( '".pg_escape_string($full_url)."', '".pg_escape_string($short_url)."' ) ";
				
				//echo $sql1;
				//die;
				
				$row1 = doSQLcmd($dbconn, $sql1);
				
			}
		
		}
		*/
		
		$full_url = $_POST['full_url'];
		$full_url = htmlspecialchars($full_url,ENT_QUOTES);
		if (filter_var($full_url, FILTER_VALIDATE_URL)) {
			
			$short_url = file_get_contents('http://127.0.0.1:5101/short_url.php?url='.$full_url);
	
			insertAuditTrail("Generate Shortened URL");
		
		}else{
			
			$error_msg = "Invalid url";
		}


	}
	
	//die;
?>
		<link href="css/assmi.css" rel="stylesheet">
		<div class="page-header padding-top-10">
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb">
					<!-- <li class="breadcrumb-item active" aria-current="page"><?php //echo $xml->shortended_url?></li> -->
					<li class="breadcrumb-item active" aria-current="page"><?php echo "Shortened URL"?></li>
				</ol>
			</nav>
		</div>

		<?php 
		$x = GetLanguage("shortended_url",$lang); 
		?>
		<div class="page-content">
			<div class="col-lg-12">
				<div class="panel panel-default">
					<div class="panel-body">
						<div id="status" class="alert alert-dismissable alert-sm text-center hidden">
							<button class="btn-close">&times;</button>
							<span id="output"></span>
						</div>
						
						<form id="shortForm" name="shortForm" method = "post" action = "shortended_url.php">
							
							<?php
							if( $error_msg != ""){
							?>
							<div class="row">
								<div class="col-lg-4">
									<font class="color-red text-align-left"><?php echo $error_msg;?></font>
								</div>
							</div>
							<hr/>
							<?php
							}
							?>
							
							<div class="row">
								
								<div class="col-lg-4">
									<input type="text" class="form-control input-sm" name="full_url" id="full_url" placeholder = "<?php echo $x->full_url;?>" value = "<?php echo $full_url;?>">
									<div id="invalid_full_url" class="invalid-feedback">
										Please provide a valid URL.
									</div>
								</div>
								
								<div class="col-lg-4">
									<button type="submit" class="btn btn-primary" id="gen_link_btn"><?php echo $x->generate;?></button>
								</div>
								
							</div>
							<hr/>
							
							<div class="row">
								
								<div class="col-lg-4">
									<!-- <input type="text" class="form-control input-sm" name="short_url" id="short_url" placeholder = "<?php //echo $x->short_link;?>" value = "<?php echo $short_url;?>" 
									readonly> -->
									<input type="text" class="form-control input-sm" name="short_url" id="short_url" placeholder = "<?php echo "Shortened URL"?>" value = "<?php echo $short_url;?>" 
									readonly>
								</div>
								
								<div class="col-lg-4">
									<button type="button" class="btn btn-info" id = "copy_link_btn"><?php echo $x->copy_link;?></button>
								</div>
								
							</div>
							<hr/>
							
							<div class="row">
								
								<div class="col-lg-4">
									<button type="button" class="btn btn-light" id = "reset_btn"><?php echo $x->reset_btn;?></button>
								</div>
								
								<div class="col-lg-4">
									
								</div>
								
							</div>
							<hr/>
							
						</form>
					</div>
				</div>
			</div>
		</div>
		<script language="javascript" src="js/txvalidator.js"></script>
		<script src="js/shortended_url_js.js"></script>
		<!-- <script nonce="<?php //echo session_id();?>">
		$( '#reset_btn' ).on("click", function() {
			$( '#full_url, #short_url' ).val('');
		});

		$( '#gen_link_btn' ).on("click", function() {
			if(!txvalidator($("#full_url").val(),"TX_URL")){
				$('#full_url').addClass("is-invalid");
				return false;				
			}
		});
		$('#full_url').on('change keyup', function(e){
			$('#full_url').removeClass("is-invalid");
		});
		
		$( '#copy_link_btn' ).on("click", function() {
			clickCopy();
		});

		function clickCopy() {
			
			/* Get the text field */
			var copyText = document.getElementById("short_url");
			
			if( document.getElementById("short_url").value != '' ){

				/* Select the text field */
				copyText.select();
				copyText.setSelectionRange(0, 99999); /*For mobile devices*/

				/* Copy the text inside the text field */
				document.execCommand("copy");

				/* Alert the copied text */
				alert("Copied the text: " + copyText.value);

			}else{
				
				alert("Nothing to copy.");
				
			}

		} 
		</script> -->
		<?php include('footnote.php'); ?>
	</div>
	<?php //include('send_sms_js.php'); ?>
</body>
</html>
