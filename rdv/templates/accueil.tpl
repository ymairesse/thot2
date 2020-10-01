<!DOCTYPE html>
<html lang="fr">

<head>
	<title>{$TITREGENERAL}</title>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">

	<link rel="stylesheet" href="screen.css" type="text/css" media="screen">
	<link rel="stylesheet" href="print.css" type="text/css" media="print">
	<link rel="stylesheet" href="bootstrap/fa/css/font-awesome.min.css" type="text/css" media="screen, print">

	<script type="text/javascript" src="js/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="js/jquery.validate.js"></script>
</head>

<body>
	<div class="container">

		{include file="entete.tpl"}
		{if (isset($message) && $message == 'faux')}
		<div class="alert alert-dismissable alert-danger">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<p>Nom d'utilisateur ou mot de passe incorrect</p>
			<p>Votre tentative d'accès, votre adresse IP et le nom de votre fournisseur d'accès ont été enregistrés.</p>
		</div>
		{/if}

		<div class="row">

			<div class="panel-group" id="accordion">

				{include file="accueil/panel1.tpl"}
				{* include file="accueil/panel2.tpl" *}
				{* include file="accueil/panel3.tpl" *}

			</div>
			<!-- panel-group -->

		</div>
		<!-- row -->

	</div>
	<!-- container -->

	{include file="footer.tpl"}

	<script type="text/javascript">
		$(document).ready(function() {

			$("input:enabled").eq(0).focus();

			$("#login").validate({
				rules: {
					userName: {
						required: true
					},
					mdp: {
						required: true
					}
				},
				errorElement: "span"
			});

			$("*[title]").tooltip();

			$(".pop").popover({
				trigger: 'hover'
			});

		})
	</script>


</body>



</html>
