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

class Category extends Model
{

    public static function listAll()
    {

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");

    }

    public function save()
    {

        $sql = new Sql();

        $result = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
            ":idcategory" => $this->getidcategory(),
            ":descategory" => $this->getdescategory()
        ));

        $this->setData($result[0]);

        Category::updateFile();

    }

    public function get($idcategory)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :IDCATEGORY", array(
            ":IDCATEGORY" => $idcategory
        ));

        $this->setData($results[0]);

    }

    public function delete()
    {

        $sql = new Sql();

        $sql->query("DELETE FROM tb_categories WHERE idcategory = :IDCATEGORY", array(
            ":IDCATEGORY" => $this->getidcategory()
        ));

        Category::updateFile();

    }

    public static function updateFile()
    {

        $categories = Category::listAll();

        $html = [];

        foreach ($categories as $row) {

            array_push($html, '<li><a href="/category/' . $row["idcategory"] . '">' . $row["descategory"] . '</a></li>');

        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "site" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));

    }

    public function getProducts($related = true)
    {

        $sql = new Sql();

        if ($related === true) {

            return $sql->select("
                SELECT * FROM tb_products WHERE idproduct IN (
                    SELECT a.idproduct
                    FROM tb_products a
                    INNER JOIN tb_categoriesproducts b ON a.idproduct = b.idproduct
                    WHERE b.idcategory = :IDCATEGORY
                )
              ", array (
                ":IDCATEGORY" => $this->getidcategory()
            ));

        } else {

            return $sql->select("
                SELECT * FROM tb_products WHERE idproduct NOT IN (
                    SELECT a.idproduct
                    FROM tb_products a
                    INNER JOIN tb_categoriesproducts b ON a.idproduct = b.idproduct
                    WHERE b.idcategory = :IDCATEGORY
                )
            ", array (
                ":IDCATEGORY" => $this->getidcategory()
            ));

        }

    }

    public function getProductsPage($page = 1, $itemsPerPage = 8)
    {

        $start = ($page - 1) * $itemsPerPage;

        $sql = new Sql();

        $resultProdutcs = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
                FROM tb_products a
                INNER JOIN tb_categoriesproducts b ON b.idproduct = a.idproduct
                INNER JOIN tb_categories c ON c.idcategory = b.idcategory
                WHERE c.idcategory = :IDCATEGORY
                LIMIT $start, $itemsPerPage
        ", array(
            ":IDCATEGORY" => $this->getidcategory()
        ));

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal");

        return [
            'data' => Product::checkList($resultProdutcs),
            'total' => (int)$resultTotal[0]['nrtotal'],
            'pages' => ceil($resultTotal[0]['nrtotal'] / $itemsPerPage)
        ];

    }

    public function addProduct($idproduct)
    {

        $sql = new Sql();

        $sql->query("INSERT INTO tb_categoriesproducts (idcategory, idproduct) VALUES (:IDCATEGORY, :IDPRODUCT)", array(
            ":IDCATEGORY" => $this->getidcategory(),
            ":IDPRODUCT" => $idproduct
        ));

    }

    public function removeProduct($idproduct)
    {

        $sql = new Sql();

        $sql->query("DELETE FROM tb_categoriesproducts WHERE idcategory = :IDCATEGORY AND idproduct = :IDPRODUCT", array(
            ":IDCATEGORY" => $this->getidcategory(),
            ":IDPRODUCT" => $idproduct
        ));

    }

}