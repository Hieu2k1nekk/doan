<div class="media">
    <div class="media-left">
        <a href="{{ route('profile.view', ['id' => $comment->user->id]) }}">
            <img src="{{ $comment->user->getAvatarImagePath() }}" class="pull-left img-circle" height="45px">
        </a>
    </div>
    <div class="media-body">
        <h4 class="media-heading" style="display: flex">
            <a class="darker_link" href="{{ route('profile.view', ['id' => $comment->user->id]) }}">
                <b>{{ $comment->user->getFullName() }}</b>
            </a>
            <i> <small style="margin-left: 4px;">- {{ $comment->created_at->diffForHumans() }} - </small></i>
            @if ($comment->canDelete($post->id))
                {!! Form::open([
                    'method' => 'DELETE',
                    'route' => ['comments.destroy', 'comment' => $comment->id],
                    'id' => 'delete-form',
                ]) !!}
                <button style="font-size: 75%; font-style: italic; background-color: transparent; " type="button" data-toggle="modal" data-target="#confirmDelete" data-title="Xóa bình luận"
                    data-message="Bạn chắc chắn muốn xóa bình luận này chứ?" class="delete-btn">
                    <i class="fa fa-trash-o" aria-hidden="true"></i> Xóa
                </button>
                {!! Form::close() !!}
            @endif
        </h4>
        <p>{{ $comment->body }}</p>


    </div>
    @error('body')
        <div class="alert alert-danger">{{ $message }}</div>
    @enderror
</div>

<!-- Modal xác nhận xóa -->
<div class="modal fade" id="confirmDelete" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmDeleteLabel">Xác Nhận Xóa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Đóng">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="delete-message"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-danger" id="confirm-delete-btn">Xóa</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Xác nhận xóa khi nút xác nhận trong modal được bấm
    document.getElementById('confirm-delete-btn').addEventListener('click', function() {
        document.getElementById('delete-form').submit(); // Gửi yêu cầu xóa bình luận
    });

    // Hiển thị nội dung thông báo xóa trong modal
    $('#confirmDelete').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var title = button.data('title');
        var message = button.data('message');
        var modal = $(this);
        modal.find('.modal-title').text(title);
        modal.find('#delete-message').text(message);
    });
</script>
