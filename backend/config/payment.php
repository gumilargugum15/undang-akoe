<?php

return [
    /*
     * Manual payment methods only — no payment gateway is integrated. The customer transfers
     * or scans, uploads proof (Transaction::proof_image), and an admin reviews it manually
     * (TransactionController::approve/reject). Update these via env vars, no code change needed.
     */
    'bank_transfer' => [
        [
            'bank' => 'BCA',
            'account_number' => env('PAYMENT_BCA_NUMBER', '1234567890'),
            'account_name' => env('PAYMENT_BCA_NAME', 'PT Undang Akoe Digital'),
        ],
        [
            'bank' => 'BNI',
            'account_number' => env('PAYMENT_BNI_NUMBER', '0987654321'),
            'account_name' => env('PAYMENT_BNI_NAME', 'PT Undang Akoe Digital'),
        ],
    ],

    'qris' => [
        'image_url' => env('PAYMENT_QRIS_IMAGE_URL'),
        'merchant_name' => env('PAYMENT_QRIS_MERCHANT_NAME', 'Undang Akoe'),
    ],

    // Fallbacks only — an admin can set the real number/name for each from the admin payment
    // settings page (stored in site_settings, see PaymentSettingService), which takes priority.
    'ewallets' => [
        'dana' => [
            'number' => env('PAYMENT_DANA_NUMBER'),
            'account_name' => env('PAYMENT_DANA_NAME', 'Undang Akoe'),
        ],
        'gopay' => [
            'number' => env('PAYMENT_GOPAY_NUMBER'),
            'account_name' => env('PAYMENT_GOPAY_NAME', 'Undang Akoe'),
        ],
    ],

    // How long a pending transaction stays valid before ExpirePendingTransactions marks it
    // Expired and returns the invitation to Draft.
    'pending_expiry_hours' => (int) env('PAYMENT_PENDING_EXPIRY_HOURS', 24),
];
