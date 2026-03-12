<?php

declare(strict_types=1);

require __DIR__ . '/../db/conexiones.php';

if (!isset($conexion) || !$conexion) {
    fwrite(STDERR, "No hay conexión a la base de datos.\n");
    exit(1);
}

$url = 'https://store.steampowered.com/stats/stats/';

$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => [
            'User-Agent: SalsaBox/1.0 (+https://localhost)',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ],
        'timeout' => 25,
    ],
]);

$html = @file_get_contents($url, false, $context);
if ($html === false || trim($html) === '') {
    fwrite(STDERR, "No se pudo descargar la página de stats de Steam.\n");
    exit(1);
}

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
$xpath = new DOMXPath($dom);

$links = $xpath->query("//a[contains(@href,'store.steampowered.com/app/')]");
if ($links === false || $links->length === 0) {
    fwrite(STDERR, "No se encontraron juegos en la página de stats (HTML cambió).\n");
    exit(1);
}

$parseNumero = static function (string $texto): ?int {
    $texto = trim($texto);
    if ($texto === '') {
        return null;
    }

    $texto = str_replace([',', ' ', "\u{00A0}"], '', $texto);
    if (!ctype_digit($texto)) {
        return null;
    }

    $valor = (int) $texto;
    return $valor >= 0 ? $valor : null;
};

$stmt = $conexion->prepare("
    INSERT INTO Steam_Players_History
        (steam_appid, current_players, peak_today, captured_at)
    VALUES
        (?, ?, ?, NOW())
");

if (!$stmt) {
    fwrite(STDERR, "No se pudo preparar el INSERT: " . mysqli_error($conexion) . "\n");
    exit(1);
}

$insertados = 0;
$vistos = [];

foreach ($links as $a) {
    $href = (string) $a->getAttribute('href');
    if (!preg_match('~/app/(\\d+)/~', $href, $m)) {
        continue;
    }

    $appid = (int) $m[1];
    if ($appid <= 0 || isset($vistos[$appid])) {
        continue;
    }

    $tr = $a;
    while ($tr !== null && $tr->nodeName !== 'tr') {
        $tr = $tr->parentNode;
    }
    if ($tr === null) {
        continue;
    }

    $tds = [];
    foreach ($tr->childNodes as $child) {
        if ($child instanceof DOMElement && $child->nodeName === 'td') {
            $tds[] = trim($child->textContent);
        }
    }

    // En la tabla de Steam Stats suele ser:
    // [0] current players, [1] peak today, [2] game name.
    $current = isset($tds[0]) ? $parseNumero($tds[0]) : null;
    $peak = isset($tds[1]) ? $parseNumero($tds[1]) : null;

    if ($current === null) {
        continue;
    }

    $currentParam = $current;
    $peakParam = $peak;

    $stmt->bind_param('iii', $appid, $currentParam, $peakParam);
    if ($stmt->execute()) {
        $insertados++;
        $vistos[$appid] = true;
    }
}

$stmt->close();

echo "Snapshots insertados: $insertados\n";

