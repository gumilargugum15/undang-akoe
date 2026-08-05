<?php

namespace App\Repositories;

use App\Models\Invitation;
use App\Models\LoveStory;
use App\Repositories\Interfaces\LoveStoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class LoveStoryRepository implements LoveStoryRepositoryInterface
{
    public function forInvitation(Invitation $invitation): Collection
    {
        return $invitation->loveStories()->orderBy('sort_order')->orderBy('story_date')->get();
    }

    public function create(Invitation $invitation, array $data): LoveStory
    {
        return $invitation->loveStories()->create($data);
    }

    public function update(LoveStory $story, array $data): LoveStory
    {
        $story->update($data);

        return $story->fresh();
    }

    public function delete(LoveStory $story): void
    {
        $story->delete();
    }
}
