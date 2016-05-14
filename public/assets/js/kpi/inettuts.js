var chartData;

if(typeof chart != 'undefined') {
    $.each(chart, function(key, val) {
        drawChartDashboard(val);
    });
}

$(document).ready(function() {

    $('.kpi-icon-tooltip').tooltip({
        placement: "top"
    });

    $(document).on('click', '.kpi-button-chart', function(){
        if ($(this).hasClass('isShow')){
            $(".chart-choice").fadeOut(500);
            $("a.kpi-button-chart button").animate({backgroundColor: '#94da2c'}, 500).animate({color: '#fff'}, 0);
            $(".kpi-button-chart span").animate({color: '#fff'}, 1200);
            $(this).removeClass('isShow');
        }else {
            $(".chart-choice").fadeIn(500);
            $("a.kpi-button-chart button").animate({backgroundColor: '#f2f2f2'}, 500).animate({color: '#767676'}, 0);
            $(".kpi-button-chart span").animate({color: '#94da2c'}, 1200);
            $(this).addClass('isShow');
        }
    });

    $(document).on('click', '.close-chart-choice', function(){
        $('.kpi-button-chart').click();
    });

    $(document).on('click', '.kpi-view', function(){
        showLoading();
            loadAsync('kpi', 'dashboard/getChartModal', {chartType : $(this).attr('chart-type')}, "kpi-modal-content", 'kpi-modal');
        hideLoading();
    });

    $(document).on('change', '[name="kpi-item"]', function(){
        var $this = $(this);

        var params = {};
        params.chartType    = $this.attr('chart-type');
        params.kpi          = $('form').serializeArray();
        params.date         = $('input[name="daterange"]').val();
        params.title        = $('input[name="title"]').val();
        params.subtitle     = $('input[name="subtitle"]').val();

        showLoading();
            loadAsync('kpi', 'chart/refreshChart', {params : params}, false, false, false, 'setOptionsChart');
        hideLoading();
    });

    $(document).on('click', '.kpi-chart-save', function(){
        showLoading();
        var params = {};
        params.chartData    = chartData;
        params.chartTitle   = $('input[name="title"]').val();
        console.log(params);
            loadAsync('kpi', 'chart/saveChart', {params : params}, false, false, false, 'returnAfterSaveChart');
            //loadAsync('kpi', 'chart/saveChart', {params : params}, false, false, false, 'put');
    });

});

function returnAfterSaveChart(data){

    var message = $.parseJSON(data);

    if (typeof message.error != 'undefined' && message.error){
        swal("Opps...", message.error, "error");
    }else {
        hideLoading();
        $('#kpi-modal').modal('hide');

        setTimeout(function () {
            window.location.reload();
        }, 1000);
    }

}

function drawChart(options){
    var chart = new Highcharts.Chart(options);
}

function drawChartDashboard(options){ console.log(options)
    optionsForChart = JSON.parse(options.ChartData);
    optionsForChart.chart.renderTo = 'kpi-chart-dashboard-' + options.ID; console.log(optionsForChart)
    var chart = new Highcharts.Chart(optionsForChart);
}

function setOptionsChart(options){
    var jsonData = JSON.parse(options).data;
    chartData = jsonData;
    var chart = new Highcharts.Chart(jsonData);
}

function initDateRange(){
    $('input[name="daterange"]').daterangepicker({
        autoUpdateInput: true,
        //startDate: startDate,
        //endDate:   endDate,
        locale: { format: 'YYYY-MM-DD' }
    });
}