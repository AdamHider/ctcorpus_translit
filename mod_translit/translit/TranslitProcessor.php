<?php 


require_once("constants.inc");


class TranslitProcessor
{
    
    
    public static function translate( $text, $toVariant ){
        include( 'exeptions.inc' );//Exeptions members
    #                global $wgContLanguageCode;
    #                $text = parent::translate( $text, $toVariant );
        $pat = "";
        $nonletters = '';
        switch( $toVariant ) {
        case 'crh-cyrl':
          $letters = CRH_L_UC . CRH_L_LC ;
          $wgContLanguageCode = 'crh-Cyrl';
          foreach( $mLatn2CyrlEx as $exword => $rep ) {
            $text = str_replace( "$exword", "$rep", $text );
          }
          break;
        case 'crh-latn':
          $letters = CRH_C_UC . CRH_C_LC;
          $wgContLanguageCode = 'crh-Latn';
          foreach( $mCyrl2LatnEx as $exword => $rep ) {
            $text = str_replace( "$exword", "$rep", $text );
          }
          break;
        default:
          $wgContLanguageCode = 'crh';
          return $text;
        }
        // we split text by word
        $regexp = '/(['.$letters.']+)/u';
    
        $words = array(); $delims = array();
        $count = self::split_by_regexp( $regexp, $text, $words, $delims );
        $delims = self::regsConverter( $delims, $toVariant );
        // merge words and non-words
        return self::merge_by_turns($words, $delims);
      }
    
    
      /**
       *  It translates word into variant by regexp
       */
    public static function regsConverter( $array_of_words, $toVariant ) {
        $text = implode("\n", $array_of_words);
        if ($text == '') return;
        include( 'regular_expressions.inc' );//Regexps members
    
        switch( $toVariant ) {
          // from CYRIL to LATIN
          case 'crh-latn':
            foreach( $mCyrl2Latn as $pat => $rep ) {
              if($pat == 'all_other_letters') {
                $text = strtr($text, $all_other_letters_cyr2lat);
              }else{
                $text = preg_replace( "@$pat@um", "$rep", $text );
              }
            }
          break;
          // from LATIN to CYRIL
          case 'crh-cyrl':
            foreach( $mLatn2Cyrl as $pat => $rep ) {
              if($pat == 'all_other_letters') {
                $text = strtr($text, $all_other_letters_lat2cyr);
              }else{
                $text = preg_replace( "@$pat@um", "$rep", $text );
              }
            }
        }
        return explode("\n", $text);
    }
    private static function split_by_regexp( $regexp, $string, &$words, &$delims ) {
          $matches = preg_split( $regexp, $string, -1, PREG_SPLIT_DELIM_CAPTURE);
          $string = ''; 
        #  print_r($matches);  
          $words = array(); $delims = array();
          foreach( $matches as $i=>$match ) {
            // on even position we have not delimiters
            if ($i%2 == 0) {
              // in $text we collect words separated with \n
              // this is to applay transliterate function on whole string at once
              $words[] = $match; #echo $i.' \'' .$match . "' <br> ";
              
            }else{
              // on odd positions we have delimiters(our words)
              $delims[] = $match; #echo $i.' ' .$match . "<br>";
            }
          }                               
         return count($delims);
        }
        /*
         * merge two arrays by turns into string 
         */
    private static function merge_by_turns( $words, $delims ) {
      $count = count($words); $string = '';
      for($i=0; $i<$count; $i++) {
          if(!isset( $delims[$i]) ){
              $delims[$i] = '';
          }
              $string .= $words[$i].$delims[$i] ;
        
      }
      return $string;
    }
}