@extends('theme::layouts.app')

@section('title', __('menu.ticket_tags'))
@section('page-title', __('menu.ticket_tags'))

@section('content')
@include('theme::partials.platform.ticket-nav')

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">{{ __('menu.add') }}</div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.ticket-tags.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">{{ __('menu.name') }}</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <button class="btn btn-primary">{{ __('menu.save') }}</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body d-flex flex-wrap gap-2">
                @forelse($tags as $tag)
                    <div class="d-inline-flex align-items-center gap-2 border rounded-pill px-3 py-2" style="background-color: {{ $tag->color }};">
                        <span>{{ $tag->name }}</span>
                        <form action="{{ route('platform.ticket-tags.destroy', $tag) }}" method="POST" onsubmit="return confirm('{{ __('menu.confirm_delete') }}')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-link text-danger p-0"><i class="bi bi-x-lg"></i></button>
                        </form>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('menu.no_data') }}</p>
                @endforelse
            </div>
            @if($tags->hasPages())<div class="card-footer">{{ $tags->links() }}</div>@endif
        </div>
    </div>
</div>
@endsection
