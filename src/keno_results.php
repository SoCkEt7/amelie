<?php
// src/keno_results.php
header('Content-Type: application/json');

function fetch_keno_data() {
    $url = 'https://www.reducmiz.com/resultat_fdj.php?jeu=keno&nb=all';
    $context = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
        ]
    ]);
    $html = @file_get_contents($url, false, $context);
    if ($html === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur de récupération des résultats Keno.']);
        exit;
    }
    return $html;
}

function extract_keno_draws($html) {
    // Extraction des blocs de tirage
    $draws = [];
    $pattern = '/<td align="left">date du tirage<\/td><td align="left"><b>([^<]+)<\/b><\/td>.*?<td align="left">tirage<\/td><td bgcolor="#8B0000"[^>]*><b><font color="#FFFFFF">([^<]+)<\/font><\/b><\/td>/si';
    preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);

    foreach ($matches as $m) {
        $date_str = trim($m[1]);
        $tirage_str = str_replace('&nbsp;', ' ', $m[2]);
        $nums = preg_split('/\s+/', trim($tirage_str));
        $numbers = [];
        foreach ($nums as $num) {
            if (is_numeric($num) && $num >= 1 && $num <= 70) {
                $numbers[] = (int)$num;
            }
        }
        // Conversion de la date (ex: 'lundi 12/05/2025 midi')
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})/', $date_str, $dmatch)) {
            $date = DateTime::createFromFormat('d/m/Y', $dmatch[1]);
            if ($date) {
                // Ajout de l'info "midi" ou "soir" dans le champ
                $moment = (strpos($date_str, 'midi') !== false) ? 'midi' : ((strpos($date_str, 'soir') !== false) ? 'soir' : '');
                $draws[] = [
                    'date' => $date->format('Y-m-d'),
                    'moment' => $moment,
                    'numbers' => $numbers
                ];
            }
        }
    }
    return $draws;
}

function analyze_keno($draws) {
    // Analyse globale
    $all_numbers = [];
    foreach ($draws as $draw) {
        $all_numbers = array_merge($all_numbers, $draw['numbers']);
    }
    $global = compute_freq($all_numbers);
    $global['total_draws'] = count($draws);

    // Analyse par semaine
    $by_week = [];
    foreach ($draws as $draw) {
        $week = date('o-W', strtotime($draw['date']));
        if (!isset($by_week[$week])) $by_week[$week] = [];
        $by_week[$week] = array_merge($by_week[$week], $draw['numbers']);
    }
    $week_stats = [];
    foreach ($by_week as $week => $numbers) {
        $week_stats[$week] = compute_freq($numbers);
    }

    // Analyse par mois
    $by_month = [];
    foreach ($draws as $draw) {
        $month = date('Y-m', strtotime($draw['date']));
        if (!isset($by_month[$month])) $by_month[$month] = [];
        $by_month[$month] = array_merge($by_month[$month], $draw['numbers']);
    }
    $month_stats = [];
    foreach ($by_month as $month => $numbers) {
        $month_stats[$month] = compute_freq($numbers);
    }

    return [
        'global' => $global,
        'par_semaine' => $week_stats,
        'par_mois' => $month_stats,
        'date_analyse' => date('d/m/Y H:i:s')
    ];
}

function compute_freq($numbers) {
    $freq = array_fill(1, 70, 0);
    foreach ($numbers as $num) {
        if (isset($freq[$num])) {
            $freq[$num]++;
        }
    }
    asort($freq);
    $least = array_slice($freq, 0, 9, true);
    arsort($freq);
    $most = array_slice($freq, 0, 9, true);
    return [
        'least' => array_keys($least),
        'most' => array_keys($most),
        'least_freq' => $least,
        'most_freq' => $most,
        'total_numbers' => count($numbers)
    ];
}

// Exécution
$html = fetch_keno_data();
$draws = extract_keno_draws($html);
$result = analyze_keno($draws);
echo json_encode($result);