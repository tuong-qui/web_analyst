<x-guest-layout>
    <form method="POST"
          action="{{ route('register') }}"
          class="flex flex-col gap-5">
        @csrf

        <div class="flex flex-col gap-2">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 text-center">
                Tạo tài khoản mới
            </h2>

            <p class="text-sm leading-6 text-slate-500 text-center">
                Nhập thông tin để bắt đầu quản lý cửa hàng của bạn.
            </p>
        </div>

        <!-- Họ và tên -->
        <div class="flex flex-col" style="gap: 6px;">
            <label for="name"
                   class="block text-sm font-medium text-slate-700"
                   style="margin-bottom: 0;">
                Họ và tên
            </label>

            <x-text-input
                id="name"
                class="input"
                type="text"
                name="name"
                :value="old('name')"
                required
                autofocus
                autocomplete="name"
                placeholder="Nhập họ và tên"
            />

            <x-input-error :messages="$errors->get('name')" />
        </div>

        <!-- Tên cửa hàng -->
        <div class="flex flex-col" style="gap: 6px;">
            <label for="shop_name"
                   class="block text-sm font-medium text-slate-700"
                   style="margin-bottom: 0;">
                Tên cửa hàng
            </label>

            <x-text-input
                id="shop_name"
                class="input"
                type="text"
                name="shop_name"
                :value="old('shop_name')"
                required
                maxlength="150"
                placeholder="Nhập tên cửa hàng"
            />

            <x-input-error :messages="$errors->get('shop_name')" />
        </div>

        <!-- Email -->
        <div class="flex flex-col" style="gap: 6px;">
            <label for="email"
                   class="block text-sm font-medium text-slate-700"
                   style="margin-bottom: 0;">
                Email
            </label>

            <x-text-input
                id="email"
                class="input"
                type="email"
                name="email"
                :value="old('email')"
                required
                autocomplete="username"
                placeholder="you@example.com"
            />

            <x-input-error :messages="$errors->get('email')" />
        </div>

        <!-- Mật khẩu -->
        <div class="flex flex-col" style="gap: 6px;">
            <label for="password"
                   class="block text-sm font-medium text-slate-700"
                   style="margin-bottom: 0;">
                Mật khẩu
            </label>

            <x-text-input
                id="password"
                class="input"
                type="password"
                name="password"
                required
                autocomplete="new-password"
                placeholder="Nhập mật khẩu"
            />

            <x-input-error :messages="$errors->get('password')" />
        </div>

        <!-- Xác nhận mật khẩu -->
        <div class="flex flex-col" style="gap: 6px;">
            <label for="password_confirmation"
                   class="block text-sm font-medium text-slate-700"
                   style="margin-bottom: 0;">
                Xác nhận mật khẩu
            </label>

            <x-text-input
                id="password_confirmation"
                class="input"
                type="password"
                name="password_confirmation"
                required
                autocomplete="new-password"
                placeholder="Nhập lại mật khẩu"
            />

            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <div class="flex flex-col-reverse items-stretch gap-4 pt-2 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('login') }}"
               class="text-center text-sm font-medium text-slate-500 hover:text-blue-600 sm:text-left">
                Đã có tài khoản? Đăng nhập
            </a>

            <x-primary-button class="justify-center px-6 py-3">
                Đăng ký
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>