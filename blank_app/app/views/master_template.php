<!DOCTYPE html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7"> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8"> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<html>
	
<head>
	
  <meta http-equiv="Content-type" content="text/html;charset=UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <title>Kurbi Health, Inc.</title>
  
  <!--[if lt IE 10]>
	      <script src="http://html5shim.googlecode.com/svn/trunk/html5.js" type="text/javascript"></script>
  <![endif]-->
  
  <!-- FAVICON -->
  <link rel="icon" type="image/png" href="/img/favicon.png">

  <!-- STYLESHEETS -->
  <link href="<?php echo ROOT_URL; ?>/css/custom.css" rel="stylesheet" type="text/css">
  <link href="<?php echo ROOT_URL; ?>/css/bootstrap.min.css" rel="stylesheet" type="text/css">

  <!--[if IE]>
    <link rel="stylesheet" type="text/css" href="/css/ie.css" />
  <![endif]-->
  <link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.0/themes/base/jquery-ui.css" type="text/css" />
  <link rel="stylesheet" href="<?php echo ROOT_URL; ?>/css/mobile.css" TYPE="text/css" />
  <link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
  <link rel="stylesheet" type="text/css" href="<?php echo ROOT_URL; ?>/js/jqplot/jquery.jqplot.css" />
  <link href='<?php echo ROOT_URL; ?>/css/fullcalendar.css' rel='stylesheet' />
  <link href='<?php echo ROOT_URL; ?>/css/fullcalendar.print.css' rel='stylesheet' media='print' />

  <!-- FONTS -->
  <link href='http://fonts.googleapis.com/css?family=Droid+Serif:400,700' rel='stylesheet' type='text/css' />
  <link href='http://fonts.googleapis.com/css?family=Crete+Round' rel='stylesheet' type='text/css' />
  <link href='http://fonts.googleapis.com/css?family=Arimo:400,700' rel='stylesheet' type='text/css' />
  
  <!-- JAVASCRIPTS -->
  <?php if(isset($pageData['current_pagegroup']) && $pageData['current_pagegroup'] == 'calendar'){ ?>
  <script type="text/javascript" src='<?php echo ROOT_URL; ?>/js/fullcalendar/jquery-1.9.1.min.js'></script>
  <script type="text/javascript" src='<?php echo ROOT_URL; ?>/js/fullcalendar/jquery-ui-1.10.2.custom.min.js'></script>
  <script type="text/javascript" src='<?php echo ROOT_URL; ?>/js/fullcalendar/fullcalendar.min.js'></script>
  <?php }else{ ?>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
  <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.9.1/jquery-ui.min.js"></script>
  <script type="text/javascript" src="<?php echo ROOT_URL; ?>/js/jqplot/jquery.jqplot.min.js"></script>
  <script type="text/javascript" src="<?php echo ROOT_URL; ?>/js/jqplot/plugins/jqplot.canvasAxisTickRenderer.min.js"></script>
  <?php } ?>
  <script type="text/javascript" async src="<?php echo ROOT_URL; ?>/js/bootstrap/bootstrap.js"></script>
  <script type="text/javascript" src="<?php echo ROOT_URL; ?>/js/site.js"></script>
  
</head>

<?php $user = currentUser(); ?> 
<?php if($user == FALSE){ ?>
<body id="home">
<?php }else{ ?>
<body id="interior">
<?php } ?>	

	<?php include MVC_APP_PATH.'app/views/_layouts/header.php'; ?>	
	
	<div id="wrapper">
		<?php 
			if(is_file($body))
				require $body;
			else
				echo $body;
		?>
	</div><!-- END #wrapper -->

	<div class="clear"></div>

	<?php include MVC_APP_PATH.'app/views/_layouts/footer.php'; ?>
	
	<div id="dialog-confirm" title="Information" style="display: none;">
		<div id="dialog-header"><span id="dialog-title">Dialog</span><div id="dialog-close-button"><i class="icon-remove"></i></div></div>
		<div id="dialog-body"></div>
		<div id="dialog-footer"></div>
	</div>

	<script type="text/javascript">
	
	$(document).ready(function() {
			
		$("#dialog-confirm").on('click','#dialog-close-button',function(){
			$("#dialog-confirm").hide();
			$("#dialog-body").empty();
			$("#dialog-title").empty();
			return false;
		});
	
	});
	
	/**
	 * DIALOG STUFF
	 */
	
	function dialogLoadUrl(url,title){
		$('#dialog-body').load(url,'',function(){
			_dialogPopup(title);
		});
	}
	
	function dialogShowText(textMsg,title){
		$("#dialog-body").text(textMsg);
		_dialogPopup(title);
	}
	
	function _dialogPopup(title){
		var windowHeight = $(window).height();
		var windowWidth = $(window).width();
		var origDialogWidth = $('#dialog-confirm').width();
		var availWidth = Math.round(windowWidth * .8);
		if(origDialogWidth < availWidth){
			dialogWidth = origDialogWidth;
		}else{
			dialogWidth = availWidth;
		}

		$('#dialog-confirm').css('left', function(index){ return ((windowWidth - dialogWidth) / 2); });
		
		$('#dialog-confirm').css('width', function(index){ return dialogWidth; });
		
		$('#dialog-footer').css('bottom', function(index){ return $("#dialog-confirm").height() * -1; });
		
		$('#dialog-body').css('max-height', function(index){ return windowHeight - 100; });
		$('#dialog-body').css('width', function(index){ return dialogWidth - 30; });
	
		$('#dialog-title').text(title);
		
		$("#dialog-confirm").show();
	}
	</script>
	
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
	
	  ga('create', 'UA-41168529-1', 'gokurbi.com');
	  ga('send', 'pageview');
	</script>
</body>
</html>