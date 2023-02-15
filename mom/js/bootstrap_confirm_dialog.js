$(document).ready(function() {
	$('#confirmModal').modal({show: false, backdrop: false});
});
function show_confirm(message) {
  show_confirm_message({
	  message: message,
	  executeYes: function() {
		$('#text_output').html('You click Yes button');
	  },
	  executeNo: function() {
	    $('#text_output').html('You click Yes button');
	  }
  });
}
function show_confirm_message(option) {
  var msg = option.message;
  if ( msg == undefined || msg == null || msg == '' )
    msg = 'Are you sure?';
  if ( msg != '' && msg != undefined && msg != null ) {
    $('#confirmContent').css('text-align', 'left');
    $('#confirmContent').html('<p>'+msg+'</p>');
    var btn_yes = $('#confirmModal').children().find('.btn_yes_confirm');
    var btn_no =  $('#confirmModal').children().find('.btn_no_confirm');
    btn_yes.click(function() {
      $('#confirmModal').modal('hide');
      if ( typeof option.executeYes === "function" )
        option.executeYes();
    });
    btn_no.click(function() {
      $('#confirmModal').modal('hide');
      if ( typeof option.executeNo === "function" )
        option.executeNo();
    });
    $('#confirmModal').modal('show');
  }
}