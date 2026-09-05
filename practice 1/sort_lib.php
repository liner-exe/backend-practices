<?php

function parseArray(string $arr): array
{
    return array_map('intval', explode(',', $arr));
}

function insertionSort(array $arr): array
{
    for ($i = 1; $i < count($arr); $i++) {
        $j = $i - 1;
        $key = $arr[$i];

        while ($j >= 0 && $arr[$j] > $key) {
            $arr[$j + 1] = $arr[$j];
            $j -= 1;
        }

        $arr[$j + 1] = $key;
    }

    return $arr;
}