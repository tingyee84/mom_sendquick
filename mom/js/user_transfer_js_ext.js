$('#transfer_btn').click(function () {

	show_confirm( 'Confirm transfer ?' );
	
});

function show_confirm(message) {

	show_confirm_message({
		
		message: message,
	
		executeYes: function() {
			
			$('#transferForm').submit();
			
		},
		executeNo: function() {
			//nothing to do
			alert('3');
		}
	
	});
	
}