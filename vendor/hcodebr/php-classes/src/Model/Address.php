<?php
/**
 * Created by PhpStorm.
 * User: Alex Botelho
 * Date: 20/01/2020
 * Time: 10:08
 */

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;

class Address extends Model
{

    const ERROR = "AddressError";

    public static function getCEP($nrcep)
    {

        $nrcep = str_replace("-", "", $nrcep);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "http://viacep.com.br/ws/" . $nrcep . "/json/");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $data = json_decode(curl_exec($ch), true);

        curl_close($ch);

        return $data;

    }

    public function loadFromCEP($zipcode)
    {

        $data = Address::getCEP($zipcode);

        if (isset($data['logradouro']) && $data['logradouro']) {

            $this->setdesaddress($data['logradouro']);
            $this->setdescomplement($data['complemento']);
            $this->setdesdistrict($data['bairro']);
            $this->setdescity($data['localidade']);
            $this->setdesstate($data['uf']);
            $this->setdescountry('Brasil');
            $this->setdeszipcode($zipcode);

        }

    }

    public function save()
    {

        $sql = new Sql();

        $result = $sql->select("CALL sp_addresses_save(:idaddress, :idperson, :desaddress, :descomplement, :desdistrict, :descity, :desstate, :descountry, :deszipcode)", array(
            ":idaddress" => $this->getidaddress(),
            ":idperson" => $this->getidperson(),
            ":desaddress" => utf8_decode($this->getdesaddress()),
            ":descomplement" => utf8_decode($this->getdescomplement()),
            ":desdistrict" => utf8_decode($this->getdesdistrict()),
            ":descity" => utf8_decode($this->getdescity()),
            ":desstate" => utf8_decode($this->getdesstate()),
            ":descountry" => utf8_decode($this->getdescountry()),
            ":deszipcode" => $this->getdeszipcode()
        ));

        if(count($result) > 0) {

            $this->setData($result[0]);

        }

    }

    public static function setMsgError($msg)
    {

        $_SESSION[Address::ERROR] = $msg;

    }

    public static function getMsgError()
    {

        $msg = (isset($_SESSION[Address::ERROR])) ? $_SESSION[Address::ERROR] : "";

        Address::clearMsgError();

        return $msg;

    }

    public static function clearMsgError()
    {

        $_SESSION[Address::ERROR] = NULL;

    }

}