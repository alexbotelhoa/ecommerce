<?php

namespace Hcode\Model;

use Hcode\DB\Sql;
use Hcode\Model;
use Hcode\Mailer;

class User extends Model
{

    const SESSION = "User";
    const SECRET = "HcodePhp7_Secret";
    const DESECRET = "HcodePhp7_Secret";
    const ERROR = "UserError";
    const ERROR_REGISTER = "UserError";
    const SUCCESS = "UserSuccess";

    public static function login($login, $password)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
            ":LOGIN" => $login
        ));

        if (count($results) === 0) {
            throw new \Exception("Usuário ou Senha não encontrado(s)!");
        }

        $data = $results[0];

        if (password_verify($password, $data["despassword"]) === true) {
            $user = new User();
            $user->setData($data);
            $_SESSION[User::SESSION] = $user->getValues();
            return $user;
        } else {
            throw new \Exception("Usuário ou Senha não encontrado(s)!");
        }
    }

    public static function verifyLogin($inadmin = true)
    {
        if (!User::checkLogin($inadmin)) {
            if ($inadmin) {
                header("Location: /eco/admin/login");
            } else {
                header("Location: /eco/login");
            }
            exit;
        }
    }

    public static function logout()
    {
        $_SESSION[User::SESSION] = null;
    }

    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson");
    }

    public static function getForgot($email, $inadmin = true)
    {
        $sql = new Sql();
        $results = $sql->select("
            SELECT * 
            FROM db_ecommerce.tb_persons a
            INNER JOIN tb_users b USING(idperson)
            WHERE a.desemail = :EMAIL;
        ", array(
            ":EMAIL" => $email
        ));

        if (count($results) === 0) {
            throw new \Exception("Não foi posível recuperar a senha!");
        } else {
            $data = $results[0];

            $results2 = $sql->select("CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
                ":iduser" => $data["iduser"],
                ":desip" => $_SERVER["REMOTE_ADDR"]
            ));

            if (count($results2) === 0) {
                throw new \Exception("Não foi posível recuperar a senha!");
            } else {
                $dataRecovery = $results2[0];

                $code = base64_encode(openssl_encrypt($dataRecovery["idrecovery"], 'AES-128-CBC', User::SECRET, 0, User::DESECRET));

                if ($inadmin === true) {
                    $link = "http://www.alexbotelhoa.com.br/eco/admin/forgot/reset?code=$code";
                } else {
                    $link = "http://www.alexbotelhoa.com.br/eco/forgot/reset?code=$code";
                }

                $mailer = new Mailer($data["desemail"], $data["desperson"], "Recuperação de Senha", "forgot", array(
                    "name" => $data["desperson"],
                    "link" => $link
                ));
                $mailer->send();

                return $data;
            }
        }
    }

    public static function validForgotDecrypt($code)
    {
        $idRecovery = openssl_decrypt(base64_decode($code), 'AES-128-CBC', User::SECRET, 0, User::DESECRET);

        $sql = new Sql();
        $results = $sql->select("
            SELECT *
            FROM tb_userspasswordsrecoveries a
            INNER JOIN tb_users b USING(iduser)
            INNER JOIN tb_persons c USING(idperson)
            WHERE a.idrecovery = :IDRECOVERY AND a.dtrecovery IS NULL AND DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW();
        ", array(
            ":IDRECOVERY" => $idRecovery
        ));

        if (count($results) === 0) {
            throw new \Exception("Não possível recurepar a senha!");
        } else {
            return $results[0];
        }
    }

    public static function setForgotUsed($idrecovery)
    {
        $sql = new Sql();
        $sql->query("UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :IDRECOVERY", array(
            ":IDRECOVERY" => $idrecovery
        ));
    }

    public static function getFromSession()
    {
        $user = new User();

        if (isset($_SESSION[User::SESSION]) && (int)$_SESSION[User::SESSION]['iduser'] > 0) {
            $user->setData($_SESSION[User::SESSION]);
        }

        $user->get((int)$user->getidperson());

        return $user;
    }

    public static function checkLogin($inadmin = true)
    {
        if (
            !isset($_SESSION[User::SESSION])
            || !$_SESSION[User::SESSION]
            || !(int)$_SESSION[User::SESSION]["iduser"] > 0
        ) {
            return false;
        } elseif ($inadmin === true && (bool)$_SESSION[User::SESSION]["inadmin"] === true) {
                return true;
        } elseif ($inadmin === false) {
                return true;
        } else {
            return false;
        }
    }

    public static function setError($msg)
    {
        $_SESSION[User::ERROR] = $msg;
    }

    public static function getError()
    {
        $msg = (isset($_SESSION[User::ERROR]) && $_SESSION[User::ERROR]) ? $_SESSION[User::ERROR] : "";

        User::clearError();

        return $msg;
    }

    public static function clearError()
    {
        $_SESSION[User::ERROR] = null;
    }

    public static function setSuccess($msg)
    {
        $_SESSION[User::SUCCESS] = $msg;
    }

    public static function getSuccess()
    {
        $msg = (isset($_SESSION[User::SUCCESS]) && $_SESSION[User::SUCCESS]) ? $_SESSION[User::SUCCESS] : "";

        User::clearSuccess();

        return $msg;
    }

    public static function clearSuccess()
    {
        $_SESSION[User::SUCCESS] = null;
    }

    public static function setErrorRegister($msg)
    {
        $_SESSION[User::ERROR_REGISTER] = $msg;
    }

    public static function getErrorRegister()
    {
        $msg = (isset($_SESSION[User::ERROR_REGISTER]) && $_SESSION[User::ERROR_REGISTER]) ? $_SESSION[User::ERROR_REGISTER] : "";

        User::clearErrorRegister();

        return $msg;
    }

    public static function clearErrorRegister()
    {
        $_SESSION[User::ERROR_REGISTER] = null;
    }

    public static function checkLoginExist($login)
    {
        $sql = new Sql();
        $result = $sql->select("SELECT * FROM tb_users WHERE deslogin = :DESLOGIN", array(
            ":DESLOGIN" => $login
        ));

        return (count($result) > 0);
    }

    public static function getPasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT, [
            "cost" => 12
        ]);
    }


    /**************************************************************************************/
    //                                  FIM DOS STATICOS                                  //
    /**************************************************************************************/

    public function setPassword($password)
    {
        $sql = new Sql();
        $sql->query("UPDATE tb_users SET despassword = :PASSWORD WHERE iduser = :IDUSER", array(
            ":PASSWORD" => $password,
            ":IDUSER" => $this->getiduser()
        ));
    }

    public function create()
    {
        $sql = new Sql();
        $result = $sql->select("CALL sp_users_create(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":desperson" => utf8_decode($this->getdesperson()),
            ":deslogin" => $this->getdeslogin(),
            ":despassword" => User::getPasswordHash($this->getdespassword()),
            ":desemail" => $this->getdesemail(),
            ":nrphone" => $this->getnrphone(),
            ":inadmin" => $this->getinadmin()
        ));

        $this->setData($result[0]);
    }

    public function get($iduser)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :IDUSER", array(
            ":IDUSER" => $iduser
        ));

        $data = $results[0];
        $data['desperson'] = utf8_encode($data['desperson']);

        $this->setData($data);
    }

    public function update()
    {
        $sql = new Sql();
        $result = $sql->select("CALL sp_users_update(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array(
            ":iduser" => $this->getiduser(),
            ":desperson" => utf8_decode($this->getdesperson()),
            ":deslogin" => $this->getdeslogin(),
            ":despassword" => User::getPasswordHash($this->getdespassword()),
            ":desemail" => $this->getdesemail(),
            ":nrphone" => $this->getnrphone(),
            ":inadmin" => $this->getinadmin()
        ));

        $this->setData($result[0]);
    }

    public function delete()
    {
        $sql = new Sql();
        $sql->query("CALL sp_users_delete(:iduser)", array(
            ":iduser" => $this->getiduser()
        ));
    }

    public function getOrders()
    {
        $sql = new Sql();
        $result = $sql->select("
            SELECT * 
            FROM tb_orders a 
            INNER JOIN tb_ordersstatus b USING(idstatus)
            INNER JOIN tb_carts c USING(idcart)
            INNER JOIN tb_users d ON d.iduser = a.iduser
            INNER JOIN tb_addresses e USING(idaddress) 
            INNER JOIN tb_persons f ON f.idperson = d.idperson
            WHERE a.iduser = :IDUSER 
        ", [
            ":IDUSER" => $this->getiduser()
        ]);

        return $result;
    }

    public static function getPage($page, $search, $itemsPerPage)
    {
        $start = ($page - 1) * $itemsPerPage;

        ($search != "") ? $likes = "WHERE b.desperson LIKE :search OR b.desemail = :search OR a.deslogin LIKE :search" : $likes = " ";

        $sql = new Sql();

        $resultUsers = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_users a 
            INNER JOIN tb_persons b USING(idperson) 
            $likes
            ORDER BY b.desperson
            LIMIT $start, $itemsPerPage
        ", [
            ":search" => "%" . $search . "%"
        ]);

        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal");

        return [
            'data' => $resultUsers,
            'total' => (int)$resultTotal[0]['nrtotal'],
            'pages' => ceil($resultTotal[0]['nrtotal'] / $itemsPerPage)
        ];
    }

}