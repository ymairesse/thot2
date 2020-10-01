<select id="selectAttente" name="selectAttente" class="form-control">
    <option value="">Sélectionner un enseignant</option>
    {foreach from=$listeEncadrement key=acronyme item=data}
        <option
            value="{$acronyme}"
            data-nomprof="{$data.nom} {$data.prenom}">
            {($data.sexe=='F')?'Mme':'M.'} {$data.nom} {$data.prenom}
        </option>
    {/foreach}

</select>
