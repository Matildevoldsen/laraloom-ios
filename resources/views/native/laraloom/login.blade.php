<scroll-view class="w-full h-full bg-theme-background safe-area">
    <column class="w-full px-5 py-8 gap-7">
        <column class="items-center gap-3">
            <column class="w-[76] h-[76] rounded-3xl bg-[#F43F8C] items-center justify-center glass:prominent">
                <text class="text-[34] font-bold text-white">L</text>
            </column>
            <text class="text-[26] font-bold text-theme-on-surface">Welcome back</text>
            <text class="text-[13] text-theme-on-surface-variant text-center">Join the conversation without handing your password to the app. Laraloom exchanges it once for a scoped mobile token.</text>
        </column>

        <column class="w-full gap-5 p-5 rounded-3xl bg-theme-surface border border-theme-outline">
            <column class="gap-2">
                <text class="text-[12] font-semibold text-theme-on-surface">Email</text>
                <outlined-text-input value="{{ $email }}" placeholder="you@example.com" @change="updateEmail" :variant="0" />
            </column>
            <column class="gap-2">
                <text class="text-[12] font-semibold text-theme-on-surface">Password</text>
                <outlined-text-input value="{{ $password }}" placeholder="Your password" :secure="true" @change="updatePassword" :variant="0" />
            </column>

            @if ($error !== '')
                <text class="text-[12] text-[#FDA4AF]">{{ $error }}</text>
            @endif

            <button class="glass:prominent:interactive" variant="accent" size="lg" :loading="$isSubmitting" :disabled="$isSubmitting" @press="submit">Sign in</button>
            <button class="glass:clear:interactive" size="md" @press="createAccount">Create an account</button>
        </column>

        <row class="items-center justify-center gap-2">
            <icon name="key.fill" :size="14" color="#86EFAC" />
            <text class="text-[11] text-theme-on-surface-variant">Protected by secure device storage · This device only</text>
        </row>
    </column>
</scroll-view>
