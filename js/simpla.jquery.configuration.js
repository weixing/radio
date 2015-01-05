$(document).ready(function(){
	
	//Sidebar Accordion Menu:
		
		//$("#main-nav li ul").hide(); // Hide all sub menus
		//$("#main-nav li a.current").parent().find("ul").slideToggle("slow"); // Slide down the current menu item's sub menu
		
		$("#main-nav li a.nav-top-item").click( // When a top menu item is clicked...
			function () {
				//$(this).parent().siblings().find("ul").slideUp("normal"); // Slide up all sub menus except the one clicked
				//$(this).next().slideToggle("normal"); // Slide down the clicked sub menu
				return false;
			}
		);
		
		$("#main-nav li a.no-submenu").click( // When a menu item with no sub menu is clicked...
			function () {
				window.location.href=(this.href); // Just open the link instead of a sub menu
				return false;
			}
		); 

    // Sidebar Accordion Menu Hover Effect:
		
		$("#main-nav li .nav-top-item").hover(
			function () {
				$(this).stop().animate({ paddingRight: "25px" }, 200);
			}, 
			function () {
				$(this).stop().animate({ paddingRight: "15px" });
			}
		);

    //Minimize Content Box
		
		$(".content-box-header h3").css({ "cursor":"s-resize" }); // Give the h3 in Content Box Header a different cursor
		$(".closed-box .content-box-content").hide(); // Hide the content of the header if it has the class "closed"
		$(".closed-box .content-box-tabs").hide(); // Hide the tabs in the header if it has the class "closed"
		
		$(".content-box-header h3").click( // When the h3 is clicked...
			function () {
			  $(this).parent().next().toggle(); // Toggle the Content Box
			  $(this).parent().parent().toggleClass("closed-box"); // Toggle the class "closed-box" on the content box
			  $(this).parent().find(".content-box-tabs").toggle(); // Toggle the tabs
			}
		);

    // Content box tabs:
		
		$('.content-box .content-box-content div.tab-content').hide(); // Hide the content divs
		$('ul.content-box-tabs li a.default-tab').addClass('current'); // Add the class "current" to the default tab
		$('.content-box-content div.default-tab').show(); // Show the div with class "default-tab"
		
		$('.content-box ul.content-box-tabs li a').click( // When a tab is clicked...
			function() { 
				$(this).parent().siblings().find("a").removeClass('current'); // Remove "current" class from all tabs
				$(this).addClass('current'); // Add class "current" to clicked tab
				var currentTab = $(this).attr('href'); // Set variable "currentTab" to the value of href of clicked tab
				$(currentTab).siblings().hide(); // Hide all content divs
				$(currentTab).show(); // Show the content div with the id equal to the id of clicked tab
				return false; 
			}
		);

    //Close button:
		
		$(".close").click(
			function () {
				$(this).parent().fadeTo(400, 0, function () { // Links with the class "close" will close parent
					$(this).slideUp(400);
				});
				return false;
			}
		);

    // Alternating table rows:
		
		$('tbody tr:even').addClass("alt-row"); // Add class "alt-row" to even table rows

    // Check all checkboxes when the one in a table head is checked:
		
		$('.check-all').click(
			function(){
				$(this).parent().parent().parent().parent().find("input[type='checkbox']").attr('checked', $(this).is(':checked'));   
			}
		);

    // Initialise Facebox Modal window:
		
		$('a[rel*=modal]').facebox(); // Applies modal window to any link with attribute rel="modal"

    // Initialise jQuery WYSIWYG:
		
		$(".wysiwyg").wysiwyg(); // Applies WYSIWYG editor to any textarea with the class "wysiwyg"



//自定义行为
		$('#search').focus();

		$("#userpay_form_pay_count").keyup(	//自动补全组网费金额
		function()
		{
			var count	=	parseInt($(this).val());
			var amount	=	parseInt($('#pay_amount').attr('avalue'));
			$(this).val(count);
			var total_amount	=	amount*count;
			$('#pay_amount').val(total_amount);
		});

		$("#user_form_username").blur(	//自动补全姓名拼音
		function()
		{
			/*//alert('a');
			var username	=	$(this).val();
			//alert(username);
			$.getJSON('/api/index/'+encodeURIComponent(username), 
				function(data){
					$("#user_form_userpinyin").val(data);
			})*/
			getNameSpell();
		});

		$("#user_form_userIDcard").blur(	//自动补全生日
		function()
		{
			var userIDcard	=	$(this).val();
			var retCheck	=	checkIdcard(userIDcard);
			if(retCheck	!=	'0')
			{
				//alert('a');
				$(this).focus();
				$(this).select();
				alert(retCheck);
			}
			//alert(username);
			var birth	=	userIDcard.substr(6,8);
			var birth_format	=	birth.substr(0,4)+'-'+birth.substr(4,2)+'-'+birth.substr(6,2);
			$("#inputDate_birth").val(birth_format);

			var sex_tag	=	userIDcard.substr(14,3);
			sex_tag	=	sex_tag%2;
			if(sex_tag	==	1)
			{
				$('#user_form_usersex').val('男');
			}
			else
			{
				$('#user_form_usersex').val('女');
			}
		});

		$("#id_end").blur(	//生成号段，检测号段
		function()
		{
			var id_end	=	parseInt($(this).val());
			var id_start	=	parseInt($('#id_start').val());
			var new_pici_count	=	parseInt($('#new_pici_count').val());
			if((id_end-id_start+1)	<	new_pici_count)
			{
				alert('输入的号段('+(id_end-id_start+1)+')少于需要生成号码的会员数('+new_pici_count+')');
			}
		});


		$("a[id^='testpass_']").click(	//考试不通过
		function()
		{
			var uid	=	parseInt($(this).attr('uid'));
			if(confirm('该会员考试确定不通过吗？'))
			{
				location.href="/user/pass/"+uid+"/0/";
			}
		});

		$("a[id^='testpassb_']").click(	//考试不通过
		function()
		{
			var uid	=	parseInt($(this).attr('uid'));
			if(confirm('该会员考试确定不通过吗？'))
			{
				location.href="/user/passb/"+uid+"/0/";
			}
		});

		$("a[id^='testpassc_']").click(	//考试不通过
		function()
		{
			var uid	=	parseInt($(this).attr('uid'));
			if(confirm('该会员考试确定不通过吗？'))
			{
				location.href="/user/passc/"+uid+"/0/";
			}
		});
		
		$("a[id^='photodel_']").click(	//删除照片
		function()
		{
			var photoid	=	parseInt($(this).attr('pid'));
			if(confirm('确定要删除这张照片吗？删除之后无法恢复'))
			{
				location.href="/user/photodel/"+photoid+"/";
			}
		});

		$("a[id^='del_pay_']").click(	//删除照片
		function()
		{
			var pid	=	parseInt($(this).attr('pid'));
			if(confirm('确定要删除这条缴费记录吗？删除之后无法恢复'))
			{
				location.href="/user/paydel/"+pid+"/";
			}
		});

		$("a[id^='delete_dev_']").click(	//删除设备
		function()
		{
			var did	=	parseInt($(this).attr('did'));
			if(confirm('确定要删除这台设备吗？'))
			{
				location.href="/device/deletedev/"+did+"/";
			}
		});

		$("a[id^='yanjiuserdel_']").click(	//从验机批次中删除用户
		function()
		{
			var uid	=	parseInt($(this).attr('uid'));
			var pid	=	parseInt($(this).attr('pid'));
			if(confirm('确定从验机批次中删除次用户吗？删除之后无法恢复'))
			{
				location.href="/user/yanjideluser/"+pid+"/"+uid+"/";
			}
		});


		$("#user_form").submit(	//生成号段，检测号段
		function()
		{
			var userIDcard	=	$('#user_form_userIDcard').val();
			var retCheck	=	checkIdcard(userIDcard);
			if(retCheck	!=	'0')
			{
				//alert('a');
				$('#user_form_userIDcard').focus();
				$('#user_form_userIDcard').select();
				alert(retCheck);
				return false
			}

			var sex_tag	=	userIDcard.substr(14,3);
			sex_tag	=	sex_tag%2;
			if(sex_tag	==	1)
			{
				var sex_tag_str	=	'男';
			}
			else
			{
				var sex_tag_str	=	'女';
			}
			if($('#user_form_usersex').val()	!=	sex_tag_str)
			{
				alert('性别和身份证信息不符');
				return false;
			}
		});

		$("#user_type_2").click(
		function()
		{
			$('#crac_check').attr('checked',false);

		});

		$("#crac_check").click(
		function()
		{
			$('#user_type_1').attr('checked',true);

		});

		//日历输入框
		$('#inputDate_zhuce').DatePicker({
			format:'Y-m-d',
			date: $('#inputDate_zhuce').val(),
			current: $('#inputDate_zhuce').val(),
			starts: 1,
			position: 'right',
			onBeforeShow: function(){
				$('#inputDate_zhuce').DatePickerSetDate($('#inputDate_zhuce').val(), true);
			},
			onChange: function(formated, dates){
				$('#inputDate_zhuce').val(formated);
				$('#inputDate_zhuce').DatePickerHide();
			}
		});

		$('#inputDate_birth').DatePicker({
			format:'Y-m-d',
			date: $('#inputDate_birth').val(),
			current: $('#inputDate_birth').val(),
			starts: 1,
			position: 'right',
			onBeforeShow: function(){
				$('#inputDate_birth').DatePickerSetDate($('#inputDate_birth').val(), true);
			},
			onChange: function(formated, dates){
				$('#inputDate_birth').val(formated);
				$('#inputDate_birth').DatePickerHide();
			}
		});

		$('#inputDate').DatePicker({
			format:'Y-m-d',
			date: $('#inputDate').val(),
			current: $('#inputDate').val(),
			starts: 1,
			position: 'right',
			onBeforeShow: function(){
				var time_tmp	=	$('#inputDate').val();
				if(time_tmp	==	'')
				{
					var myDate = new Date();
					time_tmp	=	myDate.getFullYear()+'-'+(myDate.getMonth()+1)+'-'+myDate.getDate();
				}
				$('#inputDate').DatePickerSetDate(time_tmp, true);
			},
			onChange: function(formated, dates){
				$('#inputDate').val(formated);
				$('#inputDate').DatePickerHide();
			}
		});

		$('#inputDate_start').DatePicker({
			format:'Y-m-d',
			date: $('#inputDate_start').val(),
			current: $('#inputDate_start').val(),
			starts: 1,
			position: 'right',
			onBeforeShow: function(){
				var time_tmp	=	$('#inputDate_start').val();
				if(time_tmp	==	'')
				{
					var myDate = new Date();
					time_tmp	=	myDate.getFullYear()+'-'+(myDate.getMonth()+1)+'-'+myDate.getDate();
				}
				$('#inputDate_start').DatePickerSetDate(time_tmp, true);
			},
			onChange: function(formated, dates){
				$('#inputDate_start').val(formated);
				$('#inputDate_start').DatePickerHide();
			}
		});

		$('#inputDate_end').DatePicker({
			format:'Y-m-d',
			date: $('#inputDate_end').val(),
			current: $('#inputDate_end').val(),
			starts: 1,
			position: 'right',
			onBeforeShow: function(){
				var time_tmp	=	$('#inputDate_end').val();
				if(time_tmp	==	'')
				{
					var myDate = new Date();
					time_tmp	=	myDate.getFullYear()+'-'+(myDate.getMonth()+1)+'-'+myDate.getDate();
				}
				$('#inputDate_end').DatePickerSetDate(time_tmp, true);
			},
			onChange: function(formated, dates){
				$('#inputDate_end').val(formated);
				$('#inputDate_end').DatePickerHide();
			}
		});

		$('#inputPayDate_start').DatePicker({
			format:'Y-m-d',
			date: $('#inputPayDate_start').val(),
			current: $('#inputPayDate_start').val(),
			starts: 1,
			position: 'right',
			onBeforeShow: function(){
				var time_tmp	=	$('#inputPayDate_start').val();
				if(time_tmp	==	'')
				{
					var myDate = new Date();
					time_tmp	=	myDate.getFullYear()+'-'+(myDate.getMonth()+1)+'-'+myDate.getDate();
				}
				$('#inputPayDate_start').DatePickerSetDate(time_tmp, true);
			},
			onChange: function(formated, dates){
				$('#inputPayDate_start').val(formated);
				$('#inputPayDate_start').DatePickerHide();
			}
		});

		$('#inputPayDate_end').DatePicker({
			format:'Y-m-d',
			date: $('#inputPayDate_end').val(),
			current: $('#inputPayDate_end').val(),
			starts: 1,
			position: 'right',
			onBeforeShow: function(){
				var time_tmp	=	$('#inputPayDate_end').val();
				if(time_tmp	==	'')
				{
					var myDate = new Date();
					time_tmp	=	myDate.getFullYear()+'-'+(myDate.getMonth()+1)+'-'+myDate.getDate();
				}
				$('#inputPayDate_end').DatePickerSetDate(time_tmp, true);
			},
			onChange: function(formated, dates){
				$('#inputPayDate_end').val(formated);
				$('#inputPayDate_end').DatePickerHide();
			}
		});

		$('#idcard_reader').click(
		function(){
			$.getJSON('http://192.168.0.101:8080/run.php?callback=?', 
				function(data){
					//alert(data[0]);
					if(data[0]	!=	'0')
					{
						alert('身份证读取失败，请将身份证重新放置在读卡器上，或重启读卡器');
					}
					else
					{
						//alert(data);
						$('#user_form_username').val(data[1].trim());
						$('#user_form_usersex').val(data[2].trim());
						$('#user_form_nation').val(data[3].trim());
						$('#user_form_userIDcard').val(data[9].trim());
						var birthday_tmp	=	data[4].trim();
						birthday_tmp	=	birthday_tmp.substr(0,4)+'-'+birthday_tmp.substr(4,2)+'-'+birthday_tmp.substr(6,2);
						$('#inputDate_birth').val(birthday_tmp);
						$('#user_form_idcardaddr').val(data[5].trim());
						getNameSpell();
					}
			})
		});


		$('#page_go_button').click(
		function(){
			var page_num	=	parseInt($('#page_go_num').val());
			var base_url	=	$(this).attr('baseurl');
			//alert(page_num);
			//alert(base_url);
			if(page_num>0)
			{
				location.href=base_url+page_num;
			}
		});
});
