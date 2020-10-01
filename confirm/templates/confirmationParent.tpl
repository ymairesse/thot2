{if $confirmation == 1}
    <p><strong>Votre inscription est maintenant confirmée.</strong></p>
    {elseif $confirmation == 0}
    <p><strong>Votre inscription était déjà confirmée.</strong></p>
    {elseif $confirmation == -1}
    <p>Problème technique ou bien le lien reçu par mail et que vous avez utilisé est incorrect. Nous ne pouvons confirmer votre inscription. Veuillez contacter l'administrateur.</p>
{/if}

{if $confirmation >= 0}
    <p>Vous pouvez vous connecter sur la plate-forme en utilisant votre nom d'utilisateur et votre mot de passe.</p>
    <p style="text-align:center" title="Cliquer ici"><a href="{$ADRESSETHOT}"><img src="{$ADRESSETHOT}/images/logoEcole.png" alt="{$ADRESSETHOT}"></a></p>
{/if}
