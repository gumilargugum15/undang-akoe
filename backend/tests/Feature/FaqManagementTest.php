<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FaqManagementTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function a_customer_cannot_manage_faqs(): void
    {
        $customer = User::factory()->create();

        $this->apiAs($customer)->postJson('/api/faqs', [
            'question' => 'Apa itu undangan digital?', 'answer' => 'Jawaban.',
        ])->assertForbidden();
    }

    #[Test]
    public function an_admin_can_create_update_and_delete_a_faq(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $create = $this->apiAs($admin)->postJson('/api/faqs', [
            'question' => 'Bagaimana cara membuat undangan?',
            'answer' => 'Daftar, pilih tema, lalu isi data undangan Anda.',
            'category' => 'umum',
        ]);
        $create->assertCreated()->assertJsonPath('data.category', 'umum');
        $faqId = $create->json('data.id');

        $this->apiAs($admin)->putJson("/api/faqs/{$faqId}", ['is_active' => false])
            ->assertOk()->assertJsonPath('data.is_active', false);

        $this->apiAs($admin)->deleteJson("/api/faqs/{$faqId}")->assertOk();
        $this->assertSoftDeleted('faqs', ['id' => $faqId]);
    }

    #[Test]
    public function question_and_answer_are_required(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->apiAs($admin)->postJson('/api/faqs', [])
            ->assertUnprocessable()->assertJsonValidationErrors(['question', 'answer']);
    }

    #[Test]
    public function the_public_endpoint_only_returns_active_faqs(): void
    {
        Faq::factory()->create(['question' => 'Active Q']);
        Faq::factory()->inactive()->create(['question' => 'Inactive Q']);

        $response = $this->getJson('/api/public/faqs');

        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.question', 'Active Q');
    }

    #[Test]
    public function an_admin_index_sees_every_faq_including_inactive(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Faq::factory()->create();
        Faq::factory()->inactive()->create();

        $this->apiAs($admin)->getJson('/api/faqs')->assertOk()->assertJsonCount(2, 'data');
    }
}
