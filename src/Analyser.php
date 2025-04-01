<?php

namespace HermesTecnologia\Analyser;

use DateTime;
use Exception;

class Analyser
{
    protected $value;

    static public function make($value)
    {
        return new self($value);
    }

    public function __construct($value = null)
    {
        $this->value = $value;
        //teste
        return $this;
    }

    public function cleanNumber()
    {
        $this->value = preg_replace('/[^0-9]/', '', (string) $this->value);
        return $this;
    }

    public function get()
    {
        return $this->value;
    }

    public function set($value)
    {
        $this->value = $value;
        return $this;
    }

    public function onlyNumbers()
    {
        $this->cleanNumber();
        $startswithzero = (string) $this->value[0] == "0";
        if( !$startswithzero ){
            return (int) $this->get();
        }
        return $this->get();
    }

    public function sanitize()
    {
        if(is_null($this->value)){
            return $this->value;
        }
        $this->ansi();
        $this->clearString();
        $this->clearStringSpecialChar();
        return $this->get();    
    }

    public function hideEmail($show = 2)
    {
        $arr = explode('@', $this->value);
        return substr($arr[0],0,$show) . str_repeat('*',strlen($arr[0])-$show) . '@' . $arr[1];
    }

    public function hideData($show = 4, $from_end = false)
    {
        if($from_end){
            return str_repeat('*', strlen($this->value) - $show) . substr($this->value, $show * -1);
        }
        return substr($this->value,0,$show) . str_repeat('*',strlen($this->value)-$show);
    }

    public function getWeekPeriod()
    {
        if(!$this->value instanceof DateTime){
            $this->value = new DateTime($this->value);
        }
        if(is_null($this->value)){
            $date = new DateTime();
        }
        $weekday = $date->format("W");
        $year = $date->format("Y");
        return [
            (new DateTime())->setISODate($year, $weekday)->format('Y-m-d'), //start date
            (new DateTime())->setISODate($year, $weekday, 7)->format('Y-m-d') //end date
         ];
    }

    public function toDecimal()
    {
        return (!$this->value) ? 0 : floatval( str_replace(' ','',$this->value) );
    }

    public function maskCNPJ()
    {
        $this->cleanNumber();
        return $this->maskerize('##.###.###/####-##',14);
    }

    public function maskCPF()
    {
        $this->cleanNumber();
        return $this->maskerize('###.###.###-##',11);
    }

    public function boleto()
    {
        $this->cleanNumber();
        $mask = "#####.##### #####.###### #####.###### # ##############";
        return $this->maskerize($mask,47);
    }

    function maskerize($mask = '####.####', $len = 8)
    {
        if( strlen( (string) $this->value ) !== $len ){
            return $this->value;
        }

        $return = '';
        $posicao_valor = 0;
        for($i = 0; $i<=strlen($mask)-1; $i++) {
            if( $mask[$i] == '#' ) {
                if(isset($this->value[$posicao_valor])) {
                    $return .= $this->value[$posicao_valor++];
                }
            } else {
                $return .= $mask[$i];
            }
        }
        return $return;
    }

    function maskerizeVal($mask = '####.####', $len = 8)
    {
        if( strlen( (string) $this->value ) !== $len ){
            return null;
        }

        $return = '';
        $posicao_valor = 0;
        for($i = 0; $i<=strlen($mask)-1; $i++) {
            if( $mask[$i] == '#' ) {
                if(isset($this->value[$posicao_valor])) {
                    $return .= $this->value[$posicao_valor++];
                }
            } else {
                $return .= $mask[$i];
            }
        }
        return $return;
    }

