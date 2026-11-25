<?php
declare(strict_types=1);

function tripPreferencePath(int $tripId, string $name): string
{
    return dirname(__DIR__, 2) . '/private-receipts/' . $tripId . '/' . $name . '.json';
}

function readTripPreference(string $path): array
{
    if (is_link($path) || !is_file($path)) return [];
    $raw = file_get_contents($path);
    $data = is_string($raw) ? json_decode($raw, true) : null;
    return is_array($data) ? $data : [];
}

function saveTripPreference(string $path, array $value): bool
{
    $dir = dirname($path);
    if (is_link(dirname($dir)) || is_link($dir) || is_link($path) ||
        (!is_dir($dir) && !@mkdir($dir, 0700, true) && !is_dir($dir))) return false;
    $temp = tempnam($dir, '.preference-');
    if (!$temp) return false;
    try {
        return dirname($temp) === $dir
            && file_put_contents($temp, json_encode($value, JSON_THROW_ON_ERROR)) !== false
            && rename($temp, $path);
    } finally {
        if (is_file($temp)) unlink($temp);
    }
}

function allocateSavedShares(array $weights, array $members, int $cents): array
{
    $members = array_map('intval', $members);
    if (!$weights || $cents <= 0 || $cents > 100000000000) return [];
    foreach ($weights as $id => $weight) {
        if (!in_array((int) $id, $members, true) || !is_int($weight) || $weight < 1 || $weight > 10000) return [];
    }
    $total = array_sum($weights);
    $shares = [];
    $remainders = [];
    $balance = $cents;
    foreach ($weights as $id => $weight) {
        $weighted = $cents * $weight;
        $shares[$id] = intdiv($weighted, $total);
        $remainders[$id] = $weighted % $total;
        $balance -= $shares[$id];
    }
    $ids = array_keys($weights);
    usort($ids, static fn($a, $b) => $remainders[$b] <=> $remainders[$a] ?: (int) $a <=> (int) $b);
    for ($i = 0; $i < $balance; $i++) $shares[$ids[$i]]++;
    return array_map(static fn(int $share) => $share / 100, array_filter($shares));
}
