<?php
$lines = explode("\n", file_get_contents('resources/views/agenda/show.blade.php'));
$depth = 0;
foreach ($lines as $i => $line) {
    $num = $i + 1;
    // count opens
    preg_match_all('/<div[\s>]/i', $line, $o);
    preg_match_all('/<\/div>/i', $line, $c);
    $nO = count($o[0]);
    $nC = count($c[0]);
    $depth += ($nO - $nC);
    if ($nO > 0 || $nC > 0) {
        if ($depth < 0) {
            echo "DEPTH UNDERFLOW AT LINE $num: open=$nO, close=$nC, depth=$depth | Content: " . trim($line) . "\n";
        }
    }
}
echo "FINAL DEPTH: $depth\n";