    public function maskPhone()
    {
        $nac = null;
        if( substr($this->value,0,1) == '+' ){ 
            $nac = substr($this->value,1,2);
            $this->cleanNumber();
            $this->value = substr($this->value,2,strlen($this->value));
        }else{
            $this->cleanNumber();
        }

        if( substr($this->value,0,1) == 0 ){ $this->value = substr($this->value,1,strlen($this->value));}
        
        switch(strlen($this->value)){
            case 11:
                $mask = "(##) # ####-####";
            break;
            case 10:
                $mask = "(##) ####-####";
            break;
            case 9:
                $mask = "# ####-####";
            break;
            case 8:
                $mask = "####-####";
            break;
            default:
            $mask = "(##) ####-####";
        }
        
        $this->value = $this->maskerize($mask, strlen($this->value));

        if($nac){
            return '+'.$nac.' '.$this->value;
        }
        return $this->value;
    }

    public function validateCnpj(){
        $this->cleanNumber();
        // dd($this->value);

        // Lista de CNPJs inválidos
        $invalidos = [
            '00000000000000',
            '11111111111111',
            '22222222222222',
            '33333333333333',
            '44444444444444',
            '55555555555555',
            '66666666666666',
            '77777777777777',
            '88888888888888',
            '99999999999999'
        ];
    
        // Verifica se o CNPJ está na lista de inválidos
        if (in_array($this->value, $invalidos)) {
            return false;
        }
    
        // Valida tamanho
        if (strlen($this->value) != 14){
            return false;
        }
    
        // Verifica se todos os digitos são iguais
        if (preg_match('/(\d)\1{13}/', $this->value)){
            return false;
        }
    
        // Valida primeiro dígito verificador
        for ($i = 0, $j = 5, $soma = 0; $i < 12; $i++){
            $soma += $this->value[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
        $resto = $soma % 11;
    
        if ($this->value[12] != ($resto < 2 ? 0 : 11 - $resto)){
            return false;
        }
    
        // Valida segundo dígito verificador
        for ($i = 0, $j = 6, $soma = 0; $i < 13; $i++){
            $soma += $this->value[$i] * $j;
            $j = ($j == 2) ? 9 : $j - 1;
        }
    
        $resto = $soma % 11;
        return $this->value[13] == ($resto < 2 ? 0 : 11 - $resto);
    }
    
    public function getCnpj()
    {
        $this->cleanNumber();
        if($this->validateCnpj()){
            return $this->value;
        }
        return null;
    }

    public function validateCpf() {
        $this->cleanNumber();
        if (strlen($this->value) !== 11 || preg_match('/(\d)\1{10}/', $this->value)) {
            return false;
        }
    
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $this->value[$c] * (($t + 1) - $c);
            }
    
            $d = ((10 * $d) % 11) % 10;
    
            if ($this->value[$c] != $d) {
                return false;
            }
        }
    
        return true;
    }

    public function dateToSql()
    {
        try{
            $char = preg_replace('/\d+/u', '', $this->value)[0];
        }catch(Exception $e){
            return null;
        }
        
        $date = explode($char,$this->value);
        if(count($date) < 3){ return null; }

        return $date[2].'-'.$date[1].'-'.$date[0];
    }

    public function sqlToDate($format = 'd/m/Y H:i:s')
    {
        $date = new DateTime($this->value);
        return $date->format($format);
    }

    public function getInitials()
    {
        $fullname = preg_replace("/[^a-zA-Z0-9]+/", "", $this->value);
        $name = explode(' ',$fullname);
        $nn = '';
        foreach($name as $v){
            $nn .= isset($v[0]) ? $v[0] : 'A';
        }
        return strtoupper($nn);
    }

