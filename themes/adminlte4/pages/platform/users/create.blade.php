@extends('theme::layouts.app')

@section('title', __('menu.add_user'))
@section('page-title', __('menu.add_user'))

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('platform.users.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">{{ __('menu.full_name') }}</label>
                <input name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.email') }}</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.phone') }}</label>
                <input name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.password') }}</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.role') }}</label>
                <select name="role" class="form-select @error('role') is-invalid @enderror" required>
                    @foreach($groups as $group)
                        @if($group->name !== 'user')
                            <option value="{{ $group->name }}" @selected(old('role') === $group->name)>{{ $group->name }}</option>
                        @endif
                    @endforeach
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <button class="btn btn-primary">{{ __('menu.save') }}</button>
            <a href="{{ route('platform.users.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
        </form>
    </div>
</div>
@endsection
