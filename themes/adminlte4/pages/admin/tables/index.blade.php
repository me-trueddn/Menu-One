@extends('theme::layouts.app')

@section('title', 'Masalar')
@section('page-title', 'Masalar')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Masalar</h5>
        <a href="{{ route('admin.tables.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Ekle</a>
    </div>
    <div class="card-body table-responsive p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Ad</th><th>Kapasite</th><th>Durum</th><th class="text-end">İşlem</th></tr></thead>
            <tbody>
                @foreach($tables as $table)
                    <tr>
                        <td>{{ $table->name }}</td>
                        <td>{{ $table->capacity }}</td>
                        <td><span class="badge {{ $table->status->badgeClass() }}">{{ $table->status->label() }}</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.tables.edit', $table) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
                            <form action="{{ route('admin.tables.destroy', $table) }}" method="POST" class="d-inline" onsubmit="return confirm('Silinsin mi?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger">Sil</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($tables->hasPages())<div class="card-footer">{{ $tables->links() }}</div>@endif
</div>
@endsection
