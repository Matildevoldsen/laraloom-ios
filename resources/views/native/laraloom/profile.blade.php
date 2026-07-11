<refreshable @refresh="refresh" class="w-full h-full bg-theme-background">
    <column class="w-full gap-5 px-4 pt-5 pb-10">
        @if ($error !== '')
            <column class="p-4 rounded-2xl bg-pink-50 dark:bg-[#351927] border border-pink-200 dark:border-[#6B2347]">
                <text class="text-[13] text-pink-800 dark:text-[#FBCFE8]">{{ $error }}</text>
            </column>
        @endif

        @if ($user === [])
            <column class="w-full items-center gap-5 px-6 py-10 rounded-3xl bg-theme-surface border border-theme-outline">
                <column class="w-[72] h-[72] rounded-full bg-pink-50 dark:bg-[#372239] items-center justify-center">
                    <icon name="person.crop.circle" :size="38" color="#F472B6" />
                </column>
                <column class="items-center gap-2">
                    <text class="text-[22] font-bold text-theme-on-surface">Make Laraloom yours</text>
                    <text class="text-[13] text-theme-on-surface-variant text-center">Follow builders, react to useful finds, bookmark ideas and share what you ship.</text>
                </column>
                <button class="glass:prominent:interactive" variant="accent" size="lg" @press="signIn">Sign in securely</button>
                <button class="glass:clear:interactive" size="md" @press="createAccount">Create an account</button>
                <row class="items-center gap-2">
                    <icon name="lock.shield" :size="14" color="#86EFAC" />
                    <text class="text-[11] text-theme-on-surface-variant">Your token stays in secure device storage</text>
                </row>
            </column>
        @else
            <column class="w-full gap-5 p-5 rounded-3xl bg-theme-surface border border-theme-outline">
                <row class="items-center gap-4">
                    <column class="w-[72] h-[72] rounded-full bg-pink-50 dark:bg-[#372239] items-center justify-center">
                        <text class="text-[26] font-bold text-[#F43F8C]">{{ strtoupper(substr($user['name'], 0, 1)) }}</text>
                    </column>
                    <column class="flex-1 gap-1">
                        <text class="text-[22] font-bold text-theme-on-surface">{{ $user['name'] }}</text>
                        <text class="text-[13] text-[#F472B6]">{{ '@'.$user['username'] }}</text>
                        <text class="text-[12] text-theme-on-surface-variant">{{ $user['headline'] }}</text>
                    </column>
                </row>

                @if (! empty($user['bio']))
                    <text class="text-[14] text-theme-on-surface-variant">{{ $user['bio'] }}</text>
                @endif

                <row class="w-full justify-between gap-2">
                    @foreach (['posts' => 'Posts', 'projects' => 'Projects', 'followers' => 'Followers'] as $key => $label)
                        <column class="flex-1 items-center gap-1 p-3 rounded-2xl bg-theme-surface-variant">
                            <text class="text-[19] font-bold text-theme-on-surface">{{ $user['counts'][$key] ?? 0 }}</text>
                            <text class="text-[10] text-theme-on-surface-variant">{{ $label }}</text>
                        </column>
                    @endforeach
                </row>

                <row class="items-center gap-2">
                    @foreach (array_slice($user['stack'] ?? [], 0, 4) as $technology)
                        <text class="px-2 py-1 rounded-full text-[10] text-violet-700 dark:text-[#C4B5FD] bg-violet-50 dark:bg-[#28203A]">{{ $technology }}</text>
                    @endforeach
                </row>

                @if ($user['is_admin'] ?? false)
                    <button class="glass:prominent:interactive" variant="accent" size="md" icon="checkmark.shield.fill" @press="openAdmin">Open admin tools</button>
                @endif

                <row class="items-center justify-center gap-5">
                    <pressable @press="signOut" class="py-2"><text class="text-[12] font-semibold text-theme-on-surface-variant">Sign out</text></pressable>
                    <pressable @press="openDeleteAccount" class="py-2"><text class="text-[12] font-semibold text-red-500">Delete account</text></pressable>
                </row>
            </column>

            <column class="w-full gap-3">
                <row class="items-center justify-between">
                    <text class="text-[17] font-bold text-theme-on-surface">Your posts</text>
                    <text class="text-[11] text-theme-on-surface-variant">{{ count($posts) }} total</text>
                </row>
                @forelse ($posts as $post)
                    <pressable :menu="$this->postMenu($post)" @press="editPost({{ $post['id'] }})" class="w-full p-3 gap-2 rounded-[18] bg-theme-surface border border-theme-outline" key="profile-post-{{ $post['id'] }}">
                        <row class="items-start gap-2">
                            <text class="flex-1 text-[15] font-bold text-theme-on-surface" :maxLines="2">{{ $post['title'] ?: 'Community note' }}</text>
                            <text class="px-2 py-1 rounded-full text-[9] font-semibold text-[#F43F8C] bg-pink-50 dark:bg-[#351927]">{{ strtoupper($post['status']) }}</text>
                            <pressable :menu="$this->postMenu($post)" a11y-label="Post actions" class="w-[28] h-[28] rounded-full items-center justify-center"><icon name="ellipsis" :size="17" color="#8E8797" /></pressable>
                        </row>
                        <text class="text-[12] text-theme-on-surface-variant" :maxLines="2">{{ $post['body'] ?: $post['summary'] }}</text>
                    </pressable>
                @empty
                    <column class="w-full items-center gap-2 p-8 rounded-3xl bg-theme-surface border border-theme-outline">
                        <icon name="square.and.pencil" :size="24" color="#F43F8C" />
                        <text class="text-[13] text-theme-on-surface-variant">Your first useful post starts here.</text>
                    </column>
                @endforelse
            </column>
        @endif
    </column>
</refreshable>
