# Landivo

منصة عربية/إنجليزية لإدارة صفحات الهبوط، المنتجات، العملاء والطلبات من لوحة تحكم واحدة. صُممت Landivo لتغطية رحلة الحملة التسويقية كاملة: إنشاء الصفحة، استقبال الطلب، متابعة العميل، إدارة المخزون، قياس الأداء، وإصدار التقارير والفواتير.

> Production: [carakeb.com](https://carakeb.com)
> Admin: [carakeb.com/admin](https://carakeb.com/admin)
> Repository: [github.com/madadha/Landivo](https://github.com/madadha/Landivo)

## المحتويات

- [نظرة عامة](#نظرة-عامة)
- [ترتيب لوحة التحكم](#ترتيب-لوحة-التحكم)
- [الوحدات الرئيسية](#الوحدات-الرئيسية)
- [رحلة البيانات](#رحلة-البيانات)
- [التقنيات](#التقنيات)
- [التشغيل المحلي](#التشغيل-المحلي)
- [المهام المجدولة والطوابير](#المهام-المجدولة-والطوابير)
- [الاختبارات والجودة](#الاختبارات-والجودة)
- [النشر على SiteGround](#النشر-على-siteground)
- [الأمان والنسخ الاحتياطي](#الأمان-والنسخ-الاحتياطي)
- [خارطة التطوير](#خارطة-التطوير)
- [English documentation](#english-documentation)

## نظرة عامة

Landivo ليست مجرد منشئ صفحة هبوط. النظام يربط المحتوى التجاري بالعمليات التشغيلية والتحليلات:

1. ينشئ المسؤول منتجًا ومتغيراته ومخزونه ووسائطه.
2. يبني صفحة هبوط متعددة اللغات ويرتب أقسامها.
3. ينشئ نموذج طلب ديناميكيًا ويحدد الحقول والشروط.
4. يسجل النظام الطلب ومصدر الحملة والجهاز والبيانات المرسلة.
5. يتابع الفريق الطلب عبر الحالات، النشاط، التذكيرات وواتساب.
6. تُحدّث الكمية عند التسليم وتظهر النتائج في التقارير.
7. يمكن إرسال رابط تقييم آمن للعميل بعد اكتمال الطلب.

النظام متعدد الحسابات على مستوى البيانات، ويدعم العربية والإنجليزية واتجاهي RTL وLTR.

## ترتيب لوحة التحكم

القائمة مرتبة بحسب سير العمل. أرقام الترتيب داخل كل مجموعة تبدأ من الأهم والأكثر استخدامًا.

| المجموعة | الشاشات التابعة لها | الهدف |
|---|---|---|
| المبيعات والطلبات | الطلبات، حالات الطلبات | استقبال الطلب ومتابعته حتى التسليم أو الأرشفة |
| العملاء والتواصل | العملاء، رسائل التواصل، تقييمات الزوار | ملف العميل، التواصل والتجربة بعد البيع |
| الكتالوج والمخزون | المنتجات، الوسائط | المنتجات والمتغيرات والأسعار والمخزون والصور والملفات |
| صفحات الهبوط | صفحات الهبوط، معرض الصفحات، صفحات الشكر | بناء رحلة التحويل من الإعلان حتى صفحة ما بعد الطلب |
| الموقع والمحتوى | إعداد الصفحة الرئيسية، صفحات الموقع | إدارة الموقع العام والقوائم والمحتوى الثابت |
| التسويق | النوافذ التسويقية | الحملات والنوافذ المنبثقة والاستهداف |
| التقارير والتحليلات | البحث الموحد، بحث العملاء، تحليلات الزوار، حالات الطلبات، تصدير الطلبات، تقرير التقييمات، مركز الاستيراد والتصدير | اتخاذ القرار والبحث وقياس الأداء ونقل البيانات |
| إدارة النظام | المستخدمون، الصلاحيات والأدوار، إعدادات النظام، سجل التدقيق | الهوية، الأمن، الصلاحيات وتتبع التغييرات |

لوحة التحكم الرئيسية تبقى مستقلة في أعلى القائمة لأنها نقطة البداية اليومية.

## الوحدات الرئيسية

### 1. صفحات الهبوط

- صفحات متعددة اللغات مع رابط مختصر وحالة نشر.
- ترتيب ديناميكي للأقسام.
- عنوان ووصف وصور منفصلة لكل لغة.
- معرض صور، سلايدر، فيديو، منتجات، أكورديون، HTML Blocks، نموذج، تواصل اجتماعي وفوتر.
- عداد زمني، شارات توثيق، تنبيهات مخزون وزوار وإشعارات شراء.
- شريط إعلانات متحرك ونوافذ تسويقية.
- تحكم بالألوان والخطوط والمحاذاة والخلفية والتجاوب.
- إعدادات SEO وأكواد Head وBody.
- مفاتيح تتبع الحملات مثل `utm_source` و`utm_campaign`.

### 2. Form Builder

يدعم الحقول الديناميكية التالية:

`text`, `email`, `phone`, `number`, `textarea`, `select`, `radio`, `checkbox`, `date`, `hidden`, `quantity`, `product_variant`, `address`, `city`, `country`, `file`, `image`.

كل حقل يدعم:

- الاسم الداخلي.
- عنوان وPlaceholder مترجمين.
- الإلزام وقواعد التحقق والقيمة الافتراضية.
- الخيارات والترتيب والحالة.
- شروط الظهور؛ مثال: إظهار العنوان والمدينة عند اختيار التوصيل.
- شارات للخيارات المميزة مع نص أو أيقونة أو صورة.

لا تُعرض في الصفحة العامة إلا الحقول التي أضافها المسؤول وفعّلها.

### 3. المنتجات والمخزون

- محتوى وصور متعددة اللغات.
- أسعار قبل وبعد الخصم.
- شارات عروض اختيارية ومتعددة اللغات مثل «عرض» و«عرض مميز»، مع ثلاثة تصاميم وألوان مستقلة للخلفية والنص.
- خيارات ومتغيرات Product Options / Variants.
- وسائط متعددة للمنتج.
- ترتيب رقمي موحّد يتحكم في ظهور المنتجات داخل الصفحة الرئيسية وصفحة المنتجات.
- مخزون مرتبط بالمنتج أو صفحة الهبوط.
- تحديث المخزون عند وصول الطلب إلى حالة التسليم.
- منع الحذف المباشر للسجلات المرتبطة، مع الأرشفة كخيار آمن.

### 4. الطلبات والعملاء

- حفظ بيانات النموذج كما أرسلها العميل.
- حفظ عنوان IP والجهاز والمتصفح ومصدر الحملة.
- حالات ملونة وقابلة للإدارة.
- سجل نشاط وملاحظات ومرفقات وتذكيرات للطلبات المؤجلة.
- تعديل الهاتف والبيانات التشغيلية.
- فتح واتساب بقوالب ورسائل ديناميكية.
- إرسال بيانات البنك أو رابط التقييم.
- فواتير فردية أو مجمعة PDF.
- أرشفة الطلبات التجريبية وحذفها وفق الصلاحيات وقواعد الارتباط.

### 5. الموقع العام

- صفحة رئيسية ديناميكية.
- شعار، Favicon، سلايدر مرن ومنتجات مميزة مع إعدادات مستقلة لكل شريحة.
- تحكم كامل من قاعدة البيانات بعناوين الصفحة الرئيسية، بطاقات الثقة، الصورة الجانبية، أقسام المنتجات والعروض والفوتر.
- قائمة رأس وقائمة فوتر قابلة للإدارة.
- صفحات المنتجات والتفاصيل ومن نحن والتواصل والخصوصية والشروط.
- صفحة منتجات تدعم الترقيم التقليدي أو التحميل التدريجي/التلقائي، مع عدد عناصر قابل للتحديد.
- دعم العربية والإنجليزية.

### 6. التسويق والتحليلات

- نوافذ تسويقية متعددة التصاميم واللغات.
- استهداف صفحات وجمهور وفترة ظهور وتكرار.
- تحليلات زيارات مباشرة وصفحات ومنتجات ومصادر.
- تتبع UTM من الرابط إلى الطلب.
- تقارير حالات الطلبات والمبيعات والتقييمات.
- بحث موحد باسم العميل أو الهاتف أو البريد.
- تصدير CSV وطباعة فواتير حسب الفلاتر.
- بحث مركزي فوري ومفهرس في الطلبات والعملاء والمنتجات.
- استيراد العملاء والمنتجات وتصدير الطلبات والعملاء والمنتجات عبر Queue Jobs.
- متابعة تقدم عمليات نقل البيانات وتنزيل النتائج وقوالب CSV الجاهزة.

### 7. النظام والأمان

- مستخدمون، أدوار وصلاحيات.
- إعدادات شركة متعددة اللغات وشعار وصورة مستخدم.
- مبدّل المظهر الفاتح والداكن في الشريط العلوي للوصول السريع دون ازدحام قائمة المستخدم.
- لغة افتراضية وكود دولة وبيانات اتصال وبنك وSEO.
- استعادة كلمة المرور.
- تفعيل اختياري لرمز التحقق عند دخول الإدارة.
- سجل تدقيق للتغييرات المهمة.

## رحلة البيانات

```text
إعلان / رابط UTM
        ↓
صفحة هبوط منشورة
        ↓
نموذج ديناميكي + تحقق + شروط
        ↓
عميل + طلب + مصدر حملة + بيانات جهاز
        ↓
حالة طلب + نشاط + تذكيرات + واتساب
        ↓
تسليم / تحديث مخزون / فاتورة
        ↓
صفحة شكر + تقييم + تقارير وتحليلات
```

## التقنيات

| الطبقة | التقنية |
|---|---|
| Backend | PHP 8.3+, Laravel 13 |
| Admin | Filament 5 |
| Frontend | Blade, Livewire, Alpine.js |
| Styling | Tailwind CSS 4, CSS مخصص |
| Build | Vite 8 |
| Database | MySQL / MariaDB |
| Authorization | Spatie Laravel Permission |
| PDF | Dompdf + معالجة عربية |
| Testing | PHPUnit / Laravel Testbench |

## التشغيل المحلي

### المتطلبات

- PHP `8.3` أو أحدث.
- Composer 2.
- Node.js حديث وNPM.
- MySQL أو MariaDB.
- امتدادات PHP المعتادة لـ Laravel: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `intl`, `gd`.

### التثبيت

```bash
git clone git@github.com:madadha/Landivo.git
cd Landivo
composer install
copy .env.example .env
php artisan key:generate
npm install
npm run build
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
```

على Windows/Laragon يمكن تشغيل المشروع عبر نطاق Laragon، أو:

```bash
php artisan serve
```

ثم افتح:

- الموقع: `http://landivo.test`
- لوحة التحكم: `http://landivo.test/admin`

### إعداد البيئة

لا تضع أي كلمة مرور أو مفتاح خاص داخل Git أو README. القيم الحساسة تبقى في `.env`.

```dotenv
APP_NAME=Landivo
APP_ENV=local
APP_DEBUG=true
APP_URL=http://landivo.test

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=landivo
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database

MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=465
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=
MAIL_FROM_NAME="${APP_NAME}"
```

## المهام المجدولة والطوابير

في التطوير:

```bash
php artisan queue:work --tries=3
php artisan schedule:work
```

في الإنتاج يجب تشغيل Worker دائم للطابور وإضافة Cron:

```cron
* * * * * cd /path/to/landivo && php artisan schedule:run >> /dev/null 2>&1
```

تُستخدم الطوابير للرسائل والمهام التي لا يجب أن تؤخر استجابة المستخدم.

## الاختبارات والجودة

```bash
composer test
php artisan test
npm run build
composer audit
```

قبل كل إصدار:

1. افحص `git diff --check`.
2. شغّل الاختبارات.
3. ابنِ أصول Vite.
4. خذ نسخة احتياطية من قاعدة البيانات والملفات.
5. نفّذ الترحيلات ثم افحص الصفحات العامة والإدارة.

## النشر على SiteGround

البنية الحالية:

```text
~/www/carakeb.com/
├── landivo/       # كود التطبيق، vendor، storage وملف .env
└── public_html/   # محتويات public فقط ونقطة دخول الموقع
```

النطاق: `carakeb.com`
المستودع: `madadha/Landivo`
الاستضافة: SiteGround

### مبدأ النشر الآمن

- GitHub هو مصدر الكود.
- `.env` وقاعدة البيانات وملفات `storage` بيانات إنتاج مستمرة ولا تُستبدل.
- `public_html` يحتوي ملفات `public` فقط.
- تؤخذ نسخة احتياطية قبل الترحيلات.
- لا تستخدم حذفًا عامًا أو `reset --hard` على مسار غير متحقق منه.

مثال تسلسل نشر بعد التحقق من المسارات والنسخ الاحتياطي:

```bash
cd ~/www/carakeb.com/landivo
git fetch origin
git pull --ff-only origin master
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

بعد ذلك تُزامن أصول `public/` مع `public_html/` مع الحفاظ على الربط الصحيح لـ `storage`.

### فحص ما بعد النشر

- الصفحة الرئيسية.
- تسجيل الدخول واستعادة كلمة المرور ورمز التحقق.
- صفحة هبوط بالعربية والإنجليزية.
- إرسال طلب تجريبي مع UTM.
- ظهور الطلب وبيانات النموذج والمصدر.
- الصور والملفات وPDF.
- الطوابير وCron والبريد.
- سجل الأخطاء `storage/logs/laravel.log`.

## الأمان والنسخ الاحتياطي

- لا تُرفع `.env` أو مفاتيح SSH أو كلمات المرور إلى Git.
- غيّر أي سر تم مشاركته خارج قناة آمنة.
- استخدم صلاحيات أقل امتيازًا للمستخدمين وقاعدة البيانات.
- فعّل HTTPS وSecure Cookies في الإنتاج.
- اجعل `APP_DEBUG=false` في الإنتاج.
- راقب محاولات الدخول والأخطاء والطوابير.
- نفّذ نسخة يومية لقاعدة البيانات و`storage/app/public`.
- اختبر الاستعادة دوريًا؛ النسخة غير المختبرة ليست خطة استعادة.
- احتفظ بنسخ خارج الخادم وبسياسة مدة احتفاظ واضحة.

## قواعد التطوير

- أي نص ظاهر للمستخدم يجب أن يدعم الترجمة عند الحاجة.
- لا تضف محتوى ثابتًا إلى صفحة الهبوط؛ المصدر هو إعدادات الصفحة.
- كل استعلام إداري يجب أن يحترم `account_id`.
- استخدم الأرشفة للسجلات المرتبطة بدل كسر العلاقات.
- ضع منطق الأعمال في Services/Actions وليس داخل واجهة Filament.
- أضف Migration وTest لكل تغيير بنيوي.
- لا تعرض بيانات العميل الحساسة في إشعارات التسويق.
- افحص الموبايل وRTL وLTR قبل اعتماد أي واجهة.

## خارطة التطوير

### P0 — الثبات والتشغيل

- مراقبة مركزية للأخطاء والأداء مع تنبيهات.
- لوحة لصحة Queue وScheduler والبريد والنسخ الاحتياطية.
- اختبارات End-to-End لأهم المسارات: تسجيل الدخول، الطلب، الفاتورة والتقييم.
- نشر آلي عبر CI/CD مع Release قابل للرجوع.
- نسخ احتياطي مشفر واختبار استعادة شهري.
- توثيق Runbook للأعطال والنشر والاستعادة.

### P1 — المبيعات والأتمتة

- تكامل رسمي مع WhatsApp Business API بدل الروابط فقط.
- Workflow مرن للحالات مع قواعد تلقائية وإشعارات.
- حجوزات مخزون مؤقتة ومنع البيع الزائد.
- استعادة الطلبات/النماذج غير المكتملة.
- بوابات دفع وروابط دفع ومطابقة المدفوعات.
- Dashboard موحد لعائد الحملات حسب UTM والتكلفة والتحويل.
- قوالب رسائل مع موافقات وسجل إرسال وتسليم.

### P2 — التوسع

- تحويل فصل الحسابات الحالي إلى SaaS متعدد المستأجرين مع اشتراكات وفوترة.
- API موثق وWebhooks للطلبات والمخزون والعملاء.
- صلاحيات أدق على مستوى الإجراء والسجل.
- تخزين كائنات/CDN وتحويل الصور إلى WebP/AVIF.
- استيراد الطلبات بعد بناء قواعد تحقق ومراجعة تمنع إنشاء طلبات تشغيلية ناقصة.
- استيراد وتصدير XLSX إلى جانب CSV عند الحاجة التشغيلية.

### P3 — التحسين الذكي

- A/B Testing لأقسام وصفحات الهبوط.
- Funnels وCohorts وتحليل الاحتفاظ والتحويل.
- كشف الطلبات المكررة أو المشبوهة.
- اقتراحات منتجات وعروض مبنية على السلوك.
- مساعد كتابة تسويقية مع مراجعة بشرية.
- PWA أو تطبيق موبايل للفريق التشغيلي.

## English documentation

### Overview

Landivo is a bilingual landing-page commerce and operations platform. It connects campaigns, dynamic forms, products, customers, orders, inventory, WhatsApp workflows, invoices, reviews, analytics, and public website content in one Laravel/Filament application.

### Admin information architecture

| Group | Modules |
|---|---|
| Sales & Orders | Orders, Order Statuses |
| Customers & Communication | Customers, Contact Messages, Reviews |
| Catalog & Inventory | Products, Media Library |
| Landing Pages | Landing Pages, Page Gallery, Thank-you Pages |
| Website & Content | Homepage Settings, Site Pages |
| Marketing | Marketing Popups |
| Reports & Analytics | Global Search, Customer Search, Visitor Analytics, Status Report, Order Export, Reviews Report, Data Transfer Center |
| System Administration | Users, Roles, System Settings, Audit Log |

### Core capabilities

- Fully dynamic Arabic/English landing pages with ordered sections.
- Conditional form builder with translated fields and options.
- Product variants, media, pricing, stock, and delivery-aware inventory updates.
- Localized product offer badges with optional labels, pill/ribbon/outline styles, and independent text/background colors.
- Consistent numeric product ordering across the homepage and product catalog.
- Order lifecycle, activity log, reminders, files, WhatsApp templates, and PDF invoices.
- UTM attribution from the public URL to the stored order.
- Indexed global search across orders, customers, and products.
- Queue-based CSV imports and exports with progress tracking and downloadable templates.
- Independent thank-you pages and secure customer review links.
- Fully database-driven homepage, hero slides, trust cards, featured products, offers, menus, footer, legal pages, and product catalog.
- Product catalog pagination or progressive/infinite loading with configurable page size.
- Popups, announcement tickers, social links, visitor analytics, and exports.
- Top-bar light/dark/system theme controls in the administration panel.
- Role-based administration, optional login verification, and audit trails.

### Local setup

```bash
git clone git@github.com:madadha/Landivo.git
cd Landivo
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run build
php artisan migrate --seed
php artisan storage:link
php artisan optimize:clear
php artisan serve
```

Never commit `.env`, SSH keys, production credentials, private customer exports, or database backups.

### Production

- Domain: `carakeb.com`
- Hosting: SiteGround
- Application directory: `~/www/carakeb.com/landivo`
- Public web root: `~/www/carakeb.com/public_html`
- Source repository: `madadha/Landivo`

Production deployments must preserve `.env`, the database, and persistent storage. Back up first, deploy code, install production dependencies, run migrations, rebuild Laravel caches, synchronize public assets, and perform smoke tests.

### Recommended next steps

1. Operational monitoring, automated backups, restore drills, E2E tests, and CI/CD.
2. Official WhatsApp API, payment gateways, abandoned-lead recovery, and inventory reservations.
3. SaaS billing, documented APIs/webhooks, object storage/CDN, and granular permissions.
4. Conversion experiments, funnels, fraud detection, and assisted merchandising.

---

© 2026 Landivo. Internal project documentation; update this file with every material release.
