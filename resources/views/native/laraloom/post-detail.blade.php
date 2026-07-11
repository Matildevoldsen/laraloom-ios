<scroll-view class="w-full h-full bg-theme-background">
    <column class="w-full gap-5 px-5 pt-5 pb-12">
        @if ($error !== '')
            <column class="p-4 rounded-2xl bg-pink-50 dark:bg-[#351927] border border-pink-200 dark:border-[#6B2347]">
                <text class="text-[13] text-pink-800 dark:text-[#FBCFE8]">{{ $error }}</text>
            </column>
        @endif

        @if ($post !== [])
            <row class="items-center gap-2">
                <text class="text-[10] uppercase font-semibold text-[#F43F8C]">{{ $post['kind'] }}</text>
                @if ($post['is_ai_curated'])
                    <row class="items-center gap-1"><icon name="sparkles" :size="12" color="#F472B6" /><text class="text-[10] text-theme-on-surface-variant">Curated</text></row>
                @endif
            </row>

            <text class="text-[27] font-bold text-theme-on-surface">{{ $post['title'] ?: 'Community note' }}</text>

            <row class="items-center gap-3">
                <column class="w-[36] h-[36] rounded-xl bg-pink-50 dark:bg-[#322239] items-center justify-center">
                    <icon name="person.crop.circle" :size="19" color="#F472B6" />
                </column>
                <column class="flex-1 gap-0">
                    <text class="text-[13] font-semibold text-theme-on-surface">{{ $post['author']['name'] ?? $post['source']['name'] ?? 'Laravel community' }}</text>
                    <text class="text-[11] text-theme-on-surface-variant">{{ $post['source']['author'] ?? $post['source']['name'] ?? 'Community' }}</text>
                </column>
            </row>

            @if (! empty($post['summary']))
                <text class="text-[17] text-theme-on-surface">{{ $post['summary'] }}</text>
            @endif

            @if (! empty($post['body']))
                <text class="text-[15] text-theme-on-surface-variant">{{ $post['body'] }}</text>
            @endif

            @if (! empty($post['why_it_matters']))
                <column class="w-full p-4 gap-2 rounded-[20] bg-theme-surface-variant">
                    <text class="text-[11] uppercase font-semibold text-[#F43F8C]">Why it matters</text>
                    <text class="text-[13] text-theme-on-surface-variant">{{ $post['why_it_matters'] }}</text>
                </column>
            @endif

            @if (! empty($post['tags']))
                <row class="items-center gap-2">
                    @foreach (array_slice($post['tags'], 0, 4) as $tag)
                        <text class="text-[10] text-violet-700 dark:text-[#C4B5FD]">#{{ $tag }}</text>
                    @endforeach
                </row>
            @endif

            <row class="w-full items-center justify-between py-2 border-t border-b border-theme-outline">
                <row class="items-center gap-1"><icon name="bubble.left" :size="16" color="#8E8797" /><text class="text-[11] text-theme-on-surface-variant">{{ $post['counts']['comments'] ?? 0 }}</text></row>
                <pressable @press="toggleRepost" class="px-3 py-2 rounded-full"><row class="items-center gap-1"><icon name="repeat" :size="16" color="{{ ($post['is_reposted'] ?? false) ? '#10B981' : '#8E8797' }}" /><text class="text-[11] text-theme-on-surface-variant">{{ $post['counts']['reposts'] ?? 0 }}</text></row></pressable>
                <pressable @press="toggleReaction" class="px-3 py-2 rounded-full"><row class="items-center gap-1"><icon name="heart" :size="16" color="{{ ($post['is_reacted'] ?? false) ? '#F43F8C' : '#8E8797' }}" /><text class="text-[11] text-theme-on-surface-variant">{{ $post['counts']['reactions'] ?? 0 }}</text></row></pressable>
                <pressable @press="toggleBookmark" class="px-3 py-2 rounded-full"><icon name="bookmark" :size="16" color="{{ ($post['is_bookmarked'] ?? false) ? '#F43F8C' : '#8E8797' }}" /></pressable>
            </row>

            @if (! empty($post['url']))
                <pressable @press="openSource" class="w-full py-2"><row class="items-center gap-2"><icon name="arrow.up.right" :size="15" color="#F43F8C" /><text class="text-[13] font-semibold text-[#F43F8C]">Read the original source</text></row></pressable>
            @endif

            <column class="w-full gap-3 p-4 rounded-[22] bg-theme-surface border border-theme-outline">
                @if ($parentId !== null)
                    <row class="items-center gap-2"><text class="flex-1 text-[11] text-[#F43F8C]">Replying to {{ '@'.$replyingTo }}</text><pressable @press="cancelReply"><text class="text-[11] text-theme-on-surface-variant">Cancel</text></pressable></row>
                @endif
                <outlined-text-input value="{{ $replyBody }}" placeholder="Add to the conversation…" @change="updateReplyBody" :multiline="true" :minLines="2" :maxLines="4" :maxLength="1000" :variant="0" />
                <row class="justify-end"><button class="glass:prominent:interactive" variant="accent" size="sm" :loading="$isSubmitting" :disabled="$isSubmitting" @press="submitReply">Reply</button></row>
            </column>

            <column class="w-full gap-1">
                <text class="text-[16] font-bold text-theme-on-surface">Conversation</text>
                @forelse ($comments as $comment)
                    <column class="w-full py-4 gap-2 {{ $comment['parent_id'] ? 'pl-7 border-l border-theme-outline' : '' }} border-b border-theme-outline" key="comment-{{ $comment['id'] }}">
                        <row class="items-center gap-2">
                            <column class="w-[28] h-[28] rounded-full bg-pink-50 dark:bg-[#322239] items-center justify-center"><icon name="person.crop.circle" :size="15" color="#F472B6" /></column>
                            <column class="flex-1 gap-0">
                                <text class="text-[12] font-semibold text-theme-on-surface">{{ $comment['author']['name'] ?? 'Laraloom member' }}</text>
                                <text class="text-[10] text-theme-on-surface-variant">{{ '@'.($comment['author']['username'] ?? 'member') }}</text>
                            </column>
                            <pressable :menu="$this->commentMenu($comment)" a11y-label="Reply actions" class="w-[30] h-[30] rounded-full items-center justify-center"><icon name="ellipsis" :size="16" color="#8E8797" /></pressable>
                        </row>
                        <text class="text-[13] text-theme-on-surface">{{ $comment['body'] }}</text>
                        <pressable @press="replyTo({{ $comment['id'] }})" class="self-start py-1"><text class="text-[11] font-semibold text-[#F43F8C]">Reply</text></pressable>
                    </column>
                @empty
                    <column class="items-center gap-2 py-8"><icon name="bubble.left" :size="26" color="#F43F8C" /><text class="text-[13] text-theme-on-surface-variant">Start the conversation.</text></column>
                @endforelse
            </column>
        @endif
    </column>
</scroll-view>
