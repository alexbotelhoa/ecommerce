<?php

use Hcode\Model\Address;
use Hcode\Model\User;
use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Model\Cart;
use Hcode\Model\Order;
use Hcode\Model\OrderStatus;
use Hcode\Control\Page;
use Hcode\Control\PageAdmin;

########################################################################################################################
# Páginas do Site - INICIO

$app->get("/eco", function () {

    $_SESSION['registerValues'] = null;

    $products = Product::listAll();

    $page = new Page();
    $page->setTpl("index", [
        "products" => Product::checkList($products)
    ]);

});

$app->get("/eco/admin", function () {

    User::verifyLogin();

    $page = new PageAdmin();
    $page->setTpl("index");

});


########################################################################################################################
# Categorias - INICIO

$app->get("/eco/category/:idcategory", function ($idcategory) {

    $page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

    $category = new Category();
    $category->get((int)$idcategory);

    $pagination = $category->getProductsPage($page);

    $pages = [];

    for ($i = 1; $i <= $pagination['pages']; $i++) {
        array_push($pages, [
            'link' => '/eco/category/' . $category->getidcategory() . '?page=' . $i,
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


########################################################################################################################
# Produtos - INICIO

$app->get("/eco/product/:desurl", function ($desurl) {

    $product = new Product();
    $product->getFromURL($desurl);

    $category = new Category();

    $page = new Page();
    $page->setTpl("product-detail", [
        'product' => $product->getValues(),
        'categories' => $product->getCategories()
    ]);

});


########################################################################################################################
# Carrinho - INICIO

$app->get("/eco/cart", function () {

    $cart = Cart::getFromSession();

    $page = new Page();
    $page->setTpl("cart", [
        "cart" => $cart->getValues(),
        "products" => $cart->getProducts(),
        "error" => Cart::getMsgError(),
        "total" => $cart->getProductsTotal()
    ]);

});

$app->get("/eco/cart/:idproduct/add", function ($idproduct) {

    $product = new Product();
    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();

    $qtd = (isset($_GET['qtd'])) ? (int)$_GET['qtd'] : 1;

    for ($i = 0; $i < $qtd; $i++) {
        $cart->addProduct($product);
    }

    header("Location: /eco/cart");
    exit;

});

$app->get("/eco/cart/:idproduct/minus", function ($idproduct) {

    $product = new Product();
    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();
    $cart->removeProduct($product);

    header("Location: /eco/cart");
    exit;

});

$app->get("/eco/cart/:idproduct/remove", function ($idproduct) {

    $product = new Product();
    $product->get((int)$idproduct);

    $cart = Cart::getFromSession();
    $cart->removeProduct($product, true);

    header("Location: /eco/cart");
    exit;

});

$app->post("/eco/cart/freight", function () {

    $cart = Cart::getFromSession();
    $cart->setFreight($_POST['zipcode']);

    header("Location: /eco/cart");
    exit;

});


########################################################################################################################
# Checkout - INICIO

$app->get("/eco/checkout", function () {

    User::verifyLogin(false);

    $address = new Address();

    $cart = Cart::getFromSession();

    if (isset($_GET['zipcode'])) {
        $_GET['zipcode'] = $cart->getdeszipcode();
    }

    if (isset($_GET['zipcode'])) {
        $address->loadFromCEP($_GET['zipcode']);
        $cart->setdeszipcode($_GET['zipcode']);
        if ($_SESSION['DEMOECO'] != true) $cart->save();
        $cart->getCalculateTotal();
    }

    if (!$address->getdesaddress()) {
        $address->setdesaddress('');
    }
    if (!$address->getdesnumber()) {
        $address->setdesnumber('');
    }
    if (!$address->getdescomplement()) {
        $address->setdescomplement('');
    }
    if (!$address->getdesdistrict()) {
        $address->setdesdistrict('');
    }
    if (!$address->getdescity()) {
        $address->setdescity('');
    }
    if (!$address->getdesstate()) {
        $address->setdesstate('');
    }
    if (!$address->getdescountry()) {
        $address->setdescountry('');
    }
    if (!$address->getdeszipcode()) {
        $address->setdeszipcode('');
    }

    $page = new Page();
    $page->setTpl("checkout", [
        "cart" => $cart->getValues(),
        "address" => $address->getValues(),
        "products" => $cart->getProducts(),
        "error" => Address::getMsgError()
    ]);

});

$app->post("/eco/checkout", function () {

    User::verifyLogin(false);

    if (!isset($_POST['desaddress']) || $_POST['desaddress'] == '') {
        Address::setMsgError("Informe o Endereço.");
        header("Location: /eco/checkout");
        exit;
    }

    if (!isset($_POST['desnumber']) || $_POST['desnumber'] == '') {
        Address::setMsgError("Informe o Número.");
        header("Location: /eco/checkout");
        exit;
    }

    if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] == '') {
        Address::setMsgError("Informe o Bairro.");
        header("Location: /eco/checkout");
        exit;
    }

    if (!isset($_POST['descity']) || $_POST['descity'] == '') {
        Address::setMsgError("Informe a Cidade.");
        header("Location: /eco/checkout");
        exit;
    }

    if (!isset($_POST['desstate']) || $_POST['desstate'] == '') {
        Address::setMsgError("Informe o Estado.");
        header("Location: /eco/checkout");
        exit;
    }

    if (!isset($_POST['descountry']) || $_POST['descountry'] == '') {
        Address::setMsgError("Informe o Pais.");
        header("Location: /eco/checkout");
        exit;
    }

    if (!isset($_POST['zipcode']) || $_POST['zipcode'] == '') {
        Address::setMsgError("Informe o CEP.");
        header("Location: /eco/checkout");
        exit;
    }

    $user = User::getFromSession();

    $address = new Address();

    $_POST['idperson'] = $user->getidperson();
    $_POST['deszipcode'] = $_POST['zipcode'];

    $address->setData($_POST);
    if ($_SESSION['DEMOECO'] != true) $address->save();

    $cart = Cart::getFromSession();

    $totals = $cart->getProductsTotal();

    $order = new Order();
    $order->setData([
        "idcart" => (int)$cart->getidcart(),
        "iduser" => (int)$user->getiduser(),
        "idstatus" => OrderStatus::EM_ABERTO,
        "idaddress" => $address->getidaddress(),
        "vltotal" => $totals['vlprice'] + $cart->getvlfreight()
    ]);
    if ($_SESSION['DEMOECO'] != true) $order->save();

    switch ((int)($_POST['payment-method'])) {
        case 1:
            header("Location: /eco/order/" . $order->getidorder() . "/boleto");
            break;
        case 2:
            header("Location: /eco/order/" . $order->getidorder() . "/pagseguro");
            break;
        case 3:
            header("Location: /eco/order/" . $order->getidorder() . "/paypal");
            break;
    }

    exit;

});


########################################################################################################################
# Login e Logout - INICIO

$app->get("/eco/login", function () {

    $page = new Page();
    $page->setTpl("login", [
        "error" => User::getError(),
        "errorRegister" => User::getErrorRegister(),
        "registerValues" => (isset($_SESSION['registerValues'])) ? $_SESSION['registerValues'] : ['name' => '', 'email' => '', 'phone' => '']
    ]);

});

$app->post("/eco/login", function () {

    try {
        User::login($_POST['login'], $_POST['password']);
    } catch (Exception $e) {
        User::setError($e->getMessage());
    }

    header("Location: /eco/checkout");
    exit;

});

$app->get("/eco/logout", function () {

    User::logout();

    header("Location: /eco/login");
    exit;

});


########################################################################################################################
# Perfil - INICIO

$app->get("/eco/profile", function () {

    User::verifyLogin(false);

    $user = User::getFromSession();

    $page = new Page();
    $page->setTpl("profile", array(
        "user" => $user->getValues(),
        "profileMsg" => User::getSuccess(),
        "profileError" => User::getError()
    ));

});

$app->post("/eco/profile", function () {

    User::verifyLogin(false);

    if (!isset($_POST['desperson']) || $_POST['desperson'] === '') {
        User::setError("Preencha o seu nome.");
        header("Location: /eco/profile");
        exit;
    }

    if (!isset($_POST['desemail']) || $_POST['desemail'] === '') {
        User::setError("Preencha o seu e-mail.");
        header("Location: /eco/profile");
        exit;
    }

    $user = User::getFromSession();

    if ($_POST['desemail'] !== $user->getdesemail() && User::checkLogin($_POST['desemail']) === true) {
        User::setError("Esse endereço de e-mail já está cadastrado.");
        header("Location: /eco/profile");
        exit;
    }

    $_POST['inadmin'] = $user->getinadmin();
    $_POST['despassword'] = $user->getpassword();
    $_POST['deslogin'] = $user->getdesemail();

    $user->setData($_POST);
    //$user->update();

    User::setSuccess("Dados alterados com sucesso!");

    header("Location: /eco/profile");
    exit;

});

$app->post("/eco/register", function () {

    $_SESSION['registerValues'] = $_POST;

    if (!isset($_POST['name']) || $_POST['name'] == '') {
        User::setErrorRegister("Preencha o seu nome.");
        header("Location: /eco/login");
        exit;
    }

    if (!isset($_POST['email']) || $_POST['email'] == '') {
        User::setErrorRegister("Preencha o seu email.");
        header("Location: /eco/login");
        exit;
    }

    if (!isset($_POST['password']) || $_POST['password'] == '') {
        User::setErrorRegister("Preencha a senha.");
        header("Location: /eco/login");
        exit;
    }

    if (User::checkLogin($_POST['email']) === true) {
        User::setErrorRegister("Este endereço de email já está sendo usado por outro usuário.");
        header("Location: /eco/login");
        exit;
    }

    $user = new User();
    $user->setData([
        "inadmin" => 0,
        "deslogin" => $_POST['email'],
        "desperson" => $_POST['name'],
        "desemail" => $_POST['email'],
        "despassword" => $_POST['password'],
        "nrphone" => $_POST['phone']
    ]);
    if ($_SESSION['DEMOECO'] != true) $user->create();

    User::login($_POST['email'], $_POST['password']);

    header("Location: /eco/checkout");
    exit;

});

$app->get("/eco/profile/orders", function () {

    User::verifyLogin(false);

    $user = User::getFromSession();

    $page = new Page();
    $page->setTpl("profile-orders", array(
        "orders" => $user->getOrders()
    ));

});

$app->get("/eco/profile/orders/:idorder", function ($idorder) {

    User::verifyLogin(false);

    $order = new Order();
    $order->get((int)$idorder);

    $cart = new Cart();
    $cart->get((int)$order->getidcart());

    $page = new Page();
    $page->setTpl("profile-orders-detail", array(
        "order" => $order->getValues(),
        "products" => $cart->getProducts(),
        "cart" => $cart->getValues()
    ));

});

$app->get("/eco/profile/change-password", function () {

    User::verifyLogin(false);

    $page = new Page();
    $page->setTpl("profile-change-password", array(
        "changePassError" => User::getError(),
        "changePassSuccess" => User::getSuccess()
    ));

});

$app->post("/eco/profile/change-password", function () {

    User::verifyLogin(false);

    if (!isset($_POST['current_pass']) || $_POST['current_pass'] === '') {
        User::setError("Digite a senha atual.");
        header("Location: /eco/profile/change-password");
        exit;
    }

    if (!isset($_POST['new_pass']) || $_POST['new_pass'] === '') {
        User::setError("Digite a nova senha.");
        header("Location: /eco/profile/change-password");
        exit;
    }

    if (!isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] === '') {
        User::setError("Confirme a nova senha.");
        header("Location: /eco/profile/change-password");
        exit;
    }

    if ($_POST['current_pass'] === $_POST['new_pass']) {
        User::setError("Digite uma nova senha diferente da atual.");
        header("Location: /eco/profile/change-password");
        exit;
    }

    $user = User::getFromSession();

    if (!password_verify($_POST['current_pass'], $user->getdespassword())) {
        User::setError("A senha está inválida.");
        header("Location: /eco/profile/change-password");
        exit;
    }

    $user->setdespassword($_POST['new_pass']);
    //$user->update();

    User::setSuccess("Senha alterada com sucesso.");

    header("Location: /eco/profile/change-password");
    exit;

});


########################################################################################################################
# Esqueceu Senha - INICIO

$app->get("/eco/forgot", function () {

    $page = new Page();
    $page->setTpl("forgot");

});

$app->post("/eco/forgot", function () {

    $user = User::getForgot($_POST["email"], false);

    header("Location: /eco/forgot/sent");
    exit;

});

$app->get("/eco/forgot/sent", function () {

    $page = new Page();
    $page->setTpl("forgot-sent");

});

$app->get("/eco/forgot/reset", function () {

    $user = User::validForgotDecrypt($_GET["code"]);

    $page = new Page();
    $page->setTpl("forgot-reset", array(
        "name" => $user["desperson"],
        "code" => $_GET["code"]
    ));

});

$app->post("/eco/forgot/reset", function () {

    $forgot = User::validForgotDecrypt($_POST["code"]);

    User::setForgotUsed($forgot["idrecovery"]);

    $user = new User();
    $user->get((int)$forgot["iduser"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT, [
        "cost" => 12
    ]);
    $user->setPassword($password);

    $page = new Page();
    $page->setTpl("forgot-reset-success");

});


########################################################################################################################
# Pagamento - INICIO

$app->get("/eco/order/:idorder/boleto", function ($idorder) {

    User::getFromSession();

    $order = new Order();
    $order->get((int)$idorder);

    $page = new Page();
    $page->setTpl("payment", [
        "order" => $order->getValues()
    ]);

});

$app->get("/eco/boleto/:idorder", function ($idorder) {

    User::verifyLogin(false);

    $order = new Order();
    $order->get((int)$idorder);

    // DADOS DO BOLETO PARA O SEU CLIENTE
    $dias_de_prazo_para_pagamento = 10;
    $taxa_boleto = 5.00;
    $data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006";
    $valor_cobrado = $order->getvltotal(); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
    $valor_cobrado = str_replace(",", ".", $valor_cobrado);
    $valor_boleto = number_format($valor_cobrado + $taxa_boleto, 2, ',', '');

    $dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
    $dadosboleto["numero_documento"] = $order->getidorder();    // Num do pedido ou nosso numero
    $dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
    $dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
    $dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
    $dadosboleto["valor_boleto"] = $valor_boleto;    // Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

// DADOS DO SEU CLIENTE
    $dadosboleto["sacado"] = $order->getdesperson();
    $dadosboleto["endereco1"] = $order->getdesaddress() . ", " . $order->getdesnumber() . ", " . $order->getdesdistrict();
    $dadosboleto["endereco2"] = $order->getdescity() . " - " . $order->getdesstate() . " - " . $order->getdescountry() . " - CEP: " . $order->getdeszipcode();

// INFORMACOES PARA O CLIENTE
    $dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";
    $dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
    $dadosboleto["demonstrativo3"] = "";
    $dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
    $dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
    $dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";
    $dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
    $dadosboleto["quantidade"] = "";
    $dadosboleto["valor_unitario"] = "";
    $dadosboleto["aceite"] = "";
    $dadosboleto["especie"] = "R$";
    $dadosboleto["especie_doc"] = "";


// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


// DADOS DA SUA CONTA - ITAÚ
    $dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
    $dadosboleto["conta"] = "48781";    // Num da conta, sem digito
    $dadosboleto["conta_dv"] = "2";    // Digito do Num da conta

// DADOS PERSONALIZADOS - ITAÚ
    $dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

// SEUS DADOS
    $dadosboleto["identificacao"] = "Hcode Treinamentos";
    $dadosboleto["cpf_cnpj"] = "24.700.731/0001-08";
    $dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
    $dadosboleto["cidade_uf"] = "São Bernardo do Campo - SP";
    $dadosboleto["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

// NÃO ALTERAR!
    $path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;

    require_once($path . "funcoes_itau.php");
    require_once($path . "layout_itau.php");

});

$app->get("/eco/order/:idorder/pagseguro", function ($idorder) {

    User::verifyLogin(false);

    $order = new Order();
    $order->get((int)$idorder);

    $cart = new Cart();
    $cart->get((int)$order->getidcart());

    $page = new Page([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("payment-pagseguro", [
        "order" => $order->getValues(),
        "cart" => $cart->getValues(),
        "products" => $cart->getProducts(),
        "phone" => [
            "areaCode" => substr($order->getnrphone(), 0, 2),
            "number" => substr($order->getnrphone(), 2, strlen($order->getnrphone()))
        ]
    ]);

});

$app->get("/eco/order/:idorder/paypal", function ($idorder) {

    User::verifyLogin(false);

    $order = new Order();
    $order->get((int)$idorder);

    $cart = new Cart();
    $cart->get((int)$order->getidcart());

    $page = new Page([
        "header" => false,
        "footer" => false
    ]);
    $page->setTpl("payment-paypal", [
        "order" => $order->getValues(),
        "cart" => $cart->getValues(),
        "products" => $cart->getProducts()
    ]);

});

?>