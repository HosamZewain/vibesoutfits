<?php

return [
    'categories' => ['regular' => 'Regular T-Shirts', 'oversized' => 'Oversized T-Shirts', 'hoodies' => 'Hoodies'],
    'sizes' => ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'],
    'colors' => [
        'black' => ['أسود', '#19191f'], 'bone' => ['أوف وايت', '#e8dfce'],
        'burgundy' => ['نبيتي', '#661f2a'], 'charcoal' => ['رمادي غامق', '#585761'],
        'white' => ['أبيض', '#fafafa'], 'red' => ['أحمر', '#e53442'],
    ],
    'modes' => ['ready' => 'تصميم جاهز', 'upload' => 'هرفع تصميمي', 'idea' => 'عندي فكرة'],
    'placements' => ['front' => 'أمامي', 'back' => 'خلفي'],
    'design_statuses' => ['review' => 'بنراجع فكرتك', 'preview' => 'في انتظار موافقتك', 'changes' => 'التعديلات تحت التنفيذ', 'approved' => 'تمت الموافقة'],
    'statuses' => ['new' => 'تم استلام الطلب', 'production' => 'في الإنتاج', 'shipped' => 'اتشحن', 'delivered' => 'تم التسليم', 'cancelled' => 'ملغي'],
    'deposit_percent' => (int) env('VIBE_DEPOSIT_PERCENT', 50),
    'transfer_recipient' => env('VIBE_TRANSFER_RECIPIENT'),
    'transfer_details' => env('VIBE_TRANSFER_DETAILS'),
    'email_confirmations' => env('VIBE_EMAIL_CONFIRMATIONS', false),
    'social' => [
        'Instagram' => env('VIBE_INSTAGRAM'), 'TikTok' => env('VIBE_TIKTOK'), 'Facebook' => env('VIBE_FACEBOOK'),
    ],
];
