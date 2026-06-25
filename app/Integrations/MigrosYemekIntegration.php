<?php

namespace App\Integrations;

use App\Enums\IntegrationProvider;

class MigrosYemekIntegration extends StubIntegrationAdapter
{
    public function provider(): IntegrationProvider
    {
        return IntegrationProvider::MigrosYemek;
    }
}
