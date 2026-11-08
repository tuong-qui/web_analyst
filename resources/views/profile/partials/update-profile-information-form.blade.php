<h2>Thông tin tài khoản</h2>
<p class="account-muted">Cập nhật họ tên, tên cửa hàng và địa chỉ email đăng nhập.</p>
<form method="POST" action="{{ route('profile.update') }}" class="account-form">
    @csrf
    @method('PATCH')
    <div>
        <label for="account-owner">Họ và tên</label>
        <input id="account-owner" name="owner_name" type="text" value="{{ old('owner_name', $user->owner_name) }}" maxlength="100" autocomplete="name" required aria-invalid="{{ $errors->has('owner_name') ? 'true' : 'false' }}" aria-describedby="account-owner-error">
        <p id="account-owner-error" class="account-error">{{ $errors->first('owner_name') }}</p>
    </div>
    <div>
        <label for="account-shop">Tên cửa hàng</label>
        <input id="account-shop" name="shop_name" type="text" value="{{ old('shop_name', $user->shop_name) }}" maxlength="150" autocomplete="organization" required aria-invalid="{{ $errors->has('shop_name') ? 'true' : 'false' }}" aria-describedby="account-shop-error">
        <p id="account-shop-error" class="account-error">{{ $errors->first('shop_name') }}</p>
    </div>
    <div>
        <label for="account-email">Email đăng nhập</label>
        <input id="account-email" name="email" type="email" value="{{ old('email', $user->email) }}" maxlength="120" autocomplete="email" required aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" aria-describedby="account-email-error">
        <p id="account-email-error" class="account-error">{{ $errors->first('email') }}</p>
    </div>
    <div><button type="submit" class="btn btn-primary">Lưu thay đổi</button></div>
</form>
