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

class Product extends Model
{

    public static function listAll()
    {

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");

    }

    public static function checkList($list)
    {

        foreach ($list as &$row) {

            $p = new Product();

            $p->setData($row);

            $row = $p->getValues();

        }

        return $list;

    }

    public function save()
    {

        $sql = new Sql();

        $result = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
            ":idproduct" => $this->getidproduct(),
            ":desproduct" => $this->getdesproduct(),
            ":vlprice" => $this->getvlprice(),
            ":vlwidth" => $this->getvlwidth(),
            ":vlheight" => $this->getvlheight(),
            ":vllength" => $this->getvllength(),
            ":vlweight" => $this->getvlweight(),
            ":desurl" => $this->getdesurl()
        ));

        $this->setData($result[0]);

    }

    public function get($idproduct)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :IDPRODUCT", array(
            ":IDPRODUCT" => $idproduct
        ));

        $this->setData($results[0]);

    }

    public function delete()
    {

        $sql = new Sql();

        $sql->query("DELETE FROM tb_products WHERE idproduct = :IDPRODUCT", array(
            ":IDPRODUCT" => $this->getidproduct()
        ));

    }

    public function getValues()
    {

        $this->checkPhoto();

        $values = parent::getValues();

        return $values;

    }

    public function checkPhoto()
    {

        if(file_exists(
            $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
            "res" . DIRECTORY_SEPARATOR .
            "site" . DIRECTORY_SEPARATOR .
            "img" . DIRECTORY_SEPARATOR .
            "products" . DIRECTORY_SEPARATOR .
            $this->getidproduct() . ".jpg"
        )) {

            $url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";

        } else {

            $url = "/res/site/img/product.jpg";

        }

        return $this->setdesphoto($url);

    }

    public function setPhoto($file)
    {

        if ($file["name"] != "") {

            $extension = explode(".", $file["name"]);
            $extension = end($extension);

            switch ($extension) {

                case "jpg":

                case "jpeg":
                    $image = imagecreatefromjpeg($file["tmp_name"]);
                    break;

                case "gif":
                    $image = imagecreatefromgif($file["tmp_name"]);
                    break;

                case "png":
                    $image = imagecreatefrompng($file["tmp_name"]);
                    break;

            }

            imagejpeg($image,
                $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR .
                "res" . DIRECTORY_SEPARATOR .
                "site" . DIRECTORY_SEPARATOR .
                "img" . DIRECTORY_SEPARATOR .
                "products" . DIRECTORY_SEPARATOR .
                $this->getidproduct() . ".jpg");

            imagedestroy($image);
        }

    }

    public function getFromURL($desurl)
    {

        $sql = new Sql();

        $row = $sql->select("SELECT * FROM tb_products WHERE desurl = :DESURL LIMIT 1", array(
            ":DESURL" => $desurl
        ));

        $this->setData($row[0]);

    }

    public function getCategories()
    {

        $sql = new Sql();

        return $sql->select("
            SELECT * FROM tb_categories a
            INNER JOIN tb_categoriesproducts b ON b.idcategory = a.idcategory
            WHERE b.idproduct = :IDPRODUCT", array(
            ":IDPRODUCT" => $this->getidproduct()
        ));

    }

    public static function getPage($page, $search, $itemsPerPage)
    {

        $start = ($page - 1) * $itemsPerPage;

        ($search != "") ? $likes = "WHERE desproduct LIKE :search" : $likes = " ";

        $sql = new Sql();

        $resultUsers = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_products 
            $likes
            ORDER BY desproduct
            LIMIT $start, $itemsPerPage
        ", [
            ":search" => "%" . $search . "%"
        ]);

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal");

        return [
            'data' => $resultUsers,
            'total' => (int)$resultTotal[0]['nrtotal'],
            'pages' => ceil($resultTotal[0]['nrtotal'] / $itemsPerPage)
        ];

    }

}