<?php	
    ### --------------------------------------------------------------------###
     function afficher($tableau, $die = false)
     {
         if (count($tableau) == 0) {
             echo 'Tableau vide';
         } else {
             echo '<pre>';
             print_r($tableau);
             echo '</pre>';
             echo '<hr />';
         }
         if ($die) {
             die();
         }
     }

    ### --------------------------------------------------------------------###
     function afficher_silent($tableau, $die = false)
     {
         echo '<!-- ';
         afficher($tableau, $die);
         echo '-->';
     }
