var shift = 0;


$(document).ready(function() {

    /**
     * first active tab
     */
    $('.kpi-list a:first').tab('show');

    var markBlock = function(go){

        if(go != false)
            $('[href="#' + go + '"]').click();

    }

    $('.kpi-icon-tooltip').tooltip({
        container: 'body',
        placement: "top"
    });


    /**
     * add active class for current kpi item
     */
    var element = parseSecond('active');
    if(element != false){
        markBlock(element);
    }

    function parseSecond(val) {
        var result = false,
            tmp = [];
        var items = location.search.substr(1).split("&");
        for (var index = 0; index < items.length; index++) {
            tmp = items[index].split("=");
            if (tmp[0] === val) result = decodeURIComponent(tmp[1]);
        }
        return result;
    }

    $(document).on('click', '.date-button', function() {
        var $this = $(this);
        var tab = $this.attr('tab-id');
        shift += parseInt($this.attr('data-value'));
        showLoading();
            loadAsync('kpi', 'entries/shift', {shift : shift, tab : tab}, false, false, false, 'put');
        hideLoading();
    });

    $(document).on('change', '[name="actual"], [name="target"]', function() {
        var $this = $(this);
        var params = {};
        params.KpiID            = parseInt($this.parents('tr').attr('id'));
        params.Type             = parseInt($this.attr('data-type'));
        params.Date             = parseInt($this.attr('data-date'));
        params.EntriesID        = parseInt($this.attr('data-entries-id'));
        params.Data             = $this.val();
        loadAsync('kpi', 'entries/save', {params : params}, false, false, false, 'returnID');
    });

});

function returnID(data){
    var message = $.parseJSON(data);
    if(typeof message.oldId != 'undefined')
        $('[data-entries-id="' + message.oldId + '"]').attr('data-entries-id', message.id);
}