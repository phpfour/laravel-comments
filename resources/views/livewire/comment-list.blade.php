<div @comment-created.window="$wire.$refresh" class="space-y-8">
    <div class="text-lg font-bold">{{ __('Comments') }} ({{ $total }})</div>
    @if ($comments->isNotEmpty())
        @foreach ($comments as $comment)
            <div
                x-ref="comment{{ $comment->getKey() }}"
                wire:key="{{ $comment->getKey() }}"
                class="flex gap-x-2 sm:gap-x-4"
            >
                <div class="basis-14">
                    <a href="{{ $comment->owner_photo_url }}" target="_blank">
                        <img
                            class="h-12 w-12 rounded-full border border-gray-200"
                            src="{{ $comment->owner_photo_url }}"
                            alt="{{ $comment->owner_name }}"
                        />
                    </a>
                </div>
                <div
                    wire:ignore
                    x-data="{ showUpdateForm: false }"
                    @comment-update-discarded.window="(e) => {
                             if(e.detail.commentId === @js($comment->getKey())) {
                                   showUpdateForm = false;
                             }
                        }"
                    class="basis-full"
                >
                    <div x-show="!showUpdateForm" x-transition class="rounded border">
                        <div class="mb-2 flex items-center justify-between space-x-4 border-b bg-gray-100 p-1">
                            <div class="space-x-4">
                                <span class="font-bold">
                                    {{ $guestMode ? $comment->guest_name : $comment->commenter->name }}
                                </span>
                                <span
                                    x-text="moment(@js($comment->created_at)).format('YYYY/M/D H:mm')"
                                    class="text-xs"
                                ></span>
                            </div>
                            @if ($model->canCreateComment($comment))
                                <div @click="showUpdateForm = !showUpdateForm">
                                    <x-comments::action class="text-sm">Edit</x-comments::action>
                                </div>
                            @endif
                        </div>
                        <div
                            x-ref="text"
                            @comment-updated.window="(e) => {
                                let key = @js($comment->getKey());
                                if(e.detail.commentId === key) {
                                    if(@js($model->approvalRequired())) {
                                        let elm = 'comment'+ key;
                                         setTimeout(() => {
                                           $refs[elm].remove();
                                         }, 2000);
                                        return;
                                    }
                                    $refs.text.innerHTML = e.detail.text;
                                    showUpdateForm = false;
                                }
                            }"
                            class="p-1"
                        >
                            {!! $comment->text !!}
                        </div>
                    </div>

                    <div wire:ignore x-show="!showUpdateForm" class="mt-2">
                        <livewire:comments-reactions-manager :key="$comment->getKey()" :comment="$comment" />
                    </div>

                    <div x-show="showUpdateForm" x-transition class="basis-full">
                        @if ($model->canCreateComment($comment))
                            <livewire-comments-update-form
                                :comment="$comment"
                                :model="$model"
                                :key="$comment->getKey()"
                            />
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="text-lg">{{ __('Be the first one to make the comment !') }}</div>
    @endif

    @if ($comments->isNotEmpty())
        <div class="flex items-center justify-center">
            @if ($limit < $total)
                <x-comments::button wire:click="paginate" type="button" loadingTarget="paginate">
                    {{ __('Load More') }}
                </x-comments::button>
            @else
                <div class="font-bold">{{ __('End of comments') }}</div>
            @endif
        </div>
    @endif


    @script
    <script>
        const highlightSyntax = () => {
            document.querySelectorAll('.ql-code-block').forEach((el) => {
                el.removeAttribute('data-highlighted')
                window.hljs.highlightElement(el);
            }, {once: true});
        }

        highlightSyntax();

        $wire.on('comment-updated', () => {
            setTimeout(() => {
                highlightSyntax();

            }, 500)
        });
    </script>
    @endscript
</div>
