<?php

namespace App\Integrations;

use App\Enums\IntegrationProvider;

class TrendyolIntegration extends StubIntegrationAdapter
{
    public function provider(): IntegrationProvider
    {
        return IntegrationProvider::Trendyol;
    }
}
