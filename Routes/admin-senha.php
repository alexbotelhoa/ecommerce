<?php

use Hcode\Model\User;
use Hcode\Control\PageAdmin;

########################################################################################################################
# Recuperação de Senha - INICIO

$app->get("/eco/admin/forgot", function () {

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);

    $page->setTpl("forgot");

});

$app->post("/eco/admin/forgot", function () {

    $user = User::getForgot($_POST["email"]);

    header("Location: /eco/admin/forgot/sent");
    exit;

});

$app->get("/eco/admin/forgot/sent", function () {

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("forgot-sent");

});

$app->get("/eco/admin/forgot/reset", function () {

    $user = User::validForgotDecrypt($_GET["code"]);

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("forgot-reset", array(
        "name" => $user["desperson"],
        "code" => $_GET["code"]
    ));

});

$app->post("/eco/admin/forgot/reset", function () {

    $forgot = User::validForgotDecrypt($_POST["code"]);

    $password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
        "cost" => 12
    ]);

    User::setForgotUsed($forgot["idrecovery"]);

    $user = new User();
    $user->get((int)$forgot["iduser"]);
    $user->setPassword($password);

    $page = new PageAdmin([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("forgot-reset-success");

});

?>