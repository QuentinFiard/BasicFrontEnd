<?
/**
 * Pork Formvalidator. validates fields by regexes and can sanatize them. Uses PHP filter_var built-in functions and extra regexes
 * @package pork
 */


/**
 * Pork.FormValidator
 * Validates arrays or properties by setting up simple arrays
 *
 * @package pork
 * @author SchizoDuckie
 * @copyright SchizoDuckie 2009
 * @version 1.0
 * @access public
 */

namespace utilities;

class FormValidator
{
    public static $regexes = Array(
                'date' => '|^[0-9]{4}[-/][0-9]{1,2}[-/][0-9]{1,2}$|i',
                'frenchDate' => '|^[0-9]{2}[-/][0-9]{2}[-/][0-9]{4}$|i',
                'postalCode' => '|^[0-9]{5}$|',
                'siretNumber' => '|^[0-9]{14}$|',
                'amount' => '/^[-]?[0-9]+$/i',
                'number' => '/^[-]?[0-9,]+$/i',
                'positiveNumber' => '/^[0-9.]+$/i',
    			'name' => '/^(?:[\p{L}\p{Mn}\p{Pd}\'\x{2019}]+\s?)+$/u',
                'alfanum' => '/^[0-9a-zA-Z ,.-_\\s\?\!]+$/i',
                'not_empty' => '/[a-z0-9A-Z]+/i',
                'words' => '/^[A-Za-z]+[A-Za-z \\s]*$/i',
                'phone' => '/^[0-9]{10,11}$/i',
                'prettyPhone' => '/^0[0-9] ?[0-9]{2} ?[0-9]{2} ?[0-9]{2} ?[0-9]{2}$/i',
                'zipcode' => '/^[1-9][0-9]{3}[a-zA-Z]{2}$/i',
                'plate' => '/^([0-9a-zA-Z]{2}[-]){2}[0-9a-zA-Z]{2}$/i',
                'price' => '/^[0-9.,]*(([.,][-])|([.,][0-9]{2}))?$/i',
                '2digitopt' => '/^\d+(\,\d{2})?$/i',
                '2digitforce' => '/^\d+\,\d\d$/i',
                'anything' => '/^[\d\D]{1,}$/i'
    );
    private $validations, $sanatations, $mandatories, $errors, $corrects, $fields;


    public function __construct($validations=array(), $mandatories = array(), $sanatations = array())
    {
        $this->validations = $validations;
        $this->sanatations = $sanatations;
        $this->mandatories = $mandatories;
        $this->errors = array();
        $this->corrects = array();
    }

    /**
     * Validates an array of items (if needed) and returns true or false
     *
     */
    public function validate($items)
    {
        $this->fields = $items;
        foreach($items as $key=>$val)
        {
            if((!array_key_exists($key, $this->validations)) && (!array_key_exists($key, $this->mandatories) || (strlen($val) > 0)))
            {
                if(!array_key_exists($key, $this->errors))
                {
                    $this->corrects[] = $key;
                }
                continue;
            }
            if(array_key_exists($key, $this->mandatories) && (strlen($val) == 0))
            {
                $this->addError($key);
                continue;
            }
            $result = self::validateItem($val, $this->validations[$key]);
            if($result === false) {
                $this->addError($key, $this->validations[$key]);
            }
            else
            {
                if(!array_key_exists($key, $this->errors))
                {
                    $this->corrects[] = $key;
                }
            }
        }

        return (count(array_keys($this->errors)) == 0);
    }

    /**
     *
     *  Adds unvalidated class to thos elements that are not validated. Removes them from classes that are.
     */
    public function getScript() {
        if(!empty($this->errors))
        {
            $errors = array();
            foreach($this->errors as $key=>$val) { $errors[] = "input[name={$key}]"; }

            $output = 'jQuery("'.implode(',', $errors).'").addClass("unvalidated");';
            $output .= "alert('Certains champs sont invalides');"; // or your nice validation here
        }
        if(!empty($this->corrects))
        {
            $corrects = array();
            foreach($this->corrects as $key) { $corrects[] = "input[name={$key}]"; }
            $output .= 'jQuery("'.implode(',', $corrects).'").removeClass("unvalidated");';
        }
        $output = "<script type='text/javascript'>".$output."</script>";
        return($output);
    }


    /**
     *
     * Sanatizes an array of items according to the $this->sanatations
     * sanatations will be standard of type string, but can also be specified.
     * For ease of use, this syntax is accepted:
     * $sanatations = array('fieldname', 'otherfieldname'=>'float');
     */
    public function sanatize($items)
    {
        foreach($items as $key=>$val)
        {
            if(array_search($key, $this->sanatations) === false && !array_key_exists($key, $this->sanatations)) continue;
            $items[$key] = self::sanatizeItem($val, $this->validations[$key]);
        }
        return($items);
    }


    /**
     *
     * Adds an error to the errors array.
     */
    public function addError($field, $type='string')
    {
        $this->errors[$field] = $type;
        if(($key = array_search($field, $this->corrects))!=false)
        {
            unset($this->corrects[$key]);
        }
    }

    /**
     *
     * Sanatize a single var according to $type.
     * Allows for static calling to allow simple sanatization
     */
    public static function sanatizeItem($var, $type)
    {
        $flags = NULL;
        switch($type)
        {
                case 'url':
                        $filter = FILTER_SANITIZE_URL;
                break;
                case 'int':
                        $filter = FILTER_SANITIZE_NUMBER_INT;
                break;
                case 'float':
                        $filter = FILTER_SANITIZE_NUMBER_FLOAT;
                        $flags = FILTER_FLAG_ALLOW_FRACTION | FILTER_FLAG_ALLOW_THOUSAND;
                break;
                case 'email':
                        $var = substr($var, 0, 254);
                        $filter = FILTER_SANITIZE_EMAIL;
                break;
                case 'string':
                default:
                        $filter = FILTER_SANITIZE_STRING;
                        $flags = FILTER_FLAG_NO_ENCODE_QUOTES;
                break;

        }
        $output = filter_var($var, $filter, $flags);
        return($output);
    }

    /**
     *
     * Validates a single var according to $type.
     * Allows for static calling to allow simple validation.
     *
     */
    public static function validateItem($var, $type)
    {
        if(array_key_exists($type, self::$regexes))
        {
            $returnval =  filter_var($var, FILTER_VALIDATE_REGEXP, array("options"=> array("regexp"=>self::$regexes[$type]))) !== false;
            return($returnval);
        }
        $filter = false;
        switch($type)
        {
            case 'email':
                    $var = substr($var, 0, 254);
                    $filter = FILTER_VALIDATE_EMAIL;
            break;
            case 'int':
                    $filter = FILTER_VALIDATE_INT;
            break;
            case 'boolean':
                    $filter = FILTER_VALIDATE_BOOLEAN;
            break;
            case 'ip':
                    $filter = FILTER_VALIDATE_IP;
            break;
            case 'url':
                    $filter = FILTER_VALIDATE_URL;
            break;
        }
        return ($filter === false) ? false : filter_var($var, $filter) !== false ? true : false;
    }



}