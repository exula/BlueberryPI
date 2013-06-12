<html>
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>In/Out Board</title>

	<script src="/js/jquery-1.9.1.min.js"></script>
	<script src="/js/timeago.js" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="/css/style.css">
	<script>


		var apiKey='aa625902eebedb7cf4fe100ada98996e';


		function create_user(data) {

			console.log(data);

			li = document.createElement("LI");
				div1= document.createElement("DIV");
					
					avatar = document.createElement("IMG");
					avatar.src = data.avatar;

				div2 = document.createElement("DIV");
					div2.id = data.username+"_name";
						div2.className = 'name';
						div2.appendChild(document.createTextNode(data.name));
						div2.appendChild(document.createElement("BR"));
					span1 = document.createElement("SPAN");
						span1.id = data.username+"_ago";
						span1.className ='subtext';

				div3 = document.createElement("DIV")
					div3.id=data.username+"_in";
					div3.className = 'buttons';


			div1.appendChild(avatar);
			div2.appendChild(span1);

			li.appendChild(div1);
			li.appendChild(div2);
			li.appendChild(div3);

			document.getElementById('inout').appendChild(li);

		}


		function error_display(errorMessage) {
			$('#error').show();
			$('#error').html("<h2>ERROR</h2>"+errorMessage+"</br><br/>");

		}

		function error_hide(errorMessage) {
			$('#error').hide();
		}


		origstatus = '';
		var sidebarLoaded = false;
		function refresh_status() {
			$.ajax({
			  url: "/api/",
			  data: "users=true&key="+apiKey,
			  success: function(data) {
				
			  	if( typeof data.error != "undefined" ) {
				
					error_display(data.error);
					return false;			  		
			  	} else {
			  		error_hide();
			  	}

				if(origstatus == data ) {
					return false;
				} else {
					
				}

				origstatus = data;
				
				var obj = data;


				//Update the site
				html = "<strong>"+obj.heading+"</strong><br/>"+obj.subheading;
				$('#header').html(html);

				if(sidebarLoaded == false) {

					sidebar = obj.sidebar;

					$('#sidebar').html(sidebar,function() {
						$('#sidebar').eval();
					});
					sidebarLoaded = true;
				}


				for (var i = obj.userlist.length - 1; i >= 0; i--) {

					thisName = obj.userlist[i];

					if($('#'+thisName+'_name').length == 0) {
						create_user(obj.users[thisName]);
					}
					
					$('#'+thisName+'_name').removeClass();
					$('#'+thisName+'_name').addClass('name_'+obj.users[thisName].status);							
					$('#'+thisName+'_in').html(obj.users[thisName].status);
					$('#'+thisName+'_in').removeClass();
					$('#'+thisName+'_in').addClass('buttons '+obj.users[thisName].status);
					$('#'+thisName+'_ago').html("Last update: "+jQuery.timeago(obj.users[thisName].lastseen));

				};

				}
			});		
		}
	</script>
	
	
</head>
<body id="content" onload="">
	<div id='error' style='display: none'></div>
	<div id="main">
		<div id="header">
			Loading...
		</div>
		
		<div id="inout">
		</div>
		<div id='sidebar'>
			
		</div>
	</div>
	</body>
	<script>
	window.setInterval(refresh_status,3000);
	</script>


<div id='powered' style='color: white; position: absolute; bottom: 0px'>
Powered By: 
<img height='51' src='/images/raspberry.gif'>
<img height='51' src='/images/php.gif'>
<img height='51' src='/images/mysql.png'>
<img height='51' src='/images/gnu.png'>
<img height='51' src='/images/bluetooth_logo.png'>
</div>
</html>