$(function () {
    jQuery.extend({
        crmConfirm: function (option) {
            var _default = {
                title:'提示信息',
                msg:'',
                yes:function () {
                    $('.crm_tips').fadeOut(200);
                },
                no:function () {
                    $('.crm_tips').fadeOut(200);
                }
            };
            var opt = $.extend(_default, option);
            var _h = '<div class="tip crm_tips">'+
                '<div class="tiptop"><span>'+opt.title+'</span><a></a></div>'+
                '<div class="tipinfo">'+
                '<div class="tipright">'+
                '<p>'+opt.msg+'</p>'+
                '</div>'+
                '</div>'+
                '<div class="tipbtn">'+
                '<input name="sure" type="button" class="sure" value="确定"/>&nbsp;'+
                '<input name="cancel" type="button" class="cancel" value="取消"/>'+
                '</div>'+
                '</div>';
            if (!('.crm_tips').length <= 0) $('body').append(_h);
            $('.crm_tips').fadeIn(200);
            $('.crm_tips .tiptop a').live('click', function () {
                $('.crm_tips').fadeOut(200);
            });
            $('.crm_tips .sure').live('click', function () {
                opt.yes();
            });
            $('.crm_tips .cancel').live('click', function () {
                opt.no();
            });
        },
        crmTips: function (option) {
            var _default = {
                title:'提示信息',
                msg:'',
                yes:function () {
                    $('.crm_tips').fadeOut(200);
                }
            };
            var opt = $.extend(_default, option);
            var _h = '<div class="tip crm_tips" style="height: 240px;width: 350px;">'+
                '<div class="tiptop"><span>'+opt.title+'</span><a></a></div>'+
                '<div class="tipinfo" style="height: 65px;">'+
                '<div class="tipright">'+
                '<p>'+opt.msg+'</p>'+
                '</div>'+
                '</div>'+
                '<div class="tipbtn">'+
                '<input name="sure" type="button" class="sure" value="确定"/>'+
                '</div>'+
                '</div>';
            if (!('.crm_tips').length <= 0) $('body').append(_h);
            $('.crm_tips').fadeIn(200);
            $('.crm_tips .tiptop a').live('click', function () {
                $('.crm_tips').fadeOut(200);
            });
            $('.crm_tips .sure').live('click', function () {
                opt.yes();
            });
        }
    });
});