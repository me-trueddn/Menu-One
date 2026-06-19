@extends('theme::layouts.app')
@section('title', 'Personel')
@section('page-title', 'Personel')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between"><h5 class="mb-0">Personel</h5><a href="{{ route('admin.staff.create') }}" class="btn btn-primary btn-sm">Ekle</a></div>
    <div class="card-body table-responsive p-0">
        <table class="table mb-0"><thead><tr><th>Ad</th><th>E-posta</th><th>Rol</th><th class="text-end">İşlem</th></tr></thead>
        <tbody>@foreach($staff as $user)<tr>
            <td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->getRoleNames()->first() }}</td>
            <td class="text-end"><a href="{{ route('admin.staff.edit', $user) }}" class="btn btn-sm btn-outline-primary">Düzenle</a>
            @if($user->id !== auth()->id())
            <form action="{{ route('admin.staff.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Silinsin mi?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Sil</button></form>
            @endif</td></tr>@endforeach</tbody></table>
    </div>
    @if($staff->hasPages())<div class="card-footer">{{ $staff->links() }}</div>@endif
</div>
@endsection
