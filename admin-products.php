<?php

use Hcode\Model\PageAdmin;
use Hcode\Model\User;
use Hcode\Model\Product;

########################################################################################################################
# Páginas de Administradção de Produtos - INICIO

$app->get("/eco/admin/products", function () {

    User::verifyLogin();

    $search = (isset($_GET['search'])) ? $_GET['search'] : "";

    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

    $pagination = Product::getPage($page, $search, 5);

    $pages = [];

    for ($x = 0; $x < $pagination['pages']; $x++) {
        array_push($pages, [
            "href" => '/eco/admin/produtcs?' . http_build_query([
                    "page" => $x + 1,
                    "search" => $search
                ]),
            "text" => $x + 1
        ]);
    }

    $page = new PageAdmin();
    $page->setTpl("products", [
        "products" => $pagination['data'],
        "search" => $search,
        "pages" => $pages
    ]);

});

$app->get("/eco/admin/products/create", function () {

    User::verifyLogin();

    $page = new PageAdmin();
    $page->setTpl("products-create");

});

$app->post("/eco/admin/products/create", function () {

    User::verifyLogin();

    $product = new Product();
    $product->setData($_POST);
    $product->save();

    header("Location: /eco/admin/products");
    exit;

});

$app->get("/eco/admin/products/:idproduct", function ($idproduct) {

    User::verifyLogin();

    $product = new Product();
    $product->get((int)$idproduct);

    $page = new PageAdmin();
    $page->setTpl("products-update", array(
        "product" => $product->getValues()
    ));

});

$app->post("/eco/admin/products/:idproduct", function ($idproduct) {

    User::verifyLogin();

    $product = new Product();
    $product->get((int)$idproduct);
    $product->setData($_POST);
    $product->save();
    $product->setPhoto($_FILES["file"]);

    header("Location: /eco/admin/products");
    exit;

});

$app->get("/eco/admin/products/:idproduct/delete", function ($idproduct) {

    User::verifyLogin();

    $product = new Product();
    $product->get((int)$idproduct);
    $product->delete();

    header("Location: /eco/admin/products");
    exit;

});

?>