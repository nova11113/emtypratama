<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN | SISTEM GARMENT EMTY</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { border-radius: 20px; border: none; width: 100%; max-width: 400px; }
        .login-header { background: #2c3e50; color: white; border-radius: 20px 20px 0 0; padding: 30px; text-align: center; }
        .form-control, .form-select { border-radius: 10px; padding: 12px; }
        .btn-login { background: #2c3e50; color: white; border-radius: 10px; padding: 12px; font-weight: bold; width: 100%; transition: 0.3s; }
        .btn-login:hover { background: #1a252f; color: white; }
    </style>
</head>
<body>

<div class="container p-3">
    <div class="card login-card shadow-lg mx-auto">
        <div class="login-header">
            <h2 class="fw-bold mb-0">EMTY</h2>
            <small>Sistem Monitoring Produksi</small>
        </div>
        <div class="card-body p-4">
            @if(session('error'))
                <div class="alert alert-danger small">{{ session('error') }}</div>
            @endif

            <form action="/login" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Username / Email</label>
                    <input type="text" name="email" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Masuk sebagai Divisi</label>
                    <select name="role" class="form-select bg-light fw-bold" required>
                        <option value="cutting">✂️ Divisi Cutting (Potong)</option>
                        <option value="sewing">🪡 Divisi Sewing (Jahit)</option>
                        <option value="finishing">✨ Divisi Finishing</option>
                        <option value="qc">🔍 Divisi QC (Quality Control)</option>
                        <option value="admin">👨‍💻 Admin Utama</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-login shadow-sm">MASUK KE SISTEM</button>
            </form>
        </div>
        <div class="card-footer bg-white border-0 text-center pb-4">
            <small class="text-muted">© 2026 Garment Emty Production</small>
        </div>
    </div>
</div>

</body>
</html>