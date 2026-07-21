# Landivo

[العربية](#العربية) · [English](#english)

منصة احترافية ثنائية اللغة لبناء صفحات الهبوط، إدارة المنتجات والطلبات، أتمتة التواصل، وقياس أداء الحملات التسويقية من لوحة تحكم واحدة.

> الإنتاج: [carakeb.com](https://carakeb.com) · لوحة التحكم: [carakeb.com/admin](https://carakeb.com/admin)

---

## العربية

### نظرة عامة

**Landivo** نظام مبني باستخدام Laravel وFilament لإدارة دورة التسويق والبيع كاملة: من إنشاء صفحة الهبوط والنموذج الديناميكي، مرورًا باستلام الطلب ومتابعته، وصولًا إلى المخزون والفواتير والتقييمات والتقارير.

يدعم النظام العربية والإنجليزية، اتجاهي RTL وLTR، التصميم المتجاوب، تعدد الحسابات، الصلاحيات، والتخصيص من لوحة التحكم دون الاعتماد على نصوص ثابتة داخل الواجهات.

### الوحدات الرئيسية

- **صفحات الهبوط:** محتوى متعدد اللغات، روابط مخصصة، نشر ومعاينة، أقسام قابلة للترتيب، صور وفيديو وسلايدر وأكورديون وHTML مخصص.
- **منشئ النماذج:** حقول ديناميكية، خيارات مترجمة، تحقق، ملفات وصور، عروض ومنتجات، وحقول شرطية.
- **المنتجات والمخزون:** أسعار وخصومات، وصف وتفاصيل، صور متعددة، خيارات ومتغيرات، مخزون وحسم آمن عند التسليم.
- **الطلبات والعملاء:** بيانات العميل والعرض المختار، UTM والمصدر، الملاحظات، التذكيرات، الملفات، سجل النشاط، الفواتير وإجراءات واتساب.
- **حالات الطلبات:** حالات ملونة ومرتبة وقابلة للأرشفة، مع حماية السجلات المرتبطة من الحذف غير الآمن.
- **صفحات الشكر المستقلة:** إنشاء عدد غير محدود من صفحات ما بعد التحويل مع رابط مستقل لكل صفحة.
- **التقييمات:** روابط موقعة ومؤقتة، نجوم إلزامية، تعليق اختياري، مراجعة واعتماد وعرض عام.
- **التسويق:** نوافذ تسويقية، شريط إعلانات، إشعارات شراء، عدادات ندرة وزوار، ومفاتيح تتبع الحملات.
- **التقارير:** تحليلات الزوار، حالات الطلبات، تصدير CSV، تقارير التقييمات وسجل التدقيق.
- **الموقع العام:** الرئيسية، المنتجات وتفاصيل المنتج، من نحن، الاتصال، الخصوصية، الأحكام والشروط، وقوائم ديناميكية.
- **الإدارة والأمان:** مستخدمون وأدوار وصلاحيات، استعادة كلمة المرور، تحقق اختياري عند الدخول، وهوية بصرية وإعدادات شركة.
- **مكتبة الوسائط:** رفع وتنظيم وبحث، نسخ الرابط العام، معرفة الاستخدام، وتحديد الملفات غير المستخدمة بأمان.

### آخر تحديث: صفحات الشكر المستقلة

أضيف نظام مستقل بالكامل لصفحات الشكر، دون تعديل إعدادات صفحة الشكر القديمة الموجودة داخل صفحة الهبوط ودون ربط تلقائي بينهما.

#### الإمكانات

- إنشاء عدد غير محدود من صفحات الشكر.
- رابط مستقل وقابل للنسخ بصيغة: `/thank-you/{slug}`.
- محتوى عربي وإنجليزي مع اتجاه صحيح لكل لغة.
- عنوان، وصف، نص زر، صور مستقلة لكل لغة، وشعار الصفحة.
- قوالب وتصاميم متعددة مع تحكم بالألوان والخلفية والمحاذاة.
- عداد زمني اختياري وتحويل تلقائي إلى رابط خارجي.
- رابط زر قابل للتخصيص مع اختيار طريقة الفتح.
- مفاتيح تتبع حملات من نوع **المفتاح والقيمة والشرح**.
- أكواد `Head` و`Body` مستقلة للبيكسلات وأدوات القياس.
- حالة نشر وتفعيل، معاينة مباشرة، ونسخ للرابط من لوحة التحكم.
- يمكن استخدام رابط صفحة الشكر يدويًا داخل أي HTML Block أو تدفق تسويقي.

#### الروابط

```text
لوحة الإدارة: /admin/thank-you-pages
الصفحة العامة: /thank-you/{slug}
```

#### ملاحظات الترقية

```bash
php artisan migrate --force
php artisan optimize:clear
```

Migration المضافة:

```text
2026_07_21_230000_create_thank_you_pages_table
```

### التقنيات والمتطلبات

- PHP 8.2+
- Laravel 12
- Filament 5 وLivewire
- Composer 2
- MySQL أو MariaDB للإنتاج
- Node.js وnpm وVite وTailwind CSS
- Nginx أو Apache

### التثبيت المحلي

```bash
git clone git@github.com:madadha/Landivo.git
cd Landivo
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan storage:link
php artisan serve
```

الروابط الافتراضية:

- الموقع: `http://127.0.0.1:8000`
- لوحة التحكم: `http://127.0.0.1:8000/admin`
- مع Laragon: `http://landivo.test:8000`

### إعداد البيئة

```env
APP_NAME=Landivo
APP_ENV=local
APP_DEBUG=true
APP_URL=http://landivo.test:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=landivo
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
```

لا ترفع ملف `.env` أو كلمات المرور أو مفاتيح SSH أو بيانات SMTP إلى GitHub.

### الاختبارات والجودة

```bash
php artisan test
php artisan route:list
php artisan view:cache
composer audit
```

نتيجة الاختبارات عند إصدار صفحات الشكر المستقلة:

```text
54 tests passed
214 assertions
```

### بنية المشروع

```text
app/Filament/Resources/        موارد ونماذج وجداول لوحة التحكم
app/Http/Controllers/          منطق صفحات الموقع والاستقبال
app/Models/                    نماذج قاعدة البيانات والعلاقات
app/Services/                  خدمات الأعمال والتكاملات
database/migrations/           تغييرات بنية قاعدة البيانات
resources/views/               واجهات الموقع ولوحة التحكم
resources/css/                 التصميم العام وتخصيص Filament
routes/                        مسارات الموقع والتقارير
storage/app/public/            الصور والملفات العامة
tests/                         اختبارات النظام
```

### الاستضافة الحالية

النسخة الإنتاجية مستضافة على **SiteGround** باستخدام Nginx وMySQL وHTTPS.

```text
Domain:          https://carakeb.com
Admin:           https://carakeb.com/admin
Application:     ~/www/carakeb.com/landivo
Document root:   ~/www/carakeb.com/public_html
Public storage:  public_html/storage -> ../landivo/storage/app/public
```

يجب أن يشير `public_html/index.php` إلى التطبيق الفعلي:

```php
require __DIR__.'/../landivo/vendor/autoload.php';
$app = require_once __DIR__.'/../landivo/bootstrap/app.php';
```

لا يحتوي هذا المستند على بيانات دخول الاستضافة أو قاعدة البيانات. تحفظ الأسرار في `.env` على الخادم فقط.

### النشر الآمن

1. خذ نسخة احتياطية من قاعدة البيانات و`.env` و`storage/app/public`.
2. شغّل الاختبارات محليًا.
3. راجع التغييرات وارفع Commit واضحًا إلى GitHub.
4. انقل ملفات الإصدار المختبرة إلى مجلد التطبيق مع الحفاظ على `.env` و`storage`.
5. نفّذ أوامر النشر التالية داخل مجلد التطبيق:

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

6. عند تغير CSS أو JavaScript:

```bash
npm ci
npm run build
```

7. افحص الموقع، تسجيل دخول الإدارة، الصور، نموذج الطلب، UTM، البريد، واتساب، والتقارير.

### النسخ الاحتياطي والأمان

- احتفظ بنسخة يومية من قاعدة البيانات وملفات الوسائط.
- استخدم الأرشفة بدل حذف السجلات المرتبطة.
- لا تستخدم أوامر rollback مدمرة في الإنتاج دون مراجعة أثر البيانات.
- راجع سجل التدقيق والصلاحيات والحسابات الإدارية دوريًا.
- فعّل HTTPS والتحقق الإضافي للحسابات الحساسة.
- راقب `storage/logs/laravel.log` ومساحة التخزين والمهام المجدولة.

---

## English

### Overview

**Landivo** is a bilingual Laravel and Filament platform that manages the full marketing and sales lifecycle: landing-page creation, dynamic forms, lead and order capture, CRM follow-up, inventory, invoices, reviews, and campaign analytics.

It supports Arabic and English, RTL/LTR layouts, responsive interfaces, account isolation, role-based permissions, and admin-managed content without hard-coded public-page content.

### Main modules

- Multilingual landing-page builder with sortable sections and reusable HTML blocks.
- Dynamic and conditional form builder with translated fields and options.
- Product catalogue, media, pricing, variants, options, and inventory.
- Orders, customers, attribution, reminders, files, invoices, and WhatsApp actions.
- Colour-coded order workflows with archival and relationship protection.
- Independent and reusable thank-you pages.
- Signed customer reviews and moderation.
- Marketing popups, announcement tickers, scarcity, and social proof.
- Visitor, order-status, review, export, and audit reports.
- Dynamic public website, navigation, footer, SEO, and company settings.
- Users, roles, permissions, password recovery, and optional login verification.
- Searchable media library with usage tracking.

### Latest update: Independent Thank-You Pages

This release introduces a standalone thank-you page system. It does not modify or replace the legacy thank-you settings embedded in landing pages, and pages are not linked automatically.

Features include:

- Unlimited thank-you pages.
- Copyable public URLs using `/thank-you/{slug}`.
- Arabic and English content and images.
- Templates, colours, typography, alignment, and background controls.
- Optional countdown and timed external redirect.
- Configurable CTA link and target.
- Campaign tracking key/value/comment entries.
- Independent Head and Body code for analytics and pixels.
- Publish state, admin preview, and direct URL actions.
- Manual use inside HTML blocks or any marketing flow.

Admin resource:

```text
/admin/thank-you-pages
```

Upgrade commands:

```bash
php artisan migrate --force
php artisan optimize:clear
```

### Requirements

- PHP 8.2+
- Laravel 12
- Filament 5 and Livewire
- Composer 2
- MySQL/MariaDB
- Node.js, npm, Vite, and Tailwind CSS
- Nginx or Apache

### Local installation

```bash
git clone git@github.com:madadha/Landivo.git
cd Landivo
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan storage:link
php artisan serve
```

### Quality checks

```bash
php artisan test
php artisan route:list
php artisan view:cache
composer audit
```

The independent thank-you page release passed **54 tests with 214 assertions**.

### Production topology

```text
Domain:          https://carakeb.com
Admin:           https://carakeb.com/admin
Application:     ~/www/carakeb.com/landivo
Document root:   ~/www/carakeb.com/public_html
Public storage:  public_html/storage -> ../landivo/storage/app/public
Runtime:         PHP 8.2+, MySQL/MariaDB, managed Nginx and HTTPS
```

Never commit `.env`, database credentials, SMTP passwords, SSH keys, private keys, or hosting credentials. Preserve the production `.env`, `APP_KEY`, and `storage` directory during every deployment.

### Production deployment

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Rebuild front-end assets with `npm ci && npm run build` when CSS or JavaScript changes. Verify the public website, admin authentication, compiled assets, storage link, order flow, UTM attribution, mail, WhatsApp actions, and reports after deployment.

### Security and recovery

- Back up MySQL, `.env`, and `storage/app/public` regularly.
- Archive linked records instead of deleting them.
- Restore the database, media, matching code revision, and original `APP_KEY` together.
- Deploy a known-good commit for rollback; avoid destructive production database rollbacks.
- Review permissions, audit logs, new orders, due reminders, disk usage, and Laravel logs.

## License

Landivo is maintained as a private commercial project. Final commercial licensing terms must be defined before public distribution.
