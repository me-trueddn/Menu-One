<?php

namespace App\Integrations;

use App\Enums\IntegrationProvider;

class PaketTaxiIntegration extends StubIntegrationAdapter
{
    public function provider(): IntegrationProvider
    {
        return IntegrationProvider::PaketTaxi;
    }
}
