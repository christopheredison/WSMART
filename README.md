Langkah Instalasi : 
1. Lakukan Composer Install
2. Setup Database (MariaDB atau MySQL)
3. Buat file .env dan copy isi dari .env.example
4. Jalankan perintah di bawah ini :
php artisan key:generate
php artisan migrate:fresh
php artisan db:seed --class=DatabaseSeeder
php artisan storage:link
php artisan serve

Catatan Tambahan : 
- untuk login admin bisa menggunakan username admin@gmail.com dan password 12345678
- data auth bisa dicek di file RolePermissionSeeder

