<?php

namespace Hcode;

class Model
{
    private $value = [];

    public function __call($name, $arguments)
    {

        $namethod = substr($name, 0, 3);
        $fieldname = substr($name, 3, strlen($name));

        switch ($namethod) {
            case "get":
                $this->value[$fieldname];
            break;
            case "set":
                $this->value[$fieldname] = $arguments[0];
            break;
        }

    }

    public function setData($data = array())
    {

        foreach ($data as $key => $value) {

            $this->{"set" . $key}($value);

        }

    }

    public function getValue()
    {
        return $this->value;
    }

}

?>