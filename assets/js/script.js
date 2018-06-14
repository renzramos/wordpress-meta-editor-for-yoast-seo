jQuery(document).ready(function ($) {

    var currentdate = new Date();
    var datetime =
        currentdate.getDate() + "-" +
        (currentdate.getMonth() + 1) + "-" +
        currentdate.getFullYear() + "-" +
        currentdate.getHours() + "-" +
        currentdate.getMinutes() + "-" +
        currentdate.getSeconds();

    $(".yoast-meta-editor-table").tableExport({
        position: 'top',
        formats: ['csv', 'txt'],
        filename: yoastMetaEditor.siteNameSlug + '-' + 'meta-data' + '-' + datetime
    });

    var pageID = 0;
    var values = '';
    var ajaxURL = yoastMetaEditor.ajaxURL;
    var selectedField;

    $('.yoast-meta-editor-table .field').focus(function () {


        $('.yoast-meta-editor-table .field-confirmation').remove();
        
        selectedField = $(this);
        $(this).after('<div class="field-confirmation" data-values="' + values + '">Confirm</div>');
        
    });


    $('.yoast-meta-editor-table .field').blur(function () {
    
        value = $(this).val();

        if (value == '') {
            $(this).addClass('empty').removeClass('filled');
        } else {
            $(this).addClass('filled').removeClass('empty');
        }
        initPageWithoutDescriptionCount();
    });

    

    $(document).on('click', '.yoast-meta-editor-table .field-confirmation', function(){ 

        
        id = selectedField.attr('data-id');
        value = selectedField.val();
        field = selectedField.attr('name');

        var values = encodeURI('value=' + value + '&field=' + field + '&id=' + id + '&action=yoast_meta_editor_action');

        $.post(ajaxURL, values, function (response) {
            console.log(response);
        });
        $(this).html('SAVED').addClass('saved');

    });


    

    initPageWithoutDescriptionCount();

    function initPageWithoutDescriptionCount() {
        $('.page-without-description').html($('.yoast-meta-editor-table textarea.empty').length);
    }

});