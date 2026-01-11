<?php

namespace App\Utils;

class BengaliPhonetic
{
    /**
     * Advanced Phonetic Map
     * Following standard Avro/Phonetic conventions:
     * t -> ত, T -> ট, d -> দ, D -> ড, s -> স, S/sh -> শ
     */
    protected static $map = [
        // Vowels (Stand-alone)
        'a' => 'আ',
        'A' => 'অ',
        'e' => 'এ',
        'E' => 'এ',
        'i' => 'ই',
        'I' => 'ঈ',
        'o' => 'ও',
        'O' => 'ও',
        'u' => 'উ',
        'U' => 'ঊ',
        'y' => 'এ',
        'Y' => 'ঐ',
        'ri' => 'ঋ',

        // Multi-char vowels
        'oo' => 'ঊ',
        'oi' => 'ঐ',
        'ou' => 'ঔ',

        // Consonants
        'k' => 'ক',
        'kh' => 'খ',
        'K' => 'ক',
        'g' => 'গ',
        'gh' => 'ঘ',
        'G' => 'ঙ',
        'ng' => 'ঙ',
        'Ng' => 'ঙ',
        'ch' => 'চ',
        'Ch' => 'চ',
        'chh' => 'ছ',
        'j' => 'জ',
        'jh' => 'ঝ',
        'J' => 'জ',
        't' => 'ত',
        'th' => 'থ',
        'T' => 'ট',
        'Th' => 'ঠ',
        'd' => 'দ',
        'dh' => 'ধ',
        'D' => 'ড',
        'Dh' => 'ঢ',
        'n' => 'ন',
        'N' => 'ণ',
        'p' => 'প',
        'ph' => 'ফ',
        'P' => 'প',
        'f' => 'ফ',
        'b' => 'ব',
        'B' => 'ব',
        'bh' => 'ভ',
        'v' => 'ভ',
        'm' => 'ম',
        'M' => 'ম',
        'z' => 'য',
        'Z' => 'জ',
        'r' => 'র',
        'R' => 'ড়',
        'l' => 'ল',
        'L' => 'ল',
        'sh' => 'শ',
        'S' => 'শ',
        's' => 'স',
        'h' => 'হ',
        'H' => 'ঃ',
        'w' => 'ব',
        'X' => 'ক্স',
        'x' => 'ক্স',
    ];

    /**
     * Vowel Signs (Kar)
     */
    protected static $kar_map = [
        'a' => 'া',
        'A' => '', // Inherent
        'e' => 'ে',
        'i' => 'ি',
        'I' => 'ী',
        'o' => 'ো',
        'O' => 'ো',
        'u' => 'ু',
        'U' => 'ূ',
        'y' => 'ে',
        'Y' => 'ৈ',
        'ri' => 'ৃ',
        'oo' => 'ূ',
        'oi' => 'ৈ',
        'ou' => 'ৌ',
    ];

    /**
     * Translate Romanized Bengali (Banglish) to Bengali script
     */
    public static function translate(string $text): string
    {
        $result = '';
        $i = 0;
        $len = strlen($text);
        $lastType = null; // 'vowel' or 'consonant'

        while ($i < $len) {
            $char = $text[$i];

            // Peek for multi-char patterns
            $pattern2 = ($i + 1 < $len) ? $char . $text[$i + 1] : null;
            $pattern3 = ($i + 2 < $len) ? $pattern2 . $text[$i + 2] : null;

            $key = null;
            $consumed = 1;

            if ($pattern3 && isset(self::$map[$pattern3])) {
                $key = $pattern3;
                $consumed = 3;
            } elseif ($pattern2 && isset(self::$map[$pattern2])) {
                $key = $pattern2;
                $consumed = 2;
            } elseif (isset(self::$map[$char])) {
                $key = $char;
                $consumed = 1;
            }

            if ($key !== null) {
                $isVowel = self::isVowel($key);

                // Halant Logic for Conjuncts
                if (!$isVowel && $lastType === 'consonant') {
                    // Check for special case: if last char was 'r', it might be a 'reph' or 'phala'
                    // But for simple search, we use Halant joining
                    $result .= '্';
                }

                if ($isVowel) {
                    if ($result === '' || mb_substr($result, -1) === ' ') {
                        $result .= self::$map[$key];
                    } else {
                        $result .= self::$kar_map[$key] ?? '';
                    }
                    $lastType = 'vowel';
                } else {
                    $result .= self::$map[$key];
                    $lastType = 'consonant';
                }

                $i += $consumed;
            } else {
                $result .= $char;
                $lastType = null;
                $i++;
            }
        }

        return $result;
    }

    protected static function isVowel(string $key): bool
    {
        return in_array($key, ['a', 'A', 'e', 'E', 'i', 'I', 'o', 'O', 'u', 'U', 'y', 'Y', 'ri', 'oo', 'oi', 'ou']);
    }
}
