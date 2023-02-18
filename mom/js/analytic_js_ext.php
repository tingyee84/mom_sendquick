<?php 
header("Content-type:text/javascript");
include_once("../lib/commonFunc.php");
?>

"use strict"
<?php
// FIXME need to add EXCEL for table
// select sent_by, sum(case when message_status = 'D' THEN cast(totalsms as integer) else 0 end) countD, sum(case when message_status = 'Y' then cast(totalsms as integer) else 0 end) countY from outgoing_logs group by sent_by
?>
function exportTableToExcel(tableID, filename = ''){
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    
    // Specify file name
    filename = filename?filename+'.xls':'excel_data.xls';
    
    // Create download link element
    downloadLink = document.createElement("a");
    
    document.body.appendChild(downloadLink);
    
    if(navigator.msSaveOrOpenBlob){
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob( blob, filename);
    }else{
        // Create a link to the file
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
    
        // Setting the file name
        downloadLink.download = filename;
        
        //triggering the function
        downloadLink.click();
    }
}
var utx = document.getElementById('userChart').getContext('2d');
$("#d_range").click(function () {
    if ($(this).val() == "last3months")
        $(".monthhide").show();
    else
        $(".monthhide").hide();
});
$(document).ready(function(){
    $("#month").text(moment().format('MMMM-yy'));
    //$("#range, #d_range").change(function() {
        //console.log($(this).val());
    //});
    $("#option1").on("click",function() {
        $("#1atable").hide();
        $("#1achart").show();
        $("#option2").parent().removeClass("btn-primary").addClass("btn-secondary");
        $("#option1").parent().removeClass("btn-secondary").addClass("btn-primary");
    });
    $("#option2").on("click",function() {
        $("#1atable").show();
        $("#1achart").hide();
        $("#option1").parent().removeClass("btn-primary").addClass("btn-secondary");
        $("#option2").parent().removeClass("btn-secondary").addClass("btn-primary");
    });
    <?php if (isUserAdmin($_SESSION["userid"])) { ?>
    $("#2atable").hide();
    $("#option3").on("click",function() {
        $("#2atable").hide();
        $("#2achart").show();
        $("#option4").parent().removeClass("btn-primary").addClass("btn-secondary");
        $("#option3").parent().removeClass("btn-secondary").addClass("btn-primary");
    });
    $("#option4").on("click",function() {
        $("#2atable").show();
        $("#2achart").hide();
        $("#option3").parent().removeClass("btn-primary").addClass("btn-secondary");
        $("#option4").parent().removeClass("btn-secondary").addClass("btn-primary");
    });
    <?php } ?>
    $("#range").change(function() {
        u_chart.destroy();
        u_chart = new Chart(utx, {
            // The type of chart we want to create
            type: 'horizontalBar',
            responsive: true,
            tooltips: {
            "enabled": false
            },
            // The data for our dataset
            data: {
                datasets: [{
                    label: 'Success',
                    backgroundColor: 'rgb(0, 0, 255)',
                    borderColor: 'rgb(0, 0, 200)'
                },
                {
                    label: 'Failed',
                    backgroundColor: 'rgb(255, 100, 100)',
                    borderColor: 'rgb(200, 100, 100)'
                }
                ]
            },

            // Configuration options go here
            options: {
                responsive: false,
                scales: {
                    xAxes: [
                    {
                        scaleLabel: {
                            display: true,
                            labelString: "Number of SMS & MIM Sent (" + moment().subtract(($(this).val() == "currmonth" ? 0 : 1),'month').format('MMMM-yy') + ")"
                        },
                        stacked: true
                    }
                    ],
                    yAxes: [
                    {
                        stacked: true,
                        scaleLabel: {
                            display: true,
                            labelString: "User"
                        }
                    },
                    
                    ]
                }
            }
        });
        if ($(this).val() == "currmonth") {
            $.ajax({
                cache: false,
                url: 'analytic_lib.php',
                data:'mode=top10user<?php echo isUserAdmin($_SESSION["userid"]) ? "_mom" : ""; ?>',
                type:'GET',
                success: function(data) {
                    $("#tbl_user tbody").html(data);
                    let i = 0;
                    $("#tbl_user tbody tr").each(function() {
                        let t = parseInt($(this).find("td").eq(3).text()) + parseInt($(this).find("td").eq(4).text());
                        addData(u_chart,$(this).find("td").eq(1).text()+"-Total:"+t,[$(this).find("td").eq(3).text(),$(this).find("td").eq(4).text()]);
                        if (++i > (10-1)) 
                            return false;
                    });
                }
            });
        } else {
            $.ajax({
                cache: false,
                url: 'analytic_lib.php',
                data:'mode=top10user<?php echo isUserAdmin($_SESSION["userid"]) ? "_mom" : ""; ?>_lastmonth',
                type:'GET',
                success: function(data) {
                    $("#tbl_user tbody").html(data);
                    let i = 0;
                    $("#tbl_user tbody tr").each(function() {
                        let t = parseInt($(this).find("td").eq(3).text()) + parseInt($(this).find("td").eq(4).text());
                        addData(u_chart,$(this).find("td").eq(1).text()+"-Total:"+t,[$(this).find("td").eq(3).text(),$(this).find("td").eq(4).text()]);
                        if (++i > (10-1)) 
                            return false;
                    });
                }
            });
        }
    });
    $("#range").change();
    $("#export_u_chart_pdf").click(function () {
        var pdf = new jsPDF('p','mm','A4');
        pdf.addImage($("#userChart")[0],'jpeg',0,0);
        pdf.save("allusers-chart.pdf");
    });
    $("#export_u_table_pdf").on('click',function () {
        var pdf = new jsPDF('p','mm','A4');
        pdf.fromHTML($("#tbl_user").get(0), 15, 15);
        pdf.save("allusers-table.pdf");
    });
    $("#export_u_table_xls").on('click',function () {
        exportTableToExcel("tbl_user");
    });
    <?php { // TODO check user if can view as mom level
    if (isUserAdmin($_SESSION["userid"])) {    
        ?>
    for (let i = 0 ; i < 4 ; i++) {
        $("#range_mom").append("<option value="+i+">"+moment().subtract(i,'month').format('MMMM-yy')+"</option>");
    }
    $("#range_mom").on("change",function () {
        d_chart.destroy();
        d_chart = new Chart(document.getElementById('deptChart').getContext('2d'), {
        // The type of chart we want to create
            type: 'horizontalBar',
            data: {
                datasets: [{
                    label: 'Success',
                    backgroundColor: 'rgb(0, 0, 255)',
                    borderColor: 'rgb(0, 0, 200)'
                },
                {
                    label: 'Failed',
                    backgroundColor: 'rgb(255, 100, 100)',
                    borderColor: 'rgb(200, 100, 100)'
                }
                ]
            },

            // Configuration options go here
            options: {
                responsive: false,
                scales: {
                    xAxes: [
                    {
                        scaleLabel: {
                            display: true,
                            labelString: "Number of SMS & MIM Sent (" + moment().subtract($(this).val(),'month').format('MMMM-yy') + ")"
                        },
                        stacked: true
                    }
                    ],
                    yAxes: [
                    {
                        stacked: true,
                        scaleLabel: {
                            display: true,
                            labelString: "Departments"
                        }
                    }
                    ]
                }
            }
        });
        
        $.ajax({
            cache: false,
            url: 'analytic_lib.php',
            data:'mode=top10bu_'+$(this).val()+'month',
            type:'GET',
            success: function(data) {
                $("#tbl_month tbody").html(data);
                $("#tbl_month tbody tr").each(function() {
                    let t = parseInt($(this).find("td").eq(3).text()) + parseInt($(this).find("td").eq(4).text());
                    addData(d_chart,$(this).find("td").eq(1).text()+"-Total:"+t,[$(this).find("td").eq(3).text(),$(this).find("td").eq(4).text()]);
                });
            }
        });
    });

    $("#range_mom").change();

    $("#export_d_chart_pdf").on('click',function () {
        var pdf = new jsPDF('p','mm','A4');
        pdf.addImage($("#deptChart")[0],'jpeg',0,0);
        pdf.save("alldepts-chart.pdf");
    });
    $("#export_d_table_pdf").on('click',function () {
        console.log("PDrint");
        var pdf = new jsPDF('p','mm','A4');
        pdf.fromHTML($("#tbl_month").get(0), 15, 15);
        pdf.save("alldepts-table.pdf");
    });
    $("#export_d_table_xls").on('click',function () {
        exportTableToExcel("tbl_month");
    });
    <?php }  } ?>
});


