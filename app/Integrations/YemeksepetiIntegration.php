<?php

namespace App\Integrations;

use App\Enums\IntegrationProvider;

class YemeksepetiIntegration extends StubIntegrationAdapter
{
    public function provider(): IntegrationProvider
    {
        return IntegrationProvider::Yemeksepeti;
    }
}
