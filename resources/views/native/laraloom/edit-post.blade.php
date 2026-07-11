<scroll-view class="w-full h-full bg-theme-background">
    <column class="w-full px-5 pt-5 pb-10 gap-5">
        <column class="gap-2">
            <text class="text-[24] font-bold text-theme-on-surface">Keep it useful</text>
            <text class="text-[13] text-theme-on-surface-variant">Update the context while preserving attribution to the original source.</text>
        </column>

        @if ($postId > 0)
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
                    <text class="text-[12] font-semibold text-theme-on-surface">Your take</text>
                    <outlined-text-input native:model.debounce.300ms="body" placeholder="Add context, not hype…" :variant="0" />
                </column>
                <column class="gap-2">
                    <text class="text-[12] font-semibold text-theme-on-surface">Original URL</text>
                    <outlined-text-input native:model.debounce.300ms="url" placeholder="https://" keyboard="url" :variant="0" />
                </column>
                <column class="gap-2">
                    <text class="text-[12] font-semibold text-theme-on-surface">Tags</text>
                    <outlined-text-input native:model.debounce.300ms="tags" placeholder="Laravel, NativePHP" :variant="0" />
                </column>
                <button class="glass:prominent:interactive" variant="accent" size="lg" :loading="$isSubmitting" :disabled="$isSubmitting" @press="submit">Save changes</button>
                <button variant="destructive" size="md" @press="confirmDelete">Delete post</button>
            </column>
        @endif

        @if ($error !== '')
            <column class="p-4 rounded-2xl bg-red-50 dark:bg-[#32161D] border border-red-200 dark:border-red-900">
                <text class="text-[12] text-red-700 dark:text-red-300">{{ $error }}</text>
            </column>
        @endif
    </column>
</scroll-view>
