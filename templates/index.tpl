<!DOCTYPE html>
<html lang="fr">

<head>
	<title>{$TITREGENERAL}</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

	<link rel="stylesheet" href="screen.css" type="text/css" media="screen">
	<link rel="stylesheet" href="print.css" type="text/css" media="print">
	<link rel="stylesheet" href="bootstrap/fa/css/font-awesome.min.css" type="text/css" media="screen, print">
	<link rel="stylesheet" href="font-awesome-animation.css" type="text/css" media="screen">
	<link rel="stylesheet" href="js/bootstrapDatepicker/css/datepicker3.css" media="all">
	<link rel="stylesheet" href="js/boostrapTimepicker/css/bootstrap-timepicker.css" media="screen">

	<script type="text/javascript" src="js/jquery-2.1.3.min.js"></script>
	<script src="js/jquery-ui_1.12.1_.min.js"></script>
	<script type="text/javascript">
		// Change JQueryUI plugin names to fix name collision with Bootstrap.
		// https://www.ryadel.com/en/using-jquery-ui-bootstrap-togheter-web-page/
		$.widget.bridge('uitooltip', $.ui.tooltip);
		$.widget.bridge('uidatepicker', $.ui.datepicker);
	</script>
	<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/jquery.validate.js"></script>
	<script type="text/javascript" src="js/bootbox.min.js"></script>

	<script type="text/javascript" src="js/moment-with-locales.js"></script>
	<script type="text/javascript" src="fc2/fullcalendar.js"></script>
	<script type="text/javascript" src="fc2/locale/fr.js"></script>
	<script type="text/javascript" src="js/boostrapTimepicker/js/bootstrap-timepicker.min.js"></script>
	<script type="text/javascript" src="js/bootstrapDatepicker/js/bootstrap-datepicker.js"></script>
	<script type="text/javascript" src="js/bootstrapDatepicker/js/locales/bootstrap-datepicker.fr.js"></script>

	<script src="js/fbLike/facebook-reactions.js"></script>
	<link rel="stylesheet" href="js/fbLike/stylesheet.css" media="screen">

	<script type="text/javascript" src="js/jsCookie/src/js.cookie.js"></script>

	<link rel="stylesheet" href="fc2/fullcalendar.css" media="screen">
	<link rel="stylesheet" href="css/animate/animate.min.css">

	<link rel="stylesheet" href="summernote/summernote.min.css">
	<script src="summernote/summernote.min.js"></script>
	<script src="summernote/lang/summernote-fr-FR.min.js"></script>

</head>

<body>
	<div class="container-fluid">

		{include file="entete.tpl"}
		{include file="menu.tpl"}

		{if isset($selecteur)}
			{include file="$selecteur.tpl"}
		{/if}

	{if (isset($message))}
	<div class="alert alert-dismissable alert-{$message.urgence|default:'info'}
		{if (!(in_array($message.urgence,array('danger','warning'))))} auto-fadeOut{/if}">
		<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
		<h4><span class="glyphicon
			{if in_array($message.urgence,array('success','info'))}glyphicon-ok{/if}
			{if in_array($message.urgence,array('danger','warning'))}glyphicon-exclamation-sign{/if}
			"></span> {$message.title}</h4>
		<p>{$message.texte}</p>
	</div>
	{/if}

		<div id="corpsPage">
			{if isset($corpsPage)}
			{include file="$corpsPage.tpl"}
			{/if}
		</div>

	</div>
	<!-- container -->

	{include file="footer.tpl"}

	<script type="text/javascript">

		window.setTimeout(function() {
    $(".auto-fadeOut").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove();
	    });
	}, 3000);

		$(document).ready(function() {

			$("input:enabled").eq(0).focus();

			$('[data-toggle="popover"]').popover({
				trigger : 'hover'
			});

		})
	</script>

</body>

</html>
