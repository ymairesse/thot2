<ul class="list-unstyled">
    {foreach from=$listeHeures key=id item=uneHeure}
    <li>
        <label class="radio-inline">
            <input type="radio" class="uneHeure" name="heure" value="{$uneHeure.heure|truncate:5:''}" data-id="{$id}"> <span>{$uneHeure.heure|date_format:'%H:%M'}</span>
        </label>
    </li>
    {/foreach}
</ul>

<script type="text/javascript">
    $(document).ready(function() {

        $(".uneHeure").click(function() {
            $('.uneHeure').closest('li').removeClass('selected');
            $(this).closest('li').addClass('selected');
            var date = $(".uneDate:checked").val();
            var texteDate = $(".uneDate:checked").next().text();
            var heure = $(".uneHeure:checked").val();
            var id = $(this).data('id');

            $("#dateRV").html(texteDate);
            $("#heureRV").html(heure);

            $("#waitDateHeure").addClass('hidden');
            $("#formInscription").removeClass('hidden');

            $("#id").val(id);

        })

    })
</script>
