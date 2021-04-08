<?php

use Helpers\Util;

class Index extends Base
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        Util::jsonReturn(200, MSG_CODE[200]);
    }
}
