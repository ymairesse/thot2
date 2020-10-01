<?php

class chrono
{
    public $ini;

    public function __construct()
    {
        $this->start();
    }

    public function getMicrotime()
    {
        $temps = explode(' ', microtime());

        return $temps[0] + $temps[1];
    }

    public function start()
    {
        $this->ini = $this->getMicrotime();
    }

    public function stop()
    {
        $temps = $this->getMicrotime();
        $this->duree = $temps - $this->ini;

        return $this->duree;
    }
}
