<?php
if (!isset($_GET['h'])) exit;
global $password, $h;

use Goutte\Client;

include('assets/header.php');

if (isset($_GET['logout'])) {
    header('Location: logout.php');
}

if (!isset($_SESSION['connected'])) {
    if (isset($_POST['connexion']) && $_POST['password'] == $password) {
        $_SESSION['connected'] = true;
    }
}

if (!isset($_SESSION['connected']) && htmlspecialchars($_GET['h']) != $h) { ?>
    <div align="center" class="form-group p-5">
        <h1>Connexion</h1>
        <form action="" method="post">
            <input class="form-control" type="password" name="password">
            <input type="submit" value="Connexion" name="connexion"/>
        </form>
    </div>

<?php } else {
    $_SESSION['allN'] = [];

    $client = new Client();
    // 1000 dernier à considérer : https://www.reducmiz.com/resultat_fdj.php?jeu=amigo&nb=all
    $crawler = $client->request('GET', 'https://www.reducmiz.com/resultat_fdj.php?jeu=amigo&nb=all');
    $numSortis = [];
    /*
        $crawler->filter('.num_tirage_num::first')->each(function (&$node, $i) {
        $_SESSION['last'] = (int)$node->text();
    });
    echo $_SESSION['last'];
    */

    $crawler->filter('.bs-docs-section font')->each(function (&$node, $i) {
        preg_match_all('/\d+ /', $node->text(), $_SESSION['allN'][]);

    });
    $num = [];
    $numSorti = [];
    $grilles = $_SESSION['allN'];

    foreach ($grilles as $i => $gg) {
        foreach ($gg as $g) {
            foreach ($g as $gx) {
                $num[] = (int)(trim($gx));

            }
        }
    }

    $f = [];
    foreach ($num as $k => $numSorti) {
        if (array_key_exists($numSorti, $num)) $f[$numSorti]++;
        else $f[$numSorti] = 0;
    }
    arsort($f);
    ?>

    <h1 class=" m-md-3">🎲 &nbsp;&laquo;Amélie&raquo;<span class="d-none d-md-inline"> - Génération Amigo</span></h1>
    <br/>
    <div class="container-fluid">
        <div class="row">
            <div>
                <h3>Tirage Généré le plus joué sur 1000</h3><br/>
                <?php
                $res = [];
                $i = 0;
                foreach ($f as $n => $o) {
                    if($i<=12) {
                    echo "<span style='font-size:1.8em;' class='monospaced'>".$n . "</span> ($o) &nbsp;&nbsp;&nbsp;";
                    $i++;
                    }
                }
                ?>
            </div>
        </div>
    </div>

<?php } ?>

