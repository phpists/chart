@if(!empty($charts))
    <script>var chart = [];</script>
    @for ($i = 0; $i < 3; $i++)

        <ul id="column{{{ $i }}}" class="col-md-4 column" data-column-id="{{{ $i }}}">
            @foreach($charts as $key => $chart)
                @if($chart['Column'] == $i)

                    <li class="widget color-green" id="intro">
                        <div class="widget-head" data-id="{{{ $chart['ID'] }}}">
                            <h3>{{{ $chart['ChartTitle'] }}}</h3>
                        </div>
                        <div class="widget-content">
                            <div id="kpi-chart-dashboard-{{{ $chart['ID'] }}}"></div>
                        </div>
                    </li>

                    <script>
                        chart.push({!! $chart !!})
                    </script>
                @endif
            @endforeach
        </ul>

    @endfor
@endif