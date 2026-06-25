<?php

namespace App\Integrations;

use App\Enums\IntegrationProvider;

class FiyuuIntegration extends StubIntegrationAdapter
{
    public function provider(): IntegrationProvider
    {
        return IntegrationProvider::Fiyuu;
    }
}
