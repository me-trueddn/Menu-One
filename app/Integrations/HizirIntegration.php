<?php

namespace App\Integrations;

use App\Enums\IntegrationProvider;

class HizirIntegration extends StubIntegrationAdapter
{
    public function provider(): IntegrationProvider
    {
        return IntegrationProvider::Hizir;
    }
}
