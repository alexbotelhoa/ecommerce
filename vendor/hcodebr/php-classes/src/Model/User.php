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

class User extends Model
{
    const SESSION = "User";

    public static function login($login, $password)
    {

        $sql = new Sql();

        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN" => $login
        ));

        if (count($results) === 0) {
            throw new \Exception("Usuário ou Senha não encontrado(s)!", 1);
        }

        $data = $results[0];

        if (password_verify($password, $data["despassword"]) === true) {

            $user = new User();

            $user->setData($data);

            $_SESSION[User::SESSION] = $user->getValue();

            return $user;

        } else {
            throw new \Exception("Usuário ou Senha não encontrado(s)!", 1);
        }

    }

    public static function verifyLogin($inadmin = true)
    {

        if (
            !isset($_SESSION[User::SESSION])
            ||
            !$_SESSION[User::SESSION]
            ||
            !(int)$_SESSION[User::SESSION]["iduser"] > 0
            ||
            (bool)$_SESSION[User::SESSION]["inadmin"] !== $inadmin
        ) {

            header("Location: /admin/login");
            exit;

        }

    }

    public static function logout()
    {

        $_SESSION[User::SESSION] = NULL;

    }

    public static function listAll()
    {

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

    }

    public function Update()
    {

        $sql = new Sql();

        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");

    }

}