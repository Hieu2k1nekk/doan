@isset($sharedPost)
<div class="panel" id="ShareId{{ $sharedPost->id }}">
    <div class="panel-heading">
        @if ($sharedPost->post->user)
            <img src="{{$sharedPost->sharedBy->getAvatarImagePath() }}" class="pull-left img-circle" height="45px">
            <span class="info">
                @if ($sharedPost->post->inGroup())
                    <a href="{{ route('profile.view', ['id' => $sharedPost->post->user->id]) }}" class="darker_link">
                        <b>{{ $sharedPost->post->user->getFullName() }}</b>
                    </a>
                    <img width="16" height="16" src="https://img.icons8.com/ios-glyphs/16/forward.png"
                        alt="forward" />
                    @foreach ($sharedPost->post->groups as $group)
                        <a href="{{ route('groups.show', ['id' => $group->id]) }}" class="darker_link">
                            <b>{{ $group->getName() }}</b>
                        </a>
                    @endforeach
                @else
                    <div>
                        @php

                            if (isset($sharedByFullName, $sharedId)) {
                                echo "<a href=\"" . route('profile.view', ['id' => $sharedId]) . "\" class=\"darker_link\">";
                                echo "<b>{$sharedByFullName}</b>";
                                echo "</a>";
                                echo " đã chia sẻ bài viết của";
                            }
                        @endphp
                        <a href="{{ route('profile.view', ['id' => $sharedPost->post->user->id]) }}" class="darker_link">
                        <b>{{ $sharedPost->post->user->getFullName() }}</b>
                        </a>
                    </div>
                @endif
            </span>
        @endif

        @if ($sharedPost->user_id_share == Auth::user()->id)
            {!! Form::open(['method' => 'DELETE', 'action' => ['ShareController@destroy', $sharedPost->id]]) !!}
            <span class="info" style="color: #9d9d9d"><i><small>{{ $sharedPost->created_at->diffForHumans() }}
                        -
                        <button type="button" data-toggle="modal" data-target="#confirmDelete"
                            data-title="Xóa bài viết" data-message="Bạn chắc chắn muốn xóa bài viết này?"><i
                                class="fa fa-trash-o" aria-hidden="true"></i> Xóa</button></small></i></span>
            {!! Form::close() !!}
        @else
            <span class="info"
                style="color: #9d9d9d"><i><small>{{ $sharedPost->created_at->diffForHumans() }}</small></i></span>
        @endif
    </div><!-- heading -->
    <div class="panel-body">
        <p class="post_content">{{ $sharedPost->post->body }}</p>
        @php
            $imageCount = $sharedPost->post->images->count();
            switch ($imageCount) {
                case 2:
                    $columnClass = 'col-md-6';
                    break;
                case 3:
                    $columnClass = 'col-md-4';
                    break;
                case 4:
                    $columnClass = 'col-md-3';
                    break;
                default:
                    $columnClass = 'col-md-12'; // Mặc định cho các trường hợp khác
                    break;
            }
        @endphp

        @foreach ($sharedPost->post->images as $img)
            <div class="{{ $columnClass }}">
                <p>
                    <a href="{{ asset($sharedPost->post->imagePath($img)) }}" data-lightbox="PostImage{{ $sharedPost->post->id }}" data-title="{{ $sharedPost->post->body }}">
                        <img class="img-responsive img-center" src="{{ asset($sharedPost->post->imagePath($img)) }}">
                    </a>
                </p>
            </div>
        @endforeach

        <hr>
        <span>
            <a class="pointer likeShare" data-id="{{ $sharedPost->id }}"><i class="fa fa-thumbs-o-up"
                    aria-hidden="true"></i>
                <span id="LikeShareText{{ $sharedPost->id }}">
                    @if (Auth::user()->likedShare($sharedPost->id))
                        Đã thích
                    @else
                        Thích
                    @endif
                </span>
            </a>
        </span>

        <span><i class="fa fa-commenting-o" aria-hidden="true"savedPost></i> Bình luận</span>
        <a><span class="pointer share-sharePost" data-id-share="{{ $sharedPost->id }}" data-id-post="{{ $sharedPost->post_id }}"><i class="fa fa-share" aria-hidden="true"></i>
                <span id="SaveText-Share {{$sharedPost->id }}">
                        Chia sẻ
                </span>
            </span></a>

        <span class="pull-right" id="ShareLikes{{ $sharedPost->id }}">{{ $sharedPost->infoStatusShare() }}</span>
    </div><!-- body -->
    <div class="panel-footer">
        @if ($sharedPost->commentsShares()->count())
            @foreach ($sharedPost->commentsShares as $comment)
                @include('layouts.comments_shares')
            @endforeach
        @endif
        <div id="newComment">
            {!! Form::open(['method' => 'POST', 'action' => ['CommentsSharesController@store']]) !!}
            <div class="input-group">
                {!! Form::hidden('share_id', $sharedPost->id) !!}
                {!! Form::text('body', null, [
                    'class' => 'form-control',
                    'required' => 'required',
                    'placeholder' => 'Viết bình luận của bạn..',
                ]) !!}
                <span class="input-group-btn">
                    {{ Form::button('<i class="fa fa-location-arrow" aria-hidden="true"></i> Bình luận', ['class' => 'btn btn-signature', 'type' => 'submit']) }}
                </span>
            </div><!-- /input-group -->
            @error('body')
                <div class="alert alert-danger">{{ $message }}</div>
            @enderror
            {!! Form::close() !!}
        </div>
    </div><!-- Footer -->
</div>
@endisset
