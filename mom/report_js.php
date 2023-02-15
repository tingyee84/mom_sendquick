<script src="js/bootstrap-datepicker.min.js"></script>
<script src="js/pdfmake.min.js"></script>
<script src="js/vfs_fonts.js.php" defer></script><?php // defer means to let all pages rendered finished then begin load ?>
<script src="js/dataTables.buttons.min.js?"></script>

<script src="js/buttons.html5.min.js"></script>
<script src="js/moment.min.js" type="text/javascript"></script>
<script nonce="<?php echo session_id();?>">
$('#datefrom').val(moment('<?php echo $_SESSION["report_datefrom"]; ?>','DD/MM/YYYY').format('DD/MM/YYYY'));
$('#dateto').val(moment('<?php echo $_SESSION["report_dateto"]; ?>','DD/MM/YYYY').format('DD/MM/YYYY'));
$('#datefrom, #dateto').datepicker({
	format: 'dd/mm/yyyy',
	todayHighlight:'TRUE',
	autoclose:true});
<?php
	// mom, need check permission
	if (isset($_GET["view"]) && $_GET["view"] == "alldepts") {
echo <<< END
pdfMake.fonts = {
	OpenSans : {
		normal: 'OpenSans-Regular.ttf',
		bold: 'OpenSans-Bold.ttf',
		italics: 'OpenSans-Italic.ttf',
		bolditalics: 'OpenSans-BoldItalic.ttf'
	}
}
var table = $('#tbl_dept_summary').DataTable({
	deferRender: false,
	stateSave: false,
	processing: true,
	ajax: {
		url: "report_lib.php",
		type: "GET",
		data: function() {
			return 'mode=dept_summary&datefrom='+encodeURIComponent($("#datefrom").val())+'&dateto='+encodeURIComponent($("#dateto").val());
		}
	},
	lengthMenu: [[50, 100, 200], [50, 100, 200]],
	footerCallback: function(row,data,start,end,display) {
		var api = this.api(), data;

		// converting to interger to find total
		var intVal = function ( i ) {
			return typeof i === 'string' ?
				i.replace(/[\$,]/g, '')*1 :
				typeof i === 'number' ?
					i : 0;
		};
		var totaluser = api
		.column( 1 )
		.data()
		.reduce( function (a, b) {
			return intVal(a) + intVal(b);
		}, 0 );

		var totalsent = api
			.column( 2 )
			.data()
			.reduce( function (a, b) {
				return intVal(a) + intVal(b);
			}, 0 );
		var totaldelivered = api
			.column( 4 )
			.data()
			.reduce( function (a, b) {
				return intVal(a) + intVal(b);
			}, 0 );
		var totalundelivered = api
			.column( 5 )
			.data()
			.reduce( function (a, b) {
				return intVal(a) + intVal(b);
			}, 0 );	
		$( api.column( 1 ).footer() ).html("<b>"+totaluser+"</b>");
		$( api.column( 2 ).footer() ).html("<b>"+totalsent+"</b>");
		$( api.column( 4 ).footer() ).html("<b>"+totaldelivered+"</b>");
		$( api.column( 5 ).footer() ).html("<b>"+totalundelivered+"</b>");
	}
	
});
$("#datefrom, #dateto").on("change",function(){
	table.ajax.reload();
});
function returnFilename() {
	return 'dept_report_'+moment($("#datefrom").val(),"DD/MM/YYYY").format("YYYYMMDD")+"-"+moment($("#dateto").val(),"DD/MM/YYYY").format("YYYYMMDD");
}

new $.fn.dataTable.Buttons( table, {
	buttons: [
		{extend:'csv', text: '<i class="fa fa-file-text-o"></i> {$xml_common->export} CSV', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ]}, init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-secondary btn-sm");
		},filename:function() {return returnFilename()}},
		{extend:'excel', text: '<i class="fa fa-file-excel-o"></i> {$xml_common->export} Excel', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ]},init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-success btn-sm");
		}, filename:function() {return returnFilename()}},
		{extend:'pdf', text: '<i class="fa fa-file-pdf-o"></i> {$xml_common->export} PDF', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ]}, init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-danger btn-sm");
		},filename:function() {return returnFilename()},orientation:'landscape',charset: 'utf-8', 
		customize: function(doc) {
			doc.defaultStyle.font = 'OpenSans';
		}}
]});
table.buttons().container().prependTo('#export');


