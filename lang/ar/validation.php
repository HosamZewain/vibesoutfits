<?php

return [
    'required' => 'اكتب أو اختار :attribute.',
    'required_if' => 'اكتب أو اختار :attribute.',
    'nullable' => 'القيمة دي مش صحيحة.',
    'string' => 'اكتب :attribute بشكل صحيح.',
    'integer' => 'اكتب :attribute كرقم صحيح.',
    'boolean' => 'الاختيار ده مش صحيح.',
    'in' => 'اختيار :attribute غير متاح.',
    'email' => 'اكتب الإيميل بشكل صحيح.',
    'regex' => 'اكتب رقم موبايل مصري صحيح.',
    'uploaded' => 'رفع الملف ماكملش. راجع نوع وحجم الملف وحاول تاني.',
    'mimes' => 'ارفع صورة JPG أو PNG أو WebP.',
    'mimetypes' => 'ارفع صورة JPG أو PNG أو WebP.',
    'min' => ['numeric' => ':attribute لازم يكون :min على الأقل.', 'string' => 'اكتب :attribute في :min حروف على الأقل.', 'file' => 'الملف صغير جدًا.'],
    'max' => ['numeric' => ':attribute لازم يكون :max أو أقل.', 'string' => ':attribute لازم يكون :max حرف أو أقل.', 'file' => 'حجم الملف أكبر من المسموح.'],
    'attributes' => [
        'name' => 'الاسم', 'email' => 'الإيميل', 'phone' => 'رقم الموبايل', 'password' => 'كلمة المرور',
        'zone' => 'المحافظة', 'city' => 'المدينة أو المنطقة', 'address' => 'العنوان', 'notes' => 'الملاحظات',
        'quantity' => 'الكمية', 'artwork' => 'التصميم', 'idea' => 'فكرة التصميم', 'design_mode' => 'نوع التصميم',
        'placement' => 'مكان الطباعة', 'size' => 'المقاس', 'color' => 'اللون', 'payment' => 'طريقة الدفع',
        'number' => 'رقم الطلب', 'reason' => 'التعديل المطلوب', 'description' => 'الوصف', 'category' => 'الفئة',
        'price' => 'السعر', 'custom_fee' => 'إضافة التخصيص', 'badge' => 'الشارة', 'image' => 'الصورة',
        'fee' => 'سعر الشحن', 'stock' => 'المخزون', 'amount' => 'المبلغ', 'reference' => 'مرجع العملية',
    ],
];
