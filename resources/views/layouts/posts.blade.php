@extends('layouts.share')
<div class="panel" id="PostId{{ $post->id }}">
    <div class="panel-heading">
        @if ($post->user)
            <img src="{{$post->user->getAvatarImagePath() }}" class="pull-left img-circle" height="45px">
            <span class="info">
                @if ($post->inGroup())
                    <a href="{{ route('profile.view', ['id' => $post->user->id]) }}" class="darker_link">
                        <b>{{ $post->user->getFullName() }}</b>
                    </a>
                    <img width="16" height="16" src="https://img.icons8.com/ios-glyphs/16/forward.png"
                        alt="forward" />

                    @foreach ($post->groups as $group)
                        <a href="{{ route('groups.show', ['id' => $group->id]) }}" class="darker_link">
                            <b>{{ $group->getName() }}</b>
                        </a>
                    @endforeach
                @else
                    <div>
                        <a href="{{ route('profile.view', ['id' => $post->user->id]) }}" class="darker_link">
                        <b>{{ $post->user->getFullName() }}</b>
                        </a>
                    </div>
                @endif
            </span>
        @endif

        @if ($post->user_id == Auth::user()->id)
            {!! Form::open(['method' => 'DELETE', 'action' => ['PostsController@destroy', $post->id]]) !!}
            <span class="info" style="color: #9d9d9d"><i><small>{{ $post->created_at->diffForHumans() }}
                        -
                        <button type="button" data-toggle="modal" data-target="#editPostModal{{ $post->id }}">
                            <i class="fa fa-pencil" aria-hidden="true"></i> Chỉnh sửa
                        </button>
                        -
                        <button type="button" data-toggle="modal" data-target="#confirmDelete"
                            data-title="Xóa bài viết" data-message="Bạn chắc chắn muốn xóa bài viết này?"><i
                                class="fa fa-trash-o" aria-hidden="true"></i> Xóa</button></small></i></span>
            {!! Form::close() !!}
        @else
            <span class="info"
                style="color: #9d9d9d"><i><small>{{ $post->created_at->diffForHumans() }}</small></i></span>
        @endif
    </div><!-- heading -->
    <div class="panel-body">
        <p class="post_content">{{ $post->body }}</p>
        @php
            $imageCount = $post->images->count();
            switch ($imageCount) {
                case 2:
                    $columnClass = 'col-md-6';
                    break;
                case 3:
                    $columnClass = 'col-md-4';
                    break;
                case 4:
                    $columnClass = 'col-md-6';
                    break;
                default:
                    $columnClass = 'col-md-12'; // Mặc định cho các trường hợp khác
                    break;
            }
        @endphp
        <div class="row">
            @foreach ($post->images as $img)
                <div class="{{ $columnClass }}">
                    <p>
                        <a href="{{ asset($post->imagePath($img)) }}" data-lightbox="PostImage{{ $post->id }}"
                           data-title="{{ $post->body }}">
                            <img class="img-responsive img-center" src="{{ asset($post->imagePath($img)) }}">
                        </a>
                    </p>
                </div>
            @endforeach
        </div>

        <hr>
        <span>
            <a class="pointer likePost" data-id="{{ $post->id }}"><i class="fa fa-thumbs-o-up"
                    aria-hidden="true"></i>
                <span id="LikeText{{ $post->id }}">
                    @if (Auth::user()->likedPost($post->id))
                        Đã thích
                    @else
                        Thích
                    @endif
                </span>
            </a>
        </span>

        <a><span class="pointer savePost" data-id="{{ $post->id }}"><i class="fa fa-floppy-o"
                    aria-hidden="true"></i>
                <span id="SaveText{{ $post->id }}">
                    @if (Auth::user()->savedPost($post->id))
                        Đã lưu
                    @else
                        Lưu
                    @endif
                </span>
            </span></a>

        <a><span class="pointer sharePost" data-id="{{ $post->id }}"><i class="fa fa-share" aria-hidden="true"></i>
                <span id="SaveText-Share {{ $post->id }}">
                        Chia sẻ
                </span>
            </span></a>

{{--        <span><i class="fa fa-commenting-o" aria-hidden="true"savedPost></i></span>--}}

        <span class="pull-right" id="PostLikes{{ $post->id }}">{{ $post->infoStatus() }}</span>
    </div><!-- body -->
    <div class="panel-footer">
        @if ($post->comments()->count())
            @foreach ($post->comments as $comment)
                @include('layouts.comments')
            @endforeach
        @endif
        <div id="newComment">
            {!! Form::open(['method' => 'POST', 'action' => ['CommentsController@store']]) !!}
            <div class="input-group">
                {!! Form::hidden('post_id', $post->id) !!}
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
    @include('layouts/edit_modal', ['post' => $post])
