<refreshable @refresh="refresh" class="w-full h-full bg-theme-background">
    <column class="w-full px-4 pt-5 pb-10 gap-4">
        <column class="gap-2">
            <row class="items-center gap-2">
                <icon name="checkmark.shield.fill" :size="16" color="#F43F8C" />
                <text class="text-[11] uppercase font-semibold text-[#F43F8C]">Community operations</text>
            </row>
            <text class="text-[24] font-bold text-theme-on-surface">Moderate the signal</text>
            <text class="text-[12] text-theme-on-surface-variant">Long-press a post or use its menu.</text>
        </column>

        <row class="w-full gap-2">
            @foreach (['pending_posts' => 'Pending', 'members' => 'Members', 'projects' => 'Projects'] as $key => $label)
                <column class="flex-1 py-3 items-center gap-1 rounded-2xl bg-theme-surface border border-theme-outline">
                    <text class="text-[18] font-bold text-theme-on-surface">{{ $counts[$key] ?? 0 }}</text>
                    <text class="text-[10] text-theme-on-surface-variant">{{ $label }}</text>
                </column>
            @endforeach
        </row>

        @if ($error !== '')
            <column class="p-4 rounded-2xl bg-red-50 dark:bg-[#32161D] border border-red-200 dark:border-red-900">
                <text class="text-[12] text-red-700 dark:text-red-300">{{ $error }}</text>
            </column>
        @endif

        <column class="w-full gap-3">
            @forelse ($posts as $post)
                <pressable :menu="$this->postMenu($post)" @press="edit({{ $post['id'] }})" class="w-full p-3 gap-2 rounded-[18] bg-theme-surface border border-theme-outline" key="admin-post-{{ $post['id'] }}">
                    <row class="items-center gap-2">
                        <text class="px-2 py-1 rounded-full text-[9] font-semibold text-[#F43F8C] bg-pink-50 dark:bg-[#351927]">{{ strtoupper($post['status']) }}</text>
                        <text class="flex-1 text-right text-[10] text-theme-on-surface-variant" :maxLines="1">{{ $post['author']['name'] ?? $post['source']['name'] ?? 'Discovery agent' }}</text>
                        <pressable :menu="$this->postMenu($post)" a11y-label="Moderation actions" class="w-[30] h-[30] rounded-full items-center justify-center">
                            <icon name="ellipsis" :size="18" color="#8E8797" />
                        </pressable>
                    </row>
                    <text class="text-[16] font-bold text-theme-on-surface" :maxLines="2">{{ $post['title'] ?: 'Community note' }}</text>
                    <text class="text-[12] text-theme-on-surface-variant" :maxLines="2">{{ $post['body'] ?: $post['summary'] }}</text>
                    <row class="items-center gap-3">
                        <row class="items-center gap-1"><icon name="bubble.left" :size="13" color="#8E8797" /><text class="text-[10] text-theme-on-surface-variant">{{ $post['counts']['comments'] ?? 0 }}</text></row>
                        <row class="items-center gap-1"><icon name="heart" :size="13" color="#8E8797" /><text class="text-[10] text-theme-on-surface-variant">{{ $post['counts']['reactions'] ?? 0 }}</text></row>
                        <row class="items-center gap-1"><icon name="repeat" :size="13" color="#8E8797" /><text class="text-[10] text-theme-on-surface-variant">{{ $post['counts']['reposts'] ?? 0 }}</text></row>
                    </row>
                </pressable>
            @empty
                @if ($error === '')
                    <column class="items-center gap-3 py-12">
                        <icon name="checkmark.circle.fill" :size="34" color="#16A34A" dark-color="#86EFAC" />
                        <text class="text-[17] font-bold text-theme-on-surface">All clear</text>
                    </column>
                @endif
            @endforelse
        </column>
    </column>

</refreshable>
