<?php
/**
 * amigo_scraper.php  –  version Headless Chrome
 * -------------------------------------------------------
 * • Récupère tous les tirages Amigo d’une date (YYYY‑MM‑DD)
 *   ou du jour courant si aucune date n’est fournie.
 * • Génère un fichier JSON par tirage :
 *   YYYY‑MM‑DD_<index>_historical.json
 *
 * Usage :
 *   php amigo_scraper.php            # aujourd’hui
 *   php amigo_scraper.php 2025-04-17 # date précise
 *
 * Dépendances :  composer require nesk/puphpeteer
 */

require __DIR__.'/vendor/autoload.php';

use Nesk\Puphpeteer\Puppeteer;
use Nesk\Rialto\Data\JsFunction;

date_default_timezone_set('Europe/Paris');
$iso = $argv[1] ?? date('Y-m-d');              // date ciblée (ISO 8601)

// ---------------------------- Helper : format FR ----------------------------
$months = ['janvier','février','mars','avril','mai','juin',
           'juillet','août','septembre','octobre','novembre','décembre'];
[$y,$m,$d] = explode('-', $iso);
$dateFr = sprintf('%s %s %s', (int)$d, $months[(int)$m-1], $y); // ex. 17 avril 2025

// ---------------------------- Lance Chrome Headless -------------------------
$puppeteer = new Puppeteer([
    'read_timeout' => 10,        // timeout augmenté à 10s entre PHP & Chrome
    'idle_timeout' => 60,
]);
$browser = $puppeteer->launch([
    'headless' => true,
    'args'     => ['--no-sandbox','--disable-setuid-sandbox'],
    'defaultViewport' => [ 'width' => 1280, 'height' => 1024 ],
]);

$page = $browser->newPage();

// User‑Agent Chrome 124 – évite d’être bloqué par Cloudflare
$page->setUserAgent(
    'Mozilla/5.0 (Windows NT 10.0; Win64; x64) '
   .'AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.6312.59 Safari/537.36'
);

// ---------------------------- Étape 1 : ouvre la page -----------------------
$url = 'https://tirage-gagnant.com/amigo/';
echo "▶  Ouverture $url …\n";
$page->goto($url, ['waitUntil' => 'networkidle2']);

// ---------------------------- Étape 2 : navigue jusqu’à la bonne date -------
echo "▶  Recherche de la date $dateFr …\n";
$maxClicks = 31;                            // 31 jours de look‑back max
for ($i = 0; $i < $maxClicks; $i++) {
    $found = $page->evaluate(JsFunction::createWithBody(<<<'JS'
        const cible = arguments[0].toLowerCase();
        const h1 = document.querySelector('h1, h2, h3');
        return h1 && h1.textContent.toLowerCase().includes(cible);
JS
    ), $dateFr);

    if ($found) { echo "   ✓ Date trouvée.\n"; break; }

    // Cherche le lien / bouton « Précédent » et clique‑le
    $clicked = $page->evaluate(JsFunction::createWithBody(<<<'JS'
        const link = [...document.querySelectorAll('a,button')]
            .find(el => el.textContent.trim().toLowerCase() === 'précédent');
        if (link) { link.click(); return true; }
        return false;
JS
    ));

    if (!$clicked) {
        throw new RuntimeException("Impossible d’atteindre la date désirée (bouton « Précédent » absent).");
    }
    // patiente un peu pour laisser les tirages se recharger
    $page->waitForTimeout(1400);
}
if ($i === $maxClicks) {
    throw new RuntimeException("Date non trouvée après {$maxClicks} clics de « Précédent ».");
}

// ---------------------------- Étape 3 : extrait tous les tirages ------------
echo "▶  Extraction des tirages …\n";
$draws = $page->evaluate(JsFunction::createWithBody(<<<'JS'
    // Retourne un tableau d’objets {index, blue[7], yellow[5]}
    const items = [...document.querySelectorAll('.amigo-results__item')];
    return items.map(li => {
        const index = parseInt(
            li.querySelector('.amigo-results__draw-number')?.textContent.trim()
        );
        const nums  = [...li.querySelectorAll('.amigo-results__number')]
                      .map(s => parseInt(s.textContent.trim()));
        return { index, nums };
    }).filter(d => d.nums.length === 12);
JS
));

if (!$draws) {
    throw new RuntimeException("Aucun tirage détecté pour $iso – CSS probablement modifié.");
}
echo "   ✓ ".count($draws)." tirage(s) détecté(s).\n";

// ---------------------------- Étape 4 : enregistre les JSON -----------------
foreach ($draws as $d) {
    $blue   = array_slice($d['nums'], 0, 7);
    $yellow = array_slice($d['nums'], 7, 5);
    $data   = [
        'numbers' => [[
            'blue'   => $blue,
            'yellow' => $yellow,
            'all'    => array_merge($blue, $yellow),
            'date'   => $iso,
            'index'  => $d['index'],
        ]]
    ];
    $file = sprintf('%s_%d_historical.json', $iso, $d['index']);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "      → $file\n";
}

// ---------------------------- Nettoyage Chrome -----------------------------
$browser->close();
echo "✔  Terminé – ".count($draws)." fichier(s) JSON écrit(s).\n";
?>
