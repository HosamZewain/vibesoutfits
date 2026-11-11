# Vibes OutFits

**Your Vibe, Your Choice** · متجر ملابس شبابية حسب الطلب ولوحة إدارة. مبني بـLaravel 13 وBlade، وبيشتغل من غير Node أو خطوات بناء للواجهة.

## التشغيل المحلي

المتطلبات: PHP 8.4 أو أحدث متوافق مع Laravel 13، Composer، وامتداد `pdo_sqlite` للتجربة المحلية.

```bash
composer install
cp -n .env.example .env
touch database/database.sqlite
php artisan key:generate
php artisan migrate --seed
php artisan vibe:admin
php artisan serve --host=127.0.0.1 --port=8170
```

نفّذ `key:generate` مرة واحدة عند إنشاء البيئة. أمر `vibe:admin` بيطلب الإيميل وكلمة مرور من اختيارك بدون حساب افتراضي. المتجر على [127.0.0.1:8170](http://127.0.0.1:8170/) ولوحة الإدارة على [127.0.0.1:8170/admin](http://127.0.0.1:8170/admin). في البيئة المحلية، الـSeeder بيضيف ستة منتجات تجريبية وأسعار شحن مبدئية.

## دورة الطلب

1. العميل يختار القطعة والمقاس واللون والكمية ومكان الطباعة. ممكن يشتري التصميم الجاهز أو يرفع تصميم أو يكتب فكرة.
2. الشحن بيتحسب حسب المحافظة. الجاهز يقبل الدفع عند الاستلام، والمخصص يحتاج مقدم على قيمة القطع المخصصة. الموقع بيعرض تفاصيل التحويل، وفريق الإدارة يسجّل المبلغ بعد مراجعته.
3. الفريق يجهّز المعاينة يدويًا، ويرفعها على القطعة في لوحة الإدارة. العميل يدخل برقم الطلب والموبايل، ويوافق أو يطلب تعديل. لو فيه ملف طباعة منفصل، ارفعه بعد المعاينة وقبل الموافقة.
4. الإنتاج بيتفتح بعد اكتمال المقدم والموافقة على كل القطع المخصصة. الشحن بيتسجّل باسم الشركة ورقم الشحنة، والتسليم بعد تسجيل باقي المبلغ.
5. المخزون بيتحجز عند تسجيل الطلب. الإلغاء قبل الإنتاج بيرجّع الكمية. لو الطلب دخل الإنتاج، أي قطع صالحة للرجوع للمخزون تُضاف من صفحة المنتج. تسجيل استرداد المبلغ في اللوحة يوثّق العملية بعد تنفيذها خارجيًا.

## تجهيز الإطلاق

- مصدر الكود هو فرع `main` من [HosamZewain/vibesoutfits](https://github.com/HosamZewain/vibesoutfits). على Hostinger، اضبط إصدار PHP على 8.4 وثبّت الاعتماديات من `composer.lock` بأمر `composer2 install --no-dev --optimize-autoloader`. انشر محتوى `public/` فقط كجذر الويب، واحتفظ بباقي مشروع Laravel خارجه. [ربط GitHub بـHostinger](https://www.hostinger.com/support/1583302-how-to-deploy-a-git-repository-in-hostinger/) · [Composer على Hostinger](https://www.hostinger.com/support/5792078-how-to-use-composer-at-hostinger/)
- اضبط `APP_ENV=production` و`APP_DEBUG=false` و`APP_URL=https://vibesoutfits.com` و`SESSION_SECURE_COOKIE=true`. وجّه السيرفر إلى مجلد `public/`، وخزّن قاعدة البيانات والملفات في مساحة دائمة تدخل في النسخ الاحتياطي. ملفات العملاء داخل `storage/app/private` وتُعرض فقط من خلال صلاحيات التطبيق.
- نفّذ `php artisan migrate --force` و`php artisan db:seed --force` على قاعدة البيانات الجديدة. في الإنتاج الـSeeder ينشئ المحافظات بدون تفعيلها، ومن غير منتجات تجريبية. أدخل المنتجات والمخزون والصور الفعلية، وراجع الأسعار وفعّل المحافظات من لوحة الإدارة.
- اضبط `VIBE_TRANSFER_RECIPIENT` و`VIBE_TRANSFER_DETAILS` لتفعيل التحويل الحقيقي، و`VIBE_DEPOSIT_PERCENT` لتغيير نسبة المقدم (الافتراضي 50%). التأكيد يدوي. لا يوجد ربط ببوابة بطاقات أو شركة شحن.
- اضبط `MAIL_MAILER=smtp` وإعدادات البريد و`VIBE_EMAIL_CONFIRMATIONS=true` لو هتفعّل إيميل التأكيد. أضف روابط الحسابات في `VIBE_INSTAGRAM` و`VIBE_TIKTOK` و`VIBE_FACEBOOK` عشان تظهر في الموقع.
- شغّل `php artisan schedule:run` كل دقيقة على السيرفر لتنظيف ملفات السلات المتروكة. اضبط الحد الأقصى للرفع في PHP وخادم الويب (12 ميجابايت للملف و15 ميجابايت للطلب، على الأقل).
- شغّل `php artisan test` للتحقق من التسعير والمخزون والصلاحيات ورفع الملفات وموافقة التصميم ودورة الطلب.

## الصور

صور الحملة والكتالوج في `public/assets/img/` صور عرض تجريبية صُنعت باستخدام أداة ImageGen المدمجة. استخدم صور العينات الحقيقية عند اعتماد المنتجات الفعلية. اللوجو الرسمي مصدره الملف المرفق، وتمت مواءمة ألوانه مع الواجهة الغامقة.

### أوصاف توليد الصور

الأوصاف التالية استُخدمت مع أداة ImageGen المدمجة. تُعرض النسخ المعدلة بالأحمر في المتجر:

- **صورة الحملة الأساسية:** Use case: photorealistic-natural. Asset type: wide website campaign hero photograph for Egyptian Gen Z streetwear label VIBE OUTFITS. A photoreal fashion editorial with two stylish Egyptian young adults on the RIGHT two thirds, young man wearing a black heavy oversized tee with a bold violet angular abstract graphic and young woman in dark plum oversized hoodie, authentic confident poses, downtown Cairo concrete stairwell at night, brutalist shadows, charcoal gray walls, punchy violet light from right, subtle film grain, direct flash, premium real fabric. LEFT 42% is empty dark near-black wall and negative space for website headline. Wide horizontal 3:2 crop. Keep both faces above midpoint, clear garment details. No typography, no text, no logos, no watermarks.
- **تعديل الحملة للأحمر:** Use case: precise-object-edit, photorealistic-natural. Edit the supplied VIBE OUTFITS Cairo streetwear photograph. Preserve composition, exact faces, skin tones, poses, fabric details and framing, including the empty dark left side. Change only the violet neon illumination on the right to rich scarlet red (#f22f43), the violet graphics on the black t-shirt to rich dark scarlet, and the plum hoodie to deep burgundy-black with a realistic red rim light. Retain neutral black and concrete gray shadows. Do not colorize the whole image or make skin red. Preserve photoreal cinematic streetwear aesthetic. Same 3:2 crop, no text.
- **صور الكتالوج الأساسية:** Use case: product-mockup. Asset type: photo grid for six streetwear ecommerce cards. Exactly 3 columns and 2 rows, equal rectangular tile sizes, NO gutters or borders, six independent clean premium realistic flat lay front-facing garment photographs; one centered garment per cell with generous 12% margin completely within its own cell; background concrete neutral pale grey/violet. Top left washed black oversized t-shirt with violet abstract butterfly graphic, top middle offwhite oversized tee with dark abstract racing illustration, top right heavyweight black hoodie with a violet sharp orbit emblem. Bottom left dark muted grape hoodie with minimal small lilac geometry on chest, bottom middle charcoal classic fit t-shirt with ivory moon graphic, bottom right bone white classic fit tee with a single abstract purple small chest emblem. High quality textile texture, editorial natural shadows. No hangers, no props, no humans, no captions, no text, no logos, no watermarks. Image ratio 3:2.
- **تعديل الكتالوج للأحمر:** Use case: precise-object-edit, product-mockup. Edit this six-tile photo grid for VIBE OUTFITS ecommerce. Keep the exact garments and centered positions, the exact 3x2 grid layout, identical crop, textures, and graphics. ONLY change the violet butterfly on upper-left black tee, the violet orbit symbol on upper-right black hoodie, and the violet abstract chest mark on lower-right bone white tee to deep scarlet red. Also change the lower-left purple hoodie garment to deep maroon fabric, with a clean muted scarlet chest graphic. Change the cool light lavender-gray concrete background slightly to warm neutral gray across all six images. Preserve other garments and their artwork untouched. No borders, no captions or new text.

اللوجو بصيغة SVG. حافظنا على رمز اللوجو المرفق وعدّلنا كتابة الاسم إلى VIBES OUTFITS. الملفات: `vibe-logo.svg` و`vibe-wordmark.svg` و`mark.svg` داخل `public/assets/img/`.
