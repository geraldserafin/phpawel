<?php

function merge_sort(array $array) {
    $cmp_count = 0;

    $merge = function (array $l, array $r) use (&$cmp_count) {
        $res = [];

        while (count($l) && count($r)) {
            $cmp_count += 1;
            $res[] = $l[0] < $r[0]
              ? array_shift($l)
              : array_shift($r);
        }

        return array_merge($res, $l, $r);
    };

    $merge_sort = function (array $arr) use (&$merge, &$merge_sort) {
        if (count($arr) <= 1) {
            return $arr;
        }

        [$left, $right] = array_chunk($arr, ceil(count($arr) / 2));

        return $merge($merge_sort($left), $merge_sort($right));
    };

    return [$merge_sort($array), $cmp_count];
}

$tablice = [
    [5, 3, 8, 1, 9, 2],
    [38, 27, 43, 3, 9, 82, 10, 15],
    [64, 25, 12, 22, 11, 90, 3, 47, 71, 38, 55, 8],
    [25, 24, 23, 22, 21, 20, 19, 18, 17, 16, 15, 14, 13, 12, 11, 10, 9, 8, 7, 6, 5, 4, 3, 2, 1],
];

foreach ($tablice as $array) {
    [$sorted, $comparisons] = merge_sort($array);
    $n = count($array);
    $K = round($comparisons / ($n * log($n, 2)), 3);

    echo "n = {$n}";

    echo "\t| Wejście: [".implode(",", $array) ."]\n  ";
    echo "\t| Wyjście: [".implode(",", $sorted)."]\n";

    echo "\t| Porównania: {$comparisons} | K = {$K}\n\n";
}

echo "Weryfikacja z sort(): " . (sort($tablice[3]) == merge_sort($tablice[3]) ? "ZGODNA" : "NIEZGODNA");
