<?php

use Hcode\Model\PageAdmin;
use Hcode\Model\User;

/*
 * #############################################
 * Página de Administração de Usuários - INICIO
 */

$app->get("/eco/admin/users", function () {

    User::verifyLogin();

    $search = (isset($_GET['search'])) ? $_GET['search'] : "";

    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

    $pagination = User::getPage($page, $search, 5);

    $pages = [];

    for ($x = 0; $x < $pagination['pages']; $x++) {

        array_push($pages, [
            "href" => '/admin/users?' . http_build_query([
                    "page" => $x + 1,
                    "search" => $search
                ]),
            "text" => $x + 1
        ]);

    }

    $page = new PageAdmin();

    $page->setTpl("users", array(
        "users" => $pagination['data'],
        "search" => $search,
        "pages" => $pages
    ));

});

$app->get("/eco/admin/users/create", function () {

    User::verifyLogin();

    $page = new PageAdmin();

    $page->setTpl("users-create");

});

$app->post("/eco/admin/users/create", function() {

    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

    $user->setData($_POST);

    $user->create();

    header("Location: /admin/users");

    exit;

});

$app->get("/eco/admin/users/:iduser", function ($iduser) {

    User::verifyLogin();

    $user = new User();

    $user->get((int)$iduser);

    $page = new PageAdmin();

    $page->setTpl("users-update", array(
        "user" => $user->getValues()
    ));

});

$app->post("/eco/admin/users/:iduser", function($iduser) {

    User::verifyLogin();

    $user = new User();

    $_POST["inadmin"] = (isset($_POST["inadmin"]))?1:0;

    $user->get((int)$iduser);

    $user->setData($_POST);

    $user->update();

    header("Location: /admin/users");

    exit;

});

$app->get("/eco/admin/users/:iduser/password", function($iduser) {

    User::verifyLogin();

    $user = new User();

    $user->get((int)$iduser);

    $page = new PageAdmin();

    $page->setTpl("users-password", array(
        "user" => $user->getValues(),
        "msgError" => User::getError(),
        "msgSuccess" => User::getSuccess()
    ));

});

$app->post("/eco/admin/users/:iduser/password", function($iduser) {

    User::verifyLogin();

    if (!isset($_POST['despassword']) or $_POST['despassword'] === '') {

        User::setError("Preencha a nova senha.");

        header("Location: /admin/users/$iduser/password");

        exit;

    }

    if (!isset($_POST['despassword-confirm']) or $_POST['despassword-confirm'] === '') {

        User::setError("Preencha a confirmação da nova senha.");

        header("Location: /admin/users/$iduser/password");

        exit;

    }

    if ($_POST['despassword'] !== $_POST['despassword-confirm']) {

        User::setError("As senha não conferem.");

        header("Location: /admin/users/$iduser/password");

        exit;

    }

    $user = new User();

    $user->get((int)$iduser);

    $user->setPassword(User::getPasswordHash($_POST['despassword']));

    User::setSuccess("Senha alterada com sucesso.");

    header("Location: /admin/users/$iduser/password");

    exit;

});

$app->get("/eco/admin/users/:iduser/delete", function($iduser) {

    User::verifyLogin();

    $user = new User();

    $user->get((int)$iduser);

    $user->delete();

    header("Location: /admin/users");

    exit;

});

/*
 * Páginas de Administração de Usuários - INICIO
 * #############################################
 */

?>