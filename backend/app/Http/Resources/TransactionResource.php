<?php

namespace App\Http\Resources;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin Transaction */
class TransactionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'invoice_number' => $this->invoice_number,
            'package_name_snapshot' => $this->package_name_snapshot,
            'invitation' => $this->whenLoaded('invitation', fn () => [
                'id' => $this->invitation->id,
                'title' => $this->invitation->title,
                'slug' => $this->invitation->slug,
            ]),
            'owner' => $this->when($request->user()?->isAdmin(), fn () => $this->whenLoaded('user', fn () => [
                'name' => $this->user->name,
                'email' => $this->user->email,
            ])),
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'payment_channel' => $this->payment_channel,
            'status' => $this->status,
            // Only meaningful while pending — still harmless to include once paid/failed, the
            // customer-facing checkout dialog just won't render it past that point.
            'instructions' => Transaction::paymentInstructionsFor($this->payment_method),
            'proof_image' => $this->proof_image ? Storage::disk('public')->url($this->proof_image) : null,
            'proof_uploaded_at' => $this->proof_uploaded_at?->toIso8601String(),
            'paid_at' => $this->paid_at?->toIso8601String(),
            'expired_at' => $this->expired_at?->toIso8601String(),
            'verified_at' => $this->verified_at?->toIso8601String(),
            'notes' => $this->when($request->user()?->isAdmin(), $this->notes),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
