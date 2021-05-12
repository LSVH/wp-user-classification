<?php

namespace LSVH\WordPress\Plugin\UserClassification\Pages;

interface Page
{
    public function render($fields, $opts = []);

    public function renderFields($fields);

    public function parseField($fields);
}
