<scroll-view class="w-full h-full bg-theme-background">
    <column class="w-full gap-5 px-5 pt-5 pb-10">
        @if ($error !== '')
            <column class="p-4 rounded-2xl bg-pink-50 dark:bg-[#351927] border border-pink-200 dark:border-[#6B2347]">
                <text class="text-[13] text-pink-800 dark:text-[#FBCFE8]">{{ $error }}</text>
            </column>
        @else
            <column class="w-[76] h-[76] rounded-3xl bg-pink-50 dark:bg-[#322239] items-center justify-center glass:clear">
                <text class="text-[32] font-bold text-[#F9A8D4]">{{ strtoupper(substr($project['name'], 0, 1)) }}</text>
            </column>

            <column class="gap-2">
                <row class="items-center gap-2">
                    <text class="text-[11] uppercase font-semibold text-[#F9A8D4]">{{ str_replace('_', ' ', $project['kind']) }}</text>
                    @if ($project['is_featured'])
                        <icon name="star.fill" :size="14" color="#FBBF24" />
                    @endif
                </row>
                <text class="text-[30] font-bold text-theme-on-surface">{{ $project['name'] }}</text>
                <text class="text-[17] text-theme-on-surface">{{ $project['tagline'] }}</text>
                <text class="text-[12] text-theme-on-surface-variant">Built by {{ $project['author']['name'] ?? 'the Laravel community' }}</text>
            </column>

            <text class="text-[15] text-theme-on-surface-variant">{{ $project['description'] }}</text>

            <row class="items-center gap-2">
                @foreach ($project['tags'] as $tag)
                    <text class="px-2 py-1 rounded-full text-[10] text-violet-700 dark:text-[#C4B5FD] bg-violet-50 dark:bg-[#28203A]">{{ $tag }}</text>
                @endforeach
            </row>

            <column class="w-full gap-3 p-4 rounded-3xl bg-theme-surface border border-theme-outline">
                <row class="items-center gap-2">
                    <icon name="checkmark.seal.fill" :size="17" color="#86EFAC" />
                    <text class="text-[13] text-theme-on-surface-variant">{{ $project['is_open_source'] ? 'Open source' : 'Community showcase' }}</text>
                </row>
                @if (! empty($project['laravel_cloud_url']))
                    <row class="items-center gap-2">
                        <icon name="cloud.fill" :size="17" color="#93C5FD" />
                        <text class="text-[13] text-theme-on-surface-variant">Shipped on Laravel Cloud</text>
                    </row>
                @endif
            </column>

            <button class="glass:prominent:interactive" variant="accent" size="lg" icon-trailing="arrow.up.right" @press="openWebsite">Visit project</button>
            @if (! empty($project['repository_url']))
                <button class="glass:clear:interactive" size="md" icon="chevron.left.forwardslash.chevron.right" @press="openRepository">View source</button>
            @endif
        @endif
    </column>
</scroll-view>
