<x-web-layout>
    <x-slot name="title">Tài khoản</x-slot>
    <style>
        .account-page{max-width:1100px;margin:auto;color:#172b4d}.account-header{display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:24px}.account-page h1{font-size:28px;font-weight:700;margin-bottom:8px}.account-muted{color:#64748b;font-size:14px;line-height:1.7}.account-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:24px;align-items:start}.account-panel{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:24px;min-width:0}.account-panel h2{font-size:18px;font-weight:700;margin-bottom:8px}.account-form{display:grid;gap:18px;margin-top:24px}.account-form label{display:block;font-size:14px;font-weight:600;margin-bottom:8px}.account-form input{width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;color:#172b4d}.account-form input:focus{outline:2px solid #2563eb;outline-offset:2px}.account-form input[aria-invalid=true]{border-color:#b91c1c}.account-error{color:#b91c1c;font-size:13px;margin:6px 0 0}.account-success{color:#166534;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:12px 16px;margin-bottom:20px}.account-summary{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px}.account-chip{border:1px solid #e2e8f0;background:#f8fafc;border-radius:8px;padding:8px 12px;font-size:13px;overflow-wrap:anywhere}.account-danger{margin-top:24px;border-color:#fecaca}.account-danger summary{cursor:pointer;font-weight:600;color:#b91c1c}@media(max-width:900px){.account-grid{grid-template-columns:1fr}}@media(max-width:700px){.web-layout{grid-template-columns:1fr}.web-sidebar{position:static;height:auto;max-height:180px}.web-main{padding:16px}.account-panel{padding:18px}.account-header{align-items:flex-start;flex-direction:column}}
    </style>
    <div class="account-page">
        <header class="account-header">
            <div><h1>Tài khoản</h1><p class="account-muted mb-0">Quản lý thông tin cửa hàng và bảo mật tài khoản của bạn.</p></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger"><i class="fa-solid fa-arrow-right-from-bracket me-2" aria-hidden="true"></i>Đăng xuất</button>
            </form>
        </header>
        <div class="account-summary">
            <span class="account-chip">Cửa hàng: <strong>{{ $user->shop_name }}</strong></span>
            <span class="account-chip">Tham gia: {{ $user->created_at?->format('d/m/Y') ?? '—' }}</span>
        </div>
        @if (session('status') === 'profile-updated')
            <p class="account-success" role="status">Đã cập nhật thông tin tài khoản.</p>
        @elseif (session('status') === 'password-updated')
            <p class="account-success" role="status">Đã đổi mật khẩu thành công. Hãy sử dụng mật khẩu mới cho lần đăng nhập tiếp theo.</p>
        @endif
        <div class="account-grid">
            <section class="account-panel">@include('profile.partials.update-profile-information-form')</section>
            <section class="account-panel">@include('profile.partials.update-password-form')</section>
        </div>
        <section class="account-panel account-danger">@include('profile.partials.delete-user-form')</section>
    </div>
</x-web-layout>
