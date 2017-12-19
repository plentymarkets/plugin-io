<?php

namespace IO\Helper;

class StringUtils
{
    public static function string4URL( $n )
    {
        $n = strtolower( $n );
        /**
         * & => "und" wurde geändert in & => " "
         * NIE wieder ändern!!!
         */

        // replace "Basic Latin" and "Latin-1 Supplement" characters to a-z
        $regex = array(
            '/&(a|o|u|A|O|U)ml;/u',				// &aml; => ae, &oml; => oe, &uml; => ue, &Aml; => Ae, &Oml; => Oe, &Uml; =>Ue,
            '/[äÄ\xC6\xE6]/u',					// ä,Ä,Æ,æ => ae
            '/[öÖ]/u',							// ö,Ö => oe
            '/[üÜ]/u',							// ü,Ü => ue
            '/([ß]|&szlig;)/u',					// ß,&szlig; => ss
            '/[\xC0-\xC5\xE0-\xE5]/u',			// À-Å,à-å => a
            '/[\xC7\xE7]/u',					// Ç,ç => c
            '/[\xC8-\xCB\xE8-\xEB]/u', 			// È-Ë,è-ë => e
            '/[\xCC-\xCF\xEC-\xEF]/u',			// Ì-Ï,ì-ï => i
            '/[\xD1\xF1\x{0143}-\x{014B}]/u',	// Ñ,ñ,Ń-ŋ => n
            '/[\xD2-\xD5\xF2-\xF5]/u',			// Ò-Õ,ò-õ => o
            '/[\xD9-\xDC\xF9-\xFB]/u',			// Ù-Û,ù-û => u
            '/[\xDD\xFD\xFF]/u',				// Ý,ý,ÿ => y
            '/[\\\\]/',								// \ => empty string
        );

        $replace = array('$1e','ae','oe','ue','ss','a','c','e','i','n','o','u','y','');

        // if there are UTF-8 characters with Unicode above U+0077
        // add "Latin Extended-A"
        if(preg_match('/^[\x00-\xFF]+$/u',$n))
        {
            $regex = array_merge($regex, array(
                '/[\x{0152}-\x{0153}]/u',			// Œ-œ => oe
                '/[\x{0132}-\x{0133}]/u',			// Ĳ-ĳ => ij
                '/[\x{017F}]/u',					// ſ => ss
                '/[\x{0100}-\x{0105}]/u',			// Ā-ą => a
                '/[\x{0106}-\x{010D}]/u',			// Ć-č => c
                '/[\x{010E}-\x{0111}]/u',			// Ď-đ => d
                '/[\x{0112}-\x{011B}]/u', 			// Ē-ě => e
                '/[\x{011C}-\x{0123}]/u',			// Ĝ-ģ => g
                '/[\x{0124}-\x{0127}]/u',			// Ĥ-ħ => h
                '/[\x{0128}-\x{0131}]/u',			// Ĩ-ı => i
                '/[\x{0134}-\x{0135}]/u',			// Ĵ-ĵ => j
                '/[\x{0136}-\x{0138}]/u',			// Ķ-ĸ => k
                '/[\x{0139}-\x{0142}]/u',			// Ĺ-ł => l
                '/[\x{0143}-\x{014B}]/u',			// Ń-ŋ => n
                '/[\x{014C}-\x{0151}]/u',			// Ō-ő => o
                '/[\x{0154}-\x{0159}]/u',			// Ŕ-ř => r
                '/[\x{015A}-\x{0161}]/u',			// Ś-š => s
                '/[\x{0162}-\x{0167}]/u',			// Ţ-ŧ => t
                '/[\x{0168}-\x{0173}]/u',			// Ũ-ų => u
                '/[\x{0174}-\x{0175}]/u',			// Ŵ-ŵ => w
                '/[\x{0176}-\x{0178}]/u',			// Ŷ-Ÿ => y
                '/[\x{0179}-\x{017E}]/u'			// Ź-ž => z
            ));

            $replace = array_merge($replace, array('oe','ij','ss','a','c','d','e','g','h','i','j','k','l','n','o','r','s','t','u','w','y','z'));
        }

        $regex[] = '/[^a-zA-Z0-9]+/'; // convert all which match the reg_expr /[^a-zA-Z0-9]+/ => '-'
        $replace[] = '-';

        $s = preg_replace($regex,$replace,$n);

        $s = trim($s,'-');

        $t = substr($s,0,2);

        if($t=='a-' || $t=='c-' || $t=='b-' || $t=='f-')
        {
            return substr($s,2);
        }

        return $s;
    }
}