<refreshable @refresh="refresh" class="w-full h-full bg-theme-background">
    <column class="w-full pb-8">
        <column class="w-full px-5 pt-5 pb-4 gap-3 bg-theme-background">
            <row class="items-center gap-3">
                <column class="w-[34] h-[34] rounded-xl bg-[#F43F8C] items-center justify-center">
                    <text class="text-white text-[18] font-bold">L</text>
                </column>
                <column class="flex-1 gap-0">
                    <text class="text-[11] uppercase font-semibold text-[#F43F8C]">Everything Laravel</text>
                    <text class="text-[24] font-bold text-theme-on-surface">What matters today</text>
                </column>
                <pressable @press="compose" a11y-label="Share with Laraloom" class="w-[40] h-[40] rounded-full glass:prominent:interactive items-center justify-center">
                    <icon name="square.and.pencil" :size="18" color="#F43F8C" />
                </pressable>
            </row>
        </column>

        <tab-row :selectedIndex="$selectedFeed" @change="selectFeed">
            <tab label="Today" />
            <tab label="Following" />
        </tab-row>

        @if ($error !== '')
            <column class="mx-4 my-3 p-4 gap-2 rounded-2xl bg-pink-50 dark:bg-[#351927] border border-pink-200 dark:border-[#6B2347]">
                <text class="text-[13] text-pink-800 dark:text-[#FBCFE8]">{{ $error }}</text>
                <button class="glass:clear:interactive" size="sm" @press="refresh">Try again</button>
            </column>
        @endif

        @forelse ($posts as $post)
            <column class="w-full" key="post-{{ $post['id'] }}">
                <pressable :menu="$this->postMenu($post)" @press="openPost({{ $post['id'] }})" class="w-full px-4 py-3">
                    <row class="w-full items-start gap-3">
                        <column class="w-[38] h-[38] rounded-xl bg-pink-50 dark:bg-[#2B2031] items-center justify-center">
                            <icon name="{{ $post['is_ai_curated'] ? 'sparkles' : 'person.crop.circle' }}" :size="18" color="#F472B6" />
                        </column>

                        <column class="flex-1 gap-1">
                            <row class="w-full items-center gap-1">
                                <text class="text-[14] font-bold text-theme-on-surface" :maxLines="1">{{ $post['author']['name'] ?? $post['source']['name'] ?? 'Laravel community' }}</text>
                                <text class="flex-1 text-[11] text-theme-on-surface-variant" :maxLines="1">· {{ $post['source']['name'] ?? ucfirst($post['kind']) }}</text>
                                <pressable :menu="$this->postMenu($post)" a11y-label="Post actions" class="w-[28] h-[28] rounded-full items-center justify-center">
                                    <icon name="ellipsis" :size="16" color="#8E8797" />
                                </pressable>
                            </row>

                            @if (! empty($post['title']))
                                <text class="text-[16] font-bold text-theme-on-surface" :maxLines="2">{{ $post['title'] }}</text>
                            @endif
                            <text class="text-[13] text-theme-on-surface-variant" :maxLines="2">{{ $post['summary'] ?: $post['body'] }}</text>

                            @if (! empty($post['tags']))
                                <row class="items-center gap-2 pt-1">
                                    @foreach (array_slice($post['tags'], 0, 2) as $tag)
                                        <text class="text-[11] text-[#A78BFA]">#{{ $tag }}</text>
                                    @endforeach
                                </row>
                            @endif

                            <row class="w-full items-center justify-between pt-1 pr-1">
                                <pressable @press="openPost({{ $post['id'] }})" a11y-label="View replies" class="py-1 pr-3 rounded-full">
                                    <row class="items-center gap-1"><icon name="bubble.left" :size="16" color="#8E8797" /><text class="text-[11] text-theme-on-surface-variant">{{ $post['counts']['comments'] ?? 0 }}</text></row>
                                </pressable>
                                <pressable @press="toggleRepost({{ $post['id'] }})" a11y-label="Repost" class="px-2 py-1 rounded-full">
                                    <row class="items-center gap-1"><icon name="repeat" :size="16" color="{{ ($post['is_reposted'] ?? false) ? '#10B981' : '#8E8797' }}" /><text class="text-[11] text-theme-on-surface-variant">{{ $post['counts']['reposts'] ?? 0 }}</text></row>
                                </pressable>
                                <pressable @press="toggleReaction({{ $post['id'] }})" a11y-label="Like post" class="px-2 py-1 rounded-full">
                                    <row class="items-center gap-1"><icon name="heart" :size="16" color="{{ ($post['is_reacted'] ?? false) ? '#F43F8C' : '#8E8797' }}" /><text class="text-[11] text-theme-on-surface-variant">{{ $post['counts']['reactions'] ?? 0 }}</text></row>
                                </pressable>
                                <pressable @press="toggleBookmark({{ $post['id'] }})" a11y-label="Bookmark post" class="pl-2 py-1 rounded-full">
                                    <icon name="bookmark" :size="16" color="{{ ($post['is_bookmarked'] ?? false) ? '#F43F8C' : '#8E8797' }}" />
                                </pressable>
                            </row>
                        </column>
                    </row>
                </pressable>
                <divider class="w-full" />
            </column>
        @empty
            @if ($error === '')
                <column class="w-full items-center gap-3 px-8 py-16">
                    <icon name="newspaper" :size="34" color="#F43F8C" />
                    <text class="text-[18] font-bold text-theme-on-surface">The loom is quiet</text>
                    <text class="text-[13] text-theme-on-surface-variant text-center">Pull down to check for fresh Laravel stories.</text>
                </column>
            @endif
        @endforelse
    </column>

</refreshable>
