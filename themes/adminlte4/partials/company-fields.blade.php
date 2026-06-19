<div class="col-12"><hr><h6>{{ __('menu.company_info') }}</h6></div>
<div class="col-md-6">
    <label class="form-label">{{ __('menu.company_name') }}</label>
    <input type="text" name="company_name" class="form-control"
           value="{{ old('company_name', $values['company_name'] ?? '') }}">
</div>
<div class="col-md-6">
    <label class="form-label">{{ __('menu.company_tax_number') }}</label>
    <input type="text" name="company_tax_number" class="form-control"
           value="{{ old('company_tax_number', $values['company_tax_number'] ?? '') }}">
</div>
<div class="col-md-6">
    <label class="form-label">{{ __('menu.company_phone') }}</label>
    <input type="text" name="company_phone" class="form-control"
           value="{{ old('company_phone', $values['company_phone'] ?? '') }}">
</div>
<div class="col-md-6">
    <label class="form-label">{{ __('menu.company_email') }}</label>
    <input type="email" name="company_email" class="form-control"
           value="{{ old('company_email', $values['company_email'] ?? '') }}">
</div>
<div class="col-12">
    <label class="form-label">{{ __('menu.company_address') }}</label>
    <textarea name="company_address" class="form-control" rows="2">{{ old('company_address', $values['company_address'] ?? '') }}</textarea>
</div>
