@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <h1 class="text-center">Chỉnh sửa hồ sơ của bạn</h1>
                <div class="text-center">
                    <button class="btn btn-primary" id="btnEditDetails">Chỉnh sửa thông tin người dùng</button>

                    <button class="btn btn-info" id="btnChangePassword">Đổi mật khẩu</button>
                </div>
                @include('app.profile.edit-details')
                @include('app.profile.change-password')

                {!! Form::open(['files' => 'true', 'id' => 'FormCoverProfile']) !!}
                <h3>Ảnh bìa</h3>
                <img src="{{ $user->getCoverImagePath() }}" class="img-responsive">
                <div class="alert alert-danger" id="CoverError" style="display: none" role="alert"></div>
                <p class="text-center">
                    <label for="file-upload-cover" class="pointer">
                        <i class="fa fa-cloud-upload"></i> Thay đổi ảnh bìa
                    </label>
                    <input id="file-upload-cover" name="image" type="file" style="display: none;" />
                </p>
                <div class="progress" style="display: none">
                    <div id="CoverProgressBar" class="progress-bar progress-bar-striped active" role="progressbar"
                        aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
                {!! Form::close() !!}
                {!! Form::open(['files' => 'true', 'id' => 'FormProfile']) !!}
                <h3>Ảnh đại diện</h3>
                <img src="{{ $user->getAvatarImagePath() }}" class="img-responsive">
                <div class="alert alert-danger" id="ProfileError" style="display: none" role="alert"></div>
                <p class="text-center">
                    <label for="file-upload-profile" class="pointer">
                        <i class="fa fa-cloud-upload"></i> Đổi ảnh đại diện
                    </label>
                    <input id="file-upload-profile" name="image" type="file" style="display: none;" />
                </p>
                <div class="progress" style="display: none">
                    <div id="ProfileProgressBar" class="progress-bar progress-bar-striped active" role="progressbar"
                        aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#btnChangePassword').click(function() {
                $('#changePasswordModal').modal('show');
            });

            $(document).on('submit', '#changePasswordForm', function(e) {
                e.preventDefault();

                $form = $(this);
                var formData = new FormData($form[0]);
                var url = $form.attr('action');
                var errorStatus = 'passwordError';

                uploadFile(formData, url, null, errorStatus);
            });

            function uploadFile(formData, url, progressBar, errorStatus) {
                var request = new XMLHttpRequest();

                request.onreadystatechange = function() {
                    if (request.readyState == XMLHttpRequest.DONE) {
                        var data = JSON.parse(request.responseText);

                        // Xóa thông báo lỗi cũ
                        $("#" + errorStatus).hide();
                        $("#" + errorStatus).html('');

                        if (!data.success) {
                            // Hiển thị thông báo lỗi mới
                            $("#" + errorStatus).show();
                            $("#" + errorStatus).html(data.errors.password.join('<br>'));
                        } else {
                            $("#password").val('');
                            $("#password_confirmation").val('');
                            alert('Đổi mật khẩu thành công!');
                            $('#changePasswordModal').modal('hide');
                            window.location.href = "{{ route('profile.view', ['id' => $user->id]) }}";
                        }
                    }
                }

                request.open('post', url);
                request.setRequestHeader("X-CSRF-TOKEN", '{{ csrf_token() }}');
                request.send(formData);
            }


        });

        $(document).ready(function() {

            $('#btnEditDetails').click(function() {
                $('#editDetailsModal').modal('show');
            });

            var redirectTo = "{{ route('profile.view', ['id' => $user->id]) }}";

            $(document).on('change', '#file-upload-cover', function() {

                $form = $('#FormCoverProfile');
                var formData = new FormData($form[0]);
                var url = "{{ route('profile.changeCover') }}";
                var progressBar = 'CoverProgressBar';
                var errorStatus = 'CoverError';

                uploadFile(formData, url, progressBar, errorStatus);

            });

            $(document).on('change', '#file-upload-profile', function() {

                $form = $('#FormProfile');
                var formData = new FormData($form[0]);
                var url = "{{ route('profile.changeProfile') }}";
                var progressBar = 'ProfileProgressBar';
                var errorStatus = 'ProfileError';

                uploadFile(formData, url, progressBar, errorStatus);

            });

            function uploadFile(formData, url, progressBar, errorStatus) {

                var request = new XMLHttpRequest();

                request.upload.addEventListener('progress', function(e) {

                    var percent = e.loaded / e.total * 100;
                    $('#' + progressBar).parent().show();
                    $('#' + progressBar).css('width', percent + '%').attr('aria-valuenow', percent);

                });

                request.onreadystatechange = function() {
                    if (request.readyState == XMLHttpRequest.DONE) {
                        var data = JSON.parse(request.responseText);
                        if (data.success) {
                            window.location.replace(redirectTo);
                        } else {
                            $("#" + errorStatus).show();
                            $("#" + errorStatus).html(data.errors.image);
                        }
                    }
                }

                request.open('post', url);
                request.setRequestHeader("X-CSRF-TOKEN", '{{ Session::token() }}');
                request.send(formData);
            }

        });
    </script>
@endsection