function addData(chart,label,data) {
    chart.data.labels.push(label);
    let i = 0;
    chart.data.datasets.forEach((dataset) => {
        dataset.data.push(data[i++]);
    });
    chart.update();
}

Chart.plugins.register({
  beforeDraw: function(chartInstance) {
    var ctx = chartInstance.chart.ctx;
    ctx.fillStyle = "white";
    ctx.fillRect(0, 0, chartInstance.chart.width, chartInstance.chart.height);
  }
});

var u_chart = new Chart(utx, {
    // The type of chart we want to create
    type: 'horizontalBar',
    responsive: true,
    tooltips: {
      "enabled": false
    },
    // The data for our dataset
    data: {
        datasets: [{
            label: 'Success',
            backgroundColor: 'rgb(0, 0, 255)',
            borderColor: 'rgb(0, 0, 200)'
        },
        {
            label: 'Failed',
            backgroundColor: 'rgb(255, 100, 100)',
            borderColor: 'rgb(200, 100, 100)'
        }
        ]
    },

    // Configuration options go here
    options: {
        responsive: false,
        scales: {
            xAxes: [
            {
                scaleLabel: {
                    display: true,
                    labelString: "Number of SMS & MIM Sent (" + moment().format('MMMM-yy') + ")"
                },
                stacked: true
            }
            ],
            yAxes: [
            {
                stacked: true,
                scaleLabel: {
                    display: true,
                    labelString: "User"
                }
            },
            
            ]
        }
    }
});
<?php if (isUserAdmin($_SESSION["userid"])) { ?>
var d_chart = new Chart(document.getElementById('deptChart').getContext('2d'), {
    // The type of chart we want to create
        type: 'horizontalBar',
        data: {
            datasets: [{
                label: 'Success',
                backgroundColor: 'rgb(0, 0, 255)',
                borderColor: 'rgb(0, 0, 200)'
            },
            {
                label: 'Failed',
                backgroundColor: 'rgb(255, 100, 100)',
                borderColor: 'rgb(200, 100, 100)'
            }
            ]
        },

        // Configuration options go here
        options: {
            responsive: false,
            scales: {
                xAxes: [
                {
                    scaleLabel: {
                        display: true,
                        labelString: "Number of SMS & MIM Sent (" + moment().format('MMMM-yy') + ")"
                    },
                    stacked: true
                }
                ],
                yAxes: [
                {
                    stacked: true,
                    scaleLabel: {
                        display: true,
                        labelString: "Departments"
                    }
                }
                ]
            }
        }
    });
    <?php } ?>