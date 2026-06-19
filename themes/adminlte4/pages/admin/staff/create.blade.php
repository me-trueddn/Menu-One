@extends('theme::layouts.app')
@section('title', 'Personel Ekle')
@section('page-title', 'Personel Ekle')
@section('content')
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.staff.store') }}">@csrf
<div class="mb-3"><label class="form-label">Ad Soyad</label><input name="name" class="form-control" required></div>
<div class="mb-3"><label class="form-label">E-posta</label><input type="email" name="email" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Şifre</label><input type="password" name="password" class="form-control" required></div>
<div class="mb-3"><label class="form-label">Rol</label><select name="role" class="form-select"><option value="waiter">Garson</option><option value="kitchen">Mutfak</option><option value="cafe_admin">Cafe Admin</option></select></div>
<button class="btn btn-primary">Kaydet</button><a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">İptal</a>
</form></div></div>
@endsection
