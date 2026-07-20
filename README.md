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

## Licence

Landivo is maintained as a private commercial project. Define final commercial licensing terms before public distribution.
