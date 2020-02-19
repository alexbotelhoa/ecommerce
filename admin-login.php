<?php

use Hcode\Model\PageAdmin;
use Hcode\Model\User;


########################################################################################################################
# Páginas de Login - INICIO

$app->get("/eco/admin/login", function () {

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("login", [
        "error" => User::getError(),
    ]);

});

$app->post("/eco/admin/login", function () {

    try {
        User::login($_POST['login'], $_POST['password']);
    } catch (Exception $e) {
        User::setError($e->getMessage());
    }

    header("Location: /eco/admin");
    exit;

});

$app->get("/eco/admin/logout", function () {

    User::logout();

    header("Location: /eco/admin/login");
    exit;

});

?>