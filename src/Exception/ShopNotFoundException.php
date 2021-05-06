<?php

namespace App\Exception;

class ShopNotFoundException extends \InvalidArgumentException
{
    public function __construct($shopUrl)
    {
        parent::__construct(sprintf('Store "%s" does not exist', $shopUrl));
    }
}