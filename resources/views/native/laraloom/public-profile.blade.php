<refreshable @refresh="refresh" class="w-full h-full bg-theme-background">
    <column class="w-full pb-12">
        @if ($error !== '')
            <column class="mx-4 mt-3 p-3 rounded-xl bg-pink-50 dark:bg-[#351927]"><text class="text-[12] text-pink-800 dark:text-[#FBCFE8]">{{ $error }}</text></column>
        @endif

        @if ($profile !== [])
            <column class="w-full h-[82] bg-pink-50 dark:bg-[#261522] border-b border-theme-outline" />
            <column class="w-full px-5 pb-4 border-b border-theme-outline">
                <row class="w-full -mt-[34] items-end justify-between">
                    <column class="w-[76] h-[76] rounded-full bg-theme-background border-[4] border-theme-background items-center justify-center">
                        <column class="w-[68] h-[68] rounded-full bg-[#F43F8C] items-center justify-center"><text class="text-[26] font-bold text-white">{{ strtoupper(substr($profile['name'], 0, 1)) }}</text></column>
                    </column>
                    <button class="{{ ($profile['is_following'] ?? false) ? 'glass:clear:interactive' : 'glass:prominent:interactive' }}" variant="accent" size="sm" @press="toggleFollow">{{ ($profile['is_following'] ?? false) ? 'Unfollow' : 'Follow' }}</button>
                </row>

                <column class="mt-3 gap-0">
                    <text class="text-[22] font-bold text-theme-on-surface">{{ $profile['name'] }}</text>
                    <text class="text-[12] text-theme-on-surface-variant">{{ '@'.$profile['username'] }}</text>
                </column>
                @if (! empty($profile['headline']))<text class="mt-3 text-[14] font-medium text-theme-on-surface">{{ $profile['headline'] }}</text>@endif
                @if (! empty($profile['bio']))<text class="mt-2 text-[13] leading-[19] text-theme-on-surface-variant">{{ $profile['bio'] }}</text>@endif

                <row class="mt-3 items-center gap-5">
                    <pressable @press="showPeople('followers')"><row class="items-center gap-1"><text class="text-[13] font-bold text-theme-on-surface">{{ $profile['counts']['followers'] ?? 0 }}</text><text class="text-[12] text-theme-on-surface-variant">Followers</text></row></pressable>
                    <pressable @press="showPeople('following')"><row class="items-center gap-1"><text class="text-[13] font-bold text-theme-on-surface">{{ $profile['counts']['following'] ?? 0 }}</text><text class="text-[12] text-theme-on-surface-variant">Following</text></row></pressable>
                    @if ($profile['is_available_for_work'] ?? false)<row class="items-center gap-1"><icon name="circle.fill" :size="7" color="#10B981" /><text class="text-[11] text-emerald-500">Available</text></row>@endif
                </row>
            </column>

            <column class="w-full px-2 py-2 border-b border-theme-outline"><tab-row :selectedIndex="$selectedTab" @change="selectTab"><tab-item label="Posts" /><tab-item label="Replies" /><tab-item label="Reposts" /><tab-item label="Likes" /><tab-item label="Packages" /></tab-row></column>

            @if ($selectedTab === 0)
                <column class="w-full">
                    @forelse ($profile['posts'] ?? [] as $post)
                        <pressable @press="openPost({{ $post['id'] }})" class="w-full px-5 py-4 gap-2 border-b border-theme-outline">
                            <text class="text-[15] font-bold text-theme-on-surface" :maxLines="2">{{ $post['title'] ?: 'Community note' }}</text>
                            <text class="text-[13] leading-[18] text-theme-on-surface-variant" :maxLines="3">{{ $post['body'] ?: $post['summary'] }}</text>
                            <row class="w-full items-center gap-7 pt-1"><row class="items-center gap-1"><icon name="bubble.left" :size="14" color="#8E8797" /><text class="text-[10] text-theme-on-surface-variant">{{ $post['counts']['comments'] ?? 0 }}</text></row><row class="items-center gap-1"><icon name="repeat" :size="14" color="#8E8797" /><text class="text-[10] text-theme-on-surface-variant">{{ $post['counts']['reposts'] ?? 0 }}</text></row><row class="items-center gap-1"><icon name="heart" :size="14" color="#8E8797" /><text class="text-[10] text-theme-on-surface-variant">{{ $post['counts']['reactions'] ?? 0 }}</text></row></row>
                        </pressable>
                    @empty
                        <column class="items-center gap-2 px-8 py-12"><icon name="text.bubble" :size="24" color="#F43F8C" /><text class="text-[13] text-theme-on-surface-variant">No posts yet.</text></column>
                    @endforelse
                </column>
            @elseif ($selectedTab === 1)
                <column class="w-full">
                    @forelse ($profile['replies'] ?? [] as $reply)
                        <pressable @press="openPost({{ $reply['post_id'] }})" class="w-full px-5 py-4 gap-2 border-b border-theme-outline"><row class="items-center gap-2"><icon name="arrowshape.turn.up.left" :size="13" color="#8E8797" /><text class="text-[11] text-theme-on-surface-variant">Replied in a conversation</text></row><text class="text-[14] leading-[20] text-theme-on-surface">{{ $reply['body'] }}</text></pressable>
                    @empty
                        <column class="items-center gap-2 px-8 py-12"><icon name="bubble.left.and.bubble.right" :size="24" color="#F43F8C" /><text class="text-[13] text-theme-on-surface-variant">No replies yet.</text></column>
                    @endforelse
                </column>
            @elseif (in_array($selectedTab, [2, 3], true))
                @php($activityPosts = $selectedTab === 2 ? ($profile['reposted_posts'] ?? []) : ($profile['liked_posts'] ?? []))
                <column class="w-full">
                    @forelse ($activityPosts as $post)
                        <pressable @press="openPost({{ $post['id'] }})" class="w-full px-5 py-4 gap-2 border-b border-theme-outline"><row class="items-center gap-2"><icon name="{{ $selectedTab === 2 ? 'repeat' : 'heart.fill' }}" :size="13" color="{{ $selectedTab === 2 ? '#10B981' : '#F43F8C' }}" /><text class="text-[11] text-theme-on-surface-variant">{{ $selectedTab === 2 ? 'Reposted' : 'Liked' }}</text></row><text class="text-[15] font-bold text-theme-on-surface" :maxLines="2">{{ $post['title'] ?: 'Community note' }}</text><text class="text-[13] text-theme-on-surface-variant" :maxLines="2">{{ $post['body'] ?: $post['summary'] }}</text></pressable>
                    @empty
                        <column class="items-center gap-2 px-8 py-12"><icon name="{{ $selectedTab === 2 ? 'repeat' : 'heart' }}" :size="24" color="#F43F8C" /><text class="text-[13] text-theme-on-surface-variant">No {{ $selectedTab === 2 ? 'reposts' : 'likes' }} yet.</text></column>
                    @endforelse
                </column>
            @else
                <column class="w-full">
                    @forelse ($profile['projects'] ?? [] as $project)
                        <pressable @press="openProject('{{ $project['slug'] }}')" class="w-full px-5 py-4 border-b border-theme-outline">
                            <row class="items-center gap-3"><column class="w-[42] h-[42] rounded-xl bg-pink-50 dark:bg-[#2B2031] items-center justify-center"><icon name="shippingbox" :size="19" color="#F43F8C" /></column><column class="flex-1 gap-1"><row class="items-center gap-2"><text class="text-[15] font-bold text-theme-on-surface">{{ $project['name'] }}</text><text class="text-[9] uppercase text-[#F43F8C]">{{ $project['kind'] }}</text></row><text class="text-[12] text-theme-on-surface-variant" :maxLines="2">{{ $project['tagline'] }}</text></column><icon name="chevron.right" :size="13" color="#8E8797" /></row>
                        </pressable>
                    @empty
                        <column class="items-center gap-2 px-8 py-12"><icon name="shippingbox" :size="24" color="#F43F8C" /><text class="text-[13] text-theme-on-surface-variant">No packages or projects yet.</text></column>
                    @endforelse
                </column>
            @endif

            <bottom-sheet :visible="$showPeopleModal" detents="medium,large" @dismiss="closePeople">
                <column class="w-full bg-theme-background px-5 pt-2 pb-8">
                    <row class="items-center pb-4 border-b border-theme-outline"><text class="text-[20] font-bold text-theme-on-surface">{{ ucfirst($peopleList ?? 'People') }}</text></row>
                    @forelse ($this->visiblePeople() as $person)
                        <pressable @press="openPerson({{ $person['id'] }})" class="w-full py-4 border-b border-theme-outline"><row class="items-center gap-3"><column class="w-[44] h-[44] rounded-full bg-pink-50 dark:bg-[#2B2031] items-center justify-center"><text class="font-bold text-[#F43F8C]">{{ strtoupper(substr($person['name'], 0, 1)) }}</text></column><column class="flex-1"><text class="text-[14] font-bold text-theme-on-surface">{{ $person['name'] }}</text><text class="text-[11] text-theme-on-surface-variant">{{ '@'.$person['username'] }}</text></column><icon name="chevron.right" :size="13" color="#8E8797" /></row></pressable>
                    @empty
                        <column class="items-center gap-2 py-12"><icon name="person.2" :size="24" color="#F43F8C" /><text class="text-[13] text-theme-on-surface-variant">Nobody here yet.</text></column>
                    @endforelse
                </column>
            </bottom-sheet>
        @endif
    </column>
</refreshable>
