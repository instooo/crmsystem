$(document).ready(function(){	
	$('.tablelist tbody tr:odd').addClass('odd');
	$("#clickadd").click(function(){
		layer_index = layer.open({
			type: 1,
			title: '添加角色',
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
			var html = " <li><div class=add><div class='mini-icon icon-add'></div></div><div class=arr></div><div class=standard></div><div class=node><div>"+(index+1)+"</div></div><div class=set-person>点击设置审批人</div><div class=approvers></div></li>";			
			if(index==len){
				var html = " <li><div class=add><div class='mini-icon icon-add'></div></div><div class=arr></div><div class=standard></div><div class=node><div>"+(index)+"</div></div><div class=set-person>点击设置审批人</div><div class=approvers></div></li>";				
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
			var w_name=$("input[name='w_name']").val();
			if (w_name == '') {
				layer.msg('流程名称不能为空', {icon:5,time:1000});
				return false;
			}
			$.ajax({
				type:'post',
				dataType:'json',
				data:{w_name:w_name},
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
			layer_index = layer.open({
				type: 1,
				title: '设置审批人',
				skin: 'layui-layer-rim', //加上边框
				area: ['600px', '400px'], //宽高
				shade: 0.5,
				content: $('#bumen').html()
			});
			
		});
		$.fn.zTree.init($("#treeDemo"), setting, zNodes);
		$(document).on("mousedown",'.pulldown-input',showMenu);
});

		var setting = {
			check: {
				enable: true,
				chkboxType: {"Y":"", "N":""}
			},
			view: {
				dblClickExpand: true
			},
			data: {
				simpleData: {
					enable: true
				}
			},
			callback: {
				beforeClick: beforeClick,
				onCheck: onCheck
			}
		};

		 var zNodes =[
			{id:1, pId:0, name:"北京"},
			{id:4, pId:0, name:"河北省", open:true, nocheck:true},
			{id:41, pId:4, name:"石家庄"},
			{id:42, pId:4, name:"保定"},
			{id:5, pId:0, name:"广东省", open:true, nocheck:true},
			{id:51, pId:5, name:"广州"},
			{id:52, pId:5, name:"深圳"},
			{id:6, pId:0, name:"福建省", open:true, nocheck:true},
			{id:61, pId:6, name:"福州"},
			{id:62, pId:6, name:"厦门"},
		 ];

		function beforeClick(treeId, treeNode) {
			alert (1);
			var zTree = $.fn.zTree.getZTreeObj("treeDemo");
			zTree.checkNode(treeNode, !treeNode.checked, null, true);
			return false;
		}
		
		function onCheck(e, treeId, treeNode) {
alert (2);			
			var zTree = $.fn.zTree.getZTreeObj("treeDemo"),
			nodes = zTree.getCheckedNodes(true),
			v = "";
			for (var i=0, l=nodes.length; i<l; i++) {
				v += nodes[i].name + ",";
			}
			if (v.length > 0 ) v = v.substring(0, v.length-1);
			var cityObj = $("#citySel");
			cityObj.attr("value", v);	
		}

		
		function showMenu() {		
			$(".ztree").slideDown("fast");
		}
		function hideMenu() {
			$(".ztree").fadeOut("fast");			
		}	
