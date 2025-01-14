# پروژه یونی‌لینک (Unilink)

سیستم نوبت‌دهی برای دانشگاه با استفاده از Laravel، Vue.js 3 و Inertia.js.

## ویژگی‌های پروژه

- مدیریت کاربران با احراز هویت.
- امکان رزرو نوبت برای دانشجویان و اساتید.
- استفاده از رابط کاربری مدرن و واکنش‌گرا.
- سیستم اعلان برای یادآوری جلسات. (در حال توسعه)

## پیش‌نیازها

- **PHP** نسخه ۸.۰ یا بالاتر
- **Composer** (برای مدیریت وابستگی‌های PHP)
- **Node.js** نسخه ۱۴ یا بالاتر
- **npm** (برای مدیریت وابستگی‌های JavaScript)
- **MySQL** یا هر پایگاه داده سازگار با Laravel

## مراحل نصب

۱. کلون کردن مخزن:

```bash
git clone https://github.com/mjnekunam/unilink.git
cd unilink
```

۲. نصب وابستگی‌های PHP با استفاده از Composer:

```bash
composer install
```

۳. نصب وابستگی‌های JavaScript با استفاده از npm:

```bash
npm install
```

۴. تنظیم فایل محیطی:

```bash
cp .env.example .env
```

۵. تولید کلید برنامه:

```bash
php artisan key:generate
```

۶. تنظیم اطلاعات پایگاه داده در فایل `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=نام_پایگاه_داده
DB_USERNAME=نام_کاربری
DB_PASSWORD=رمز_عبور
```

۷. اجرای مهاجرت‌ها و داده‌های اولیه:

```bash
php artisan migrate --seed
```

۸. کامپایل منابع Frontend:
برای توسعه:

```bash
npm run dev
```

برای محیط تولید:

```bash
npm run build
```

۹. اجرای سرور محلی:

```bash
php artisan serve
```

اکنون می‌توانید به آدرس `http://localhost:8000` مراجعه کنید.

## مشارکت

پیشنهادات و بهبودهای شما برای این پروژه پذیرفته می‌شود. لطفاً قبل از ارسال Pull Request، یک Issue باز کنید.

## مجوز

این پروژه تحت مجوز [MIT](LICENSE) منتشر شده است.
