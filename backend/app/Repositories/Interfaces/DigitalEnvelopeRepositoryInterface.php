<?php

namespace App\Repositories\Interfaces;

use App\Models\DigitalEnvelope;
use App\Models\Invitation;
use Illuminate\Database\Eloquent\Collection;

interface DigitalEnvelopeRepositoryInterface
{
    /**
     * @return Collection<int, DigitalEnvelope>
     */
    public function forInvitation(Invitation $invitation): Collection;

    /**
     * @return Collection<int, DigitalEnvelope>
     */
    public function publicList(Invitation $invitation): Collection;

    public function create(Invitation $invitation, array $data): DigitalEnvelope;

    public function update(DigitalEnvelope $envelope, array $data): DigitalEnvelope;

    public function delete(DigitalEnvelope $envelope): void;
}
