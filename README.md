# Landivo

[العربية](#العربية) · [English](#english)

Landivo is a bilingual Laravel platform for building high-converting landing pages, managing products and orders, and measuring marketing performance from one Filament administration panel.

---

## العربية

### نظرة عامة

**Landivo** نظام احترافي ثنائي اللغة لبناء صفحات الهبوط وإدارة المنتجات والعملاء والطلبات والحملات التسويقية. صُمم النظام ليجمع إنشاء الصفحة، استقبال الطلب، متابعة العميل، المخزون، التقييمات، والتحليلات في لوحة تحكم واحدة متجاوبة.

### الوحدات الرئيسية

- **صفحات الهبوط:** محتوى عربي وإنجليزي، رابط مخصص، حالة النشر، معاينة مباشرة، ترتيب مرن للأقسام وتصميم متجاوب.
- **منشئ النماذج:** حقول ديناميكية من أنواع النص والبريد والهاتف والرقم والنص الطويل والقائمة والراديو والتحديد والتاريخ والملف والصورة والكمية والعنوان والمدينة والدولة ومتغير المنتج.
- **الشروط الديناميكية:** إظهار وإخفاء الحقول بناءً على إجابات العميل، مع قواعد تحقق وقيم افتراضية وخيارات مترجمة.
- **المنتجات:** أسعار وخصومات ومخزون وخيارات ومتغيرات ووسائط متعددة ووصف وتفاصيل بالعربية والإنجليزية.
- **بطاقات المنتجات:** منتج واحد أو عدة منتجات، شبكة أو سلايدر، مع التحكم بالعنوان والألوان والخطوط والترتيب.
- **إدارة الطلبات:** بيانات العميل، العرض المختار، مصدر الطلب، UTM، عنوان IP، الجهاز، الملاحظات، الملفات، سجل النشاط، التذكيرات، الفاتورة وروابط واتساب.
- **حالات الطلبات:** حالات قابلة للتخصيص والترتيب والتلوين والأرشفة، مع حماية السجلات المرتبطة من الحذف غير الآمن.
- **المخزون:** تتبع كمية المنتج والمتغير، وخصم المخزون عند انتقال الطلب إلى حالة التسليم وفق إعدادات النظام.
- **التقييمات:** رابط تقييم موقّع يمكن إرساله للعميل، نجوم إلزامية ونص اختياري، مراجعة واعتماد من لوحة التحكم وعرض عام أنيق.
- **النوافذ التسويقية:** Popups متعددة القوالب والصور، استهداف صفحات وجمهور، جدولة، تكرار ظهور، أولوية، ألوان وسلوك قابل للتخصيص.
- **التسويق:** أكواد Head وBody، HTML Blocks، شريط إعلاني متحرك، إشعارات شراء، عداد محدودية المخزون وعدد المتصفحين.
- **SEO:** عنوان ووصف وكلمات مفتاحية وCanonical وOpen Graph وSitemap وGoogle Search Console وGoogle Analytics وأكواد تحقق إضافية.
- **التقارير:** تحليلات الزوار، تقرير حالات الطلبات، تصدير الطلبات CSV، تقرير التقييمات وسجل التدقيق.
- **الموقع العام:** صفحة رئيسية، منتجات وتفاصيل المنتج، من نحن، تواصل معنا، سياسة الخصوصية، الأحكام والشروط، قوائم رأس وفوتر ديناميكية.
- **الإدارة والأمان:** مستخدمون وأدوار وصلاحيات، استعادة كلمة المرور، تحقق ثنائي بالبريد اختياري، شعار وفافيكون وصورة مستخدم.
- **مكتبة الوسائط:** رفع الصور والملفات، تصنيفها والبحث عنها ومعرفة استخدامها ونسخ مسارها وتنظيف الملفات غير المستخدمة بأمان.

### التقنيات

- Laravel 12 / PHP 8.2+
- Filament 5
- Livewire
- MySQL في الإنتاج، مع دعم قواعد البيانات التي يدعمها Laravel
- Vite وTailwind CSS
- تخزين Laravel Public Disk

### متطلبات التشغيل

- PHP 8.2 أو أحدث مع إضافات Laravel المعتادة
- Composer 2
- Node.js وnpm
- MySQL أو MariaDB أو SQLite للتطوير
- خادم ويب Nginx أو Apache

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

روابط التطوير الافتراضية:

- الموقع: `http://127.0.0.1:8000`
- لوحة التحكم: `http://127.0.0.1:8000/admin`

مع Laragon يوضع المشروع في `C:\laragon\www\Landivo` ويمكن استخدام `http://landivo.test:8000`.

### إعداد البيئة

انسخ `.env.example` إلى `.env` واضبط القيم التالية دون رفع الملف إلى GitHub:

```env
APP_NAME=Landivo
APP_ENV=local
APP_URL=http://landivo.test:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=landivo
DB_USERNAME=root
DB_PASSWORD=

FILESYSTEM_DISK=public
```

للتنبيهات واستعادة كلمة المرور اضبط SMTP في `.env`. لا تُسجّل كلمات المرور أو مفاتيح SSH أو بيانات SMTP داخل المستودع.

### مهام ما بعد التثبيت

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

بعد الدخول إلى لوحة التحكم:

1. اضبط هوية الشركة واللغات وSEO من **إعدادات النظام**.
2. أنشئ حالات الطلبات المناسبة لمسار العمل.
3. أضف المنتجات ووسائطها وخياراتها ومتغيراتها ومخزونها.
4. أنشئ صفحة هبوط، واربط المنتج، وابنِ النموذج ورتّب الأقسام.
5. أضف بريد التنبيهات وقوالب واتساب وصفحة ما بعد الطلب.
6. اختبر طلبًا كاملًا قبل نشر الإعلان.

### الاختبارات والجودة

```bash
php artisan test
php artisan route:list
php artisan view:cache
composer audit
```

يجب تشغيل الاختبارات قبل كل رفع. لا تعدّل بيانات الإنتاج أو تنفذ Seeder على الإنتاج إلا بعد مراجعة أثره.

### الاستضافة الحالية

- **مزود الاستضافة:** SiteGround
- **النطاق الإنتاجي:** `https://carakeb.com`
- **خادم الويب:** Nginx عبر SiteGround
- **قاعدة البيانات:** MySQL
- **مجلد التطبيق:** `~/www/carakeb.com/landivo`
- **المجلد العام:** `~/www/carakeb.com/public_html`
- **رابط الوسائط:** `public_html/storage` مرتبط بـ `../landivo/storage/app/public`

يحفظ ملف `.env` ومجلد `storage` على الخادم ولا يُستبدلان أثناء النشر. لا يحتوي هذا المستند على أي بيانات دخول أو أسرار إنتاجية.

### أسلوب النشر الآمن

1. نفّذ الاختبارات محليًا.
2. راجع `git diff` ثم أنشئ Commit واضحًا وارفعه إلى `master`.
3. اسحب التحديث في نسخة النشر على SiteGround.
4. انقل الملفات المتغيرة إلى مجلد التطبيق النشط دون استبدال `.env` أو `storage`.
5. نفّذ `composer install --no-dev --optimize-autoloader` عند تغير الاعتمادات.
6. نفّذ migrations باستخدام `--force` عند وجودها.
7. امسح الكاش ثم أنشئ config وroute وview cache.
8. تحقق من الصفحة العامة ولوحة التحكم والوسائط بعد النشر.

### هيكل مهم

```text
app/Filament/                 لوحة التحكم والموارد والصفحات والتقارير
app/Models/                   نماذج البيانات والعلاقات
app/Http/Controllers/         منطق الموقع العام والطلبات والتقارير
app/Services/                 خدمات الوسائط والنوافذ والتكاملات
resources/views/              واجهات الموقع ولوحة التحكم
resources/css/                التصميم العام وتصميم Filament
database/migrations/          بنية قاعدة البيانات
database/seeders/             بيانات التطوير والتهيئة الاختيارية
routes/                       مسارات الويب والتقارير
storage/app/public/           الصور والملفات المرفوعة
tests/                        اختبارات النظام
```

### النسخ الاحتياطي والأمان

- احتفظ بنسخة دورية من قاعدة البيانات و`storage/app/public` و`.env`.
- لا تحذف حالة طلب أو منتجًا مرتبطًا؛ استخدم الأرشفة.
- لا ترفع المفاتيح الخاصة أو كلمات المرور أو ملفات `.env`.
- راجع صلاحيات المستخدمين وسجل التدقيق دوريًا.
- استخدم HTTPS وتحقق البريد الثنائي للحسابات الإدارية الحساسة.

### خريطة لوحة التحكم

| الوحدة | الغرض التشغيلي |
|---|---|
| لوحة التحكم | مؤشرات الطلبات والإيراد والتحويل، أحدث الطلبات، الصفحات الأعلى أداءً وإجراءات سريعة |
| العملاء والطلبات المحتملة | ملف موحّد للعميل وسجل طلباته وبيانات التواصل |
| معرض صفحات الهبوط | عرض الصفحات كبطاقات مع الزيارات والطلبات والتحويل والإيراد وروابط الإدارة والمعاينة |
| صفحات الهبوط | بناء المحتوى والنموذج والأقسام والتصميم والتتبع وصفحة ما بعد الطلب |
| حالات الطلبات | تصميم دورة العمل وتحديد حالة التسليم وحماية الحالات المرتبطة |
| الطلبات | المتابعة، التذكير، النشاط، المرفقات، الفاتورة، واتساب، المصدر وUTM |
| المنتجات | الأسعار والمخزون والوسائط والمتغيرات والخيارات والمحتوى متعدد اللغات |
| تقييمات الزوار | إدارة التقييمات واعتمادها وإرسال روابط التقييم الموقعة |
| مكتبة الوسائط | إدارة التخزين، معرفة الاستخدام ونسخ الرابط العام بأمان |
| بحث العملاء | بحث فوري بجزء من الاسم أو الهاتف أو البريد مع عرض الطلبات والبيانات |
| التقارير | تحليلات الزوار، حالات الطلبات، التصدير، التقييمات وسجل التدقيق |
| إعدادات النظام | الهوية، اللغات، بيانات الشركة، البريد، SEO، القوائم والأمان |

### رحلة البيانات في النظام

1. يصل الزائر إلى صفحة عامة أو صفحة هبوط، ويُسجّل النظام الزيارة ومصدر الحملة ومعلمات UTM.
2. تُعرض اللغة والمحتوى والوسائط والأقسام والنموذج حسب إعدادات الصفحة.
3. عند الإرسال، يتحقق Laravel من الحقول والشروط ثم ينشئ أو يحدّث العميل والطلب وعناصره.
4. يحتفظ الطلب بنسخة من بيانات النموذج، المصدر، الحملة، الجهاز وIP لتبقى القراءة التاريخية دقيقة.
5. يسجل النظام النشاط والتعديلات والتذكيرات ويربطها بالمستخدم الذي نفذ الإجراء.
6. عند انتقال الطلب إلى حالة تسليم مهيأة، تُزامن كميات المنتج أو المتغير بطريقة آمنة.
7. تُجمّع الزيارات والطلبات والإيرادات في لوحات التحليل والتقارير دون خلط بيانات الحسابات.

### حدود الحسابات والصلاحيات

- كل سجل تشغيلي مرتبط بـ `account_id`، وتُقيّد استعلامات لوحة التحكم بالحساب الحالي.
- الأدوار تحدد الوصول إلى المنتجات والطلبات والعملاء والتقارير والإعدادات.
- لا يكفي إخفاء عنصر من القائمة؛ يجب أن تبقى الصلاحية مطبقة على المورد أو الصفحة والخادم.
- روابط التقييم الخاصة بالطلبات موقعة ومؤقتة، ولا تعرض بيانات العميل الحساسة.
- يستخدم سجل التدقيق لمراجعة الإنشاء والتعديل والحذف مع حجب القيم الحساسة.

### أهم جداول قاعدة البيانات

```text
accounts              الحسابات وإعداداتها
users                 مستخدمو لوحة التحكم
products              المنتج والسعر والمخزون والحالة
product_translations  الاسم والوصف والتفاصيل لكل لغة
product_variants      المتغيرات والأسعار والكميات
product_media         صور ووسائط المنتج حسب اللغة
landing_pages         هوية صفحة الهبوط وحالتها وإعداداتها
landing_page_*        الترجمات والأقسام والحقول ومحتوى الصفحة
customers             ملف العميل وبيانات التواصل
orders                الطلب والمصدر والحملة والحالة والقيمة
order_items           المنتجات والمتغيرات والكميات داخل الطلب
order_activities      سجل النشاط والمتابعة
order_attachments     الصور والملفات المرتبطة بالطلب
order_statuses        دورة العمل القابلة للتخصيص
visitor_events        الزيارات والأحداث التحليلية
reviews               التقييمات ومصدرها وحالة اعتمادها
media_assets          فهرس مكتبة الوسائط واستخدام الملفات
audit_logs            سجل التدقيق الإداري
```

### إعداد الإنتاج على SiteGround

الإعداد الحالي يستخدم فصلًا واضحًا بين كود التطبيق والجذر العام:

```text
Domain:          https://carakeb.com
Admin:           https://carakeb.com/admin
Application:     ~/www/carakeb.com/landivo
Document root:   ~/www/carakeb.com/public_html
Upload staging:  ~/www/carakeb.com/upload
Public storage:  public_html/storage -> ../landivo/storage/app/public
PHP:             8.2 أو أحدث
Database:        MySQL / MariaDB
Web server:      Nginx managed by SiteGround
TLS:             SiteGround SSL مع تحويل HTTPS
```

يجب أن يشير `public_html/index.php` إلى ملفات التطبيق الفعلية:

```php
require __DIR__.'/../landivo/vendor/autoload.php';
$app = require_once __DIR__.'/../landivo/bootstrap/app.php';
```

نموذج متغيرات الإنتاج، مع وضع القيم السرية في `.env` على الخادم فقط:

```env
APP_NAME=Landivo
APP_ENV=production
APP_DEBUG=false
APP_URL=https://carakeb.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=CHANGE_ME
DB_USERNAME=CHANGE_ME
DB_PASSWORD=CHANGE_ME

FILESYSTEM_DISK=public
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax

MAIL_MAILER=smtp
MAIL_HOST=CHANGE_ME
MAIL_PORT=465
MAIL_ENCRYPTION=ssl
MAIL_USERNAME=CHANGE_ME
MAIL_PASSWORD=CHANGE_ME
MAIL_FROM_ADDRESS=CHANGE_ME
```

لا تُكتب القيم الحقيقية أو بيانات SSH داخل README أو Git. تُدار من SiteGround Site Tools وملف `.env` المحمي.

### إجراء نشر Production موصى به

```bash
# داخل مجلد التطبيق بعد وصول الملفات المختبرة
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# عند تغيّر CSS أو JavaScript، يُبنى محليًا ثم تُنشر public/build
npm ci
npm run build
```

قائمة التحقق بعد النشر:

- الصفحة العامة وملفات CSS وJavaScript ترجع `200`.
- `/admin` يحول إلى تسجيل الدخول عند عدم وجود جلسة، ويفتح للمستخدم المصرح.
- الصور الجديدة والقديمة تعمل عبر `/storage`.
- إنشاء طلب تجريبي يحفظ العميل والعرض والمصدر وUTM.
- البريد واستعادة كلمة المرور وروابط واتساب تعمل بالإعدادات الإنتاجية.
- لا يوجد `APP_DEBUG=true` ولا أسرار في Git أو السجلات العامة.

### النسخ الاحتياطي والاستعادة

- نسخة يومية من MySQL و`storage/app/public` ونسخة مشفرة من `.env`.
- قبل migration كبير، تؤخذ نسخة يدوية مع اختبار إمكانية استعادتها.
- الاستعادة تبدأ بقاعدة البيانات ثم `storage` ثم نفس نسخة الكود و`APP_KEY` الأصلي.
- العودة عن إصدار تتم بنشر Commit سابق؛ لا تستخدم `migrate:rollback` في الإنتاج دون مراجعة أثر البيانات.

### المراقبة والصيانة

- راقب سجل Laravel في `storage/logs/laravel.log` ومساحة التخزين وحجم قاعدة البيانات.
- شغّل Laravel Scheduler كل دقيقة من Cron عند تفعيل مهام مجدولة: `php artisan schedule:run`.
- نظّف الملفات غير المستخدمة من واجهة الوسائط بعد التأكد من تقرير الاستخدام، وليس بحذف مباشر من الخادم.
- راجع التذكيرات المستحقة والطلبات الجديدة وسجل التدقيق وصلاحيات المستخدمين دوريًا.
- بعد تحديث PHP أو Composer، شغّل الاختبارات وتحقق من توافق Filament والحزم قبل الإنتاج.

---

## English

### Overview

**Landivo** is a bilingual commerce and marketing platform for creating landing pages, capturing leads and orders, managing products and inventory, communicating with customers, and analysing campaign performance from a single responsive Filament admin panel.

### Core modules

- **Landing pages:** Arabic and English content, custom slugs, publishing workflow, live preview, responsive layouts, and sortable sections.
- **Dynamic Form Builder:** text, email, phone, number, textarea, select, radio, checkbox, date, file, image, quantity, address, city, country, and product-variant fields.
- **Conditional fields:** translated labels and placeholders, validation rules, defaults, options, and conditional visibility.
- **Products:** multilingual names, descriptions and details, pricing, discounts, stock, options, variants, and multiple media files.
- **Product showcases:** single or multiple products displayed as grids or sliders with configurable styling and position.
- **Orders and CRM:** customer details, selected offer, source and UTM tracking, IP and device information, notes, attachments, activities, reminders, invoices, and WhatsApp actions.
- **Order statuses:** sortable, colour-coded and archivable workflow states with safe relationship protection.
- **Inventory:** product and variant quantities with status-driven stock deduction.
- **Reviews:** signed customer review links, required rating, optional comment, moderation, reporting, and public display.
- **Marketing popups:** multiple templates, desktop/mobile images, page and audience targeting, schedules, frequency, priority, colours, and behaviour settings.
- **Marketing components:** Head and Body code, reusable HTML blocks, ticker bars, purchase notifications, scarcity and live-viewer counters.
- **SEO:** titles, descriptions, keywords, canonical URLs, Open Graph, sitemap, Search Console, Analytics, and custom verification code.
- **Reports:** visitor analytics, order-status performance, CSV order export, review reporting, and audit logs.
- **Public website:** homepage, product catalogue and details, About, Contact, Privacy Policy, Terms, and dynamic header/footer menus.
- **Administration and security:** users, roles, permissions, password reset, optional email MFA, company branding, favicon, and profile avatars.
- **Media library:** upload, classify, search, track usage, copy public paths, and safely identify unused files.

### Stack and requirements

- PHP 8.2+
- Laravel 12
- Filament 5 and Livewire
- Composer 2
- Node.js, npm, Vite, and Tailwind CSS
- MySQL/MariaDB for production; SQLite is suitable for local development
- Nginx or Apache

### Local setup

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

Default URLs:

- Public site: `http://127.0.0.1:8000`
- Admin panel: `http://127.0.0.1:8000/admin`

### Production hosting

The current production installation is hosted on **SiteGround** at `https://carakeb.com`, using Nginx and MySQL.

```text
Application: ~/www/carakeb.com/landivo
Web root:    ~/www/carakeb.com/public_html
Media link:  public_html/storage -> ../landivo/storage/app/public
```

Production credentials, SSH keys, SMTP passwords, and the `.env` file must never be committed. The active `.env` and `storage` directory must be preserved during deployment.

### Safe deployment checklist

1. Run the test suite locally.
2. Review and commit only intended changes.
3. Push the tested commit to `master`.
4. Pull it into the SiteGround deployment copy.
5. Update the active application while preserving `.env` and `storage`.
6. Run Composer only when dependencies changed.
7. Run migrations with `--force` when required.
8. Rebuild Laravel caches.
9. Verify the public site, admin panel, uploads, and critical order flow.

### Maintenance commands

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan test
composer audit
```

### Security and backups

- Back up the database, `.env`, and `storage/app/public` regularly.
- Archive linked records instead of deleting them.
- Keep secrets out of Git and documentation.
- Review permissions and the audit log regularly.
- Enforce HTTPS and enable email MFA for privileged users.

### Administration map

| Area | Operational purpose |
|---|---|
| Dashboard | Orders, revenue, conversion, recent activity, top pages, and quick actions |
| Landing Page Gallery | Visual cards with page status, visits, orders, conversion, revenue, edit and preview links |
| Landing Pages | Content, form, sections, styling, tracking, and post-submission experience |
| Products | Multilingual catalogue, pricing, inventory, media, options, and variants |
| Orders | CRM workflow, reminders, activity, files, invoices, WhatsApp, attribution, and UTM |
| Customer Search | Instant partial search by name, phone, or email with full customer and order context |
| Reports | Visitor analytics, status performance, order export, reviews, and audit history |
| System Settings | Branding, company details, locales, mail, SEO, menus, and security |

### Request and data flow

1. A visit records the landing page, campaign source, UTM values, session, and device context.
2. The page resolves its locale, product, translated assets, ordered sections, and dynamic form.
3. A valid submission creates or updates the customer, then creates the order and line items.
4. The order preserves submitted form data and attribution for reliable historical reporting.
5. Staff activity, follow-ups, files, and status transitions are recorded against the order.
6. Delivery statuses can trigger safe product or variant inventory deduction.
7. Account-scoped analytics aggregate visits, conversions, orders, and revenue.

### Account isolation and authorization

- Operational records are owned by `account_id`; admin queries are scoped to the signed-in account.
- Roles and permissions control dashboard and resource access.
- Server-side authorization remains required even when a navigation item is hidden.
- Order review URLs are temporary and signed.
- Audit logging records administrative changes while masking sensitive values.

### Current SiteGround topology

```text
Domain:          https://carakeb.com
Admin:           https://carakeb.com/admin
Application:     ~/www/carakeb.com/landivo
Document root:   ~/www/carakeb.com/public_html
Upload staging:  ~/www/carakeb.com/upload
Public storage:  public_html/storage -> ../landivo/storage/app/public
Runtime:         PHP 8.2+, MySQL/MariaDB, managed Nginx and HTTPS
```

The public `index.php` must load `../landivo/vendor/autoload.php` and `../landivo/bootstrap/app.php`. Production secrets belong only in the server-side `.env`; never commit database credentials, mail passwords, private keys, or SiteGround credentials.

### Production deployment runbook

```bash
composer install --no-dev --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

When front-end assets change, run `npm ci && npm run build` in a trusted build environment and deploy the resulting `public/build` directory. Preserve the production `.env`, `storage`, and original `APP_KEY`.

Post-deployment verification must cover the public site, admin authentication, compiled assets, the storage symlink, a test order with UTM attribution, outbound mail, password reset, and critical WhatsApp links.

### Recovery and operations

- Back up MySQL, `storage/app/public`, and an encrypted copy of `.env` every day.
- Take an additional backup before high-risk migrations and verify restore procedures.
- Restore the database, storage, the matching code revision, and the original `APP_KEY` together.
- Roll back by deploying a known-good commit; do not run destructive database rollback commands without a data-impact review.
- Monitor Laravel logs, disk usage, database growth, due follow-ups, new orders, audit events, and privileged roles.
- Configure the scheduler through SiteGround Cron when scheduled jobs are enabled: `php artisan schedule:run` every minute.

## Licence

Landivo is maintained as a private commercial project. Define final commercial licensing terms before public distribution.
