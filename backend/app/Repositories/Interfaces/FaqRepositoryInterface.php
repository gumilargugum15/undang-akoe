<?php

namespace App\Repositories\Interfaces;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Collection;

interface FaqRepositoryInterface
{
    /**
     * @return Collection<int, Faq>
     */
    public function all(bool $activeOnly): Collection;

    public function create(array $data): Faq;

    public function update(Faq $faq, array $data): Faq;

    public function delete(Faq $faq): void;
}
