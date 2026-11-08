<details @if ($errors->userDeletion->any()) open @endif>
    <summary>Xóa tài khoản</summary>
    <p class="account-muted mt-3">Thao tác này không thể hoàn tác. Tài khoản còn sở hữu bộ dữ liệu hoặc dữ liệu liên quan cần được xử lý dữ liệu trước khi xóa.</p>
    <form method="POST" action="{{ route('profile.destroy') }}" class="account-form" onsubmit="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản này?')">
        @csrf
        @method('DELETE')
        <div>
            <label for="account-delete-password">Nhập mật khẩu để xác nhận</label>
            <input id="account-delete-password" name="password" type="password" autocomplete="current-password" required aria-invalid="{{ $errors->userDeletion->has('password') ? 'true' : 'false' }}" aria-describedby="account-delete-error">
            <p id="account-delete-error" class="account-error" role="alert">{{ $errors->userDeletion->first('password') }}</p>
        </div>
        <div><button type="submit" class="btn btn-outline-danger">Xóa tài khoản vĩnh viễn</button></div>
    </form>
</details>
