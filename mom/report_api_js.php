<?php
header("Content-type:text/javascript");
include_once('./lib/commonFunc.php');
$page_mode = '800'; // Ty's Comment: can't really understand what does it for  
$chk_mode = 68;  // when mom
if (isset($_GET["view"])) {
	if ($_GET["view"] == "dept") { // when BU view
		$chk_mode = 71; 
	}
}
include_once('checkAccess.php');
?>
$('#from, #to, #from_mim, #to_mim').datepicker({
	format: 'dd/mm/yyyy',
	todayHighlight:'TRUE',
    autoclose:true}
);
$('#from, #from_mim').val(moment(<?php echo isset($_GET["datefrom"]) ? "'".$_GET["datefrom"]."','DD/MM/YYYY'" : ""; ?>).format('DD/MM/YYYY'));
$('#to, #to_mim').val(moment(<?php echo isset($_GET["dateto"]) ? "'".$_GET["dateto"]."','DD/MM/YYYY'" : ""; ?>).format('DD/MM/YYYY'));
var date = moment().format('YYYYMMDD-hhmmss');
<?php if ($chk_mode == 68) { ?>
var table = $('#tbl_dept_list').DataTable({
	deferRender: false,
	stateSave: false,
	responsive: true,
	ajax: {
		url: "report_api_lib.php",
		type: "POST",
		data: function() {
			return 'mode=listapi&datefrom='+encodeURIComponent($("#from").val())+'&dateto='+encodeURIComponent($("#to").val());
		}
	},
    lengthMenu: [[50,100,200],[50,100,200]],
    columns : [
        {data:null,
        render:function(data,type,row) {
            return "<a href='report_api.php?view=dept&dept="+data[5]+"&datefrom="+encodeURIComponent($("#from").val())+"&dateto="+encodeURIComponent($("#to").val())+"'>"+data[0]+"</a>";
        }},
        {data:1},
        {data:2},
        {data:3},
        {data:4},
    ],
	processing: true
});

$('#from, #to').on("changeDate",function(ev){
    table.ajax.reload();
});
<?php } else if ($chk_mode == 71) {  // FIXME need to add BU ?>

function produceSummary() {
	$.ajax({
		cache: false,
		url: 'report_api_lib.php?',
		data:'mode=summarymsg<?php echo isset($_GET["dept"])? "&dept=".$_GET["dept"]:""; ?>&datefrom='+encodeURIComponent($("#from").val())+'&dateto='+encodeURIComponent($("#to").val()),
		type:'POST',
		success:function(data) {
			$("#tbl_summary tbody").html(data);
		}
	});
}
function produceSummary_mim() {
	$.ajax({
		cache: false,
		url: 'report_api_lib.php?',
		data:'mode=summarymsg_mim<?php echo isset($_GET["dept"])? "&dept=".$_GET["dept"]:""; ?>&datefrom='+encodeURIComponent($("#from_mim").val())+'&dateto='+encodeURIComponent($("#to_mim").val()),
		type:'POST',
		success:function(data) {
			$("#tbl_summary_mim tbody").html(data);
		}
	});
}
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
var table = $('#tbl_msg_list').DataTable({
	autoWidth: false,
	deferRender: false,
	stateSave: false,
	responsive: true,
	ajax: {
		url: "report_api_lib.php",
		type: "POST",
		data: function() {
			return 'mode=listmsg<?php echo isset($_GET["dept"])? "&dept=".$_GET["dept"]:""; ?>&datefrom='+encodeURIComponent($("#from").val())+'&dateto='+encodeURIComponent($("#to").val());
		}
    },
    columns: [
        {data:0,width:'100px',render:function(data,type,row) {
			return moment(data).format('YYYY-MM-DD HH:mm'); 
		}},
        {data:1,width:'130px'},
        {data:2,width:'125px'},
        {data:3},
        {data:4,width:'75px'},
        {data:5,width:'75px'},
        {data:null,render:function(data,type,row) {
            if (data[6] == null || data[6] == 0) {
                return "None";
            }
            return "Yes";
        },width:'75px'}
    ],
    lengthMenu: [[50,100,200],[50,100,200]],

    processing: true,
    initComplete: function(settings,json) {
        produceSummary();
    }
});
function returnFilename() {
	return 'api_report_'+moment($("#from").val(),"DD/MM/YYYY").format("YYYYMMDD")+"-"+moment($("#to").val(),"DD/MM/YYYY").format("YYYYMMDD");
}
function returnFilename_mim() {
	return 'api_report_mim_'+moment($("#from_mim").val(),"DD/MM/YYYY").format("YYYYMMDD")+"-"+moment($("#to_mim").val(),"DD/MM/YYYY").format("YYYYMMDD");
}
new $.fn.dataTable.Buttons( table, {
	buttons: [
		{
			extend:'csv',
			text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export.' CSV'; ?>',
			exportOptions: {orthogonal: "exportcsv" },
			filename: function() {return returnFilename();},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");
			}},
		{
			extend:'excel',
			text: '<i class="fa fa-file-excel-o"></i> <?php echo $xml_common->export.' Excel'; ?>',
			exportOptions: {orthogonal: "exportxls" },
			filename: function() {return returnFilename();},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-success btn-sm");
			},
			charset: 'utf-8'},
		{
			extend:'pdf', text: '<i class="fa fa-file-pdf-o"></i> <?php echo $xml_common->export.' PDF'; ?>',
			exportOptions: {orthogonal: "exportpdf" },
			customize: function(doc) {
			},
			filename: function () {return returnFilename();},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-danger btn-sm");
			},
			charset: 'utf-8',
			title: function () {
				return "API Report ("+$('#from').val() + " - " + $('#to').val()+")";
			}
		}
	]
	}
);
table.buttons().container().appendTo('#export');

