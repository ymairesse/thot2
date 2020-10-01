<div id="selecteur" class="selecteur noprint" style="clear:both; {if ($listeDates|@count) < 1}display:none;{/if}">

	<form name="selecteur" id="formSelecteur" method="POST" action="index.php" role="form" class="form-inline">

		<select name="idRP" id="idRP">
			<option value="">Sélection de la date</option>
			{foreach from=$listeDates key=unIdRP item=uneDate}
			<option value="{$unIdRP}" {if isset($idRP) && ($unIdRP==$idRP)} selected="selected" {/if}>
				Réunion du {$uneDate.date}
			</option>
			{/foreach}
		</select>

		<button type="submit" class="btn btn-primary btn-sm">OK</button>

		<input type="hidden" name="action" value="reunionParents">
		<input type="hidden" name="etape" value="show">

	</form>

</div>

<script type="text/javascript">
	$(document).ready(function() {

		$("#date").change(function() {
			$("#formSelecteur").submit();
		})

	})
</script>
