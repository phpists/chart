$(document).ready(function() {

    $(document).on('click', '.modal-category', function () {
        showLoading();
        var categoryID = $(this).parents('li').attr('category-id');
        var id = typeof categoryID != 'undefined' ? categoryID : null;
        loadAsync('kpi', 'category/getModal', {id: id}, "uploaded-modal-content", 'main-modal');
        hideLoading();
    });

    $(document).on('click', '.manage-category-save', function () {
        loadAsync('kpi', 'category/saveCategory',
            {
                categoryID: $('[name="categoryID"]').val(),
                categoryName: $('[name="categoryName"]').val()
            }
            , false, false, false, 'put');
        loadAsync('kpi', 'category/refreshSelectCategories', {}, false, false, false, 'put');
        $('#main-modal').modal('hide');
    });

    $(document).on('click', '.delete-category', function () {

        var categoryID = $(this).parents('li').attr('category-id')
        var id = typeof categoryID != 'undefined' ? categoryID : null;
        swal({
                title: translate.delete_title,
                text: translate.delete_text_default,
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                cancelButtonText: translate.delete_button_cancel,
                confirmButtonText: translate.delete_title,
                closeOnConfirm: false }, function(){

                loadAsync('kpi', 'category/deleteCategory', {id: id}, false, false, false, 'afterDeleteCategory');
                $('[category-id="' + categoryID + '"]').remove();
            }
        );


    });

    $(document).on('click', '.manage-btn, .close-manage-category, #btn-category', function () {
        $("#manage-category").toggleClass("active");
    });

    $(document).on('click', '.category-load', function() {
        var $this = $(this);
        showLoading();
        $('.selected-category').removeClass('selected-category');
        var categoryID = parseInt($this.attr('category-id'));
        createCookie('KpiCategoryID', categoryID);
        $this.children().addClass('selected-category');
        loadAsync('kpi', 'refreshTable', {categoryID : categoryID}, false, false, false, 'put');
        refreshSelectCategories(categoryID);
        hideLoading();
    });

});

function refreshSelectCategories(category){
    loadAsync('kpi', 'category/refreshSelectCategories', {id: parseInt(category)}, false, false, false, 'put');
}

function afterDeleteCategory(category){
    loadAsync('kpi', 'category/refreshSelectCategories', {id: parseInt(category)}, false, false, false, 'put');
    swal(translate.deleted_success, '', "success");
}

function refreshCategories(){
    loadAsync('kpi', 'category/refreshCategoryList', {}, false, false, false, 'put');
}

function createCookie(name, value, days) {
    var expires;

    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
        expires = "; expires=" + date.toGMTString();
    } else {
        expires = "";
    }
    document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
}

function readCookie(name) {
    var nameEQ = encodeURIComponent(name) + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) === ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
    }
    return null;
}