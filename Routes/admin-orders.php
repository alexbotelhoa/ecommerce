<?php

use Hcode\Model\User;
use Hcode\Model\Order;
use Hcode\Model\OrderStatus;
use Hcode\Model\Cart;
use Hcode\Control\PageAdmin;

########################################################################################################################
# Páginas de Ordem de Pagamento - INICIO

$app->get("/eco/admin/orders/:idorder/status", function ($idorder) {

    User::verifyLogin();

    $order = new Order();
    $order->get((int)$idorder);

    $cart = new Cart();
    $cart->get((int)$order->getidcart());

    $page = new PageAdmin();
    $page->setTpl("order-status", [
        "order" => $order->getValues(),
        "status" => OrderStatus::listAll(),
        "msgError" => Order::getError(),
        "msgSuccess" => Order::getSuccess()
    ]);

});

$app->post("/eco/admin/orders/:idorder/status", function ($idorder) {

    User::verifyLogin();

    if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0) {
        Order::setError("Informe o status atual.");
        header("Location: /eco/admin/orders/" . $idorder . "/status");
        exit;
    }

    $order = new Order();
    $order->get((int)$idorder);
    $order->setidstatus((int)$_POST['idstatus']);
    if ($_SESSION['DEMOECO'] != true) $order->delete();

    Order::setSuccess("Status atualizado.");

    header("Location: /eco/admin/orders/" . $idorder . "/status");
    exit;

});

$app->get("/eco/admin/orders/:idorder/delete", function ($idorder) {

    User::verifyLogin();

    $order = new Order();
    $order->get((int)$idorder);
    if ($_SESSION['DEMOECO'] != true) $order->delete();

    header("Location: /eco/admin/orders");
    exit;

});

$app->get("/eco/admin/orders/:idorder", function ($idorder) {

    User::verifyLogin();

    $order = new Order();
    $order->get((int)$idorder);

    $cart = new Cart();
    $cart->get((int)$order->getidcart());

    $page = new PageAdmin();
    $page->setTpl("order", [
        "order" => $order->getValues(),
        "products" => $cart->getProducts(),
        "cart" => $cart->getValues()
    ]);

});

$app->get("/eco/admin/orders", function () {

    User::verifyLogin();

    $search = (isset($_GET['search'])) ? $_GET['search'] : "";

    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

    $pagination = Order::getPage($page, $search, 5);

    $pages = [];

    for ($x = 0; $x < $pagination['pages']; $x++) {
        array_push($pages, [
            "href" => '/eco/admin/orders?' . http_build_query([
                    "page" => $x + 1,
                    "search" => $search
                ]),
            "text" => $x + 1
        ]);
    }

    $page = new PageAdmin();
    $page->setTpl("orders", [
        "orders" => $pagination['data'],
        "search" => $search,
        "pages" => $pages
    ]);

});

?>