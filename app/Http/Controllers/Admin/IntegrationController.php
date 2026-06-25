<?php

namespace App\Http\Controllers\Admin;

use App\Enums\IntegrationProvider;
use App\Http\Controllers\Controller;
use App\Models\IntegrationProductMapping;
use App\Models\Product;
use App\Models\TenantIntegration;
use App\Support\IntegrationRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(): View
    {
        $tenant = tenant();
        $providers = IntegrationProvider::all();
        $integrations = TenantIntegration::query()
            ->get()
            ->keyBy('provider');

        $cards = collect($providers)->map(function (IntegrationProvider $provider) use ($integrations, $tenant) {
            $record = $integrations->get($provider->value);
            $schema = config("integration_providers.{$provider->value}", []);

            return [
                'provider' => $provider,
                'schema' => $schema,
                'integration' => $record,
                'webhook_url' => IntegrationRegistry::webhookUrl($provider, (string) $tenant->slug),
            ];
        });

        return view('theme::pages.admin.integrations.index', compact('cards'));
    }

    public function edit(string $provider): View
    {
        $providerEnum = IntegrationProvider::tryFromSlug($provider) ?? abort(404);
        $integration = TenantIntegration::forProvider($providerEnum);
        $schema = config("integration_providers.{$providerEnum->value}", []);
        $tenant = tenant();

        $config = [];
        foreach ($schema['fields'] ?? [] as $field) {
            $key = $field['key'];
            $value = $integration?->configValue($key);
            $config[$key] = ($field['secret'] ?? false) && $value ? '••••••••' : $value;
        }

        return view('theme::pages.admin.integrations.edit', [
            'provider' => $providerEnum,
            'schema' => $schema,
            'integration' => $integration,
            'config' => $config,
            'webhook_url' => IntegrationRegistry::webhookUrl($providerEnum, (string) $tenant->slug),
            'has_webhook_secret' => $integration?->hasWebhookSecret() ?? false,
        ]);
    }

    public function update(Request $request, string $provider): RedirectResponse
    {
        $providerEnum = IntegrationProvider::tryFromSlug($provider) ?? abort(404);
        $schema = config("integration_providers.{$providerEnum->value}", []);
        $integration = TenantIntegration::forProvider($providerEnum) ?? new TenantIntegration([
            'provider' => $providerEnum->value,
        ]);

        $rules = [
            'is_enabled' => ['nullable', 'boolean'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
        ];

        foreach ($schema['fields'] ?? [] as $field) {
            $rules[$field['key']] = ['nullable', 'string', 'max:500'];
        }

        $validated = $request->validate($rules);

        $integration->is_enabled = $request->boolean('is_enabled');
        $integration->provider = $providerEnum->value;

        foreach ($schema['fields'] ?? [] as $field) {
            $key = $field['key'];
            $incoming = $request->input($key);

            if (($field['secret'] ?? false) && ($incoming === null || $incoming === '' || $incoming === '••••••••')) {
                continue;
            }

            if ($incoming !== null && $incoming !== '') {
                $integration->setConfigValue($key, $incoming, (bool) ($field['secret'] ?? false));
            }
        }

        if ($request->filled('webhook_secret')) {
            $integration->setWebhookSecret($request->input('webhook_secret'));
        }

        $integration->save();

        return redirect()
            ->route('admin.integrations.edit', $providerEnum->slug())
            ->with('success', __('menu.integration_saved'));
    }

    public function mappings(string $provider): View
    {
        $providerEnum = IntegrationProvider::tryFromSlug($provider) ?? abort(404);
        $mappings = IntegrationProductMapping::query()
            ->where('provider', $providerEnum->value)
            ->with('product')
            ->orderBy('external_name')
            ->get();
        $products = Product::query()->where('is_active', true)->orderBy('name')->get();

        return view('theme::pages.admin.integrations.mappings', compact(
            'providerEnum',
            'mappings',
            'products',
        ));
    }

    public function storeMapping(Request $request, string $provider): RedirectResponse
    {
        $providerEnum = IntegrationProvider::tryFromSlug($provider) ?? abort(404);

        $validated = $request->validate([
            'external_id' => ['required', 'string', 'max:191'],
            'external_name' => ['required', 'string', 'max:191'],
            'product_id' => ['nullable', Rule::exists('products', 'id')],
        ]);

        IntegrationProductMapping::query()->updateOrCreate(
            [
                'provider' => $providerEnum->value,
                'external_id' => $validated['external_id'],
            ],
            [
                'external_name' => $validated['external_name'],
                'product_id' => $validated['product_id'] ?? null,
            ],
        );

        return back()->with('success', __('menu.integration_mapping_saved'));
    }

    public function destroyMapping(string $provider, IntegrationProductMapping $mapping): RedirectResponse
    {
        $providerEnum = IntegrationProvider::tryFromSlug($provider) ?? abort(404);
        abort_unless($mapping->provider === $providerEnum->value, 404);
        $mapping->delete();

        return back()->with('success', __('menu.integration_mapping_deleted'));
    }
}
