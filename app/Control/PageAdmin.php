<?php

namespace Hcode\Control;

class PageAdmin extends Page
{

    public function __construct(array $opts = array(), $tlp_dir = "/ecommerce/app/Views/admin/")
    {
        parent::__construct($opts, $tlp_dir);
    }

}