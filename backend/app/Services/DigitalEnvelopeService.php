<?php

namespace App\Services;

use App\Models\DigitalEnvelope;
use App\Models\Invitation;
use App\Repositories\Interfaces\DigitalEnvelopeRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class DigitalEnvelopeService
{
    public function __construct(
        private readonly DigitalEnvelopeRepositoryInterface $envelopes,
    ) {}

    /**
     * @return Collection<int, DigitalEnvelope>
     */
    public function getForInvitation(Invitation $invitation): Collection
    {
        return $this->envelopes->forInvitation($invitation);
    }

    /**
     * @return Collection<int, DigitalEnvelope>
     */
    public function getPublicList(Invitation $invitation): Collection
    {
        return $this->envelopes->publicList($invitation);
    }

    public function create(Invitation $invitation, array $data): DigitalEnvelope
    {
        return $this->envelopes->create($invitation, $data);
    }

    public function update(DigitalEnvelope $envelope, array $data): DigitalEnvelope
    {
        return $this->envelopes->update($envelope, $data);
    }

    public function delete(DigitalEnvelope $envelope): void
    {
        $this->envelopes->delete($envelope);
    }
}
