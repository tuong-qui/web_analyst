<h2>Đổi mật khẩu</h2>
<p class="account-muted">Sử dụng mật khẩu có ít nhất 8 ký tự và giữ bí mật thông tin đăng nhập.</p>
<form method="POST" action="{{ route('password.update') }}" class="account-form">
    @csrf
    @method('PUT')
    <div>
        <label for="account-current-password">Mật khẩu hiện tại</label>
        <input id="account-current-password" name="current_password" type="password" autocomplete="current-password" required aria-invalid="{{ $errors->updatePassword->has('current_password') ? 'true' : 'false' }}" aria-describedby="account-current-password-error">
        <p id="account-current-password-error" class="account-error">{{ $errors->updatePassword->first('current_password') }}</p>
    </div>
    <div>
        <label for="account-new-password">Mật khẩu mới</label>
        <input id="account-new-password" name="password" type="password" minlength="8" autocomplete="new-password" required aria-invalid="{{ $errors->updatePassword->has('password') ? 'true' : 'false' }}" aria-describedby="account-new-password-error">
        <p id="account-new-password-error" class="account-error">{{ $errors->updatePassword->first('password') }}</p>
    </div>
    <div>
        <label for="account-confirm-password">Xác nhận mật khẩu mới</label>
        <input id="account-confirm-password" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required>
    </div>
    <div><button type="submit" class="btn btn-primary">Đổi mật khẩu</button></div>
</form>
