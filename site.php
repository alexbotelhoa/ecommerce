<?php

use Hcode\Page;
use Hcode\PageAdmin;
use Hcode\Model\User;
use Hcode\Model\Category;
use Hcode\Model\Product;

/*
 * #############################################
 * Páginas do Site - INICIO
 */

$app->get("/", function() {

    $products = Product::listAll();

    $page = new Page();

    $page->setTpl("index", [
        "products" => $products
    ]);

});

$app->get("/admin", function() {

    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("index");

});

$app->get("/category/:idcategory", function($idcategory) {

    $category = new Category();

    $category->get((int)$idcategory);

    $page = new Page();

    $page->setTpl("category", [
        "category" => $category->getValues(),
        "products" => []
    ]);
});

/*
 * Páginas do Site - FIM
 * #############################################
 */

?>