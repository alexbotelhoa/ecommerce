<?php
/**
 * Created by PhpStorm.
 * User: Alex Botelho
 * Date: 23/12/2019
 * Time: 11:23
 */

namespace Hcode;

use Rain\Tpl;

class Page
{
    private $tpl;
    private $options = [];
    private $default = [
        "data" => []
    ];

    private function setData($data = array())
    {
        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }
    }

    public function __construct($opts = array())
    {
        $this->options = array_merge($this->default, $opts);

        //Array copiado do exemple-semplie.php e modificado para localizar minhas templates nesse projeto
        $config = array(
            "tpl_dir" => $_SERVER["DOCUMENT_ROOT"] . "/views/",
            "cache_dir" => $_SERVER["DOCUMENT_ROOT"] . "/views-cache/",
            "debug" => false
        );

        Tpl::configure($config);

        $this->tpl = new Tpl();

        $this->setData($this->options["data"]);

        $this->tpl->draw("header");

    }

    public function setTpl($name, $data = array(), $returnHTML = false)
    {
        $this->setData($data);

        return $this->tpl->draw($name, $returnHTML);
    }

    public function __destruct()
    {
        $this->tpl->draw("footer");
    }
}