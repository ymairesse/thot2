<div class="panel panel-info">
    <div class="panel-heading">
        <h3>Frères et sœurs: <span id="nomEleve">{$nomEleve}</span></h3>
    </div>
    <div class="panel-body">
        <ul class="list-unstyled">

            {foreach from=$eleves key=matricule item=data}
            <li data-matricule="{$matricule}">
                <div class="btn-group btn-group-justified">
                    <div class="btn-group" style="width:90%">
                        <a href="javascript:void(0)" data-user="{$data.userName}" class="btn btn-default frereSoeur{if $data.userName == $userName} gras{/if}">{$data.nom} ({$data.userName})</a>
                    </div>
                    <div class="btn-group" style="width:10%">
                        <button type="button" class="btn btn-danger delFratrie" data-username="{$data.userName}" data-userproprio="{$userName}" title="Supprimer le lien"{if $data.userName == $userName} disabled{/if}>X</button>
                    </div>
                </div>
            </li>
            {/foreach}

        </ul>
    </div>
</div>
