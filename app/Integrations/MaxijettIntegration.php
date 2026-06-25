<?php

namespace App\Integrations;

use App\Enums\IntegrationProvider;

class MaxijettIntegration extends StubIntegrationAdapter
{
    public function provider(): IntegrationProvider
    {
        return IntegrationProvider::Maxijett;
    }
}
