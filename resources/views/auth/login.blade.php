<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        @if (session('login_attempts', 0) >= 2)
            <div class="mt-4">
                <x-input-label for="captcha" :value="__('Mã xác nhận (Captcha)')" />
                
                <div class="flex items-center mt-1 gap-2">
                    <img src="{{ url('/captcha-image') }}" id="captcha-img" class="rounded border" alt="Captcha">
                    <button type="button" 
                            onclick="document.getElementById('captcha-img').src = '{{ url('/captcha-image') }}?' + Math.random()" 
                            class="text-sm text-gray-500 hover:underline">
                        {{ __('Đổi mã khác') }}
                    </button>
                </div>

                <x-text-input id="captcha" class="block mt-2 w-full" type="text" name="captcha" required placeholder="Nhập chữ hoa chữ thường chính xác" />
                <x-input-error :messages="$errors->get('captcha')" class="mt-2" />
            </div>
        @endif

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 shadow-sm" name="remember" style="accent-color: #990000;">
                <span class="ms-2 text-sm text-gray-600">{{ __('Ghi nhớ tôi') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm rounded-md focus:outline-none me-4" href="{{ route('password.request') }}" style="color: #990000;">
                    {{ __('Quên mật khẩu?') }}
                </a>
            @endif

            <button type="submit" class="px-4 py-2 rounded-lg text-white font-semibold transition" style="background-color: #990000;">
                {{ __('Đăng nhập') }}
            </button>
        </div>
    </form>
</x-guest-layout>
