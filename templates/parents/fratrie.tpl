<div class="panel panel-info">
    <div class="panel-heading">
        <h3>Frères et sœurs de: <span id="nomEleve">{$nomEleve}</span></h3>
    </div>
    <div class="panel-body">
        <p>Dans ce panneau figurent tous les élèves membres de la famille et que vous avez déjà déclarés.</p>
        <p>Pour passer d'un profil d'élève à l'autre, il vous suffit de cliquer sur le nom de l'enfant correspondant.</p>

        <ul class="list-unstyled">

            {foreach from=$eleves key=matricule item=data}
            <li data-matricule="{$matricule}">
                <div class="btn-group btn-group-justified">
                    <div class="btn-group" style="width:90%">
                        <a href="javascript:void(0)"
                            data-user="{$data.userParent}"
                            class="btn btn-default frereSoeur{if $matricule == $identite.matricule} gras{/if}">
                        {$data.nom} -> {$data.userEleve}
                        </a>
                    </div>
                </div>
            </li>
            {/foreach}

        </ul>
    </div>
</div>
