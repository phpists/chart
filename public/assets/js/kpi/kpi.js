$(document).ready(function() {



    $(document).on('click', '.kpi-button button', function(){
        if ($(this).hasClass('isShow')){
            formReset();
            $(".kpi-prev-text").fadeIn(500);
            $(".table-kpi").fadeOut(1200);
            $("a.kpi-button button").animate({backgroundColor: '#94da2c'}, 500).animate({color: '#fff'}, 0);
            $(".kpi-button span").animate({color: '#fff'}, 1200);
            $(this).removeClass('isShow');
        }else {
            formReset();
            $(".kpi-prev-text").fadeOut(500);
            $(".table-kpi").fadeIn(1200);
            $("a.kpi-button button").animate({backgroundColor: '#f2f2f2'}, 500).animate({color: '#767676'}, 0);
            $(".kpi-button span").animate({color: '#94da2c'}, 1200);
            $(this).addClass('isShow');
        }
    });

    $(document).on('click', '.kpi-close', function(){
        $('.kpi-button button').click();
    });

    $(document).on('click', '.kpi-save', function(){

        var returnArray = {};
        var getArray = $('form').serializeArray();
        $.each(getArray, function() {
            if (returnArray[this.name] !== undefined) {
                if (!returnArray[this.name].push) {
                    returnArray[this.name] = [returnArray[this.name]];
                }
                returnArray[this.name].push(this.value || '');
            } else {
                returnArray[this.name] = this.value || '';
            }
        });

        //returnArray.companyID = $('body').find('[name="companies"]').val();

        loadAsync('kpi', 'saveKpi', {post : returnArray}, false, false, false, 'returnAfterSaveKpi');

    });

    $(document).on('click', '.remove-item-kpi', function(){
        var id = $(this).parents('tr').attr('data-id');
        swal({
                title: translate.delete_title,
                text: translate.delete_text_default,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                cancelButtonText: translate.delete_button_cancel,
                confirmButtonText: translate.delete_title,
                closeOnConfirm: false }, function(){

                loadAsync('kpi', 'removeKpi', {id : id}, false, false, false, 'refresh');
            }
        );
    });

    $(document).on('click', '.edit-item-kpi', function() {
        $('.kpi-button button').click();
        loadAsync('kpi', 'loadDataForEditKpi', {id : $(this).parents('tr').attr('data-id')}, false, false, false, 'insertData');
    });



    $(document).on('click', '.add-company-kpi', function() {
        if ($(this).hasClass('isShow')){
            $('.form-company-kpi').removeClass('hidden-element');
            $(this).removeClass('isShow');
        }else {
            $('.form-company-kpi').addClass('hidden-element');
            $(this).addClass('isShow');
        }
    });

    $(document).on('click', '.edit-company', function() {
        var $this = $(this);
        var companyID = $this.parents('span').attr('company-id');
        $('.add-company-kpi').click();
        loadAsync('kpi', 'company/loadDataForCompany', {companyID : companyID}, false, false, false, 'insertCompanyData');
    });

    $(document).on('click', '.save-kpi-button', function() {
        var name = $('.kpi-company-name').val();
        loadAsync('kpi', 'company/add', {name : name}, false, false, false, 'returnAfterSaveCompany');
    });

    $(document).on('click', '.delete-company', function() {
        var $this = $(this);
        var companyID = $this.parents('span').attr('company-id');
        swal({
                title: translate.delete_title,
                text: translate.delete_text_default,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                cancelButtonText: translate.delete_button_cancel,
                confirmButtonText: translate.delete_title,
                closeOnConfirm: false }, function(){

                loadAsync('kpi', 'company/deleteCompany', {id : companyID}, false, false, false, 'refreshCompanies');
            }
        );
    });

    $(document).on('change', '.companies-select', function() {
        var $this = $(this);
        var companyID = parseInt($this.val());
        $this.next().next().attr('company-id', companyID);
    });

    function formatState (state) {
        if (!state.id) { return state.text; }
        var $state = $(
            '<span><img src="assets/images/flags/' + state.element.value.toLowerCase() + '.png" class="img-flag" /></span>'
        );
        return $state;
    };

    $(".select-kpi-images").select2({
        templateSelection: formatState,
        templateResult: formatState,
        showSearchBox: false
    });

    $(".select-kpi").select2({
    });

    $(".companies-select").select2();

    $('.kpi-icon-info').popover({
        container: ".table-kpi",
        trigger: "hover",
        placement: "right",
        title: translate.information,
        html: true
    });

    $('.kpi-icon-tooltip').tooltip({
        placement: "top"
    });

});

function insertCompanyData(data){
    var returnData = $.parseJSON(data);
    if (typeof returnData != 'undefined'){
        $('.kpi-company-name').val(returnData.Name);
    }
}

function insertData(data){
    var returnData = $.parseJSON(data);
    if (typeof returnData != 'undefined'){
        $('[name="ID"]').val(returnData.ID);
        $('[name="name"]').val(returnData.Name);
        $('[name="description"]').val(returnData.Description);
        $('[name="target"]').val(returnData.Target);
        $('[name="country"]').val(returnData.CountryCode);
        $('[name="entered"]').val(returnData.Entered);
        $('[name="format"]').val(returnData.Format);
        $('[name="direction"]').val(returnData.Direction);
        $('[name="categories"]').val(returnData.CategoryID);
    }
}

function refreshCompanies(){
    loadAsync('kpi', 'company/refreshCompany', {}, false, false, false, 'put');
    swal(translate.deleted_success, '', "success");
}

function refresh(data){
    var message = $.parseJSON(data);
    loadAsync('kpi', 'refreshTable', {categoryID : message.categoryID}, false, false, false, 'put');
    refreshCategories();
    swal(translate.deleted_success, '', "success");
}

function returnAfterSaveKpi(data){

    var message = $.parseJSON(data);

    if (typeof message.error != 'undefined' && message.error){
        swal("Opps...", message.error, "error");
    }else {
        loadAsync('kpi', 'refreshTable', {categoryID : message.categoryID}, false, false, false, 'put');
        refreshCategories();
        $('.kpi-button button').click();
        formReset();
        $("#kpi-form").trigger('reset');
    }
}

function returnAfterSaveCompany(data){

    var message = $.parseJSON(data);

    if (typeof message.error != 'undefined' && message.error){
        swal("Opps...", message.error, "error");
    }else {
        loadAsync('kpi', 'company/refreshCompany', {}, false, false, false, 'put');
        $('.add-company-kpi').click();
        $(".kpi-company-name").val('');
    }
}

function formReset(){
    $('[name="ID"]').val('');
    $('[name="name"]').val('');
    $('[name="description"]').val('');
    $('[name="target"]').val('');
    $('[name="entered"]').val(1);
    $('[name="format"]').val(1);
    $('[name="direction"]').val(1);
}
