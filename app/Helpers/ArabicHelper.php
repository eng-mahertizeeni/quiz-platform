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
        $text = preg_replace(array_keys($map), array_values($map), $text);
        // Normalize Arabic-Indic digits to Western digits
        $arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $westernDigits = range(0, 9);
        $text = str_replace($arabicDigits, $westernDigits, $text);
        return $text;
    }

    public static function isNumeric(string $text): bool
    {
        return preg_match('/^[0-9]+(\.[0-9]+)?$/', $text) === 1;
    }

    public static function matches(string $input, string $correct): bool
    {
        $a = self::normalize($input);
        $b = self::normalize($correct);

        if ($a === $b) return true;

        // For purely numeric answers: only exact match allowed
        if (self::isNumeric($b)) {
            return $a === $b;
        }

        // For text answers: allow up to 1 character difference
        $dist = levenshtein($a, $b);
        return $dist <= 1;
    }
}
