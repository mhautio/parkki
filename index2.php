<!-- Mika Autio // Parkkitilanteen haku rajapinnasta ja näyttö // VERSIO 1.1 -->

<?php
// Muuttujat automaattista sivun päivittämistä varten
$page = $_SERVER['PHP_SELF'];
$sec = "30"; // Tähän laitetaan aika sekunteina, jonka jälkeen sivu ladataan uudelleen (refresh-aika) eli näin data pysyy ajantasaisena (viivettä sec-muuttujan verran)
$secforjs = $sec * 1000 / 2; // Kaava kuvien vaihteluun eli kuvien vaihteluaika on aina puolet sivun uudelleenlatausajasta
?>

<!-- HTML- ja CSS-määrittelyt -->
<!DOCTYPE html>
<html>
  <head>
<!-- Laitetaan sivu latautumaan uudelleen sec-muuttujassa määritellyn ajan jälkeen -->
  <meta http-equiv="refresh" content="<?php echo $sec ?>;URL='<?php echo $page ?>'">
    <style>
    body { margin: 0px; background: black; }
    </style>

        <!-- Kuvien vaihtuvuustoiminnot JavaScriptillä. Vaihtelu on vakiona puolet refresh-ajasta -->
        <script type="text/javascript">

            function changeImageTaynna()
            {
                if (document.getElementById("img_taynna") !== null ) {
                    var img_taynna = document.getElementById("img_taynna");
                    img_taynna.src = images_taynna[x];
                    x++;

                    if(x >= images_taynna.length){
                        x = 0;
                    } 
                    setTimeout("changeImageTaynna()", <?php echo $secforjs ?>);
                }
            }

            function changeImageTilaa()
            {
                if (document.getElementById("img_tilaa") !== null ) {
                    var img_tilaa = document.getElementById("img_tilaa");
                    img_tilaa.src = images_tilaa[x];
                    x++;

                    if(x >= images_tilaa.length){
                        x = 0;
                    } 
                    setTimeout("changeImageTilaa()", <?php echo $secforjs ?>);
                }
            }

            var images_taynna = [],
            x = 0;

            var images_tilaa = [],
            x = 0;

            images_taynna[0] = "taynna_valk-pun.jpg";
            images_taynna[1] = "taynna_pun-valk.jpg";
            setTimeout("changeImageTaynna()", <?php echo $secforjs ?>);

            images_tilaa[0] = "tilaa_valk-vihr.jpg";
            images_tilaa[1] = "tilaa_vihr-valk.jpg";
            setTimeout("changeImageTilaa()", <?php echo $secforjs ?>);

        </script>

  </head>
  <body>

<?php

//////////////////////////
// RAJAPINTAHAKU (XML) //
/////////////////////////

// Parametrit (poistettu tästä julkisesta versiosta)
$username = '';
$password = '';
$api = '';
// HUOM! Varmista että URL:n perässä ei ole /-viivaa, sen käyttö antoi eri tuloksia rajapinnasta


// Kirjaudutaan sisään käyttäen cURL:ia
$ch = curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_URL, $api);
curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
$result = curl_exec($ch);
curl_close($ch);

// Haetaan XML-sisältö kokonaisuudessaan ja tehdään virheenkäsittely
$xml = simplexml_load_string($result);
if ($xml === false) {
    $readyData = 'error';
} else {

// Haetaan haluttu tieto oikeasta taulusta (stringinä)
    $availability = (string)$xml->payloadPublication->genericPublicationExtension->parkingStatusPublication->parkingRecordStatus->parkingSiteStatus;

// Sanitoidaan varmuuden vuoksi tuloksesta pois tagit ja muut merkit paitsi kirjaimet ja numerot
    $sanitizedData = filter_var($availability, FILTER_SANITIZE_STRING);
    $readyData = preg_replace("/[^a-zA-Z]/", "", $sanitizedData);
}

// Vertailuarvot rajapinnasta saadulle arvolle
$compareData1 = 'spacesAvailable';
$compareData2 = 'almostFull';
$compareData3 = 'fullAtEntrance';
$compareData4 = 'full';

// TULOSTUS

// Vertaillaan rajapinnan dataa ja vertailuarvoja. Palautetaan joko tilaa tai täynnä sen mukaan, mikä tulos on. Muussa tapauksessa tyhjä ruutu.
if ($readyData === $compareData1 or $readyData === $compareData2) {
    echo '<img id="img_tilaa" src="tilaa_vihr-valk.jpg" />';
} elseif ($readyData === $compareData3 or $readyData === $compareData4) {
    echo '<img id="img_taynna" src="taynna_pun-valk.jpg" />';
} else {
    echo '';
}

?>

<!-- Suljetaan HTML-sivu -->
  </body>
</html>