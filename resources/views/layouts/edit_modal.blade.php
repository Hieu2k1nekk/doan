<div class="modal fade" id="editPostModal{{ $post->id }}" tabindex="-1" role="dialog"
    aria-labelledby="editPostModalLabel{{ $post->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div style="width: 100%; display: flex; justify-content: space-between; padding:20px; border:1px;">
                <h3 class="modal-title" id="editPostModalLabel{{ $post->id }}" style="text-align:center;">Chỉnh sửa
                    bài viết</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {!! Form::model($post, [
                    'method' => 'PATCH',
                    'action' => ['PostsController@update', $post->id],
                    'files' => true,
                    'id' => 'editPostForm',
                ]) !!}
                <!-- ... Các trường và nút submit ... -->
                <div class="form-group">
                    {!! Form::label('body', 'Nội dung bài viết') !!}
                    {!! Form::textarea('body', null, ['class' => 'form-control', 'rows' => 3]) !!}
                </div>

                @foreach ($post->images as $img)
                    <p>
                        <a href="{{ asset($post->imagePath($img)) }}" data-lightbox="PostImage{{ $post->id }}"
                            data-title="{{ $post->body }}">
                            <img class="img-responsive img-center" src="{{ asset($post->imagePath($img)) }}">
                        </a>
                    </p>
                @endforeach

                <div class="form-group">
                    {!! Form::label('image', 'Hình ảnh') !!}
                    {!! Form::file('images[]', ['class' => 'form-control', 'id' => 'imageInput', 'multiple' => true]) !!}

                </div>
                <div id="editPostError" class="alert alert-danger" style="display: none;"></div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    {!! Form::submit('Lưu thay đổi', ['class' => 'btn btn-primary', 'id' => 'saveEditBtn']) !!}
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

