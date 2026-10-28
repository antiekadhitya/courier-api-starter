# Courier API (Laravel)

REST API untuk master data kurir. Tidak ada frontend/UI.

## Persyaratan
- PHP sesuai versi Laravel yang dipakai
- Composer
- Database MySQL atau SQLite
- Laravel project baru

## Instalasi
```bash
composer create-project laravel/laravel courier-api
cd courier-api
php artisan install:api
```

Salin file pada folder `app/`, `database/`, `routes/`, dan `tests/` dari paket ini ke lokasi yang sama pada project Laravel. Jika nama file migration berbenturan, pertahankan timestamp baru dari migration yang disalin.

Atur `.env` untuk database, lalu jalankan:
```bash
php artisan migrate
php artisan test
php artisan serve
```

## Endpoint
| Method | Endpoint | Fungsi |
|---|---|---|
| GET | `/api/couriers` | List, pagination, search, filter, sorting |
| POST | `/api/couriers` | Membuat kurir |
| GET | `/api/couriers/{courier}` | Detail kurir |
| PUT/PATCH | `/api/couriers/{courier}` | Memperbarui kurir |
| DELETE | `/api/couriers/{courier}` | Menghapus kurir |

### Query parameters
- `search=budi agung`: semua kata harus cocok dengan nama, tidak harus berurutan/bersebelahan.
- `level=2,3`: filter level 2 atau 3.
- `sort=registered_at`: urut tanggal pendaftaran terbaru dulu. Default `sort=name` (A–Z).
- `per_page=10`: jumlah hasil per halaman, dibatasi 1–100.
- `page=2`: halaman pagination.

Contoh:
`GET /api/couriers?search=budi%20agung&level=2,3&sort=registered_at&per_page=10`

## Contoh request POST
```json
{
  "name": "Budi Agung",
  "phone": "081234567890",
  "email": "budi@example.com",
  "level": 3,
  "is_active": true
}
```

## Contoh request PATCH
```json
{
  "level": 4
}
```

## Catatan
- Email bersifat opsional tetapi harus unik jika diisi.
- `level` wajib untuk create dan harus bernilai 1–5.
- Pada update, field yang tidak dikirim tidak diubah.
- Gunakan database testing terpisah; jangan jalankan feature test pada database produksi.
