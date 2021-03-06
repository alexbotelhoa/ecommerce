<?php

namespace Hcode\Control;

use Rain\Tpl;

class Page
{
    private $tpl;
    private $options = [];
    private $default = [
        "header" => true,
        "footer" => true,
        "data" => []
    ];

    public function __construct($opts = array(), $tlp_dir = "/ecommerce/app/Views/site/")
    {
        $this->options = array_merge($this->default, $opts);

        //Array copiado do exemple-semplie.php e modificado para localizar minhas templates nesse projeto
        $config = array(
            "tpl_dir" => $_SERVER["DOCUMENT_ROOT"] . $tlp_dir,
            "cache_dir" => $_SERVER["DOCUMENT_ROOT"] . "/ecommerce/app/Views/cache/",
            "debug" => false
        );

        Tpl::configure($config);

        $this->tpl = new Tpl();
        $this->setData($this->options["data"]);

        if ($this->options["header"] === true) $this->tpl->draw("header");

    }

    private function setData($data = array())
    {
        foreach ($data as $key => $value) {
            $this->tpl->assign($key, $value);
        }
    }

    public function setTpl($name, $data = array(), $returnHTML = false)
    {
        $this->setData($data);

        return $this->tpl->draw($name, $returnHTML);
    }

    public function __destruct()
    {
        if ($this->options["footer"] === true) $this->tpl->draw("footer");
    }

}