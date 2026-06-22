@props([
    'invitations',
    'revokeRoute',
])

<div class="card mt-3">
    <div class="card-header">
        <h6 class="mb-0">{{ __('menu.staff_invitations') }}</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm mb-0">
                <thead>
                    <tr>
                        <th>{{ __('menu.full_name') }}</th>
                        <th>{{ __('menu.email') }}</th>
                        <th>{{ __('menu.role') }}</th>
                        <th>{{ __('menu.invitation_sent_at') }}</th>
                        <th>{{ __('menu.expires_at') }}</th>
                        <th>{{ __('menu.status') }}</th>
                        <th class="text-end">{{ __('menu.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invitations as $invitation)
                        <tr>
                            <td>{{ $invitation->user->name }}</td>
                            <td>{{ $invitation->user->email }}</td>
                            <td>{{ __('menu.role_'.$invitation->role) }}</td>
                            <td class="text-nowrap small text-muted">
                                {{ $invitation->created_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                                @if($invitation->invitedBy)
                                    <div class="text-muted">{{ __('menu.invited_by') }}: {{ $invitation->invitedBy->name }}</div>
                                @endif
                            </td>
                            <td class="text-nowrap small text-muted">
                                {{ $invitation->expires_at->timezone(config('app.timezone'))->format('d.m.Y H:i') }}
                            </td>
                            <td>
                                <span class="badge {{ $invitation->statusBadgeClass() }}">{{ $invitation->statusLabel() }}</span>
                                @if($invitation->status() === 'revoked' && $invitation->revokedBy)
                                    <div class="small text-muted mt-1">{{ $invitation->revokedBy->name }}</div>
                                @endif
                            </td>
                            <td class="text-end">
                                @if($invitation->isPending())
                                    <form method="POST" action="{{ $revokeRoute($invitation) }}" class="d-inline"
                                          onsubmit="return confirm('{{ __('menu.confirm_revoke_invitation') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('menu.revoke_invitation') }}</button>
                                    </form>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">{{ __('menu.no_staff_invitations') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
