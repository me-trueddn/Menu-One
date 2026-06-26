@extends('theme::layouts.app')

@section('title', __('menu.ticket_open_new'))
@section('page-title', __('menu.ticket_open_new'))

@section('content')
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('ticket.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="form-label">{{ __('menu.ticket_category') }}</label>
                <select name="category_id" class="form-select" required>
                    <option value="">{{ __('menu.select') }}</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.ticket_subject') }}</label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject') }}" required maxlength="255">
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.ticket_message') }}</label>
                @include('theme::partials.ticket-rich-editor', ['name' => 'body'])
            </div>
            <div class="mb-3">
                <label class="form-label">{{ __('menu.ticket_attachments') }}</label>
                <input type="file" name="attachments[]" class="form-control" multiple>
                <div class="form-text">{{ __('menu.ticket_attachments_hint') }}</div>
            </div>
            <button class="btn btn-primary">{{ __('menu.ticket_submit') }}</button>
            <a href="{{ route('ticket.index') }}" class="btn btn-secondary">{{ __('menu.cancel') }}</a>
        </form>
    </div>
</div>
@endsection
