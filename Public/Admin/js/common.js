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
