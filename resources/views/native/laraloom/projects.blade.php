<refreshable @refresh="refresh" class="w-full h-full bg-theme-background">
    <column class="w-full gap-4 px-4 pt-4 pb-8">
        <column class="w-full p-5 gap-3 rounded-3xl bg-pink-50 dark:bg-[#241525] border border-pink-200 dark:border-[#48203B]">
            <row class="items-center gap-2">
                <icon name="shippingbox.fill" :size="22" color="#F472B6" />
                <text class="text-[12] uppercase font-semibold text-[#F9A8D4]">Shipped by the community</text>
            </row>
            <text class="text-[24] font-bold text-theme-on-surface">Laravel in the wild</text>
            <text class="text-[13] text-theme-on-surface-variant">Discover polished products, ambitious experiments and open-source work built on Laravel.</text>
        </column>

        @if ($error !== '')
            <column class="p-4 gap-2 rounded-2xl bg-pink-50 dark:bg-[#351927] border border-pink-200 dark:border-[#6B2347]">
                <text class="text-[13] text-pink-800 dark:text-[#FBCFE8]">{{ $error }}</text>
                <button class="glass:clear:interactive" size="sm" @press="refresh">Try again</button>
            </column>
        @endif

        @foreach ($projects as $project)
            <column @press="openProject('{{ $project['slug'] }}')" class="w-full gap-3 p-4 rounded-3xl bg-theme-surface border border-theme-outline" key="project-{{ $project['id'] }}">
                <row class="items-start gap-3">
                    <column class="w-[48] h-[48] rounded-2xl bg-pink-50 dark:bg-[#322239] items-center justify-center">
                        <text class="text-[20] font-bold text-[#F9A8D4]">{{ strtoupper(substr($project['name'], 0, 1)) }}</text>
                    </column>
                    <column class="flex-1 gap-1">
                        <row class="items-center gap-2">
                            <text class="flex-1 text-[17] font-bold text-theme-on-surface">{{ $project['name'] }}</text>
                            @if ($project['is_featured'])
                                <icon name="star.fill" :size="14" color="#FBBF24" />
                            @endif
                        </row>
                        <text class="text-[13] text-theme-on-surface-variant">{{ $project['tagline'] }}</text>
                        <text class="text-[11] text-theme-on-surface-variant">by {{ $project['author']['name'] ?? 'Laravel community' }}</text>
                    </column>
                </row>
                <row class="items-center gap-2">
                    @foreach (array_slice($project['tags'], 0, 3) as $tag)
                        <text class="px-2 py-1 rounded-full text-[10] text-violet-700 dark:text-[#C4B5FD] bg-violet-50 dark:bg-[#28203A]">{{ $tag }}</text>
                    @endforeach
                    @if ($project['is_open_source'])
                        <text class="px-2 py-1 rounded-full text-[10] text-[#86EFAC] bg-[#133322]">OPEN SOURCE</text>
                    @endif
                </row>
            </column>
        @endforeach
    </column>
</refreshable>
