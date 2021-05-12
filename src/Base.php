<?php

namespace LSVH\WordPress\Plugin\UserClassification;

abstract class Base
{
    protected $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }
}
