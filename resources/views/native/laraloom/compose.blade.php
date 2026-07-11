<scroll-view class="w-full h-full bg-theme-background">
    <column class="w-full px-5 pt-5 pb-10 gap-5">
        <column class="gap-2">
            <text class="text-[24] font-bold text-theme-on-surface">What are you sharing?</text>
            <text class="text-[13] text-theme-on-surface-variant">Useful, original and properly sourced beats loud. Every time.</text>
        </column>

        <tab-row :selectedIndex="$kindIndex" @change="updateKind">
            <tab label="Note" />
            <tab label="Article" />
            <tab label="Package" />
            <tab label="Project" />
        </tab-row>

        <column class="w-full gap-5 p-5 rounded-3xl bg-theme-surface border border-theme-outline">
            <column class="gap-2">
                <text class="text-[12] font-semibold text-theme-on-surface">Title</text>
                <outlined-text-input native:model.debounce.300ms="title" placeholder="A clear, useful headline" :variant="0" />
            </column>

            <column class="gap-2">
                <text class="text-[12] font-semibold text-theme-on-surface">What should Laravel developers know?</text>
                <outlined-text-input native:model.debounce.300ms="body" placeholder="Add context, not hype…" :variant="0" />
            </column>

            <column class="gap-2">
                <text class="text-[12] font-semibold text-theme-on-surface">Original URL</text>
                <outlined-text-input native:model.debounce.300ms="url" placeholder="https://" keyboard="url" :variant="0" />
            </column>

            <column class="gap-2">
                <text class="text-[12] font-semibold text-theme-on-surface">Tags</text>
                <outlined-text-input native:model.debounce.300ms="tags" placeholder="Laravel, NativePHP, open source" :variant="0" />
            </column>

            @if ($error !== '')
                <text class="text-[12] text-red-600 dark:text-red-300">{{ $error }}</text>
            @endif

            <button class="glass:prominent:interactive" variant="accent" size="lg" :loading="$isSubmitting" :disabled="$isSubmitting" @press="submit">Publish to Laraloom</button>
        </column>

        <row class="items-center justify-center gap-2">
            <icon name="checkmark.shield" :size="14" color="#16A34A" dark-color="#86EFAC" />
            <text class="text-[11] text-theme-on-surface-variant">Community posts link back to you and publish immediately</text>
        </row>
    </column>
</scroll-view>
