@extends('theme::layouts.app')
@section('title', 'Kategoriler')
@section('page-title', 'Kategoriler')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between"><h5 class="mb-0">Kategoriler</h5><a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">Ekle</a></div>
    <div class="card-body table-responsive p-0">
        <table class="table mb-0"><thead><tr><th>Ad</th><th>Sıra</th><th class="text-end">İşlem</th></tr></thead>
        <tbody>@foreach($categories as $cat)<tr><td>{{ $cat->name }}</td><td>{{ $cat->sort_order }}</td>
        <td class="text-end"><a href="{{ route('admin.categories.edit', $cat) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
        <form action="{{ route('admin.categories.destroy', $cat) }}" method="POST" class="d-inline" onsubmit="return confirm('Silinsin mi?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Sil</button></form></td></tr>@endforeach</tbody></table>
    </div>
    @if($categories->hasPages())<div class="card-footer">{{ $categories->links() }}</div>@endif
</div>
@endsection
