<?php

namespace App\Services;

use App\Models\Faq;
use App\Repositories\Interfaces\FaqRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class FaqService
{
    public function __construct(
        private readonly FaqRepositoryInterface $faqs,
    ) {}

    /**
     * @return Collection<int, Faq>
     */
    public function list(bool $activeOnly): Collection
    {
        return $this->faqs->all($activeOnly);
    }

    public function create(array $data): Faq
    {
        return $this->faqs->create($data);
    }

    public function update(Faq $faq, array $data): Faq
    {
        return $this->faqs->update($faq, $data);
    }

    public function delete(Faq $faq): void
    {
        $this->faqs->delete($faq);
    }
}
