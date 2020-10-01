<p>Quelqu'un, sans doute vous, à l'adresse IP <strong>{$identiteReseau.ip}</strong> ({$identiteReseau.hostname}) a utilisé votre adresse mail <strong>{$rv.email}</strong> comme identifiant pour demander une pré-inscription d'un futur-élève dans notre établissement scolaire.</p>
<p>Si vous n'avez pas fait cette demande, veuillez transférer ce mail à <a href="mailto:admin@isnd.be">admin@isnd.be</a> qui prendra les mesures nécessaires.</p>

<p>Si vous êtes bien à l'origine de cette demande, veuillez lire attentivement ce qui suit.</p>
<p>Nous avons bien noté votre demande de rendez-vous pour une pré-inscription de votre enfant <strong>{$rv.prenom} {$rv.nom}</strong>.<br>
Vous avez demandé à obtenir un rendez-vous le {$rv.date} à {$rv.heure}. Nous avons temporairement réservé ce moment à votre intention.</p>
<br>
<p><strong>Pour confirmer votre demande</strong>, veuillez cliquer sur le lien ci-dessous ou recopier ce lien dans votre navigateur.</p>
<a href="http://isnd.be/thot/rdv/index.php?action=confirm&amp;token={$rv.md5conf}">http://isnd.be/thot/rdv/index.php?action=confirm&amp;token={$rv.md5conf}</a>
<p>Attention, ce lien ne sera actif que <strong>pour une durée limitée</strong>. Au-delà du délai de 4h après la demande sur notre site web, nous considérerons que vous renoncez à ce rendez-vous et la place redeviendra libre pour une autre personne.</p>