var table_mim = $('#tbl_msg_list_mim').DataTable({
	autoWidth: false,
	deferRender: false,
	stateSave: false,
	responsive: true,
	ajax: {
		url: "report_api_lib.php",
		type: "POST",
		data: function() {
			return 'mode=listmsg_mim<?php echo isset($_GET["dept"])? "&dept=".$_GET["dept"]:""; ?>&datefrom='+encodeURIComponent($("#from_mim").val())+'&dateto='+encodeURIComponent($("#to_mim").val());
		}
    },
    columns: [
        {data:0,width:'100px',render:function(data,type,row) {
			return moment(data).format('YYYY-MM-DD HH:mm'); 
		}},
        {data:1,width:'130px'},
        {data:2,width:'125px'},
        {data:3},
        {data:4,width:'75px'},
        {data:5,width:'75px'},
        {data:null,render:function(data,type,row) {
            if (data[6] == null || data[6] == 0) {
                return "None";
            }
            return "Yes";
        },width:'75px'}
    ],
    lengthMenu: [[50,100,200],[50,100,200]],

    processing: true,
    initComplete: function(settings,json) {
        produceSummary_mim();
    }
});

new $.fn.dataTable.Buttons( table_mim, {
	buttons: [
		{
			extend:'csv',
			text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export.' CSV'; ?>',
			exportOptions: {orthogonal: "exportcsv" },
			filename: function() {return returnFilename_mim();},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");
			}},
		{
			extend:'excel',
			text: '<i class="fa fa-file-excel-o"></i> <?php echo $xml_common->export.' Excel'; ?>',
			exportOptions: {orthogonal: "exportxls" },
			filename: function() {return returnFilename_mim();},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-success btn-sm");
			},
			charset: 'utf-8'},
		{
			extend:'pdf', text: '<i class="fa fa-file-pdf-o"></i> <?php echo $xml_common->export.' PDF'; ?>',
			exportOptions: {orthogonal: "exportpdf" },
			customize: function(doc) {
			},
			filename: function () {return returnFilename_mim();},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-danger btn-sm");
			},
			charset: 'utf-8',
			title: function () {
				return "API Report ("+$('#from_mim').val() + " - " + $('#to_mim').val()+")";
			}
		}
	]});
table_mim.buttons().container().appendTo('#export_mim');

$('#from, #to').on("changeDate",function(ev){
    produceSummary();
    table.ajax.reload();
});
$('#from_mim, #to_mim').on("changeDate",function(ev){
    produceSummary_mim();
    table_mim.ajax.reload();
});
<?php } ?>