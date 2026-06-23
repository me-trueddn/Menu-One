<tr>
    <td>{{ $table->name }}</td>
    <td>{{ $table->capacity }}</td>
    <td><span class="badge {{ $table->status->badgeClass() }}">{{ $table->status->label() }}</span></td>
    <td class="text-end">
        <a href="{{ route('admin.tables.edit', $table) }}" class="btn btn-sm btn-outline-primary">{{ __('menu.edit') }}</a>
        <form action="{{ route('admin.tables.destroy', $table) }}" method="POST" class="d-inline"
              onsubmit="return confirm('{{ __('menu.confirm_delete') }}')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger">{{ __('menu.delete') }}</button>
        </form>
    </td>
</tr>
