<?php

namespace App\Model;

use Nette\SmartObject;

/**
 * @package App\Model
 */
class HashManager
{
    use SmartObject;

    public function encode($rawString) 
    {
        $ans = array();
        $string = str_split($rawString);

        for ($i = 0; $i < count($string); $i++) {
            $ascii = (string) ord($string[$i]);
            if (strlen($ascii) < 3)
                $ascii = '0'.$ascii;
            $ans[] = $ascii;
        }
    
        return implode('', $ans);
    }

    public function tokenize($string)
    {
        return implode('-', array_slice(str_split($string, 4), 0, 5));
    }

    public function decode($encodedString)
    {
        $ans = '';
        $string = str_split($encodedString);
        $chars = array();
    
        #construct the characters by going over the three numbers
        for ($i = 0; $i < count($string); $i+=3)
            $chars[] = $string[$i] . $string[$i+1] . $string[$i+2];
    
        #chr turns a single integer into its ASCII value
        for ($i = 0; $i < count($chars); $i++)
            $ans .= chr($chars[$i]);
    
        return $ans;
    }
}

?>