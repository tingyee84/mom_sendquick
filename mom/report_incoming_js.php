<script src="js/bootstrap-datepicker.min.js" async></script>
<script src="js/pdfmake.min.js"></script>
<script src="js/vfs_fonts.js.php" defer></script>
<script src="js/dataTables.buttons.min.js?"></script>
<script src="js/jszip.min.js"></script>

<script src="js/buttons.html5.min.js"></script>
<script src="js/moment.min.js" type="text/javascript"></script>
<script nonce="<?php echo session_id();?>">
$('#datefrom, #dateto').val(moment().format('DD/MM/YYYY'));
$('#datefrom, #dateto').datepicker({
	format: 'dd/mm/yyyy',
	todayHighlight:'TRUE',
	autoclose:true});

pdfMake.fonts = {
	OpenSans : {
		normal: 'OpenSans-Regular.ttf',
		bold: 'OpenSans-Bold.ttf',
		italics: 'OpenSans-Italic.ttf',
		bolditalics: 'OpenSans-BoldItalic.ttf'
    },
    
	Simsum : {
		normal: 'SIMSUN-regular.ttf',
		bold: 'SIMSUN-regular.ttf',
		italics: 'SIMSUN-regular.ttf',
		bolditalics: 'SIMSUN-regular.ttf'
	}
}
function returnFilename() {
	return 'incoming_report_'+moment($("#datefrom").val(),"DD/MM/YYYY").format("YYYYMMDD")+"-"+moment($("#dateto").val(),"DD/MM/YYYY").format("YYYYMMDD");
}
var table = $('#tbl_msg_list').DataTable({
	deferRender: false,
	stateSave: false,
	ajax: {
		url: "report_incoming_lib.php",
		type: "POST",
		data: function (){
			return "mode=listmessage&datefrom="+encodeURIComponent($("#datefrom").val())+"&dateto="+encodeURIComponent($("#dateto").val())
		}
	},
	lengthMenu: [[100,500,1000], [100,500,1000]],
	pageLength: 100,
	processing: true,
	columns: [
		{data:'datetime',width:"100px"},
		{data:'mobile_numb',width:"100px",render:function(data,type,row){
			if (type==='display')
				return data+"<a href='send_sms.php?mobile_numb="+encodeURIComponent(data)+"'><i class='fa fa-comment' title='Reply'></i></a>";
			else
				return data;
		}},
		{data:'message',render:function(data,type,row) {
			if (type === 'display')
				return data.length > 200 ? data.substr(0,200)+"&#8230" : data;
			if (type === 'exportxls' || type === 'exportpdf' || type === 'exportcsv')
				return data;
			return data;
		},className:"text-left",charset:"utf-8"
		},
		{data:'dept',width:"100px"},
		{data:'keyword',width:"75px"}
	]
});
new $.fn.dataTable.Buttons( table, {
	buttons: [
		{extend:'csv', text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export; ?> CSV', exportOptions: {orthogonal: "exportcsv" }, filename:function() {return returnFilename();},init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-secondary btn-sm");
		}},
		{extend:'excel', text: '<i class="fa fa-file-excel-o"></i> <?php echo $xml_common->export; ?> Excel', exportOptions: {orthogonal: "exportxls" }, filename:function() {return returnFilename();},charset: 'utf-8',init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-success btn-sm");
		}},
		{extend:'pdf', text: '<i class="fa fa-file-pdf-o"></i> <?php echo $xml_common->export; ?> PDF', exportOptions: {orthogonal: "exportpdf"},init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-danger btn-sm");
		},
		customize: function(doc) {
			doc.defaultStyle.font = 'Simsum';
		}, filename:function() {return returnFilename();}, charset: 'utf-8',title:"Incoming Report ("+$('#datefrom').val() + " - " + $('#dateto').val()+")"}
]});
table.buttons().container().appendTo('#export');

function produceSummary() {
	$.ajax({
		cache: false,
		url: 'report_incoming_lib.php?',
		data:'mode=summary&datefrom='+encodeURIComponent($("#datefrom").val())+'&dateto='+encodeURIComponent($("#dateto").val()),
		type:'POST',
		success:function(data) {
			$("#tbl_summary tbody").html(data);
		}
	});
}

$("#datefrom, #dateto").on("changeDate",function(){
	produceSummary();
	table.ajax.reload();
});
produceSummary();
</script>