    public function clearString()
    {
        $transliteration = array(
            'Ĳ' => 'I','Ö' => 'O','Œ' => 'O','Ü' => 'U','ä' => 'a','æ' => 'a',
            'ĳ' => 'i','ö' => 'o','œ' => 'o','ü' => 'u','ß' => 's','ſ' => 's',
            'À' => 'A','Á' => 'A','Â' => 'A','Ã' => 'A','Ä' => 'A','Å' => 'A',
            'Æ' => 'A','Ā' => 'A','Ą' => 'A','Ă' => 'A','Ç' => 'C','Ć' => 'C',
            'Č' => 'C','Ĉ' => 'C','Ċ' => 'C','Ď' => 'D','Đ' => 'D','È' => 'E',
            'É' => 'E','Ê' => 'E','Ë' => 'E','Ē' => 'E','Ę' => 'E','Ě' => 'E',
            'Ĕ' => 'E','Ė' => 'E','Ĝ' => 'G','Ğ' => 'G','Ġ' => 'G','Ģ' => 'G',
            'Ĥ' => 'H','Ħ' => 'H','Ì' => 'I','Í' => 'I','Î' => 'I','Ï' => 'I',
            'Ī' => 'I','Ĩ' => 'I','Ĭ' => 'I','Į' => 'I','İ' => 'I','Ĵ' => 'J',
            'Ķ' => 'K','Ľ' => 'K','Ĺ' => 'K','Ļ' => 'K','Ŀ' => 'K','Ł' => 'L',
            'Ñ' => 'N','Ń' => 'N','Ň' => 'N','Ņ' => 'N','Ŋ' => 'N','Ò' => 'O',
            'Ó' => 'O','Ô' => 'O','Õ' => 'O','Ø' => 'O','Ō' => 'O','Ő' => 'O',
            'Ŏ' => 'O','Ŕ' => 'R','Ř' => 'R','Ŗ' => 'R','Ś' => 'S','Ş' => 'S',
            'Ŝ' => 'S','Ș' => 'S','Š' => 'S','Ť' => 'T','Ţ' => 'T','Ŧ' => 'T',
            'Ț' => 'T','Ù' => 'U','Ú' => 'U','Û' => 'U','Ū' => 'U','Ů' => 'U',
            'Ű' => 'U','Ŭ' => 'U','Ũ' => 'U','Ų' => 'U','Ŵ' => 'W','Ŷ' => 'Y',
            'Ÿ' => 'Y','Ý' => 'Y','Ź' => 'Z','Ż' => 'Z','Ž' => 'Z','à' => 'a',
            'á' => 'a','â' => 'a','ã' => 'a','ā' => 'a','ą' => 'a','ă' => 'a',
            'å' => 'a','ç' => 'c','ć' => 'c','č' => 'c','ĉ' => 'c','ċ' => 'c',
            'ď' => 'd','đ' => 'd','è' => 'e','é' => 'e','ê' => 'e','ë' => 'e',
            'ē' => 'e','ę' => 'e','ě' => 'e','ĕ' => 'e','ė' => 'e','ƒ' => 'f',
            'ĝ' => 'g','ğ' => 'g','ġ' => 'g','ģ' => 'g','ĥ' => 'h','ħ' => 'h',
            'ì' => 'i','í' => 'i','î' => 'i','ï' => 'i','ī' => 'i','ĩ' => 'i',
            'ĭ' => 'i','į' => 'i','ı' => 'i','ĵ' => 'j','ķ' => 'k','ĸ' => 'k',
            'ł' => 'l','ľ' => 'l','ĺ' => 'l','ļ' => 'l','ŀ' => 'l','ñ' => 'n',
            'ń' => 'n','ň' => 'n','ņ' => 'n','ŉ' => 'n','ŋ' => 'n','ò' => 'o',
            'ó' => 'o','ô' => 'o','õ' => 'o','ø' => 'o','ō' => 'o','ő' => 'o',
            'ŏ' => 'o','ŕ' => 'r','ř' => 'r','ŗ' => 'r','ś' => 's','š' => 's',
            'ť' => 't','ù' => 'u','ú' => 'u','û' => 'u','ū' => 'u','ů' => 'u',
            'ű' => 'u','ŭ' => 'u','ũ' => 'u','ų' => 'u','ŵ' => 'w','ÿ' => 'y',
            'ý' => 'y','ŷ' => 'y','ż' => 'z','ź' => 'z','ž' => 'z','Α' => 'A',
            'Ά' => 'A','Ἀ' => 'A','Ἁ' => 'A','Ἂ' => 'A','Ἃ' => 'A','Ἄ' => 'A',
            'Ἅ' => 'A','Ἆ' => 'A','Ἇ' => 'A','ᾈ' => 'A','ᾉ' => 'A','ᾊ' => 'A',
            'ᾋ' => 'A','ᾌ' => 'A','ᾍ' => 'A','ᾎ' => 'A','ᾏ' => 'A','Ᾰ' => 'A',
            'Ᾱ' => 'A','Ὰ' => 'A','ᾼ' => 'A','Β' => 'B','Γ' => 'G','Δ' => 'D',
            'Ε' => 'E','Έ' => 'E','Ἐ' => 'E','Ἑ' => 'E','Ἒ' => 'E','Ἓ' => 'E',
            'Ἔ' => 'E','Ἕ' => 'E','Ὲ' => 'E','Ζ' => 'Z','Η' => 'I','Ή' => 'I',
            'Ἠ' => 'I','Ἡ' => 'I','Ἢ' => 'I','Ἣ' => 'I','Ἤ' => 'I','Ἥ' => 'I',
            'Ἦ' => 'I','Ἧ' => 'I','ᾘ' => 'I','ᾙ' => 'I','ᾚ' => 'I','ᾛ' => 'I',
            'ᾜ' => 'I','ᾝ' => 'I','ᾞ' => 'I','ᾟ' => 'I','Ὴ' => 'I','ῌ' => 'I',
            'Θ' => 'T','Ι' => 'I','Ί' => 'I','Ϊ' => 'I','Ἰ' => 'I','Ἱ' => 'I',
            'Ἲ' => 'I','Ἳ' => 'I','Ἴ' => 'I','Ἵ' => 'I','Ἶ' => 'I','Ἷ' => 'I',
            'Ῐ' => 'I','Ῑ' => 'I','Ὶ' => 'I','Κ' => 'K','Λ' => 'L','Μ' => 'M',
            'Ν' => 'N','Ξ' => 'K','Ο' => 'O','Ό' => 'O','Ὀ' => 'O','Ὁ' => 'O',
            'Ὂ' => 'O','Ὃ' => 'O','Ὄ' => 'O','Ὅ' => 'O','Ὸ' => 'O','Π' => 'P',
            'Ρ' => 'R','Ῥ' => 'R','Σ' => 'S','Τ' => 'T','Υ' => 'Y','Ύ' => 'Y',
            'Ϋ' => 'Y','Ὑ' => 'Y','Ὓ' => 'Y','Ὕ' => 'Y','Ὗ' => 'Y','Ῠ' => 'Y',
            'Ῡ' => 'Y','Ὺ' => 'Y','Φ' => 'F','Χ' => 'X','Ψ' => 'P','Ω' => 'O',
            'Ώ' => 'O','Ὠ' => 'O','Ὡ' => 'O','Ὢ' => 'O','Ὣ' => 'O','Ὤ' => 'O',
            'Ὥ' => 'O','Ὦ' => 'O','Ὧ' => 'O','ᾨ' => 'O','ᾩ' => 'O','ᾪ' => 'O',
            'ᾫ' => 'O','ᾬ' => 'O','ᾭ' => 'O','ᾮ' => 'O','ᾯ' => 'O','Ὼ' => 'O',
            'ῼ' => 'O','α' => 'a','ά' => 'a','ἀ' => 'a','ἁ' => 'a','ἂ' => 'a',
            'ἃ' => 'a','ἄ' => 'a','ἅ' => 'a','ἆ' => 'a','ἇ' => 'a','ᾀ' => 'a',
            'ᾁ' => 'a','ᾂ' => 'a','ᾃ' => 'a','ᾄ' => 'a','ᾅ' => 'a','ᾆ' => 'a',
            'ᾇ' => 'a','ὰ' => 'a','ᾰ' => 'a','ᾱ' => 'a','ᾲ' => 'a','ᾳ' => 'a',
            'ᾴ' => 'a','ᾶ' => 'a','ᾷ' => 'a','β' => 'b','γ' => 'g','δ' => 'd',
            'ε' => 'e','έ' => 'e','ἐ' => 'e','ἑ' => 'e','ἒ' => 'e','ἓ' => 'e',
            'ἔ' => 'e','ἕ' => 'e','ὲ' => 'e','ζ' => 'z','η' => 'i','ή' => 'i',
            'ἠ' => 'i','ἡ' => 'i','ἢ' => 'i','ἣ' => 'i','ἤ' => 'i','ἥ' => 'i',
            'ἦ' => 'i','ἧ' => 'i','ᾐ' => 'i','ᾑ' => 'i','ᾒ' => 'i','ᾓ' => 'i',
            'ᾔ' => 'i','ᾕ' => 'i','ᾖ' => 'i','ᾗ' => 'i','ὴ' => 'i','ῂ' => 'i',
            'ῃ' => 'i','ῄ' => 'i','ῆ' => 'i','ῇ' => 'i','θ' => 't','ι' => 'i',
            'ί' => 'i','ϊ' => 'i','ΐ' => 'i','ἰ' => 'i','ἱ' => 'i','ἲ' => 'i',
            'ἳ' => 'i','ἴ' => 'i','ἵ' => 'i','ἶ' => 'i','ἷ' => 'i','ὶ' => 'i',
            'ῐ' => 'i','ῑ' => 'i','ῒ' => 'i','ῖ' => 'i','ῗ' => 'i','κ' => 'k',
            'λ' => 'l','μ' => 'm','ν' => 'n','ξ' => 'k','ο' => 'o','ό' => 'o',
            'ὀ' => 'o','ὁ' => 'o','ὂ' => 'o','ὃ' => 'o','ὄ' => 'o','ὅ' => 'o',
            'ὸ' => 'o','π' => 'p','ρ' => 'r','ῤ' => 'r','ῥ' => 'r','σ' => 's',
            'ς' => 's','τ' => 't','υ' => 'y','ύ' => 'y','ϋ' => 'y','ΰ' => 'y',
            'ὐ' => 'y','ὑ' => 'y','ὒ' => 'y','ὓ' => 'y','ὔ' => 'y','ὕ' => 'y',
            'ὖ' => 'y','ὗ' => 'y','ὺ' => 'y','ῠ' => 'y','ῡ' => 'y','ῢ' => 'y',
            'ῦ' => 'y','ῧ' => 'y','φ' => 'f','χ' => 'x','ψ' => 'p','ω' => 'o',
            'ώ' => 'o','ὠ' => 'o','ὡ' => 'o','ὢ' => 'o','ὣ' => 'o','ὤ' => 'o',
            'ὥ' => 'o','ὦ' => 'o','ὧ' => 'o','ᾠ' => 'o','ᾡ' => 'o','ᾢ' => 'o',
            'ᾣ' => 'o','ᾤ' => 'o','ᾥ' => 'o','ᾦ' => 'o','ᾧ' => 'o','ὼ' => 'o',
            'ῲ' => 'o','ῳ' => 'o','ῴ' => 'o','ῶ' => 'o','ῷ' => 'o','А' => 'A',
            'Б' => 'B','В' => 'V','Г' => 'G','Д' => 'D','Е' => 'E','Ё' => 'E',
            'Ж' => 'Z','З' => 'Z','И' => 'I','Й' => 'I','К' => 'K','Л' => 'L',
            'М' => 'M','Н' => 'N','О' => 'O','П' => 'P','Р' => 'R','С' => 'S',
            'Т' => 'T','У' => 'U','Ф' => 'F','Х' => 'K','Ц' => 'T','Ч' => 'C',
            'Ш' => 'S','Щ' => 'S','Ы' => 'Y','Э' => 'E','Ю' => 'Y','Я' => 'Y',
            'а' => 'A','б' => 'B','в' => 'V','г' => 'G','д' => 'D','е' => 'E',
            'ё' => 'E','ж' => 'Z','з' => 'Z','и' => 'I','й' => 'I','к' => 'K',
            'л' => 'L','м' => 'M','н' => 'N','о' => 'O','п' => 'P','р' => 'R',
            'с' => 'S','т' => 'T','у' => 'U','ф' => 'F','х' => 'K','ц' => 'T',
            'ч' => 'C','ш' => 'S','щ' => 'S','ы' => 'Y','э' => 'E','ю' => 'Y',
            'я' => 'Y','ð' => 'd','Ð' => 'D','þ' => 't','Þ' => 'T','ა' => 'a',
            'ბ' => 'b','გ' => 'g','დ' => 'd','ე' => 'e','ვ' => 'v','ზ' => 'z',
            'თ' => 't','ი' => 'i','კ' => 'k','ლ' => 'l','მ' => 'm','ნ' => 'n',
            'ო' => 'o','პ' => 'p','ჟ' => 'z','რ' => 'r','ს' => 's','ტ' => 't',
            'უ' => 'u','ფ' => 'p','ქ' => 'k','ღ' => 'g','ყ' => 'q','შ' => 's',
            'ჩ' => 'c','ც' => 't','ძ' => 'd','წ' => 't','ჭ' => 'c','ხ' => 'k',
            'ჯ' => 'j','ჰ' => 'h' 
            );
            $this->value = str_replace(
                                array_keys( $transliteration ),
                                array_values( $transliteration ),
                                $this->value
                            );
            return $this;
    }

