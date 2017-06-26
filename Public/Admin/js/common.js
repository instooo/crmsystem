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