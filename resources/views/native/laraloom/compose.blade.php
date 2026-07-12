<column class="w-full h-full bg-theme-background">
    <scroll-view class="flex-1 w-full">
        <column class="w-full px-5 pt-5 pb-8 gap-5">
            <row class="w-full items-start gap-3">
                <column class="w-[44] h-[44] items-center justify-center rounded-full bg-[#F43F8C]/15">
                    <icon name="person.crop.circle.fill" :size="34" color="#F472B6" />
                </column>

                <column class="flex-1 gap-3">
                    <bare-text-input
                        native:model.debounce.200ms="body"
                        placeholder="What’s happening in Laravel?"
                        :multiline="true"
                        :minLines="4"
                        :maxLines="8"
                        :maxLength="1500"
                        color="#18181B"
                        dark-color="#E4E4E7"
                        class="w-full h-[132] px-1 py-2 text-[19] text-theme-on-surface"
                    />

                    @if ($mediaPaths !== [])
                        <column class="w-full overflow-hidden rounded-2xl bg-theme-surface-variant">
                            <image src="{{ $mediaPaths[0] }}" alt="Selected media preview" class="w-full h-[170] object-cover" />
                            <row class="w-full items-center justify-between px-4 py-3">
                                <row class="items-center gap-2">
                                    <icon name="photo.stack.fill" :size="18" color="#F472B6" />
                                    <text class="text-[13] font-semibold text-theme-on-surface">{{ count($mediaPaths) }} {{ count($mediaPaths) === 1 ? 'item' : 'items' }} ready</text>
                                </row>
                                <button class="glass:clear:interactive" size="sm" icon="xmark" @press="clearMedia">Remove</button>
                            </row>
                        </column>
                    @endif

                    <row class="items-center gap-2">
                        <icon name="globe" :size="14" color="#F43F8C" />
                        <text class="text-[12] font-semibold text-[#F43F8C]">Everyone in Laraloom can reply</text>
                    </row>
                </column>
            </row>

            <divider class="w-full" />

            <row class="w-full items-center justify-between">
                <row class="items-center gap-1">
                    <button class="glass:clear:interactive" size="lg" icon="photo.on.rectangle.angled" @press="chooseMedia" />
                    <button class="glass:clear:interactive" size="lg" icon="link" @press="toggleDetails" />
                    <button class="glass:clear:interactive" size="lg" icon="number" @press="toggleDetails" />
                </row>

                <button class="glass:prominent:interactive px-5" variant="accent" size="lg" :loading="$isSubmitting" :disabled="$isSubmitting" @press="submit">Post</button>
            </row>

            <row class="w-full items-center">
                <button class="glass:clear:interactive" size="sm" icon="slider.horizontal.3" @press="toggleDetails">
                    {{ $showsDetails ? 'Hide details' : 'Add title, link, tags or post type' }}
                </button>
            </row>

            @if ($error !== '')
                <row class="w-full items-start gap-2 rounded-2xl bg-red-500/10 px-4 py-3">
                    <icon name="exclamationmark.circle.fill" :size="17" color="#EF4444" />
                    <text class="flex-1 text-[12] text-red-600 dark:text-red-300">{{ $error }}</text>
                </row>
            @endif
        </column>
    </scroll-view>

    <bottom-sheet :visible="$showsDetails" detents="medium,large" @dismiss="closeDetails">
        <column class="w-full bg-theme-background px-5 pt-2 pb-8 gap-5">
            <row class="w-full items-center justify-between pb-3 border-b border-theme-outline">
                <column class="gap-1">
                    <text class="text-[20] font-bold text-theme-on-surface">Post details</text>
                    <text class="text-[12] text-theme-on-surface-variant">Optional context helps people find your work.</text>
                </column>
                <button class="glass:clear:interactive" size="sm" icon="xmark" @press="closeDetails" />
            </row>

            <tab-row :selectedIndex="$kindIndex" @change="updateKind">
                <tab label="Note" />
                <tab label="Article" />
                <tab label="Package" />
                <tab label="Project" />
            </tab-row>

            <outlined-text-input native:model.debounce.300ms="title" placeholder="Title" :variant="0" />
            <outlined-text-input native:model.debounce.300ms="url" placeholder="Original URL" keyboard="url" :variant="0" />
            <outlined-text-input native:model.debounce.300ms="tags" placeholder="Laravel, NativePHP, open source" :variant="0" />

            <button class="glass:prominent:interactive" variant="accent" size="lg" @press="closeDetails">Done</button>
        </column>
    </bottom-sheet>
</column>
