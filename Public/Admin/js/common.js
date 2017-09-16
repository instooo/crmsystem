$(function () {
    $(document).on('click', '.multi_input', function () {
        $(this).siblings('.multi_list').show();
    });
    $(document).click(function (event) {
        if (!$('.multi_input').is(event.target) && $('.multi_input').has(event.target).length === 0) {
            if (!$('.multi_list').is(event.target) && $('.multi_list').has(event.target).length === 0) {
                $('.multi_list').hide();
            }
        }
    });
    $(document).on('click', '.multi_list dd', function () {
        var checkbox = $(this).find('input[type="checkbox"]');
        checkbox.prop('checked', !checkbox.prop('checked'));
        var multi_list = $(this).parents('.multi_list');
        var value = '';
        multi_list.find('dd').each(function () {
            if ($(this).find('input[type="checkbox"]').prop('checked')) {
                var s_val = $(this).find('input[type="checkbox"]').val();
                value += (value == '')?s_val:','+s_val;
            }
        });
        multi_list.siblings('.multi_input').val(value);
    });
});


//监听关闭layer窗口
function listenLayerClose(index, callback) {
    $.removeCookie('layercloseflag', {path:'/'});
    var layercloseflag;
    var _t = setInterval(function () {
        layercloseflag = $.cookie('layercloseflag');
        if (layercloseflag == 1) {
            clearInterval(_t);
            layer.close(index);
            $.removeCookie('layercloseflag', {path:'/'});
            if (callback) callback();
        }
    }, 500);
}
/**
 * 中文Unicode编码解码
 * */
var Unicode = {

    encode: function (str) {

        var res = [],
            len = str.length;

        for (var i = 0; i < len; ++i) {
            res[i] = ("00" + str.charCodeAt(i).toString(16)).slice(-4);
        }

        return str ? "\\u" + res.join("\\u") : "";
    },

    decode: function (str) {

        str = str.replace(/\\/g, "%");
        return unescape(str);
    }
};
/**
 * 格式化时间戳
 * */
function date_formate(timestamp, istime) {
    var date = new Date(timestamp*1000);//如果date为10位不需要乘1000
    var Y = date.getFullYear() + '-';
    var M = (date.getMonth()+1 < 10 ? '0'+(date.getMonth()+1) : date.getMonth()+1) + '-';
    var D = (date.getDate() < 10 ? '0' + (date.getDate()) : date.getDate());
    var h = ' ' + (date.getHours() < 10 ? '0' + date.getHours() : date.getHours()) + ':';
    var m = (date.getMinutes() <10 ? '0' + date.getMinutes() : date.getMinutes()) + ':';
    var s = (date.getSeconds() <10 ? '0' + date.getSeconds() : date.getSeconds());
    return istime?Y+M+D+h+m+s:Y+M+D;
}
/**
 *  htmlspecialchars_decode
 * */
function htmlspecialchars_decode (string, quoteStyle) {
    // eslint-disable-line camelcase
    //       discuss at: http://locutus.io/php/htmlspecialchars_decode/
    //      original by: Mirek Slugen
    //      improved by: Kevin van Zonneveld (http://kvz.io)
    //      bugfixed by: Mateusz "loonquawl" Zalega
    //      bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
    //      bugfixed by: Brett Zamir (http://brett-zamir.me)
    //      bugfixed by: Brett Zamir (http://brett-zamir.me)
    //         input by: ReverseSyntax
    //         input by: Slawomir Kaniecki
    //         input by: Scott Cariss
    //         input by: Francois
    //         input by: Ratheous
    //         input by: Mailfaker (http://www.weedem.fr/)
    //       revised by: Kevin van Zonneveld (http://kvz.io)
    // reimplemented by: Brett Zamir (http://brett-zamir.me)
    //        example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES')
    //        returns 1: '<p>this -> &quot;</p>'
    //        example 2: htmlspecialchars_decode("&amp;quot;")
    //        returns 2: '&quot;'
    var optTemp = 0
    var i = 0
    var noquotes = false
    if (typeof quoteStyle === 'undefined') {
        quoteStyle = 2
    }
    string = string.toString()
        .replace(/&lt;/g, '<')
        .replace(/&gt;/g, '>')
    var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    }
    if (quoteStyle === 0) {
        noquotes = true
    }
    if (typeof quoteStyle !== 'number') {
        // Allow for a single string or an array of string flags
        quoteStyle = [].concat(quoteStyle)
        for (i = 0; i < quoteStyle.length; i++) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
            if (OPTS[quoteStyle[i]] === 0) {
                noquotes = true
            } else if (OPTS[quoteStyle[i]]) {
                optTemp = optTemp | OPTS[quoteStyle[i]]
            }
        }
        quoteStyle = optTemp
    }
    if (quoteStyle & OPTS.ENT_HTML_QUOTE_SINGLE) {
        // PHP doesn't currently escape if more than one 0, but it should:
        string = string.replace(/&#0*39;/g, "'")
        // This would also be useful here, but not a part of PHP:
        // string = string.replace(/&apos;|&#x0*27;/g, "'");
    }
    if (!noquotes) {
        string = string.replace(/&quot;/g, '"')
    }
    // Put this in last place to avoid escape being double-decoded
    string = string.replace(/&amp;/g, '&')
    return string
}
