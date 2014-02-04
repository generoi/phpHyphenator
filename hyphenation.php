<?php
/*
      phpHyphenator 1.5
      Developed by yellowgreen designbüro
      PHP version of the JavaScript Hyphenator 10 (Beta) by Matthias Nater

      Licensed under Creative Commons Attribution-Share Alike 2.5 Switzerland
      http://creativecommons.org/licenses/by-sa/2.5/ch/deed.en

      Associated pages:
      http://yellowgreen.de/soft-hyphenation-generator/
      http://yellowgreen.de/phphyphenator/
      http://www.dokuwiki.org/plugin:hyphenation

      Special thanks to:
      Dave Gööck (webvariants.de)
      Markus Birth (birth-online.de)
 */

mb_internal_encoding("utf-8");

// Convert patterns
function phphyphenator_convert_patterns($patterns) {
  $patterns = mb_split(' ', $patterns);
  $new_patterns = array();
  for($i = 0; $i < count($patterns); $i++) {
    $value = $patterns[$i];
    $new_patterns[preg_replace('/[0-9]/', '', $value)] = $value;
  }
  return $new_patterns;
}

// Split string to array
function mb_split_chars($string) {
  $strlen = mb_strlen($string);
  while($strlen) {
    $array[] = mb_substr($string, 0, 1, 'utf-8');
    $string = mb_substr($string, 1, $strlen, 'utf-8');
    $strlen = mb_strlen($string);
  }
  return $array;
}

// Word hyphenation
function phphyphenator_word_hyphenation($word, $patterns) {
  $leftmin = 2;
  $rightmin = 2;
  $charmin = 2;
  $charmax = 10;
  $hyphen = "&shy;";

  if(mb_strlen($word) < $charmin) return $word;
  if(mb_strpos($word, $hyphen) !== false) return $word;

  $text_word = '_' . $word . '_';
  $word_length = mb_strlen($text_word);
  $single_character = mb_split_chars($text_word);
  $text_word = mb_strtolower($text_word);
  $hyphenated_word = array();
  $numb3rs = array('0' => true, '1' => true, '2' => true, '3' => true, '4' => true, '5' => true, '6' => true, '7' => true, '8' => true, '9' => true);

  for($position = 0; $position <= ($word_length - $charmin); $position++) {
    $maxwins = min(($word_length - $position), $charmax);

    for($win = $charmin; $win <= $maxwins; $win++) {
      if(isset($patterns[mb_substr($text_word, $position, $win)])) {
        $pattern = $patterns[mb_substr($text_word, $position, $win)];
        $digits = 1;
        $pattern_length = mb_strlen($pattern);

        for($i = 0; $i < $pattern_length; $i++) {
          $char = $pattern[$i];
          if(isset($numb3rs[$char])) {
            $zero = ($i == 0) ? $position - 1 : $position + $i - $digits;
            if(!isset($hyphenated_word[$zero]) || $hyphenated_word[$zero] != $char) $hyphenated_word[$zero] = $char;
            $digits++;
          }
        }
      }
    }
  }

  $inserted = 0;
  for($i = $leftmin; $i <= (mb_strlen($word) - $rightmin); $i++) {
    if(isset($hyphenated_word[$i]) && $hyphenated_word[$i] % 2 != 0) {
      array_splice($single_character, $i + $inserted + 1, 0, $hyphen);
      $inserted++;
    }
  }

  return implode('', array_slice($single_character, 1, -1));
}

// Text hyphenation
function phphyphenator_hyphenation($text, $patterns) {
  $word = ""; $tag = ""; $tag_jump = 0; $output = array();

  $word_boundaries = "<>\t\n\r\0\x0B !\"§$%&/()=?….,;:-–_„”«»‘’'/\\‹›()[]{}*+´`^|©℗®™℠¹²³";
  $text = $text . " ";

  for($i = 0; $i < mb_strlen($text); $i++) {
    $char = mb_substr($text, $i, 1);
    if(mb_strpos($word_boundaries, $char) === false && $tag == "") {
      $word .= $char;
    } else {
      if($word != "") { $output[] = phphyphenator_word_hyphenation($word, $patterns); $word = ""; }
      if($tag == "" && $char != "<" && $char != ">") $output[] = $char;
    }
  }
  $text = join($output);
  return substr($text, 0, strlen($text) - 1);
}
