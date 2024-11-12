<!-- Mã HTML và Blade của bạn -->

<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel">
    <div class="modal-dialog" role="document">  
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="changePasswordModalLabel">Đổi mật khẩu</h4>
            </div>
            <div class="modal-body">
                {!! Form::open(['route' => ['profile.changePassword', $user->id], 'method' => 'POST', 'id' => 'changePasswordForm']) !!}
                    <div class="form-group">
                        {!! Form::label('password', 'Mật khẩu mới:') !!}
                        {!! Form::password('password', ['class' => 'form-control', 'required', 'placeholder' => 'Mật khẩu mới']) !!}
                    </div>
                    
                    <div class="form-group">
                        {!! Form::label('password_confirmation', 'Xác nhận mật khẩu mới:') !!}
                        {!! Form::password('password_confirmation', ['class' => 'form-control', 'required', 'placeholder' => 'Xác nhận mật khẩu mới']) !!}
                    </div>

                    <div id="password_confirmationError" class="alert alert-danger" style="display: none;"></div>

                    <div class="form-group">
                        {!! Form::submit('Đổi mật khẩu', ['class' => 'btn btn-primary']) !!}
                    </div>
                    <div id="passwordError" class="alert alert-danger" style="display: none;"></div>

                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
