<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;

class Cart extends Model
{

    const SESSION = "Cart";
    const ERROR = "CartError";

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

    public static function formatValueToDecimal($value): float
    {
        return str_replace(',', '.', $value);
    }

    public static function setMsgError($msg)
    {
        $_SESSION[Cart::ERROR] = $msg;
    }

    public static function getMsgError()
    {
        $msg = (isset($_SESSION[Cart::ERROR])) ? $_SESSION[Cart::ERROR] : "";

        Cart::clearMsgError();

        return $msg;
    }

    public static function clearMsgError()
    {
        $_SESSION[Cart::ERROR] = null;
    }


    /**************************************************************************************/
    //                                  FIM DOS STATICOS                                  //
    /**************************************************************************************/

    public function save()
    {
        $sql = new Sql();
        $result = $sql->select("CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", array(
            ":idcart" => $this->getidcart(),
            ":dessessionid" => $this->getdessessionid(),
            ":iduser" => $this->getiduser(),
            ":deszipcode" => $this->getdeszipcode(),
            ":vlfreight" => $this->getvlfreight(),
            ":nrdays" => $this->getnrdays()
        ));

        $this->setData($result[0]);
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

        $this->getCalculateTotal();
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

        $this->getCalculateTotal();
    }

    public function getProducts()
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

    public function getProductsTotal()
    {
        $sql = new Sql();
        $result = $sql->select("
            SELECT 
                SUM(vlprice) AS vlprice, 
                SUM(vlwidth) AS vlwidth, 
                SUM(vlheight) AS vlheight, 
                SUM(vllength) AS vllength,
                SUM(vlweight) AS vlweight,
                COUNT(*) AS nrqtd
            FROM tb_products a
            INNER JOIN tb_cartsproducts b USING (idproduct)
            WHERE b.idcart = :IDCART AND dtremoved IS NULL", array(
            ":IDCART" => $this->getidcart()
        ));

        if (count($result) > 0) {
            return $result[0];
        } else {
            return [];
        }
    }

    public function setFreight($nrzipcode)
    {
        $nrzipcode = str_replace('-', '', $nrzipcode);

        $total = $this->getProductsTotal();

        if ($total['nrqtd'] > 0) {
            if ($total['vlheight'] < 2) {
                $total['vlheight'] = 2;
            }
            if ($total['vllength'] < 16) {
                $total['vllength'] = 16;
            }

            $qs = http_build_query([
                "nCdEmpresa" => '',
                "sDsSenha" => '',
                "nCdServico" => '40010',
                "sCepOrigem" => '09853120',
                "sCepDestino" => $nrzipcode,
                "nVlPeso" => $total['vlweight'],
                "nCdFormato" => '1',
                "nVlComprimento" => $total['vllength'],
                "nVlAltura" => $total['vlheight'],
                "nVlLargura" => $total['vlwidth'],
                "nVlDiametro" => '0',
                "sCdMaoPropria" => 'S',
                "nVlValorDeclarado" => $total['vlprice'],
                "sCdAvisoRecebimento" => 'S'
            ]);

            $xml = simplexml_load_file("http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?" . $qs);

            $result = $xml->Servicos->cServico;

            if ($result->MsgErro != "") {
                Cart::setMsgError("$result->MsgErro");
            } else {
                Cart::clearMsgError();
            }

            $this->setnrdays($result->PrazoEntrega);
            $this->setvlfreight(Cart::formatValueToDecimal($result->Valor));
            $this->setdeszipcode($nrzipcode);
            $this->save();

            return $result;
        }
    }

    public function updateFreight()
    {
        if ($this->getdeszipcode() != '') {
            $this->setFreight($this->getdeszipcode());
        }
    }

    public function getValues()
    {
        $this->getCalculateTotal();

        return parent::getValues();
    }

    public function getCalculateTotal()
    {
        $this->updateFreight();
        $total = $this->getProductsTotal();
        $this->setvlsubtotal($total['vlprice']);
        $this->setvltotal($total['vlprice'] + $this->getvlfreight());
    }

}