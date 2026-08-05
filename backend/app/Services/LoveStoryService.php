<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\LoveStory;
use App\Repositories\Interfaces\LoveStoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LoveStoryService
{
    public function __construct(
        private readonly LoveStoryRepositoryInterface $stories,
    ) {}

    /**
     * @return Collection<int, LoveStory>
     */
    public function getForInvitation(Invitation $invitation): Collection
    {
        return $this->stories->forInvitation($invitation);
    }

    public function create(Invitation $invitation, array $data): LoveStory
    {
        return $this->stories->create($invitation, $data);
    }

    public function update(LoveStory $story, array $data): LoveStory
    {
        return $this->stories->update($story, $data);
    }

    public function delete(LoveStory $story): void
    {
        $this->stories->delete($story);
    }
}
