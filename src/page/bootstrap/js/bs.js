/**
 * Created by benying.zou on 13.07.2018.
 */
var onSubmit = function () {
    $('#resdiv').html('抓取中......');

    var fnname = $('#fnname').val();
    var methode = $('input[name="methode"]:checked').val();
    var strParams = $('#params').val();
    var params = {};
    if (strParams) {
        var $erstC = strParams.substring(0, 1);
        if ($erstC === '{' || $erstC === '[') {
            params = strParams;
        } else {
            strParams = strParams.split("\n");
            for (var i=0; i<strParams.length; i++) {
                var it = strParams[i].split('=>');
                if (it.length === 2) {
                    params[it[0].trim()] = it[1].trim();
                }
            }
        }
    }
    if (fnname && methode) {

        var ajaxOption = {
            fn: fnname,
            method: methode,
            page: $('#page').val(),
            itemsPerPage: $('#itemsPerPage').val()
        };
        if (Object.keys(params).length > 0) {
            ajaxOption['params'] = params;
        }

        $.ajax({
            url: Cake.api.path + "rest/test/index",
            method: 'POST',
            data: ajaxOption
        }).done(function(data) {
            $('#resdiv').html(data);
        });
    } else {
        $('#resdiv').html('<div class="alert alert-danger">错误！请进行一些更改。</div>');
    }


};