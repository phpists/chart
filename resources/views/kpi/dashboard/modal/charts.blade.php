<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-label="{{{ Translate::translate('close') }}}"><i class="icon-cross3 close-modal"></i></button>
    <h2>{{{ Translate::translate('add') }}} {{{ $chartTitle }}}</h2>
</div>

<div class="kpiview">
    <div class="kpiview-row">
        <div class="kpiview-left analytics">

            <div class="kpi-view">
                <div class="kpi-view-item">
                    <div id="kpi-chart"></div>
                </div>
                <br class="clear">
            </div>
        </div>

        <div class="kpiview-right">

            @if(!empty($items))
                <form class="form-horizontal">

                    <h3>{{{ Translate::translate('name') }}}</h3>
                    <div class="form-group horizontal-padding-15">
                        <input type="text" name="title" class="col-md-12 form-control">
                    </div>

                    <h3>{{{ Translate::translate('description') }}}</h3>
                    <div class="form-group horizontal-padding-15">
                        <input type="text" name="subtitle" class="col-md-12 form-control">
                    </div>

                    <h3>{{{ Translate::translate('select_kpi_and_date_range') }}}</h3>

                    @foreach($items as $key => $value)
                        <div class="icheckbox">
                            <input type="checkbox" class="check" chart-type="{{{ $chartType }}}" value="{{{ $value->ID }}}" name="kpi-item"> {{{ $value->Name }}}
                        </div>
                    @endforeach

                    <div class="form-group">
                        <div class="input-group padding-15">
                            <span class="input-group-addon"><i class="icon-calendar"></i></span>
                            <input type="text" name="daterange" class="col-md-12 form-control">
                        </div>
                    </div>

                </form>
            @endif

        </div>
    </div>
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">{{{ Translate::translate('cancel') }}}</button>
    <button class="btn btn-primary kpi-chart-save xs-btn" type="button"><i class="icon-floppy-disk"></i> {{{ Translate::translate('save') }}}</button>
    </button>
</div>

<script>
    drawChart({!! $gData  !!});
    checkboxInit();
    initDateRange();
</script>