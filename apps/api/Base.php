<?php

class Base
{
    protected int $timestamp;
    protected $datetime;
    protected array $reqData;

    public function __construct()
    {
        $this->timestamp = time();
        $this->datetime = date('Y-m-d H:i:s', $this->timestamp);
        $this->reqData = $_REQUEST ?? [];
    }
}
