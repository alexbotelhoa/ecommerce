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
        "products" => Product::checkList($products)
    ]);

});

$app->get("/admin", function() {

    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("index");

});

$app->get("/category/:idcategory", function($idcategory) {

    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

    $category = new Category();

    $category->get((int)$idcategory);

    $pagination = $category->getProductsPage($page);

    $pages = [];

    for ($i = 1; $i <= $pagination['pages']; $i++) {
        array_push($pages, [
            'link' => '/category/' . $category->getidcategory() . '?page=' . $i,
            'page' => $i
        ]);
    }

    $page = new Page();

    $page->setTpl("category", [
        "category" => $category->getValues(),
        "products" => $pagination['data'],
        "pages" => $pages
    ]);
});

$app->get("/product/:desurl", function($desurl) {

    $product = new Product();

    $product->getFromURL($desurl);

    $category = new Category();

    //$category->get((int)$idcategory);

    $page = new Page();

    $page->setTpl("product-detail", [
        'product' => $product->getValues(),
        'categories' => $product->getCategories()
    ]);
});

/*
 * Páginas do Site - FIM
 * #############################################
 */

?>