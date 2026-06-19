<div class="table-responsive">
    <table class="table table-bordered align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>{{ __('menu.module') }}</th>
                <th class="text-center" style="width:120px">{{ __('menu.permission_view') }}</th>
                <th class="text-center" style="width:120px">{{ __('menu.permission_edit') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($modules as $key => $module)
                <tr>
                    <td>
                        <i class="bi {{ $module['icon'] }} me-1"></i>
                        {{ __($module['label']) }}
                    </td>
                    <td class="text-center">
                        <input type="hidden" name="permissions[{{ $key }}][view]" value="0">
                        <input type="checkbox" class="form-check-input module-view"
                               name="permissions[{{ $key }}][view]" value="1"
                               data-module="{{ $key }}"
                               @checked(old("permissions.{$key}.view", $permissions[$key]['view'] ?? false))>
                    </td>
                    <td class="text-center">
                        <input type="hidden" name="permissions[{{ $key }}][edit]" value="0">
                        <input type="checkbox" class="form-check-input module-edit"
                               name="permissions[{{ $key }}][edit]" value="1"
                               data-module="{{ $key }}"
                               @checked(old("permissions.{$key}.edit", $permissions[$key]['edit'] ?? false))>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<p class="form-text mt-2 mb-0">{{ __('menu.module_permissions_hint') }}</p>

@push('scripts')
<script>
document.querySelectorAll('.module-edit').forEach(function (editBox) {
    editBox.addEventListener('change', function () {
        if (this.checked) {
            const view = document.querySelector('.module-view[data-module="' + this.dataset.module + '"]');
            if (view) view.checked = true;
        }
    });
});
document.querySelectorAll('.module-view').forEach(function (viewBox) {
    viewBox.addEventListener('change', function () {
        if (!this.checked) {
            const edit = document.querySelector('.module-edit[data-module="' + this.dataset.module + '"]');
            if (edit) edit.checked = false;
        }
    });
});
</script>
@endpush
