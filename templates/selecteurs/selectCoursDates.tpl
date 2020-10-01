<form class="form-inline">
    <select class="form-control" name="selectCoursDates" id="selectCoursDates">
        <option value="">Sélectionner un cours</option>
        {foreach from=$listeCours key=titu item=leCours}
        <option value="{$leCours.coursGrp}">
            {$leCours.libelle} {$leCours.nbheures}h {if $leCours.sexe=='F'}Mme{else}M.{/if} {$leCours.nom}
        </option>
        {/foreach}
    </select>

    <input type="date" name="startDate" id="startDate" class="form-control datepicker" value="{$startDate|default:''}">
    <input type="date" name="endDate" id="endDate" class="form-control datepicker" value="{$endDate|default:''}">
</form>

<script type="text/javascript">

$(document).ready(function(){

	$("#startDate").datepicker({
		clearBtn: true,
		language: "fr",
		calendarWeeks: true,
		autoclose: true,
		todayHighlight: true
	})

})

</script>
