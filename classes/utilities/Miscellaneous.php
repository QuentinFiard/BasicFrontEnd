<?php

namespace utilities;

class Miscellaneous {

	private static $html_entities_table = null;

	public static function textToHTML($text)
	{
		$text = nl2br($text);
		if(!self::$html_entities_table)
		{
			$list = get_html_translation_table(HTML_ENTITIES);
			unset($list['"']);
			unset($list['<']);
			unset($list['>']);
			unset($list['&']);
			self::$html_entities_table = $list;
		}
		$search = array_keys(self::$html_entities_table);
		$values = array_values(self::$html_entities_table);
		$text = str_replace($search, $values, $text);

		return $text;
	}

	static public function isValidDigest($digest)
	{
		return strlen($digest)==64 && ctype_xdigit($digest);
	}

	static public function isValidConfirmationId($digest)
	{
		return strlen($digest)==32 && ctype_xdigit($digest);
	}

	static private $allowedCharacters = null;

	static public function initAllowedCharacters()
	{
		self::$allowedCharacters = array();

		$char = 'a';
		$value = ord($char);

		while($value<=ord('z'))
		{
			self::$allowedCharacters[] = $char;
			$value++;
			$char = chr($value);
		}

		$char = 'A';
		$value = ord($char);

		while($value<=ord('Z'))
		{
			self::$allowedCharacters[] = $char;
			$value++;
			$char = chr($value);
		}

		$char = '0';
		$value = ord($char);

		while($value<=ord('9'))
		{
			self::$allowedCharacters[] = $char;
			$value++;
			$char = chr($value);
		}
	}

	static public function passwordFromBytes($bytes)
	{
		if(!self::$allowedCharacters)
		{
			self::initAllowedCharacters();
		}
		$hex = \bin2hex($bytes);
		$res="";
		for($i=0 ; $i<strlen($bytes) ; $i++)
		{
			$byte = \hexdec(substr($hex, 2*$i,2));
			$index = $byte%count(self::$allowedCharacters);
			$res .= self::$allowedCharacters[$index];
		}
		return $res;
	}

	static public function generateRandomPassword($length)
	{
		$random_bytes = openssl_random_pseudo_bytes($length);
		return self::passwordFromBytes($random_bytes);
	}

	static public function hex2bin( $data ) {
		/* Original code by josh <at> superfork.com */

		$len = strlen($data);
		$newdata = '';
		for( $i=0; $i < $len; $i += 2 ) {
			$newdata .= pack( "C", hexdec( substr( $data, $i, 2) ) );
		}
		return $newdata;
	}

	static public function isInt($var)
	{
		if (strval(intval($var)) == strval($var)) {
			return true;
		}
		return false;
	}

	static public function prettifyPhoneNumber($phoneNumber)
	{
	    $res = "";
	    for($i=0 ; $i<5 ; $i++)
	    {
    	    if($i>0)
	        {
    	            $res .= ' ';
    	    }
    	    $res .= substr($this->phoneNumber, 2*$i, 2);
        }
        return $res;
	}

	static public function prettifyDate($date)
	{
	    setlocale(LC_ALL, 'fr_FR');
	    return strftime("%e %B %Y",$date);
	}

	static public function prettifyDate2($date)
	{
	    setlocale(LC_ALL, 'fr_FR');
	    return strftime("%e %b %Y",$date);
	}

	static public function prettifyDate3($date)
	{
	    return strftime("%d/%m/%Y",$date);
	}

	static public function alert($message)
	{
	    $message = str_replace('"','\"', $message);
	    ?>
	    <script type="text/javascript">
            alert("<?php echo $message ?>");
	    </script>
	    <?php
	}

}

?>