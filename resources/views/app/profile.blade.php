@extends('layouts.app')

@section('content')

    <div class="container-fluid" id="Profile">
        <div class="row">
            @include('layouts.cover', ['active' => 'timeline'])
            <div class="col-md-8 col-md-offset-2" style="padding:0">
                <div class="col-md-8" style="padding:0">
                    <div class="posts">
                        @php
                            $posts = $user->posts()->orderByDesc('created_at')->get();
                            $shares = Auth::user()->shares()->orderByDesc('created_at')->get();

                            $allPosts = $posts->concat($shares)->sortByDesc('created_at')->filter(function($item) {
                                return $item instanceof \App\Post;
                            });
                        @endphp

                        @if ($allPosts->count() > 0)
                            @foreach ($allPosts as $post)
                                @if ($post->user_id === $user->id)
                                    @include('layouts.posts')
                                @endif
                            @endforeach
                            @if (Auth::user()->id === $user->id)

                                @foreach (Auth::user()->shares->sortByDesc('post_id') as $sharedPost)
                                    @if ($sharedPost->user_id_share === Auth::user()->id)
                                        @php
                                            $sharedByFullName = $sharedPost->sharedBy->getFullName();
                                            $sharedId = $sharedPost->sharedBy->id;
                                        @endphp
                                        @include('layouts.share', ['sharedPost' => $sharedPost, 'sharedByFullName' => $sharedByFullName])
                                    @endif
                                @endforeach
                            @else
                                @foreach ($user->shares->sortByDesc('post_id') as $sharedPost)
                                    @if ($sharedPost->user_id_share === $user->id)
                                        @php
                                            $sharedByFullName = $sharedPost->sharedBy->getFullName();
                                            $sharedId = $sharedPost->sharedBy->id;
                                        @endphp
                                        @include('layouts.share', ['sharedPost' => $sharedPost, 'sharedByFullName' => $sharedByFullName])
                                    @endif
                                @endforeach
                            @endif
                        @else
                            <p>{{ $user->getFullName() }} chưa có bài viết nào.</p>
                        @endif
                    </div>
                </div>

{{--                <div class="col-md-8" style="padding:0">--}}
{{--                    <div class="posts">--}}
{{--						@if ($user->posts()->count())--}}
{{--								@foreach ($user->posts as $post)--}}
{{--									@include('layouts.posts')--}}
{{--								@endforeach--}}
{{--						@else--}}
{{--							<p>{{ $user->getFullName() }} chưa có bài viết nào.</p>--}}
{{--						@endif--}}
{{--							@if (Auth::user()->id === $user->id)--}}
{{--								--}}{{-- Nếu đang xem profile của chính người dùng --}}
{{--								@foreach (Auth::user()->shares->sortByDesc('post_id') as $sharedPost)--}}
{{--									@if ($sharedPost->user_id_share === Auth::user()->id)--}}
{{--										@php--}}
{{--											$sharedByFullName = $sharedPost->sharedBy->getFullName();--}}
{{--                                            $sharedId = $sharedPost->sharedBy->id;--}}
{{--										@endphp--}}
{{--										@include('layouts.share', ['sharedPost' => $sharedPost, 'sharedByFullName' => $sharedByFullName])--}}
{{--									@endif--}}
{{--								@endforeach--}}
{{--							@else--}}
{{--								--}}{{-- Hiển thị nội dung chia sẻ của người chia sẻ --}}
{{--                                    @foreach ($user->shares->sortByDesc('post_id') as $sharedPost)--}}
{{--									@if ($sharedPost->user_id_share === $user->id)--}}
{{--										@php--}}
{{--											$sharedByFullName = $sharedPost->sharedBy->getFullName();--}}
{{--                                            $sharedId = $sharedPost->sharedBy->id;--}}
{{--										@endphp--}}
{{--										@include('layouts.share', ['sharedPost' => $sharedPost, 'sharedByFullName' => $sharedByFullName])--}}
{{--									@endif--}}
{{--								@endforeach--}}
{{--							@endif--}}


{{--					</div>--}}
{{--                </div>--}}


                <div class="col-md-4 sidebar" style="padding-left:20px; padding-right: 0;">
                    @if ($user->id !== Auth::user()->id)
                        <div id="friendStatusDiv">
                            @include('layouts.friend_status', ['user' => $user, 'profileView' => 'true'])
                        </div>
                    @endif
                    <div class="panel panel-default">
                        <div class="panel-heading" style=" display: flex;">
                            <i style="padding-right: 10px; font-size: 20px;" class="fas fa-info-circle"></i><strong>Thông
                                tin cá nhân</strong>
                        </div>
                        <div class="panel-body" style="display: inline-block; width: 100%;">
                            <p style="text-align: center;"> {{ $user->description }}</p>
                            <p><i style="padding-right: 16px; font-size: 20px;"
                                  class="fas fa-map-marker-alt icon-spacing"></i>Sống tại
                                <strong>{{ $user->address }}</strong></p>
                            <p><strong><i style="padding-right: 16px; font-size: 15px;"
                                          class="fas fa-envelope icon-spacing"></i>Email:</strong> {{ $user->email }}
                            </p>
                            <p><strong><i style="padding-right: 16px; font-size: 18px;"
                                          class="fas fa-birthday-cake icon-spacing"></i>Ngày
                                    sinh:</strong> {{ \Carbon\Carbon::parse($user->birthday)->format('d - m - Y') }}</p>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Bạn bè
                        </div>
                        <div class="panel-body">
                            @foreach ($user->friends() as $friend)
                                <a href="{{ route('profile.view', ['id' => $friend->id]) }}"><img
                                            src="{{ $friend->getAvatarImagePath() }}"
                                            title="{{ $friend->getFullName() }}" height="50px"
                                            style="margin-bottom:3px;"></a>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="col-md-4 sidebar" style="padding-left:20px; padding-right: 0;">

                </div>
            </div>
        </div>
    </div>

@stop

@section('scripts')

    <script type="text/javascript">
        function friendEvents() {
            $('.addFriend').click(function () {
                var id = $(this).attr('data-id');

                $.ajax({
                    type: "POST",
                    url: "{{ route('friend.add') }}",
                    data: {id: id, _token: '{{ Session::token() }}'},
                    success: function (response) {
                        location.reload();
                    }
                });
            });

            $('.cancelFriend').click(function () {
                var id = $(this).attr('data-id');

                $.ajax({
                    type: "POST",
                    url: "{{ route('friend.cancel') }}",
                    data: {id: id, _token: '{{ Session::token() }}'},
                    success: function (response) {
                        location.reload();
                    }
                });
            });

            $('.removeFriend').click(function () {
                var id = $(this).attr('data-id');

                $.ajax({
                    type: "POST",
                    url: "{{ route('friend.remove') }}",
                    data: {id: id, _token: '{{ Session::token() }}'},
                    success: function (response) {
                        location.reload();
                    }
                });
            });

            $('.acceptFriend').click(function () {
                var id = $(this).attr('data-id');

                $.ajax({
                    type: "POST",
                    url: "{{ route('friend.accept') }}",
                    data: {id: id, _token: '{{ Session::token() }}'},
                    success: function (response) {
                        location.reload();
                    }
                });
            });
        }

        friendEvents();
    </script>

@append