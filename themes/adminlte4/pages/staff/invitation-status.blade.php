@extends('theme::layouts.app')

@section('title', __('menu.staff_invitation'))
@section('page-title', __('menu.staff_invitation'))

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                @if($status === 'invalid')
                    <i class="bi bi-envelope-x text-danger fs-1 mb-3 d-block"></i>
                    <h5>{{ __('menu.staff_invitation_invalid') }}</h5>
                    <p class="text-muted mb-0">{{ __('menu.staff_invitation_invalid_hint') }}</p>
                @elseif($status === 'wrong_account')
                    <i class="bi bi-person-x text-warning fs-1 mb-3 d-block"></i>
                    <h5>{{ __('menu.staff_invitation_wrong_account') }}</h5>
                    <p class="text-muted">{{ __('menu.staff_invitation_wrong_account_hint', ['email' => $invitation->user->email]) }}</p>
                @endif
                <a href="{{ route('dashboard') }}" class="btn btn-outline-primary mt-4">{{ __('menu.back_to_dashboard') }}</a>
            </div>
        </div>
    </div>
</div>
@endsection