END;
	} else if(isset($_GET["view"]) && $_GET["view"] == "users") {
		// FIXME may have many users in just one department that viewed by BU admin
		$dept = isset($_GET["dept"]) ? $_GET["dept"] : $_SESSION["department"];
		$result = pg_query($dbconn,"SELECT department FROM department_list WHERE department_id = '".dbSafe($dept)."'");
		if ($row = pg_fetch_array($result)) {
			echo "$('#txtdeptname').text('{$row[0]} ');$('#txtdeptname_mim').text('{$row[0]} ');\n\n";
		}
echo <<< END
pdfMake.fonts = {
	OpenSans : {
		normal: 'OpenSans-Regular.ttf',
		bold: 'OpenSans-Bold.ttf',
		italics: 'OpenSans-Italic.ttf',
		bolditalics: 'OpenSans-BoldItalic.ttf'
	}
}
function produceSummary() {
	$.ajax({
		cache: false,
		url: 'report_lib.php?',
		data:'mode=d_summary&dept=$dept&datefrom='+encodeURIComponent($("#datefrom").val())+'&dateto='+encodeURIComponent($("#dateto").val()),
		type:'GET',
		success:function(data) {
			$("#tbl_summary tbody").html(data);
		}
	});
}
function produceSummary_mim() {
	$.ajax({
		cache: false,
		url: 'report_lib.php?',
		data:'mode=d_summary_mim&dept=$dept&datefrom='+encodeURIComponent($("#datefrom").val())+'&dateto='+encodeURIComponent($("#dateto").val()),
		type:'GET',
		success:function(data) {
			$("#tbl_summary_mim tbody").html(data);
		}
	});
}
var table = $('#tbl_users_list').DataTable({
	deferRender: false,
	stateSave: false,
	responsive: true,
	ajax: {
		url: "report_lib.php",
		type: "GET",
		data: function() {
			return 'mode=users_list&dept=$dept&datefrom='+encodeURIComponent($("#datefrom").val())+'&dateto='+encodeURIComponent($("#dateto").val());
		}
	},
	lengthMenu: [[50,100,200],[50,100,200]],
	processing: true,
});
var table_mim = $('#tbl_users_list_mim').DataTable({
	deferRender: false,
	stateSave: false,
	responsive: true,
	ajax: {
		url: "report_lib.php",
		type: "GET",
		data: function() {
			return 'mode=users_list_mim&dept=$dept&datefrom='+encodeURIComponent($("#datefrom").val())+'&dateto='+encodeURIComponent($("#dateto").val());
		}
	},
	lengthMenu: [[50,100,200],[50,100,200]],
	processing: true,
});
function returnFilename() {
	return $('#txtdeptname').text()+'_report_'+moment($("#datefrom").val(),"DD/MM/YYYY").format("YYYYMMDD")+"-"+moment($("#dateto").val(),"DD/MM/YYYY").format("YYYYMMDD");
}
var date = moment().format('YYYYMMDD-hhmmss');
new $.fn.dataTable.Buttons( table, {
	buttons: [
		{extend:'csv', text: '<i class="fa fa-file-text-o"></i> {$xml_common->export} CSV', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ]},init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-secondary btn-sm");
		},  filename:function(){return returnFilename()}},
		{extend:'excel', text: '<i class="fa fa-file-excel-o"></i> {$xml_common->export} Excel', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ]},init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-success btn-sm");
		},  filename:function(){return returnFilename()}},
		{extend:'pdf', text: '<i class="fa fa-file-pdf-o"></i> {$xml_common->export} PDF', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ]},init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-danger btn-sm");
		},  filename:function(){return returnFilename()},orientation:'landscape',
		customize: function(doc) {
			doc.defaultStyle.font = 'OpenSans';
		}}
]});
table.buttons().container().prependTo('#export');
new $.fn.dataTable.Buttons( table_mim, {
	buttons: [
		{extend:'csv', text: '<i class="fa fa-file-text-o"></i> {$xml_common->export} CSV', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ]},init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-secondary btn-sm");
		},  filename:function(){return returnFilename()}},
		{extend:'excel', text: '<i class="fa fa-file-excel-o"></i> {$xml_common->export} Excel', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ]},init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-success btn-sm");
		},  filename:function(){return returnFilename()}},
		{extend:'pdf', text: '<i class="fa fa-file-pdf-o"></i> {$xml_common->export} PDF', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ]},init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-danger btn-sm");
		},  filename:function(){return returnFilename()},orientation:'landscape',
		customize: function(doc) {
			doc.defaultStyle.font = 'OpenSans';
		}}
]});
table_mim.buttons().container().prependTo('#export_mim');
$("#datefrom, #dateto").on("change",function(){
	produceSummary();
	produceSummary_mim();
	table.ajax.reload();
	table_mim.ajax.reload();
});

