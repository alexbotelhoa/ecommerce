<?php

namespace Hcode;

class PageAdmin extends Page
{
    public function __construct(array $opts = array(), $tlp_dir = "/views/admin/")
    {
        parent::__construct($opts, $tlp_dir);
    }
}

