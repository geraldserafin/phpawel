<?php

interface Query {}

readonly class QueryAnd implements Query {
    public function __construct(public Query $left, public Query $right) {}
}

readonly class QueryOr implements Query {
    public function __construct(public Query $left, public Query $right) {}
}

readonly class QueryVal implements Query {
    public function __construct(public string $val) {}
}

function print_query(Query $query) {
    return match ($query::class) {
        QueryVal::class => $query->val,
        QueryOr::class  => "(".print_query($query->left)." OR " .print_query($query->right).")",
        QueryAnd::class => "(".print_query($query->left)." AND ".print_query($query->right).")"
    };
}

class DocumentIndex {
    private array $stop_words = ['i', 'w', 'na', 'do', 'z', 'są', 'lub', 'być', 'może', 'jest', 'się'];
    private array $index = [];

    public function __construct(array $docs) {
        foreach ($docs as $doc_id => $doc) {
            $words = explode(" ", strtolower($doc));
            $words_count = array_count_values($words);

            foreach ($words_count as $word => $count) {
                if (in_array($word, $this->stop_words) || strlen($word) < 3) {
                    continue;
                }

                $this->index[$word][$doc_id] = $count;
            }
        }
    }

    public function getIndex(): array {
        return $this->index;
    }
}

class IndexQueryEvaluator {
    public function __construct(private readonly DocumentIndex $index) {}

    public function most_common_words(): array {
        $counts = array_map("array_sum", $this->index->getIndex());
        arsort($counts);
        return  $counts;
    }

    public function evaluate(Query $query): array {
        return match ($query::class) {
            QueryVal::class => $this->evaluate_val($query),
            QueryOr::class  => $this->evaluate_or($query->left, $query->right),
            QueryAnd::class => $this->evaluate_and($query->left, $query->right),
            default => throw new InvalidArgumentException("Unknown query type"),
        };
    }

    private function evaluate_val(QueryVal $query): array {
        $word = $query->val;
        $docs = $this->index->getIndex()[$word] ?? [];
        $result = [];

        foreach ($docs as $docId => $count) {
            $result[$docId] = [$word => $count];
        }

        return $result;
    }

    private function evaluate_or(Query $left, Query $right): array {
        $result = $this->evaluate($left);

        foreach ($this->evaluate($right) as $docId => $words) {
            $result[$docId] = array_merge($result[$docId] ?? [], $words);
        }

        return $result;
    }

    private function evaluate_and(Query $left, Query $right): array {
        $leftDocs = $this->evaluate($left);
        $rightDocs = $this->evaluate($right);

        $intersectedDocs = array_intersect_key($leftDocs, $rightDocs);

        $result = [];
        foreach ($intersectedDocs as $docId => $leftWords) {
            $result[$docId] = array_merge($leftWords, $rightDocs[$docId]);
        }

        return $result;
    }
}

$docs = [
    0 => "PHP jest językiem skryptowym używanym do tworzenia stron internetowych",
    1 => "Tablice w PHP mogą być indeksowane lub asocjacyjne i bardzo przydatne",
    2 => "Funkcje array_map i array_filter ułatwiają przetwarzanie tablic w PHP",
    3 => "PHP obsługuje tablice wielowymiarowe i zagnieżdżone struktury danych",
    4 => "Serwer Apache współpracuje z PHP do obsługi żądań HTTP i połączeń",
    5 => "Bazy danych MySQL są często używane razem z PHP do przechowywania",
    6 => "Funkcja usort sortuje tablice w PHP według różnych kryteriów i warunków",
    7 => "JavaScript i PHP razem tworzą dynamiczne aplikacje internetowe i serwisy",
    8 => "PHP posiada wbudowane funkcje do pracy z plikami tablicami i bazami",
    9 => "Bezpieczeństwo aplikacji PHP wymaga walidacji danych wejściowych i filtrów",
];
$index = new DocumentIndex($docs);
$search_engine = new IndexQueryEvaluator($index);

echo "Top 5 najczęstszych słów:\n";

foreach (array_slice($search_engine->most_common_words(), length: 5, offset: 0) as $word => $count) {
    echo "'$word': {$count}x\n";
}

echo "\n";

$queries = [
    new QueryAnd(
        new QueryVal("php"),
        new QueryVal("tablice")
    ),
    new QueryOr(
        new QueryVal("mysql"),
        new QueryVal("javascript")
    ),
    new QueryAnd(
        new QueryVal("php"),
        new QueryOr(
            new QueryVal("mysql"),
            new QueryVal("javascript")
        ),
    ),
];

foreach ($queries as $query) {
    echo "Wyniki dla: " . print_query($query) . "\n";

    $results = $search_engine->evaluate($query);
    $i = 1;

    foreach ($results as $doc_id => $words) {
        $score = array_sum($words);

        $word_details = implode(
            ', ',
            array_map(
                fn ($word, $count) => "$word:$count",
                array_keys($words),
                $words
            )
        );

        echo "$i. Dokument ID: $doc_id | Score: $score ($word_details)\n";
    }

    echo "\n";
}
