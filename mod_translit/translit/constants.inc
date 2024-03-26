<?php
// Defines working character ranges
define( 'WORD_BOUND',   '\r\s"\'\(\)-<>\[\]\/\.,:;!\?' ); # 
#define( 'WORD_BEGINS', '\b' );
#define( 'WORD_ENDS', '\b' );
// Cyrillic
define( 'CRH_C_UC', 'АБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ' ); # Crimean Tatar Cyrillic uppercase
define( 'CRH_C_LC', 'абвгдеёжзийклмнопрстуфхцчшщъыьэюя' ); # Crimean Tatar Cyrillic lowercase
define( 'CRH_C_CONS_UC', 'БВГДЖЗЙКЛМНПРСТФХЦЧШЩCÑ' ); # Crimean Tatar Cyrillic + CÑ uppercase consonants 
define( 'CRH_C_CONS_LC', 'бвгджзйклмнпрстфхцчшщcñ' ); # Crimean Tatar Cyrillic + CÑ lowercase consonants 
define( 'CRH_C_CONS', 'бвгджзйклмнпрстфхцчшщcñБВГДЖЗЙКЛМНПРСТФХЦЧШЩCÑ' ); # Crimean Tatar Cyrillic + CÑ consonants 
define( 'CRH_C_M_CONS', 'бгкмпшcБГКМПШC' ); # Crimean Tatar Cyrillic M-type consonants
 
// Latin
define( 'CRH_L_UC', 'AÂBCÇDEFGĞHIİJKLMNÑOÖPQRSŞTUÜVXYZ' ); # Crimean Tatar Latin uppercase
define( 'CRH_L_LC', 'aâbcçdefgğhıijklmnñoöpqrsştuüvxyz' ); # Crimean Tatar Latin lowercase
define( 'CRH_L_N_CONS_UC', 'ÇNRSTZ' ); # Crimean Tatar Latin N-type upper case consonants
define( 'CRH_L_N_CONS_LC', 'çnrstz' ); # Crimean Tatar Latin N-type lower case consonants
define( 'CRH_L_N_CONS', 'çnrstzÇNRSTZ' ); # Crimean Tatar Latin N-type consonants
define( 'CRH_L_M_CONS', 'bcgkmpşBCGKMPŞ' ); # Crimean Tatar Latin M-type consonants
define( 'CRH_L_CONS_UC', 'BCÇDFGHJKLMNÑPRSŞTVZ' ); # Crimean Tatar Latin uppercase consonants 
define( 'CRH_L_CONS_LC', 'bcçdfghjklmnñprsştvz' ); # Crimean Tatar Latin lowercase consonants 
define( 'CRH_L_CONS', 'bcçdfghjklmnñprsştvzBCÇDFGHJKLMNÑPRSŞTVZ' ); # Crimean Tatar Latin consonants 
define( 'CRH_L_VOW_UC', 'AÂEIİOÖUÜ' ); # Crimean Tatar Latin uppercase vowels
define( 'CRH_L_VOW', 'aâeıioöuüAÂEIİOÖUÜ' ); # Crimean Tatar Latin vowels
define( 'CRH_L_F_UC', 'EİÖÜ' ); # Crimean Tatar Latin uppercase front vowels 
define( 'CRH_L_F', 'eiöüEİÖÜ' ); # Crimean Tatar Latin front vowels 
?>
