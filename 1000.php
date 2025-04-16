<?php
if(!isset($_GET['h']))exit;
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
    $_SESSION['numSortisB'] = [];
    $_SESSION['numSortis'] = [];
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
    $crawler->filter('.bs-docs-section > table > td > font')->each(function (&$node, $i) {
        echo $node->html();
        $_SESSION['numSortis'][$i] = (int)$node->text();
        $_SESSION['numSortisB'][$i] = (int)$node->text();
    });

    $crawler->filter('.chance')->each(function (&$node, $i) {
        $_SESSION['numSortis'][$i] = (int)$node->text();
    });

    $grille = array();
    $pgrille = [];
    foreach ($_SESSION['numSortis'] as $k => $numSorti) {
        if (array_key_exists($numSorti, $grille)) $grille[$numSorti]++;
        else $grille[$numSorti] = 0;
    }
    ksort($grille);
    $pgrille = $grille;


    $grilleB = array();
    foreach ($_SESSION['numSortisB'] as $k => $numSortiB) {
        if (array_key_exists($numSortiB, $grilleB)) $grilleB[$numSortiB]++;
        else $grilleB[$numSortiB] = 0;
    }
    ksort($grilleB);
    ?>

    <h1 class=" m-md-3">🎲 &nbsp;&laquo;Amélie&raquo;<span class="d-none d-md-inline"> - Génération Amigo</span></h1>
    <br/>
    <div class="container-fluid">
        <div class="row">
            <div>
                <h3>Tirage Généré le moins joué</h3><br/>

                <?php
                $i = 0;
                asort($grille);
                $kgrille = $grille;
                $nb = count($grille);
                foreach ($kgrille as $nombre => $occurence) {
                    $i++;
                    $res[] = $nombre;
                    if ($i >= 7) break;
                }

                sort($res);
                foreach ($res as $re) {
                    echo "<span style='font-size:1.8em;' class='monospaced'>$re</span> &nbsp; &nbsp; &nbsp;";
                }

                echo "<br/><br><br><h3>Nombres bleus les moins joués</h3><br/>";
                $j = 0;
                asort($grilleB);
                $kgrilleB = $grilleB;

                foreach ($kgrilleB as $nombre => $occurence) {
                    $j++;
                    $resB[] = $nombre;
                    if ($j >= 7) break;
                }

                sort($resB);
                foreach ($resB as $re) {
                    echo "<span style='font-size:1.8em;' class='monospaced'>$re</span> &nbsp; &nbsp; &nbsp;";
                }
                echo "<br/><br><br><h3>Corrélations</h3><br/>";

                foreach ($resB as $rea) {
                    foreach ($res as $reb) {
                        if ($rea == $reb) echo "<span style='font-size:1.8em;' class='monospaced'>$reb</span> &nbsp; &nbsp; &nbsp;";
                    }
                } ?>

                <br/>
                <hr>
                <br>
                <h3>Nombre les plus sortis</h3><br/>

                <?php
                $k = 0;
                $resK = [];
                arsort($grille);
                $kgrille = $grille;

                foreach ($kgrille as $nombre => $occurence) {
                    $k++;
                    $resK[] = $nombre;
                    if ($k >= 7) break;
                }
                sort($resK);
                foreach ($resK as $re) {
                    echo "<span style='font-size:1.8em;'>$re</span> &nbsp; &nbsp; &nbsp;";
                }

                ?>
            </div>

            <div class="col">
                <h2>Derniers résultats</h2> <br/>

                <?php
                foreach ($grille as $nombre => $occurence) {
                    echo "<b style='font-size:1.2em' class='monospaced'>$nombre</b> ($occurence fois)  &nbsp;&nbsp; -  &nbsp;&nbsp; ";
                }
                ?><br/><br/><br/>

                <h2>Nombres les moins joués</h2> <br/>

                <?php
                foreach ($grille as $nombre => $occurence) {
                    echo "<b  style='font-size:1.12em' >$nombre</b> ($occurence fois)  &nbsp;&nbsp; -  &nbsp;&nbsp; ";
                }
                ?>
                <br/> <br/> <br/>
                <h2>Nombres bleus les moins joués</h2> <br/>

                <?php
                foreach ($grilleB as $nombre => $occurence) {
                    echo "<b  style='font-size:1.2em'>$nombre</b> ($occurence fois) &nbsp;&nbsp; - &nbsp;&nbsp; ";
                }
                ?>
            </div>
        </div>
    </div>

<?php } ?>
<?php include('assets/footer.php');
