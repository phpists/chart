@extends('layout.default')
@section('content')

    @include('kpi.parts.menu')

    <div class="tab-content chart-panel margin-bottom-15">
        <div class="row">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12">
                        <a class="kpi-button-chart pull-right cursor-pointer">
                            <button class="btn"><span>+</span> {{{\getLaunched\Translate::translate('add_chart')}}}</button>
                        </a>
                    </div>
                </div>

                <div class="chart-choice row">

                    <div class="col-md-12">
                        <a title="{{{ \getLaunched\KpiCharts::$charts['line']['title'] }}}" chart-type="line" class="kpi-view kpi-icon-tooltip cursor-pointer"><i class="icon-stats-dots orange font-size-3"></i></a>
                        <a title="{{{ \getLaunched\KpiCharts::$charts['column']['title'] }}}" chart-type="column" class="kpi-view kpi-icon-tooltip cursor-pointer"><i class="icon-stats-bars orange font-size-3"></i></a>
                        <a title="{{{ \getLaunched\KpiCharts::$charts['pie']['title'] }}}" chart-type="pie" class="kpi-view kpi-icon-tooltip cursor-pointer"><i class="icon-pie-chart8 orange font-size-3"></i></a>
                        {{--<a title="{{{ \getLaunched\KpiCharts::$charts['solidgauge']['title'] }}}" chart-type="solidgauge" class="kpi-view kpi-icon-tooltip cursor-pointer"><i class="icon-meter-slow orange font-size-3"></i></a>--}}
                        <a title="{{{ \getLaunched\KpiCharts::$charts['area']['title'] }}}" chart-type="area" class="kpi-view kpi-icon-tooltip cursor-pointer"><i class="icon-chart orange font-size-3"></i></a>

                        <i class="icon-cross red cursor-pointer pull-right margin-top-15 close-chart-choice"></i>
                    </div>
                </div>


                <div id="columns" class="row">

                    <div id="list-charts">
                        @include('kpi.dashboard.parts.chart-lists')
                    </div>

                </div>
            </div>
        </div>
    </div>



    @include('kpi.dashboard.modal.skeleton_modal')
@stop