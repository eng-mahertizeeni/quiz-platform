<?php
namespace App\Helpers;

class ArabicHelper
{
    public static function normalize(string $text): string
    {
        $text = trim($text);
        $map = [
            '/[إأآٱ]/u' => 'ا',
            '/ة/u'      => 'ه',
            '/ى/u'      => 'ي',
            '/[ًٌٍَُِّْٰ]/u' => '',
            '/ـ/u'      => '',
            '/\s+/u'    => ' ',
        ];
        return preg_replace(array_keys($map), array_values($map), $text);
    }

    public static function matches(string $input, string $correct): bool
    {
        $a = self::normalize($input);
        $b = self::normalize($correct);

        if ($a === $b) return true;

        $byteDist = levenshtein($a, $b);
        $byteLen  = strlen($b);

        if ($byteDist <= 2) return true;
        if ($byteDist <= max(2, intdiv($byteLen, 6))) return true;

        similar_text($a, $b, $pct);
        if ($pct >= 85) return true;

        return false;
    }
}
