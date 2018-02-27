<?php
?>
<html>
    <head>
        <meta charset="utf-8" />
        <title>Envoi du net</title>
    </head>
    <h1>Destination</h1>
    <body><br>
        <form method="POST" action="control.php">
            Code postal : <input id="toPostcode" name="toPostcode" type="text" ><br><br>
            Ville       : <input id="toCity"     name="toCity"     type="text" ><br><br>
            Pays        : <input id="toCountry"  name="toCountry"  type="text" ><br><br>
            <input type="submit"  value="Valider" />
        </form><br>
    </body>
</html>


<?php

?>