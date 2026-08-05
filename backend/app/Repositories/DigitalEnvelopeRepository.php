<?php

namespace App\Repositories;

use App\Models\DigitalEnvelope;
use App\Models\Invitation;
use App\Repositories\Interfaces\DigitalEnvelopeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DigitalEnvelopeRepository implements DigitalEnvelopeRepositoryInterface
{
    public function forInvitation(Invitation $invitation): Collection
    {
        return $invitation->envelopes()->orderBy('sort_order')->get();
    }

    public function publicList(Invitation $invitation): Collection
    {
        return $invitation->envelopes()->where('is_active', true)->orderBy('sort_order')->get();
    }

    public function create(Invitation $invitation, array $data): DigitalEnvelope
    {
        return $invitation->envelopes()->create($data);
    }

    public function update(DigitalEnvelope $envelope, array $data): DigitalEnvelope
    {
        $envelope->update($data);

        return $envelope->fresh();
    }

    public function delete(DigitalEnvelope $envelope): void
    {
        $envelope->delete();
    }
}