produceSummary();
produceSummary_mim();
END;
	} else {

		/*$base64 = base64_encode(file_get_contents("./images/icons/icon_text@3x.png"));
		$base64w = base64_encode(file_get_contents("./images/icons/icon_whatsapp@3x.png"));
		$base64l = base64_encode(file_get_contents("./images/icons/icon_line@3x.png"));
		$base64t = base64_encode(file_get_contents("./images/icons/icon_telegram@3x.png"));
		$base64c = base64_encode(file_get_contents("./images/icons/icon_wechat@3x.png"));
		$base64s = base64_encode(file_get_contents("./images/icons/slack_2x.png"));
		$base64f = base64_encode(file_get_contents("./images/icons/icon_messenger@3x.png"));
		$base64v = base64_encode(file_get_contents("./images/icons/icon_viber@3x.png"));
		$base64m = base64_encode(file_get_contents("./images/icons/icon_teams@3x.png"));
		$base64e = base64_encode(file_get_contents("./images/icons/icon_webex_round.png"));*/
		// self or buadmin view the page. DO check bu admin permission here!
		$user = isset($_GET["user"]) ? $_GET["user"] : $_SESSION["userid"]; // FIXME check using getUserType nexttime
		
echo <<< END

function resend_check () {
	$("#tbl_msg_list button.btn-resend").each(function() {
		$(this).on("click",function (ev) {
			$.redirect('send_sms.php',{'msgid':$(this).attr("data")});
		});
	});

	$("#tbl_msg_list_mim button.btn-resend").each(function() {
		$(this).on("click",function (ev) {
			$.redirect('send_sms.php',{'msgid':$(this).attr("data")});
		});
	});
}

jQuery.fn.dataTable.render.ellipsis = function ( cutoff, wordbreak, escapeHtml ) {
    var esc = function ( t ) {
        return t
            .replace( /&/g, '&amp;' )
            .replace( /</g, '&lt;' )
            .replace( />/g, '&gt;' )
            .replace( /"/g, '&quot;' );
    };
 
    return function ( d, type, row ) {
        // Order, search and type get the original data
        if ( type !== 'display' ) {
            return d;
        }
 
        if ( typeof d !== 'number' && typeof d !== 'string' ) {
            return d;
        }
 
        d = d.toString(); // cast numbers
 
        if ( d.length <= cutoff ) {
            return d;
        }
 
        var shortened = d.substr(0, cutoff-1);
 
        // Find the last white space character in the string
        if ( wordbreak ) {
            shortened = shortened.replace(/\s([^\s]*)$/, '');
        }
 
        // Protect against uncontrolled HTML input
        if ( escapeHtml ) {
            shortened = esc( shortened );
        }
 
        return '<span style="color:#444444" title="'+esc(d)+'">'+shortened+'&#8230;</span>';
    };
};

function produceSummary() {
	$.ajax({
		cache: false,
		url: 'report_lib.php?',
		data:'mode=u_summary&user=$user&datefrom='+encodeURIComponent($("#datefrom").val())+'&dateto='+encodeURIComponent($("#dateto").val()),
		type:'GET',
		success:function(data) {
			$("#tbl_summary tbody").html(data);
		}
	});
}

function produceSummary_mim() {
	$.ajax({
		cache: false,
		url: 'report_lib.php?',
		data:'mode=u_summary_mim&user=$user&datefrom='+encodeURIComponent($("#datefrom").val())+'&dateto='+encodeURIComponent($("#dateto").val()),
		type:'GET',
		success:function(data) {
			$("#tbl_summary_mim tbody").html(data);
		}
	});
}

var date = moment().format('YYYYMMDD-hhmmss');
var mim = new Array();
mim[0] = new Array("text","SMS");
mim[1] = new Array("line","LINE");
mim[2] = new Array("messenger","FACEBOOK");
mim[3] = new Array("slack","SLACK");
mim[4] = new Array("viber","VIBER");
mim[5] = new Array("wechat","WECHAT");
mim[6] = new Array("telegram","TELEGRAM");
mim[8] = new Array("teams","MICROSOFT TEAMS");
mim[9] = new Array("webex","WEBEX TEAMS");
mim[10] = new Array("whatsapp","WHATSAPP");
mim[11] = new Array("wechat","WECHAT WORK");
mim[13] = new Array("whatsapp","Whatsapp DC");
mim[14] = new Array("line","LINE NOTIFY");
mim[20] = new Array("unknown","UNKNOWN");
function returnFilename() {
	return '{$user}_report_'+moment($("#datefrom").val(),"DD/MM/YYYY").format("YYYYMMDD")+"-"+moment($("#dateto").val(),"DD/MM/YYYY").format("YYYYMMDD");
}
function mm(picfile,title) {
	if (picfile != "unknown")
		return " <img height='20px' src='./images/icons/icon_"+picfile+"@3x.png' title='"+title+"'>";
	return " <i class='fa fa-question-circle' title='Unknown'></i>";
}

pdfMake.fonts = {
	Simsum : {
		normal: 'SIMSUN-regular.ttf',
		bold: 'SIMSUN-regular.ttf',
		italics: 'SIMSUN-regular.ttf',
		bolditalics: 'SIMSUN-regular.ttf'
	},
	OpenSans : {
		normal: 'OpenSans-Regular.ttf',
		bold: 'OpenSans-Bold.ttf',
		italics: 'OpenSans-Italic.ttf',
		bolditalics: 'OpenSans-BoldItalic.ttf'
	}
}

var table = $('#tbl_msg_list').DataTable({
	autoWidth: false,
	deferRender: false,
	stateSave: false,
	ajax: {
		type: 'POST',
		url: 'report_lib.php',
		data: function () {
			return 'mode=detail&user=$user&datefrom='+encodeURIComponent($("#datefrom").val())+'&dateto='+encodeURIComponent($("#dateto").val());
		}
	},
	lengthMenu: [[100,500,1000], [100,500,1000]],
	pageLength: 100,
	processing: true,
	columns: [
		{data:'created',width:"100px"},
		{data:'campaign_name',width:"150px"},
		{data:'mobile_numb',width:"120px",render:function(data,type,row) {
				index = row["mim"] != undefined ? row["mim"] : 20;
				file = mim[index][0]; 
				if (type === 'display')
					return data+mm(mim[index][0],mim[index][1]);
				return data+"("+mim[index][1]+")";
			}
		},
		{data:'message',render:function(data,type,row) {
				if (type === 'display')
					return data.length > 200 ? data.substr(0,200)+"&#8230" : data;
				return data;
			},className:"text-left",charset:"utf-8"
		},
		{data:'stat',width:"100px"},
		{data:'totalsms',width:"75px"},
		{data:'trackid',width:"150px",orderable:false,
			render:function(data,type,row) {
				if (type === "display") {
					let attcy = "";
					if (row["file_location"] != "") {
						attcy = " <a title='View Image' target='_blank' href='"+row["file_location"]+"' class='btn btn-secondary btn-xs' style='font-size:12px'><i class='fa fa-paperclip'></i></a>";
					}
					return "<button class='btn btn-secondary btn-xs btn-resend' style='font-size:12px' data='"+row["msgid"]+"'>Resend</button> <button class='btn btn-secondary btn-xs' style='font-size:12px' data-bs-toggle='modal' data-id='"+row["trackid"]+"' data-bs-target='#msgdetailmodal' title='Detail' style='cursor:pointer'><i class='fa fa-info-circle'></i></button>"+attcy;
				} else
					return data;
			}
		},
		{
			data:'mim',visible:false
		}
	],
	initComplete: function(settings,json) {
		resend_check();
	}
});

new $.fn.dataTable.Buttons( table, {
	buttons: [
		{extend:'csv', text: '<i class="fa fa-file-text-o"></i> {$xml_common->export} CSV', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ], orthogonal: "exportcsv" }, filename: function() {return returnFilename();},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-secondary btn-sm");
			}
		},
		{extend:'excel', text: '<i class="fa fa-file-excel-o"></i> {$xml_common->export} Excel', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ], orthogonal: "exportxls" }, filename: function() {return returnFilename();},
		init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-success btn-sm");
		},charset: 'utf-8'},
		{
			extend:'pdf', text: '<i class="fa fa-file-pdf-o"></i> {$xml_common->export} PDF',
			exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ], orthogonal: "exportpdf" },
			customize: function(doc) {
				doc.defaultStyle.font = 'Simsum';
			},
			filename: function() {return returnFilename();},
			init: function(api,node,config) {
				$(node).removeClass("dt-button buttons-csv buttons-html5");
				$(node).addClass("btn btn-danger btn-sm");
			},

			charset: 'utf-8',
			title:function() {
				return "$user Report ("+$('#datefrom').val() + " - " + $('#dateto').val()+")";
			}
		}
]});
table.buttons().container().appendTo('#export');

