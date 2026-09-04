<?php

function parseCode(int $code): array
{
    $shapes = ["circle", "rect", "polygon"];
    $colors = ["red", "green", "blue", "yellow", "black", "purple"];

    $shape = $code & 0b11;
    $color = ($code >> 2) & 0b111;
    $size = ($code >> 5) & 0b1111;

    return [
        'shape' => $shapes[$shape % 3],
        'color' => $colors[$color % 6],
        'size' => $size === 0 ? 1 : $size
    ];
}

function buildShape(int $number): string
{
    $params = parseCode($number);

    $scale = $params['size'] * 30;
    $color = $params['color'];

    $center = $scale / 2;
    $radius = $scale / 2;

    $shape = match ($params['shape']) {
        'circle' => "<circle cx='$center' cy='$center' r='$radius' fill='$color'></circle>",
        'rect' => "<rect width='$scale' height='$scale' fill='$color'></rect>",
        'polygon' => "<polygon points='$center,0,0,$scale $scale,$scale' fill='$color'></polygon>"
    };

    return "<svg width='$scale' height='$scale'>$shape</svg>";
}