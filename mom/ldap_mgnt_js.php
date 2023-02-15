var table = $('#ldaptable').DataTable({
	deferRender: true,
	autoWidth: false,
	ajax: {type:'POST',url:'ldap_lib.php',data:{mode:'listLDAPServer'}},
	columnDefs: [{ "orderable": false, "targets": [9,10] }]
});
$('#addldap').click(function(){
	$(location).attr('href','ldap_add.php');
});
$('#delete').on('click', function(e)
{
	if(confirm(strdelete)) {
		$('input[type=checkbox]').each(function() {
			if (this.checked) {
				$.post('ldap_lib.php',{mode:'deleteLDAPServer',ldapid: this.value},function(data){
					table.ajax.reload();
				});
			}
		});
		$('#all').prop('checked',false);
	}
});
$('#all').change(function () {
	var cells = table.cells().nodes();
    $(cells).find(':checkbox').prop('checked', $(this).is(':checked'));
});
