<ul class="list-unstyled">
    {foreach from=$listeSujets key=idSujet item=unSujet}
        <li>
            <button type="button"
                class="btn btn-primary btn-block btn-sujet"
                data-idsujet="{$unSujet.idSujet}"
                data-idcategorie="{$unSujet.idCategorie}"
                >Thème: {$unSujet.libelle} - {$unSujet.sujet}<br>
            <span class="micro">{$unSujet.nomProf} le {$unSujet.ladate} à {$unSujet.heure}</span>
            </button>
    </li>
    {/foreach}
</ul>
