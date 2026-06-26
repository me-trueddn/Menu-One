<?php

namespace App\Services;

use App\Enums\TicketStatus;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketCategory;
use App\Models\TicketMessage;
use App\Models\TicketTag;
use App\Models\User;
use App\Support\TicketBodyFormatter;
use App\Support\TicketSettings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TicketService
{
    public function createForCustomer(
        User $customer,
        Tenant $tenant,
        TicketCategory $category,
        string $subject,
        string $body,
        string $bodyFormat = 'html',
        array $attachments = [],
    ): Ticket {
        abort_unless($customer->isLinkedToTenant($tenant), 403);

        return DB::transaction(function () use ($customer, $tenant, $category, $subject, $body, $bodyFormat, $attachments) {
            $ticket = Ticket::create([
                'number' => $this->generateNumber(),
                'user_id' => $customer->id,
                'tenant_id' => $tenant->id,
                'category_id' => $category->id,
                'subject' => $subject,
                'status' => TicketStatus::New,
                'started_at' => now(),
            ]);

            $this->addMessage($ticket, $customer, $body, $bodyFormat, false, $attachments);

            return $ticket->fresh(['category', 'user', 'assignee', 'tags', 'messages.user', 'messages.attachments']);
        });
    }

    public function addCustomerReply(
        Ticket $ticket,
        User $customer,
        string $body,
        string $bodyFormat = 'html',
        array $attachments = [],
    ): TicketMessage {
        abort_unless($ticket->isOwnedBy($customer), 403);
        abort_if($ticket->isClosed(), 422, __('menu.ticket_closed_cannot_reply'));

        return DB::transaction(function () use ($ticket, $customer, $body, $bodyFormat, $attachments) {
            $message = $this->addMessage($ticket, $customer, $body, $bodyFormat, false, $attachments);

            $ticket->update(['status' => TicketStatus::Answered]);

            return $message;
        });
    }

    public function addStaffReply(
        Ticket $ticket,
        User $staff,
        string $body,
        string $bodyFormat = 'html',
        array $attachments = [],
    ): TicketMessage {
        abort_unless($staff->isPlatformStaffMember(), 403);

        return DB::transaction(function () use ($ticket, $staff, $body, $bodyFormat, $attachments) {
            if ($ticket->assigned_to_user_id === null) {
                $ticket->update(['assigned_to_user_id' => $staff->id]);
            }

            if ($ticket->status === TicketStatus::New || $ticket->status === TicketStatus::Answered) {
                $ticket->update(['status' => TicketStatus::InProgress]);
            }

            return $this->addMessage($ticket, $staff, $body, $bodyFormat, true, $attachments);
        });
    }

    public function updateStatus(Ticket $ticket, TicketStatus $status): Ticket
    {
        $payload = ['status' => $status];

        if ($status->closesTicket()) {
            $payload['closed_at'] = $ticket->closed_at ?? now();
        } else {
            $payload['closed_at'] = null;
        }

        $ticket->update($payload);

        return $ticket->fresh();
    }

    public function assign(Ticket $ticket, ?User $assignee): Ticket
    {
        if ($assignee !== null) {
            abort_unless($assignee->isPlatformStaffMember(), 422);
        }

        $ticket->update(['assigned_to_user_id' => $assignee?->id]);

        return $ticket->fresh(['assignee']);
    }

    /** @param  list<int>  $tagIds */
    public function syncTags(Ticket $ticket, array $tagIds): Ticket
    {
        $tagIds = TicketTag::query()->whereIn('id', $tagIds)->pluck('id')->all();
        $ticket->tags()->sync($tagIds);

        return $ticket->fresh(['tags']);
    }

    /** @param  list<UploadedFile>  $files */
    private function addMessage(
        Ticket $ticket,
        User $author,
        string $body,
        string $bodyFormat,
        bool $isStaff,
        array $files,
    ): TicketMessage {
        $normalized = TicketBodyFormatter::normalize($body, $bodyFormat);

        if ($normalized === '') {
            throw ValidationException::withMessages(['body' => __('menu.ticket_body_required')]);
        }

        $message = TicketMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $author->id,
            'body' => $normalized,
            'body_format' => 'html',
            'is_staff' => $isStaff,
        ]);

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $this->storeAttachment($message, $file);
        }

        return $message->load('attachments');
    }

    private function storeAttachment(TicketMessage $message, UploadedFile $file): TicketAttachment
    {
        $ext = strtolower($file->getClientOriginalExtension());
        abort_unless(in_array($ext, TicketSettings::allowedExtensions(), true), 422, __('menu.ticket_file_type_not_allowed'));

        $path = $file->store('tickets/'.$message->ticket_id, 'public');

        return TicketAttachment::create([
            'ticket_message_id' => $message->id,
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime' => (string) $file->getMimeType(),
            'size' => (int) $file->getSize(),
        ]);
    }

    private function generateNumber(): string
    {
        do {
            $number = 'TKT-'.strtoupper(Str::random(8));
        } while (Ticket::query()->where('number', $number)->exists());

        return $number;
    }
}
