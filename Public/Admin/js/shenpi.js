$(document).ready(function(){	
	$('.tablelist tbody tr:odd').addClass('odd');
	$("#clickadd").click(function(){
		layer_index = layer.open({
			type: 1,
			title: '添加流程',
			skin: 'layui-layer-rim', //加上边框
			area: ['800px', '600px'], //宽高
			shade: 0.1,
			content: $('#addlc').html()
		});
	}); 
		$(document).on('click', '.steps ol li .del',function(){	
			//设置样式宽度
			var len = $(".steps ol li").length-3;	
			var wid=len*130-75;			
			$('.steps ol').width(wid);		
			index = $(this).parents('li').next("li").find(".node div").html();	
			
			$(".steps ol li").each(function(){			
				//后续节点的数据都加1								
					var aa = $(".steps ol li").index(this)-1;					
					if(aa>index){										
						$(".steps ol li").eq(aa).find('.node div').html(aa-2);
					}
			});
			$(this).parents('li').remove();
		});
		$(document).on('click', '.steps ol li .add',function(){	
			//设置样式宽度
			var len = $(".steps ol li").length-2;	
			var wid=(len+1)*130-75;			
			$('.steps ol').width(wid);
			var _this= $(this).parents('li');
			index = $(".steps ol li").index(_this)-1;
			var html = " <li><div class=add><div class='mini-icon icon-add'></div></div><div class=arr></div><div class=standard></div><div class=node><div>"+(index+1)+"</div></div><div class=set-person data-uid='' data-act='' data-extend_tit=''>点击设置审批人</div><div class=approvers></div></li>";			
			if(index==len){
				var html = " <li><div class=add><div class='mini-icon icon-add'></div></div><div class=arr></div><div class=standard></div><div class=node><div>"+(index)+"</div></div><div class=set-person data-uid='' data-act='' data-extend_tit=''>点击设置审批人</div><div class=approvers></div></li>";				
				$(_this).before(html);				
			}else{	
				$(_this).after(html);	
				$(".steps ol li").each(function(){			
				//后续节点的数据都加1								
					var aa = $(".steps ol li").index(this)-1;					
					if(aa>index){
						var bb=aa-1;
						$(".steps ol li").eq(aa).find('.node div').html(bb);
					}
			    });
					
			}
			
					
		});
		$(document).on('mouseover', '.steps ol li',function(){
			var len = $(".steps ol li").length;				
			if(len>4){
				var html='<span class="del">x</span>';
				$(this).prepend(html);
			}
		});
		$(document).on('mouseleave', '.steps ol li',function(){	
			var html='<span class="del">x</span>';
			$(this).find(".del").remove();
		});			
		$(document).on('click', '#addbtn',function(){
			var w_name=$(".w_name").eq(1).val();			
			if (w_name == '') {
				layer.msg('流程名称不能为空', {icon:5,time:1000});
				return false;
			}
			var len = $(".steps ol li").length-2;
			var formjson="";
			var actjson="";				
			for(var i=1;i<len;i++){
				var bb= $(".steps ol li").eq(i+1).find(".set-person").attr('data-uid');	
				var act= $(".steps ol li").eq(i+1).find(".set-person").attr('data-act');				
				if(bb=='undefined'||!bb){
					layer.msg('审批流程不能为空', {icon:5,time:1000});
					return false;
				}
				if(act=='undefined'||!act){
					layer.msg('操作不能为空', {icon:5,time:1000});
					return false;
				}
				formjson += "steps"+i+":"+bb + ",";	
				actjson += "steps"+i+":"+act + ",";		
			}
			if (formjson.length > 0 ) formjson = formjson.substring(0, formjson.length-1);			
			
			$.ajax({
				type:'post',
				dataType:'json',
				data:{w_name:w_name,step:formjson,act:actjson},
				url:'/workflow/workflow_add',
				error:function () {
					layer.msg('未知错误', {icon:5,time:1000});
				},
				success:function (data) {
					if (data.code == 1) {
						layer.msg('添加成功', {icon:6,time:1000}, function () {location.reload()});
					}else {
						layer.msg(data.msg, {icon:5,time:1000});
					}
				}
			});
		});
		$(document).on('click', '.set-person',function(){
			var wid = $(this).parent('li').find('.node div').html();
			var datauid = $(this).attr('data-uid');
			var dataact = $(this).attr('data-act');
			var dataextend_tit = $(this).attr('data-extend_tit');	
			var title_html=$(this).html();
			layer_index = layer.open({
				type: 2,
				title: '设置审批人',
				skin: 'layui-layer-rim', //加上边框
				area: ['600px', '400px'], //宽高
				shade: 0.5,
				content: '/role/index/wid/'+wid+'?uid='+datauid+'&act='+dataact+'&extend_tit='+dataextend_tit+'&title_html='+title_html,
			});			
		});
	
		
});

function del_lc(wid){
	layer.msg('去人要删除吗', {
	   time: 0 //不自动关闭
	  ,btn: ['确认', '取消']
	  ,yes: function(index){
		 layer.msg('正在删除。。。', {time: 1000})
		$.ajax({
				type:'post',
				dataType:'json',
				data:{wid:wid},
				url:'/workflow/workflow_del',
				error:function () {
					layer.msg('未知错误', {icon:5,time:1000});
				},
				success:function (data) {
					if (data.code == 1) {
						layer.msg('删除成功', {icon:6,time:1000}, function () {location.reload()});
					}else {
						layer.msg(data.msg, {icon:5,time:1000});
					}
				}
			});		  
		
		layer.close(index);		
	  }
	});
}