var table_mim = $('#tbl_msg_list_mim').DataTable({
	autoWidth: false,
	deferRender: false,
	stateSave: false,
	ajax: {
		type: 'POST',
		url: 'report_lib.php',
		data: function () {
			return 'mode=detail_mim&user=$user&datefrom='+encodeURIComponent($("#datefrom").val())+'&dateto='+encodeURIComponent($("#dateto").val());
		}
	},
	lengthMenu: [[100,500,1000], [100,500,1000]],
	pageLength: 100,
	processing: true,
	columns: [
		{data:'created',width:"100px"},
		{data:'campaign_name',width:"150px"},
		{data:'mobile_numb',width:"120px",render:function(data,type,row) {
				index = row["mim"] != undefined ? row["mim"] : 20;
				file = mim[index][0]; 
				if (type === 'display')
					return data+mm(mim[index][0],mim[index][1]);
				return data+"("+mim[index][1]+")";
			}
		},
		{data:'message',render:function(data,type,row) {
				if (type === 'display')
					return data.length > 200 ? data.substr(0,200)+"&#8230" : data;
				return data;
			},className:"text-left",charset:"utf-8"
		},
		{data:'stat',width:"100px"},
		{data:'trackid',width:"150px",orderable:false,
			render:function(data,type,row) {
				if (type === "display") {
					let attcy = "";
					if (row["file_location"] != "") {
						attcy = " <a title='View Image' target='_blank' href='"+row["file_location"]+"' class='btn btn-secondary btn-xs' style='font-size:12px'><i class='fa fa-paperclip'></i></a>";
					}
					return "<button class='btn btn-secondary btn-xs btn-resend' style='font-size:12px' data='"+row["msgid"]+"'>Resend</button> <button class='btn btn-secondary btn-xs' style='font-size:12px' data-bs-toggle='modal' data-id='"+row["trackid"]+"' data-bs-target='#msgdetailmodal' title='Detail' style='cursor:pointer'><i class='fa fa-info-circle'></i></button>"+attcy;
				} else
					return data;
			}
		},
		{
			data:'mim',visible:false
		}
	],
	initComplete: function(settings,json) {
		resend_check();
	}
});

