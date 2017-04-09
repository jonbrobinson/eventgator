<?php

namespace EventGator;

use EventGator\Helpers\FbApiHelper;

Class EventGatorCLient
{
    protected $fbApiHelper;

    public function __construct(FbApiHelper $fbApiHelper)
    {
        $this->fbApiHelper = $fbApiHelper;
    }
}