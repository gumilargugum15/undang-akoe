<?php

namespace App\Repositories\Interfaces;

use App\Models\Invitation;
use App\Models\LoveStory;
use Illuminate\Database\Eloquent\Collection;

interface LoveStoryRepositoryInterface
{
    /**
     * @return Collection<int, LoveStory>
     */
    public function forInvitation(Invitation $invitation): Collection;

    public function create(Invitation $invitation, array $data): LoveStory;

    public function update(LoveStory $story, array $data): LoveStory;

    public function delete(LoveStory $story): void;
}