    public function ansi()
    {
        $ansi_table = [
            "\u00c0" =>"À",
            "\u00c1" =>"Á",
            "\u00c2" =>"Â",
            "\u00c3" =>"Ã",
            "\u00c4" =>"Ä",
            "\u00c5" =>"Å",
            "\u00c6" =>"Æ",
            "\u00c7" =>"Ç",
            "\u00c8" =>"È",
            "\u00c9" =>"É",
            "\u00ca" =>"Ê",
            "\u00cb" =>"Ë",
            "\u00cc" =>"Ì",
            "\u00cd" =>"Í",
            "\u00ce" =>"Î",
            "\u00cf" =>"Ï",
            "\u00d1" =>"Ñ",
            "\u00d2" =>"Ò",
            "\u00d3" =>"Ó",
            "\u00d4" =>"Ô",
            "\u00d5" =>"Õ",
            "\u00d6" =>"Ö",
            "\u00d8" =>"Ø",
            "\u00d9" =>"Ù",
            "\u00da" =>"Ú",
            "\u00db" =>"Û",
            "\u00dc" =>"Ü",
            "\u00dd" =>"Ý",
            "\u00df" =>"ß",
            "\u00e0" =>"à",
            "\u00e1" =>"á",
            "\u00e2" =>"â",
            "\u00e3" =>"ã",
            "\u00e4" =>"ä",
            "\u00e5" =>"å",
            "\u00e6" =>"æ",
            "\u00e7" =>"ç",
            "\u00e8" =>"è",
            "\u00e9" =>"é",
            "\u00ea" =>"ê",
            "\u00eb" =>"ë",
            "\u00ec" =>"ì",
            "\u00ed" =>"í",
            "\u00ee" =>"î",
            "\u00ef" =>"ï",
            "\u00f0" =>"ð",
            "\u00f1" =>"ñ",
            "\u00f2" =>"ò",
            "\u00f3" =>"ó",
            "\u00f4" =>"ô",
            "\u00f5" =>"õ",
            "\u00f6" =>"ö",
            "\u00f8" =>"ø",
            "\u00f9" =>"ù",
            "\u00fa" =>"ú",
            "\u00fb" =>"û",
            "\u00fc" =>"ü",
            "\u00fd" =>"ý",
            "\u00ff" =>"ÿ",
        ];
        $this->value = str_replace(
            array_keys( $ansi_table ),
            array_values( $ansi_table ),
            $this->value
        );
    }

    public function clearStringSpecialChar( $extra = false )
    {
        $arr = ['!','@','#','$','%','&','*','?','=','+','/',"/\/",'|','§','[',']','{','}','(',')','>','<','~',':',',',';',"'",'"'];
        if($extra){
            $arr[] = '.';
        }
	    $this->value = str_replace($arr,'',$this->value);
        $this->value = preg_replace('/[^\x00-\x7F]/', '', $this->value);
    }
}