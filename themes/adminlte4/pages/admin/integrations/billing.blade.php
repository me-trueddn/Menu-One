@extends('theme::layouts.app')

@section('title', __('menu.integration_billing_defaults'))
@section('page-title', __('menu.integration_billing_defaults'))

@section('content')
<div class="mb-3">
    <a href="{{ route('admin.integrations.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> {{ __('menu.integrations') }}
    </a>
</div>

<form method="POST" action="{{ route('admin.integrations.billing.update') }}">
    @csrf
    @method('PUT')

    <div class="card">
        <div class="card-header"><h6 class="mb-0">{{ __('menu.integration_billing_defaults') }}</h6></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input type="hidden" name="integration_billing_e_invoice_enabled" value="0">
                        <input class="form-check-input" type="checkbox" id="integration_billing_e_invoice_enabled" name="integration_billing_e_invoice_enabled" value="1"
                               @checked(old('integration_billing_e_invoice_enabled', $settings['integration_billing_e_invoice_enabled'] ?? '0') === '1')>
                        <label class="form-check-label" for="integration_billing_e_invoice_enabled">{{ __('menu.integration_e_invoice_enabled') }}</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-check form-switch">
                        <input type="hidden" name="integration_billing_e_waybill_enabled" value="0">
                        <input class="form-check-input" type="checkbox" id="integration_billing_e_waybill_enabled" name="integration_billing_e_waybill_enabled" value="1"
                               @checked(old('integration_billing_e_waybill_enabled', $settings['integration_billing_e_waybill_enabled'] ?? '0') === '1')>
                        <label class="form-check-label" for="integration_billing_e_waybill_enabled">{{ __('menu.integration_e_waybill_enabled') }}</label>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('menu.integration_billing_company_name') }}</label>
                    <input name="integration_billing_company_name" class="form-control"
                           value="{{ old('integration_billing_company_name', $settings['integration_billing_company_name'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('menu.integration_billing_tax_office') }}</label>
                    <input name="integration_billing_tax_office" class="form-control"
                           value="{{ old('integration_billing_tax_office', $settings['integration_billing_tax_office'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('menu.integration_billing_tax_number') }}</label>
                    <input name="integration_billing_tax_number" class="form-control"
                           value="{{ old('integration_billing_tax_number', $settings['integration_billing_tax_number'] ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('menu.integration_billing_invoice_prefix') }}</label>
                    <input name="integration_billing_invoice_prefix" class="form-control"
                           value="{{ old('integration_billing_invoice_prefix', $settings['integration_billing_invoice_prefix'] ?? 'INV') }}">
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('menu.integration_billing_company_address') }}</label>
                    <textarea name="integration_billing_company_address" rows="2" class="form-control">{{ old('integration_billing_company_address', $settings['integration_billing_company_address'] ?? '') }}</textarea>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button class="btn btn-primary">{{ __('menu.save') }}</button>
        </div>
    </div>
</form>
@endsection

