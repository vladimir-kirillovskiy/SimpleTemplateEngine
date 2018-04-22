<?php

class Engine {
    public function render($view, $params = []) {

        if (file_exists($view)) {

            $html = file_get_contents($view);
            $output = "";
            foreach ($params as $key => $val) {


                $html = $this->handleString($html, $key, $val);

                // handle each loop
                $output = $this->handleLoop($html, $key, $val);
                echo $output;
            }

        } else {
            throw new \Exception(sprintf('The file %s could not be found.', $view));
        }

    }

    /*
    * @desc function to replace all occurrences of the given key in the string 
    * @param string $string string to work with
    * @param string $key string to look for and replace
    * @param string $value string used to replace $key 
    * @return string
    */
    private function handleString($string, $key, $value) {
        if (!is_array($value)) $string = str_replace('{{' . $key . '}}', $value, $string);
        return $string;
    }

    /*
    * @desc function to impement each loop functionality
    * @param string $string string to work with
    * @param string $key index of the array iterate through
    * @param $array $arr array which contain indexes and values to replace 
    * @return string
    */
    private function handleLoop($string, $key, $arr) {
        $final_each_content = "";

        // get the string between "each" tags
        $each_content = $this->stripStringFromTemplateTags($string, "each", $key);

        // foreach ($arr as $sval) {
        for ($i = 0; $i < count($arr); $i++) {

            // make a temp variable to replace multiple values
            $temp_content = $each_content;
            // iterate through multiple values and replace where needed
            foreach ($arr[$i] as $sk => $ssv) {
                $temp_content = $this->handleString($temp_content, $sk, $ssv);
            }

            $final_each_content .= $this->handleUnless($temp_content, $i, count($arr));
        }

        // contatinate final string
        return $this->stripStringFromTemplateTags($string, "each", $key, $final_each_content);
    }

    /*
    * @desc function to get the the contend of the template tags. 
    * @param string $string - string with all template tags to work with 
    * @param string $tag - type of the tag - #each, #unless, ...
    * @param string $key - value to replace
    * @param string $reverseString - string to reverse process, to concatinate instead of strip
    * @return string - Return striped string or if $reverseString is set
    * return full string with tags removed. 
    */
    private function stripStringFromTemplateTags($string, $tag, $key, $reverseString="") {
        // string for each section to search
        $str_start = "{{#" . $tag . " " . $key . "}}";

        if (strpos($string, $str_start) != false) {
            // length of the bracketed string
            $str_len = strlen($str_start);
            // start position of the each section including brackets and etc
            $pos_start = strpos($string, $str_start);
            // position of the string content 
            $content_pos = $pos_start + $str_len;

            // string for the end of the each section
            $str_end = "{{/" . $tag . "}}";
            // position of the closing tag for each section
            $pos_end = strpos($string, $str_end);

            if ($reverseString != "") {
                return substr($string, 0, $pos_start) 
                . $reverseString . substr($string, $pos_end + strlen($str_end));
            }

            // part of the text to repeat 
            return substr($string, $content_pos, $pos_end - $content_pos);
           
        }
    }

    /*
    * @desc function to handle #unless tags. works @first and @last conditions
    * @param string $string - a string to work with; to replace tags with values
    * @param int $count - counter for parent loop.
    * @param int $arr_length - total number of items in array we iterate in parent loop
    * @return string
    */
    private function handleUnless($string, $count, $arr_length) {

        $unless_options = ["@first", "@last"];

        $option = "";
        foreach ($unless_options as $value) {
            if (strpos($string, $value)) $option = $value;
        }

        if ($option != "") {
            // strip unless tags
            $temp_string = $this->stripStringFromTemplateTags($string, "unless", $option);
            // echo $temp_string . "<br>";

            // sort out else tag
            $else_string = "{{else}}";
            $else_pos = strpos($temp_string, $else_string);
            
            $opt1 = $temp_string;
            $opt2 = "";
            //  if else clause exists
            if ( $else_pos != false) {
                $opt1 = substr($temp_string, 0, $else_pos);
                $opt2 = substr($temp_string, $else_pos + strlen($else_string));
            } 

            // switch for @last and @first
            switch ($option) {
                case '@first':
                    // of this is a first loop and else clause exist 
                    // then return string with value from else clause
                    if ($count == 0 && $else_pos != false) {
                        return $this->stripStringFromTemplateTags($string, "unless", $option, $opt2);
                    } else {
                        return $this->stripStringFromTemplateTags($string, "unless", $option, $opt1);
                    }
                    # code...
                    break;
                case '@last':
                    // of this is a last loop and else clause exist 
                    // then return string with value from else clause
                    if ($count == ($arr_length - 1) && $else_pos != false) {
                        return $this->stripStringFromTemplateTags($string, "unless", $option, $opt2);
                    } else {
                        return $this->stripStringFromTemplateTags($string, "unless", $option, $opt1);
                    }
                    break;
                default:
                    // if unknow case - just return original string
                    return $string;
                    break;
            }

        } else {
            return $string;
        }

    }


}

?>