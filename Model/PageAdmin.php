<?php

namespace Hcode\Model;

class PageAdmin extends Page
{
    public function __construct(array $opts = array(), $tlp_dir = "/ecommerce/app/views/admin/")
    {
        parent::__construct($opts, $tlp_dir);
    }
}