new $.fn.dataTable.Buttons( table_mim, {
	buttons: [
		{extend:'csv', init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-secondary btn-sm");
		},text: '<i class="fa fa-file-text-o"></i> {$xml_common->export} CSV', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ], orthogonal: "exportcsv" }, filename:function() {return returnFilename();}},
		{extend:'excel',init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-success btn-sm");
		}, text: '<i class="fa fa-file-excel-o"></i> {$xml_common->export} Excel', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ], orthogonal: "exportxls" }, filename:function() {return returnFilename();},charset: 'utf-8'},
		{extend:'pdf', init: function(api,node,config) {
			$(node).removeClass("dt-button buttons-csv buttons-html5");
			$(node).addClass("btn btn-danger btn-sm");
		},text: '<i class="fa fa-file-pdf-o"></i> {$xml_common->export} PDF', exportOptions: {columns: [ 0, 1, 2, 3, 4, 5 ], orthogonal: "exportpdf" }, 
		customize: function(doc) {
			doc.defaultStyle.font = 'Simsum';
		}, filename: function() {return returnFilename();}, charset: 'utf-8',title:function() {
			return "$user Report ("+$('#datefrom').val() + " - " + $('#dateto').val()+")";
		}}
]});
table_mim.buttons().container().appendTo('#export_mim');

$("#datefrom, #dateto").on("change",function(){
	produceSummary();
	produceSummary_mim();
	table.ajax.reload();
	table_mim.ajax.reload();
	resend_check();
});

$("#msgdetailmodal").on("show.bs.modal",function(e) {
	var modal = $(this), id = $(e.relatedTarget).data('id');
	$.ajax({
		cache: false,
		url: 'report_lib.php',
		data:'mode=msgdetail&id='+id,
		type:'GET',
		dataType:'json',
		success: function(val)
		{
			modal.find(".modal-title").html("Detail");
			modal.find("#trackid").html(val.trackid);
			modal.find("#recipient").html(val.recipient);
			modal.find("#status").html(val.status);
			modal.find("#message").html(val.message);
			if ($("#nav-smspanel").hasClass("active")) {
				modal.find("#smsmimswitch").show();
				modal.find("#totalsms").html(val.totalsms);
			} else {
				modal.find("#smsmimswitch").hide();
			}
			modal.find("#sent_dtm").html(val.sent_dtm);
			modal.find("#completed_dtm").html(val.completed_dtm);
			modal.find("#callerid").html(val.callerid);
			modal.find("#campaign").html(val.campaign);
		}
	});
});
produceSummary();
produceSummary_mim();
END;
	}
?>
</script>