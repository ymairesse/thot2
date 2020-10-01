<div id="selecteur" class="selecteur noprint" style="clear:both; {if ($listeDates|@count) <= 1}display:none;{/if}">

	<form name="selecteur" id="formSelecteur" method="POST" action="index.php" role="form" class="form-inline">

		<select name="date" id="date">
			<option value="">Sélection de la date</option>
			{foreach from=$listeDates item=uneDate}
			<option value="{$uneDate}" {if isset($date) && ($uneDate==$date)} selected="selected" {/if}>
				Réunion du {$uneDate}
			</option>
			{/foreach}
		</select>

		<button type="submit" class="btn btn-primary btn-sm">OK</button>

		<input type="hidden" name="action" value="{$action}">
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
