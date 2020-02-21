<?php

namespace Hcode\Model;

use Hcode\Model;
use Hcode\Control\Sql;

class Order extends Model
{

    const ERROR = "OrderError";
    const SUCCESS = "OrderSuccess";

    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("
            SELECT * 
            FROM tb_orders a 
            INNER JOIN tb_ordersstatus b USING(idstatus)
            INNER JOIN tb_carts c USING(idcart)
            INNER JOIN tb_users d ON d.iduser = a.iduser
            INNER JOIN tb_addresses e USING(idaddress) 
            INNER JOIN tb_persons f ON f.idperson = d.idperson
            ORDER BY a.dtregister DESC
        ");
    }

    public static function setError($msg)
    {
        $_SESSION[Order::ERROR] = $msg;
    }

    public static function getError()
    {
        $msg = (isset($_SESSION[Order::ERROR]) && $_SESSION[Order::ERROR]) ? $_SESSION[Order::ERROR] : "";

        Order::clearError();

        return $msg;
    }

    public static function clearError()
    {
        $_SESSION[Order::ERROR] = null;
    }

    public static function setSuccess($msg)
    {
        $_SESSION[Order::SUCCESS] = $msg;
    }

    public static function getSuccess()
    {
        $msg = (isset($_SESSION[Order::SUCCESS]) && $_SESSION[Order::SUCCESS]) ? $_SESSION[Order::SUCCESS] : "";

        Order::clearSuccess();

        return $msg;
    }

    public static function clearSuccess()
    {
        $_SESSION[Order::SUCCESS] = null;
    }

    public static function getPage($page, $search, $itemsPerPage)
    {
        $start = ($page - 1) * $itemsPerPage;

        ($search != "") ? $likes = "WHERE a.idorder = :id OR f.desperson LIKE :search" : $likes = " ";

        $sql = new Sql();

        $resultUsers = $sql->select("

            SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_orders a 
            INNER JOIN tb_ordersstatus b USING(idstatus)
            INNER JOIN tb_carts c USING(idcart)
            INNER JOIN tb_users d ON d.iduser = a.iduser
            INNER JOIN tb_addresses e USING(idaddress) 
            INNER JOIN tb_persons f ON f.idperson = d.idperson
            $likes
            ORDER BY a.dtregister DESC
            LIMIT $start, $itemsPerPage
        ", [
            ":search" => "%" . $search . "%",
            ":id" => $search
        ]);

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal");

        return [
            'data' => $resultUsers,
            'total' => (int)$resultTotal[0]['nrtotal'],
            'pages' => ceil($resultTotal[0]['nrtotal'] / $itemsPerPage)
        ];
    }


    /**************************************************************************************/
    //                                  FIM DOS STATICOS                                  //
    /**************************************************************************************/

    public function save()
    {
        $sql = new Sql();
        $result = $sql->select("CALL sp_orders_save(:idorder, :idcart, :iduser, :idstatus, :idaddress, :vltotal)", [
            ":idorder" => $this->getidorder(),
            ":idcart" => $this->getidcart(),
            ":iduser" => $this->getiduser(),
            ":idstatus" => $this->getidstatus(),
            ":idaddress" => $this->getidaddress(),
            ":vltotal" => $this->getvltotal()
        ]);

        if (count($result) > 0) {
            $this->setData($result[0]);
        }
    }

    public function get($idorder)
    {
        $sql = new Sql();
        $result = $sql->select("
            SELECT *, a.dtregister AS dtregister 
            FROM tb_orders a 
            INNER JOIN tb_ordersstatus b USING(idstatus)
            INNER JOIN tb_carts c USING(idcart)
            INNER JOIN tb_users d ON d.iduser = a.iduser
            INNER JOIN tb_addresses e USING(idaddress) 
            INNER JOIN tb_persons f ON f.idperson = d.idperson
            WHERE a.idorder = :IDORDER 
        ", [
            ":IDORDER" => $idorder
        ]);

        if (count($result) > 0) {
            $this->setData($result[0]);
        }
    }

    public function delete()
    {
        $sql = new Sql();
        $sql->query("DELETE FROM tb_orders WHERE idorder = :IDORDER", array(
            ":IDORDER" => $this->getidorder()
        ));
    }

}