</div>

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            // Xử lý sự kiện cho nút share
            $('.sharePost').click(function() {
                var id = $(this).attr("data-id");

                $.ajax({
                    type: "POST",
                    url: "{{ route('share.post') }}",
                    data: {
                        id: id,
                        _token: '{{ Session::token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.reload();
                        } else {
                            alert("Lỗi không chia sẻ được.");
                        }
                    }
                });
            });
            // Xử lý sự kiện cho nút share share-post
            $('.share-sharePost').click(function() {
                var id_share = $(this).attr("data-id-share");
                var id_post = $(this).attr("data-id-post");

                $.ajax({
                    type: "POST",
                    url: "{{ route('share.sharepost') }}",
                    data: {
                        id_share: id_share,
                        _token: '{{ Session::token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            window.location.reload();
                        } else {
                            alert("Lỗi không chia sẻ được.");
                        }
                    }
                });
            });

            $('.savePost').click(function() {
                var id = $(this).attr("data-id");

                $.ajax({
                    type: "POST",
                    url: "{{ route('save.post') }}",
                    data: {
                        id: id,
                        _token: '{{ Session::token() }}'
                    },
                    success: function(response) {
                        if (response == 1) {
                            $("#SaveText" + id).text('Đã lưu');
                        } else {
                            $("#SaveText" + id).text('Lưu');
                        }
                    }
                });
            });

            // Xử lý sự kiện cho nút thích share
            $('.likeShare').click(function() {
                var id = $(this).attr("data-id");
                var url = "{{ route('share.like') }}";
                var token = '{{ Session::token() }}';
                var urlStatus = "{{ route('share.info') }}";

                $.ajax({
                    type: "POST",
                    url: url,
                    data: {
                        id: id,
                        _token: token
                    },
                    success: function(result) {
                        if (result == 1) {
                            $("#LikeShareText" + id).text('Đã thích');
                        } else {
                            $("#LikeShareText" + id).text('Thích');
                        }

                        // Cập nhật thông tin về số lượt thích
                        $.ajax({
                            type: "POST",
                            url: urlStatus,
                            data: {
                                id: id,
                                _token: token
                            },
                            success: function(result) {
                                $('#ShareLikes' + id).text(result);
                            }
                        });
                    }
                });
            });
            // Xử lý sự kiện cho nút thích
            $('.likePost').click(function() {
                var id = $(this).attr("data-id");
                var url = "{{ route('post.like') }}";
                var token = '{{ Session::token() }}';
                var urlStatus = "{{ route('post.info') }}";

                $.ajax({
                    type: "POST",
                    url: url,
                    data: {
                        id: id,
                        _token: token
                    },
                    success: function(result) {
                        if (result == 1) {
                            $("#LikeText" + id).text('Đã thích');
                        } else {
                            $("#LikeText" + id).text('Thích');
                        }

                        // Cập nhật thông tin về số lượt thích
                        $.ajax({
                            type: "POST",
                            url: urlStatus,
                            data: {
                                id: id,
                                _token: token
                            },
                            success: function(result) {
                                $('#PostLikes' + id).text(result);
                            }
                        });
                    }
                });
            });

            // Xử lý sự kiện cho nút lưu thay đổi của modal
            $('#saveEditBtn').click(function(e) {
                e.preventDefault();

                // Kiểm tra xem trường body có giá trị không
                var bodyValue = $('#editPostForm textarea[name="body"]').val();

                if (!bodyValue.trim()) {
                    // Hiển thị thông báo khi trường body trống
                    $('#editPostError').show();
                    $('#editPostError').html('Vui lòng nhập nội dung bài viết.');
                    return;
                } else {
                    // Ẩn thông báo nếu trường body có giá trị
                    $('#editPostError').hide();
                }

                var formData = new FormData($('#editPostForm')[0]);

                // Thêm CSRF token vào formData
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                // Check if there are new images; if not, append existing image paths
                if (formData.getAll('images[]').length === 0 && {!! json_encode(!empty($post->images)) !!}) {
                    @foreach ($post->images as $img)
                        formData.append('images[]', {!! json_encode($img) !!});
                    @endforeach
                }

                $.ajax({
                    type: 'POST',
                    url: '/posts/' + {{ $post->id }},
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.success) {
                            $('#editPostModal{{ $post->id }}').modal('hide');
                            // Hiển thị thông báo thành công
                            alert('Bài viết đã được cập nhật thành công!');
                            window.location.href = "{{ route('index') }}";
                        } else {
                            // Hiển thị thông báo lỗi
                            $('#editPostError').show();
                            if (response.errors.image) {
                                $('#editPostError').html(response.errors.image.join('<br>'));
                            } else {
                                console.error('Image errors are undefined in the response.');
                            }
                        }
                    }
                });
            });

        });
    </script>
@endsection
