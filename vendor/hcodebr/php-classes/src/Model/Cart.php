<?php
/**
 * Created by PhpStorm.
 * User: Alex Botelho
 * Date: 27/12/2019
 * Time: 09:07
 */

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;
use Hcode\Model\User;

class Cart extends Model
{

    const SESSION = "Cart";

    public function save()
    {

        $sql = new Sql();

        $result = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", array(
            ":idcart" => $this->getidcart(),
            ":dessessionid" => $this->getdessessionid(),
            ":iduser" => $this->getiduser(),
            ":deszipcode" => $this->getidaddress(),
            ":vlfreight" => $this->getvlfreight(),
            ":nrdays" => $this->getnrdays()
        ));

        $this->setData($result[0]);

    }

    public static function getFromSession()
    {

        $cart = new Cart();

        if (isset($_SESSION[Cart::SESSION]) && (int)$_SESSION[Cart::SESSION]['idcart'] > 0) {

            $cart->get((int)$_SESSION[Cart::SESSION]['idcart']);

        } else {

            $cart->getFromSessionID();

            if (!(int)$cart->getidcart() > 0) {

                $data = [
                    'dessessionid' => SESSION_ID(),
                ];

                if (User::checkLogin(false)) {

                    $user = User::getFromSession();

                    $data['iduser'] = $user->getiduser();

                }

                $cart->setData($data);

                $cart->save();

                $cart->setToSession();

            }

        }

        return $cart;

    }

    public function setToSession()
    {

        $_SESSION[Cart::SESSION] = $this->getValues();

    }

    public function get($idcart)
    {

        $sql = new Sql();

        $result = $sql->select("SELECT * FROM tb_carts WHERE idcart = :IDCART", array(
            ":IDCART" => $idcart
        ));

        if (count($result) > 0) {

            $this->setData($result[0]);

        }

    }

    public function getFromSessionID()
    {

        $sql = new Sql();

        $result = $sql->select("SELECT * FROM tb_carts WHERE dessessionid = :DESSESSIONID", array(
            ":DESSESSIONID" => SESSION_ID()
        ));

        if (count($result) > 0) {

            $this->setData($result[0]);

        }

    }

    public function addProduct(Product $product)
    {

        $sql = new Sql();

        $sql->query("INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES (:IDCART, :IDPRODUCT)", array(
            ":IDCART" => $this->getidcart(),
            ":IDPRODUCT" => $product->getidproduct()
        ));

    }

    public function removeProduct(Product $product, $all = false)
    {

        $sql = new Sql();

        if ($all) {

            $sql->query("
                UPDATE tb_cartsproducts 
                SET dtremoved = NOW() 
                WHERE idcart = :IDCART AND idproduct = :IDPRODUCT AND dtremoved IS NULL", array(
                ":IDCART" => $this->getidcart(),
                ":IDPRODUCT" => $product->getidproduct()
            ));

        } else {

            $sql->query("
                UPDATE tb_cartsproducts 
                SET dtremoved = NOW() 
                WHERE idcart = :IDCART AND idproduct = :IDPRODUCT AND dtremoved IS NULL LIMIT 1", array(
                ":IDCART" => $this->getidcart(),
                ":IDPRODUCT" => $product->getidproduct()
            ));

        }

    }

    public function getProduct()
    {

        $sql = new Sql();

        $rows = $sql->select("
            SELECT b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
            FROM tb_cartsproducts a 
            INNER JOIN tb_products b ON a.idproduct = b.idproduct 
            WHERE a.idcart = :IDCART AND a.dtremoved IS NULL 
            GROUP BY b.idproduct, b.desproduct, b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
            ORDER BY b.desproduct", array(
            ":IDCART" => $this->getidcart()
        ));

        return Product::checkList($rows);

    }

}