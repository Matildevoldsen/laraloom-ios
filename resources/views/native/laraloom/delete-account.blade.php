<scroll-view class="w-full h-full bg-theme-background">
    <column class="w-full px-5 pt-8 pb-10 gap-6">
        <column class="items-center gap-3">
            <column class="w-[64] h-[64] rounded-3xl bg-red-50 dark:bg-[#32161D] items-center justify-center">
                <icon name="trash" :size="28" color="#EF4444" />
            </column>
            <text class="text-[24] font-bold text-theme-on-surface">Leave Laraloom</text>
            <text class="text-[13] text-theme-on-surface-variant text-center">Deleting your account permanently removes your profile, posts, replies, follows and saved activity.</text>
        </column>

        <column class="w-full gap-4 p-5 rounded-[22] bg-theme-surface border border-theme-outline">
            <column class="gap-2">
                <text class="text-[12] font-semibold text-theme-on-surface">Confirm your password</text>
                <outlined-text-input native:model.debounce.400ms="password" placeholder="Your password" :secure="true" @submit="confirmDelete" :variant="0" />
            </column>

            @if ($error !== '')
                <text class="text-[12] text-red-500">{{ $error }}</text>
            @endif

            <button variant="destructive" size="lg" :loading="$isSubmitting" :disabled="$isSubmitting" @press="confirmDeletion">Delete my account</button>
        </column>

        <row class="items-start gap-2 px-2"><icon name="exclamationmark.triangle" :size="15" color="#F59E0B" /><text class="flex-1 text-[11] text-theme-on-surface-variant">This is immediate and cannot be undone.</text></row>
    </column>
</scroll-view>
