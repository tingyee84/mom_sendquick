<?php header("Content-type:text/javascript");
include_once("./lib/commonFunc.php");
$page_mode = '800'; // Ty's Comment: can't really understand what does it for  
$chk_mode = 67;
include('checkAccess.php');
?>
$('#from, #to').val(moment().format('DD/MM/YYYY'));
$('#from, #to').datepicker({
	format: 'dd/mm/yyyy',
	todayHighlight:'TRUE',
    autoclose:true}
);
function returnFilename(title='survey_report') {
	return title+"_"+moment($("#from").val(),"DD/MM/YYYY").format("YYYYMMDD")+"-"+moment($("#to").val(),"DD/MM/YYYY").format("YYYYMMDD");
}

<?php if ($chk_mode == 67) {
    if (isset($_GET["pageview"]) && $_GET["pageview"] == "campaign") { ?>
function produceSummary() {
	$.ajax({
		cache: false,
		url: 'survey_report_lib.php?',
		data:'mode=summaryresponses&id=<?php echo $_GET["id"]; ?>&datefrom='+encodeURIComponent($("#from").val())+'&dateto='+encodeURIComponent($("#to").val()),
		type:'POST',
		success:function(data) {
			// $("#tbl_summary tbody").html(data);
            $json = JSON.parse(data);
            $("#campaignname").text($json[0]);
            $("#campaignout").text($json[1]);
            $("#campaignin").text($json[2]);
            $("#breadcrumb_curr").text($json[0]);
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
var table = $('#tbl_response_list').DataTable({
    deferRender: false,
    stateSave: false,
    responsive: true,
    ajax: {
        url: "survey_report_lib.php",
        type: "POST",
        data: function() {
            return 'mode=listresponses&id=<?php echo $_GET["id"]; ?>&datefrom='+encodeURIComponent($("#from").val())+'&dateto='+encodeURIComponent($("#to").val());
        }
    },
    lengthMenu: [[50,100,200],[50,100,200]],
    columns : [
        {data:0,width:"100px"},
        {data:1,width:"100px"},
        {data:2,className:"text-left",charset:"utf-8"}
    ],
    processing: true,
    initComplete: function(settings,json) {
        produceSummary();
    }
});

new $.fn.dataTable.Buttons( table, {
	buttons: [
		{extend:'csv', text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export; ?> CSV', exportOptions: {orthogonal: "exportcsv" }, init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-secondary btn-sm");
		},filename:function() {return returnFilename();}},
		{extend:'excel', text: '<i class="fa fa-file-excel-o"></i> <?php echo $xml_common->export; ?> Excel', exportOptions: {orthogonal: "exportxls" }, init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-success btn-sm");
		},filename:function() {return returnFilename();},charset: 'utf-8'},
		{extend:'pdf', text: '<i class="fa fa-file-pdf-o"></i> <?php echo $xml_common->export; ?> PDF', exportOptions: {orthogonal: "exportpdf" }, 
		customize: function(doc) {
			doc.defaultStyle.font = 'Simsum';
		}, init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-danger btn-sm");
		},filename:function() {return returnFilename($("#breadcrumb_curr").text());}, charset: 'utf-8',title:function() {return $("#breadcrumb_curr").text() + " ("+$('#from').val() + " - " + $('#to').val()+")";}}
]});
table.buttons().container().appendTo('#export');
$('#from, #to').on("changeDate",function(ev){
    table.ajax.reload();
    produceSummary();
});

<?php } else { ?>
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

var table = $('#tbl_campaign_list').DataTable({
    deferRender: false,
    stateSave: false,
    responsive: true,
    ajax: {
        url: "survey_report_lib.php",
        type: "POST",
        data: function() {
            return 'mode=listcampaigns&datefrom='+encodeURIComponent($("#from").val())+'&dateto='+encodeURIComponent($("#to").val());
        }

    },
    lengthMenu: [[50,100,200],[50,100,200]],
    columns : [
        {data:null,
        render:function(data,type,row) {
            return "<a href='survey_report.php?pageview=campaign&id="+data[0]+"'>"+data[1]+"</a>";
        }},
        {data:3},
        {data:4},
        {data:2}
    ],
    processing: true

});
new $.fn.dataTable.Buttons( table, {
	buttons: [
		{extend:'csv', text: '<i class="fa fa-file-text-o"></i> <?php echo $xml_common->export; ?> As CSV', exportOptions: {orthogonal: "exportcsv" }, init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-secondary btn-sm");
		},filename:function() {return returnFilename();}},
		{extend:'excel', text: '<i class="fa fa-file-excel-o"></i> <?php echo $xml_common->export; ?> Excel', exportOptions: {orthogonal: "exportxls" }, init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-success btn-sm");
		},filename:function() {return returnFilename();},charset: 'utf-8'},
		{extend:'pdf', text: '<i class="fa fa-file-pdf-o"></i> <?php echo $xml_common->export; ?> PDF', exportOptions: {orthogonal: "exportpdf" }, 
		customize: function(doc) {
			doc.defaultStyle.font = 'Simsum';
		},  init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-danger btn-sm");
		},filename:function() {return returnFilename();}, charset: 'utf-8',title:function(){return "<?php echo $x->title; ?> ("+$('#from').val() + " - " + $('#to').val()+")";}}
]});
table.buttons().container().appendTo('#export');
$('#from, #to').on("changeDate",function(ev){
    table.ajax.reload();
});
<?php } // end list of campaign
}
?>