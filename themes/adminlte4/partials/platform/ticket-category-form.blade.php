<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">{{ __('menu.name') }}</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">{{ __('menu.slug') }}</label>
        <input type="text" name="slug" class="form-control" value="{{ old('slug', $category->slug ?? '') }}">
    </div>
    <div class="col-12">
        <label class="form-label">{{ __('menu.description') }}</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $category->description ?? '') }}</textarea>
    </div>
    <div class="col-md-4">
        <label class="form-label">{{ __('menu.sort_order') }}</label>
        <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0">
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check">
            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="catActive" @checked(old('is_active', $category->is_active ?? true))>
            <label class="form-check-label" for="catActive">{{ __('menu.active') }}</label>
        </div>
    </div>
</div>
