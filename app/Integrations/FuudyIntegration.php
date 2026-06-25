<?php

namespace App\Integrations;

use App\Enums\IntegrationProvider;

class FuudyIntegration extends StubIntegrationAdapter
{
    public function provider(): IntegrationProvider
    {
        return IntegrationProvider::Fuudy;
    }
}
