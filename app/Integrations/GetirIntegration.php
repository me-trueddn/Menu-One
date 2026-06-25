<?php

namespace App\Integrations;

use App\Enums\IntegrationProvider;

class GetirIntegration extends StubIntegrationAdapter
{
    public function provider(): IntegrationProvider
    {
        return IntegrationProvider::Getir;
    }
}
