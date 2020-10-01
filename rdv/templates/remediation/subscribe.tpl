<h5>Date: <strong>{$offre.date} {$offre.heure}</strong> - Durée: {$offre.duree|truncate:5:''} - Local: {$offre.local} <span class="pull-right">[{if $offre.sexe == 'F'}Mme{else}M.{/if} {$offre.initiale} {$offre.nom}]</span></h5>
<p>{$offre.title}</p>
<div class="bordure">
    {$offre.contenu}
</div>
<p class="text-danger">Si, pour une raison de force majeure, je ne peux pas me présenter à cette remédiation, je m'engage à prévenir le professeur dans les meilleurs délais.</p>

<div class="btn-group pull-right">
    <button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>
    <button type="button" class="btn btn-success" id="btnConfirmSubscribe" data-idoffre="{$offre.idOffre}">Je confirme mon inscription</button>
</div>
<div class="clearfix">
</div>
