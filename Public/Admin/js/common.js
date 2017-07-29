//监听关闭layer窗口
function listenLayerClose(index, callback) {
    var layercloseflag;
    var _t = setInterval(function () {
        layercloseflag = $.cookie('layercloseflag');
        if (layercloseflag == 1 || $.layerClosed) {
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
