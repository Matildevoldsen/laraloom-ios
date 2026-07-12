<scroll-view class="w-full h-full bg-theme-background">
    <column class="w-full pb-12">
        @if ($error !== '')
            <column class="mx-4 mt-3 p-3 rounded-xl bg-pink-50 dark:bg-[#351927]"><text class="text-[12] text-pink-800 dark:text-[#FBCFE8]">{{ $error }}</text></column>
        @endif

        @if ($post !== [])
            <column class="w-full px-5 pt-4 pb-3 border-b border-theme-outline">
                @if (isset($post['author']['username']))
                    <pressable @press="openProfile" class="w-full mb-3">
                        <row class="items-center gap-3">
                            <column class="w-[44] h-[44] rounded-full bg-[#F43F8C] items-center justify-center"><text class="text-[17] font-bold text-white">{{ strtoupper(substr($post['author']['name'], 0, 1)) }}</text></column>
                            <column class="flex-1 gap-0"><text class="text-[14] font-bold text-theme-on-surface">{{ $post['author']['name'] }}</text><text class="text-[12] text-theme-on-surface-variant">{{ '@'.$post['author']['username'] }}</text></column>
                            <button class="glass:clear:interactive" size="xs" icon="person" @press="openProfile">Profile</button>
                        </row>
                    </pressable>
                @else
                    <row class="items-center gap-3 mb-3"><column class="w-[44] h-[44] rounded-full bg-pink-50 dark:bg-[#2B2031] items-center justify-center"><icon name="sparkles" :size="18" color="#F43F8C" /></column><column class="flex-1"><text class="text-[14] font-bold text-theme-on-surface">{{ $post['source']['name'] ?? 'Laraloom' }}</text><text class="text-[11] text-theme-on-surface-variant">Curated Laravel signal</text></column></row>
                @endif

                @if (! empty($post['title']))<text class="text-[20] font-bold leading-[25] text-theme-on-surface">{{ $post['title'] }}</text>@endif
                @if (! empty($post['body']))
                    <text class="mt-2 text-[15] leading-[21] text-theme-on-surface">{{ $post['body'] }}</text>
                @elseif (! empty($post['summary']))
                    <text class="mt-2 text-[15] leading-[21] text-theme-on-surface">{{ $post['summary'] }}</text>
                @endif

                @if (! empty($post['attachments']))
                    <column class="w-full gap-2 mt-3">
                        @foreach ($post['attachments'] as $attachmentIndex => $attachment)
                            <list-item
                                headline="{{ $attachment['type'] === 'video' ? 'Video attachment' : 'Photo attachment' }}"
                                supporting="Tap to view full size"
                                @if ($attachment['type'] === 'image') :leadingImage="$attachment['url']" @else leadingIcon="play.rectangle" @endif
                                @press="openMedia({{ $attachmentIndex }})"
                            />
                        @endforeach
                    </column>
                @endif

                @if (! empty($post['tags']))
                    <row class="items-center gap-2 mt-3">@foreach (array_slice($post['tags'], 0, 4) as $tag)<text class="text-[11] text-[#F43F8C]">#{{ $tag }}</text>@endforeach</row>
                @endif

                <row class="items-center gap-2 mt-4 pb-3 border-b border-theme-outline"><text class="text-[11] text-theme-on-surface-variant">{{ ucfirst($post['kind']) }}</text>@if (! empty($post['source']['name']))<text class="text-[11] text-theme-on-surface-variant">· {{ $post['source']['name'] }}</text>@endif</row>

                <row class="w-full items-center justify-between pt-2">
                    <row class="items-center gap-1 px-2 py-2"><icon name="bubble.left" :size="18" color="#8E8797" /><text class="text-[11] text-theme-on-surface-variant">{{ $post['counts']['comments'] ?? 0 }}</text></row>
                    <pressable @press="toggleRepost" class="px-3 py-2 rounded-full"><row class="items-center gap-1"><icon name="repeat" :size="18" color="{{ ($post['is_reposted'] ?? false) ? '#10B981' : '#8E8797' }}" /><text class="text-[11] text-theme-on-surface-variant">{{ $post['counts']['reposts'] ?? 0 }}</text></row></pressable>
                    <pressable @press="toggleReaction" class="px-3 py-2 rounded-full"><row class="items-center gap-1"><icon name="heart{{ ($post['is_reacted'] ?? false) ? '.fill' : '' }}" :size="18" color="{{ ($post['is_reacted'] ?? false) ? '#F43F8C' : '#8E8797' }}" /><text class="text-[11] text-theme-on-surface-variant">{{ $post['counts']['reactions'] ?? 0 }}</text></row></pressable>
                    <pressable @press="toggleBookmark" class="px-3 py-2 rounded-full"><icon name="bookmark{{ ($post['is_bookmarked'] ?? false) ? '.fill' : '' }}" :size="18" color="{{ ($post['is_bookmarked'] ?? false) ? '#F43F8C' : '#8E8797' }}" /></pressable>
                    @if (! empty($post['url']))<pressable @press="openSource" class="pl-3 pr-2 py-2 rounded-full"><icon name="square.and.arrow.up" :size="18" color="#8E8797" /></pressable>@endif
                </row>
            </column>

            <column class="w-full px-4 py-3 gap-2 border-b border-theme-outline">
                @if ($parentId !== null)<row class="items-center gap-2 pl-[45]"><text class="flex-1 text-[11] text-[#F43F8C]">Replying to {{ '@'.$replyingTo }}</text><pressable @press="cancelReply" class="px-2 py-1"><text class="text-[11] font-semibold text-theme-on-surface-variant">Cancel</text></pressable></row>@endif
                <row class="items-end gap-2">
                    <column class="w-[38] h-[38] rounded-full bg-pink-50 dark:bg-[#2B2031] items-center justify-center"><icon name="person.fill" :size="16" color="#F43F8C" /></column>
                    <bare-text-input native:model.debounce.200ms="replyBody" placeholder="Post your reply" :multiline="true" :minLines="1" :maxLines="5" :maxLength="1000" :keepFocusOnSubmit="true" @submit="submitReply" class="flex-1 min-h-[42] rounded-[21] bg-theme-surface-variant px-4 py-3 text-theme-on-surface" />
                    <button class="glass:prominent:interactive" variant="accent" size="sm" icon="arrow.up" a11y-label="Post reply" :loading="$isSubmitting" :disabled="$isSubmitting" @press="submitReply" />
                </row>
                @if ($replyBody !== '')<text class="pr-[48] text-right text-[10] text-theme-on-surface-variant">{{ mb_strlen($replyBody) }}/1000</text>@endif
            </column>

            <column class="w-full">
                @forelse ($comments as $comment)
                    <column class="w-full px-5 py-4 border-b border-theme-outline" key="comment-{{ $comment['id'] }}">
                        <row class="items-start gap-3">
                            <column class="items-center self-stretch"><pressable @press="openProfile({{ $comment['id'] }})" class="w-[40] h-[40] rounded-full bg-pink-50 dark:bg-[#2B2031] items-center justify-center"><text class="font-bold text-[#F43F8C]">{{ strtoupper(substr($comment['author']['name'] ?? 'L', 0, 1)) }}</text></pressable>@if ($comment['parent_id'])<column class="mt-2 w-[2] flex-1 bg-theme-outline" />@endif</column>
                            <column class="flex-1 gap-1">
                                <row class="items-center gap-1"><pressable @press="openProfile({{ $comment['id'] }})"><text class="text-[13] font-bold text-theme-on-surface">{{ $comment['author']['name'] ?? 'Laraloom member' }}</text></pressable><text class="flex-1 text-[11] text-theme-on-surface-variant" :maxLines="1">{{ '@'.($comment['author']['username'] ?? 'member') }}</text><pressable :menu="$this->commentMenu($comment)" a11y-label="Reply actions" class="w-[28] h-[28] items-center justify-center"><icon name="ellipsis" :size="15" color="#8E8797" /></pressable></row>
                                <text class="text-[14] leading-[20] text-theme-on-surface">{{ $comment['body'] }}</text>
                                <row class="items-center gap-6 pt-2"><pressable @press="replyTo({{ $comment['id'] }})"><icon name="bubble.left" :size="15" color="#8E8797" /></pressable><icon name="heart" :size="15" color="#8E8797" /></row>
                            </column>
                        </row>
                    </column>
                @empty
                    <column class="items-center gap-2 px-8 py-9"><text class="text-[16] font-bold text-theme-on-surface">Start the conversation</text><text class="text-[12] text-theme-on-surface-variant">Be the first to reply.</text></column>
                @endforelse
            </column>
        @endif
    </column>
</scroll-view>
