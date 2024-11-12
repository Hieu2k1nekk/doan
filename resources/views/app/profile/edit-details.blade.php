<div class="modal fade" id="editDetailsModal" tabindex="-1" role="dialog" aria-labelledby="editDetailsModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="editDetailsModalLabel">Chỉnh sửa thông tin người dùng</h4>
            </div>
            <div class="modal-body">
                {!! Form::model($user, ['route' => ['profile.update_info', $user->id], 'method' => 'POST', 'files' => true]) !!}
                <div class="form-group">
                    {!! Form::label('first_name', 'Tên:') !!}
                    {!! Form::text('first_name', $user->first_name, ['class' => 'form-control', 'required']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('last_name', 'Họ:') !!}
                    {!! Form::text('last_name', $user->last_name, ['class' => 'form-control', 'required']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('email', 'Email:') !!}
                    {!! Form::email('email', $user->email, ['class' => 'form-control', 'readonly' => 'readonly', 'required']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('description', 'Mô tả bản thân:') !!}
                    {!! Form::textarea('description', $user->description, ['class' => 'form-control', 'rows' => 3]) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('address', 'Địa chỉ:') !!}
                    {!! Form::text('address', $user->address, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('birthday', 'Ngày sinh:') !!}
                    {!! Form::date('birthday', $user->birthday, ['class' => 'form-control']) !!}
                </div>
                <div class="form-group">
                    {!! Form::submit('Lưu thông tin', ['class' => 'btn btn-primary']) !!}
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>