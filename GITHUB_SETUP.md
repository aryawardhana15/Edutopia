# Setup GitHub Repository

## Langkah-langkah Push ke GitHub

### 1. Buat Repository di GitHub
1. Buka https://github.com
2. Klik "New repository"
3. Nama repository: `RSI-E-Learning-Platform` (atau nama lain sesuai keinginan)
4. Pilih Public atau Private
5. **JANGAN** centang "Initialize with README" (karena kita sudah punya files)
6. Klik "Create repository"

### 2. Push ke GitHub

Setelah repository dibuat, jalankan perintah berikut:

```bash
# Tambahkan remote repository (ganti YOUR_USERNAME dan REPO_NAME)
git remote add origin https://github.com/YOUR_USERNAME/REPO_NAME.git

# Atau jika menggunakan SSH:
# git remote add origin git@github.com:YOUR_USERNAME/REPO_NAME.git

# Push ke GitHub
git branch -M main
git push -u origin main
```

### 3. Jika sudah ada remote, update:

```bash
# Cek remote yang ada
git remote -v

# Jika perlu update remote URL
git remote set-url origin https://github.com/YOUR_USERNAME/REPO_NAME.git

# Push
git push -u origin main
```

## Struktur Repository

```
RSI/
├── backend/          # Laravel 12 API
├── frontend/         # React App
├── README.md
├── API_DOCUMENTATION.md
├── COMPLIANCE_REPORT.md
└── ...
```

## Catatan

- Pastikan `.env` file **TIDAK** di-commit (sudah ada di .gitignore)
- Pastikan `node_modules` dan `vendor` **TIDAK** di-commit
- File sensitif seperti API keys jangan di-commit

