<scroll-view class="w-full h-full bg-theme-background safe-area">
    <column class="w-full px-5 py-7 gap-6">
        <column class="items-center gap-3">
            <column class="w-[72] h-[72] rounded-3xl bg-[#F43F8C] items-center justify-center glass:prominent">
                <icon name="person.badge.plus" :size="31" color="#FFFFFF" />
            </column>
            <text class="text-[26] font-bold text-theme-on-surface">Join the Laravel signal</text>
            <text class="text-[13] text-theme-on-surface-variant text-center">Follow builders, save useful finds and share what you ship.</text>
        </column>

        <column class="w-full gap-4 p-5 rounded-3xl bg-theme-surface border border-theme-outline">
            <column class="gap-2">
                <text class="text-[12] font-semibold text-theme-on-surface">Name</text>
                <outlined-text-input native:model.debounce.400ms="name" placeholder="Your name" :variant="0" />
            </column>
            <column class="gap-2">
                <text class="text-[12] font-semibold text-theme-on-surface">Username</text>
                <outlined-text-input native:model.debounce.400ms="username" placeholder="laravelbuilder" :variant="0" />
            </column>
            <column class="gap-2">
                <text class="text-[12] font-semibold text-theme-on-surface">Email</text>
                <outlined-text-input native:model.debounce.400ms="email" placeholder="you@example.com" keyboard="email" :variant="0" />
            </column>
            <column class="gap-2">
                <text class="text-[12] font-semibold text-theme-on-surface">Password</text>
                <outlined-text-input native:model.debounce.400ms="password" placeholder="Create a password" :secure="true" :variant="0" />
            </column>
            <column class="gap-2">
                <text class="text-[12] font-semibold text-theme-on-surface">Confirm password</text>
                <outlined-text-input native:model.debounce.400ms="passwordConfirmation" placeholder="Repeat your password" :secure="true" @submit="submit" :variant="0" />
            </column>

            @if ($error !== '')
                <text class="text-[12] text-red-600 dark:text-red-300">{{ $error }}</text>
            @endif

            <button class="glass:prominent:interactive" variant="accent" size="lg" :loading="$isSubmitting" :disabled="$isSubmitting" @press="submit">Create secure account</button>
        </column>

        <row class="items-center justify-center gap-2">
            <icon name="lock.shield" :size="14" color="#16A34A" dark-color="#86EFAC" />
            <text class="text-[11] text-theme-on-surface-variant">Your sign-in token is encrypted in secure device storage</text>
        </row>
    </column>
</scroll-view>
