<div id="selecteur" class="noprint" style="clear:both">
	<form name="selecteur" id="formSelecteur" method="POST" action="index.php">

		<select name="classe" id="selectClasse">
			<option value="">Classe</option>
			{foreach from=$listeClasses item=uneClasse}
			<option value="{$uneClasse}" {if isset($classe) && ($uneClasse==$ classe)} selected="selected" {/if}>{$uneClasse}</option>
			{/foreach}
		</select>

		<span id="choixEleve">
			{include file="listeEleves.tpl"}
		</span>

		<input type="submit" value="OK" name="OK" id="envoi" style="display:none">

		<input type="hidden" name="etape" value="{$etape|default:Null}">
		<input type="hidden" name="action" value="{$action}">
		<input type="hidden" name="mode" value="{$mode}">
	</form>
</div>

<script type="text/javascript">
	$(document).ready(function() {

		$("#formSelecteur").submit(function() {
			if ($("#selectEleve").val() == '')
				return false;
		})

		$("#selectClasse").change(function() {
			// on a choisi une classe dans la liste déroulante
			var classe = $(this).val();
			if (classe != '') {
				$("#envoi").show();
			}
			// la fonction listeEleves.inc.php renvoie la liste déroulante des élèves de la classe sélectionnée
			$.post("inc/listeEleves.inc.php", {
					'classe': classe
				}, // ne pas oublier les "espaces" autour...
				function(resultat) {
					$("#choixEleve").html(resultat)
				}
			)
		});

		$("#choixEleve").on("change", "#selectEleve", function() {
			if ($(this).val() != '') {
				$("#envoi").show();
				// si la liste de sélection des élèves renvoie une valeur significative
				// le formulaire est soumis
				$("#formSelecteur").submit();
			}
		})




	})
</script>
