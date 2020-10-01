<ul class="list-unstyled">
    {foreach from=$listeElevesCours key=matricule item=dataEleve}
        <li><a href="javascript:void(0)" class="mail" data-mail="{$dataEleve.mail}">{$dataEleve.classe} - {$dataEleve.nom} {$dataEleve.prenom}</a></li>
    {/foreach}
</ul>
