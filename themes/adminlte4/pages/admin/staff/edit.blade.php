@extends('theme::layouts.app')
@section('title', 'Personel Düzenle')
@section('page-title', 'Personel Düzenle')
@section('content')
<div class="card"><div class="card-body"><form method="POST" action="{{ route('admin.staff.update', $staff) }}">@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Ad Soyad</label><input name="name" class="form-control" value="{{ old('name', $staff->name) }}" required></div>
<div class="mb-3"><label class="form-label">E-posta</label><input type="email" name="email" class="form-control" value="{{ old('email', $staff->email) }}" required></div>
<div class="mb-3"><label class="form-label">Yeni Şifre (opsiyonel)</label><input type="password" name="password" class="form-control"></div>
<div class="mb-3"><label class="form-label">Rol</label><select name="role" class="form-select">
@foreach(['waiter'=>'Garson','kitchen'=>'Mutfak','cafe_admin'=>'Cafe Admin'] as $val=>$label)
<option value="{{ $val }}" @selected($staff->hasRole($val))>{{ $label }}</option>@endforeach</select></div>
<button class="btn btn-primary">Güncelle</button><a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">İptal</a>
</form></div></div>
@endsection
