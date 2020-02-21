<?php

use Hcode\Model\User;
use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Control\PageAdmin;


########################################################################################################################
# Páginas de Administradção de Categorias - INICIO

$app->get("/eco/admin/categories", function () {

    User::verifyLogin();

    $search = (isset($_GET['search'])) ? $_GET['search'] : "";

    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

    $pagination = Category::getPage($page, $search, 5);

    $pages = [];

    for ($x = 0; $x < $pagination['pages']; $x++) {
        array_push($pages, [
            "href" => '/eco/admin/categories?' . http_build_query([
                    "page" => $x + 1,
                    "search" => $search
                ]),
            "text" => $x + 1
        ]);
    }

    $page = new PageAdmin();
    $page->setTpl("categories", [
        "categories" => $pagination['data'],
        "search" => $search,
        "pages" => $pages
    ]);

});

$app->get("/eco/admin/categories/create", function () {

    User::verifyLogin();

    $page = new PageAdmin();
    $page->setTpl("categories-create");

});

$app->post("/eco/admin/categories/create", function () {

    User::verifyLogin();

    $category = new Category();
    $category->setData($_POST);
    if ($_SESSION['DEMOECO'] != true) $category->save();

    header("Location: /eco/admin/categories");
    exit;

});

$app->get("/eco/admin/categories/:idcategory", function ($idcategory) {

    User::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);

    $page = new PageAdmin();
    $page->setTpl("categories-update", array(
        "category" => $category->getValues()
    ));

});

$app->post("/eco/admin/categories/:idcategory", function ($idcategory) {

    User::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);
    $category->setData($_POST);
    if ($_SESSION['DEMOECO'] != true) $category->save();

    header("Location: /eco/admin/categories");
    exit;

});

$app->get("/eco/admin/categories/:idcategory/delete", function ($idcategory) {

    User::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);
    if ($_SESSION['DEMOECO'] != true) $category->delete();

    header("Location: /eco/admin/categories");
    exit;

});

$app->get("/eco/admin/categories/:idcategory/products", function ($idcategory) {

    User::verifyLogin();

    $category = new Category();
    $category->get((int)$idcategory);

    $page = new PageAdmin();
    $page->setTpl("categories-products", array(
        "category" => $category->getValues(),
        "productsRelated" => $category->getProducts(),
        "productsNotRelated" => $category->getProducts(false)
    ));

});

$app->get("/eco/admin/categories/:idcategory/products/:idproduct/add", function ($idcategory, $idproduct) {

    User::verifyLogin();

    $product = new Product();
    $product->get((int)$idproduct);

    $category = new Category();
    $category->get((int)$idcategory);
    $category->addProduct($idproduct);

    header("Location: /eco/admin/categories/" . $idcategory . "/products");
    exit;

});

$app->get("/eco/admin/categories/:idcategory/products/:idproduct/remove", function ($idcategory, $idproduct) {

    User::verifyLogin();

    $product = new Product();
    $product->get((int)$idproduct);

    $category = new Category();
    $category->get((int)$idcategory);
    $category->removeProduct($idproduct);

    header("Location: /eco/admin/categories/" . $idcategory . "/products");
    exit;

});

?>