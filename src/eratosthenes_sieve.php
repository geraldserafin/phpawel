<?php

function sieve(int $n): array
{
    $A = array_pad([false, false], $n, true);

    foreach (range(2, sqrt($n)) as $i) {
        if (!$A[$i]) {
            continue;
        }

        foreach (range($i ** 2, $n + 1, $i) as $j) {
            $A[$j] = false;
        }
    }

    return array_keys(array_filter($A));
}

function hundreds(array $a): array
{
    $result = [];

    foreach ($a as $p) {
        $result[floor($p / 100)][] = $p;
    }

    return $result;
}

function find_goldbach_pairs(int $n, array|null &$primes = null): array
{
    if (!$primes) {
        $primes = sieve($n);
    }

    $primes_map = array_flip($primes);
    $pairs = [];

    foreach ($primes as $p) {
        if ($p > ($n / 2)) {
            break;
        }

        $diff = $n - $p;

        if (isset($primes_map[$diff])) {
            $pairs[] = [$p, $diff];
        }
    }

    return $pairs;
}

function find_goldbach_pairs_in_range(int $a, int $b): array
{
    $primes = sieve($b);
    $result = [];

    foreach (range($a, $b) as $n) {
        $result[$n] = find_goldbach_pairs($n, $primes);
    }

    return $result;
}

$blocks = hundreds(sieve(500));

echo "Liczby pierwsze [1–100] (bloki po 10):\n";

foreach (array_chunk($blocks[0], 10) as $chunk) {
    echo "[" . implode(", ", $chunk) . "]\n";
}

echo "\nGęstość liczb pierwszych:\n";

foreach ($blocks as $key => $block) {
    $start = 1 + $key * 100;
    $end = (1 + $key) * 100;
    $theoretical = round(($end - $start) / log(($start + $end) / 2), 1);

    echo "Przedział [{$start}-{$end}]:\t";
    echo count($block) . " | (teoretycznie ~{$theoretical})\n";
}

$pairs = find_goldbach_pairs_in_range(4, 200);
$pair_counts = array_map('count', $pairs);

arsort($pair_counts);

$n = array_key_first($pair_counts);
$n_pairs = $pair_counts[$n];

echo "\nGoldbach — najwięcej par w [4, 200]: Liczba {$n} ({$n_pairs} par)\n";
echo "Pary Goldbacha dla 30: ";
echo implode(", ", array_map(fn ($p) => "[{$p[0]}+{$p[1]}]", $pairs[30]));
