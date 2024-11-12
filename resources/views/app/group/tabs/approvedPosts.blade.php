<div >
    <h2>Bài viết đã được chấp nhận</h2>
    @if ($approvedPosts->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Tên người dùng</th>
                    <th>Nội dung</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($approvedPosts->sortByDesc('created_at') as $post)
                    <tr>
                        <td>{{ $post->user->getFullName() }}</td>
                        <td>{{ $post->body }}</td>
                        <td>
                            <form
                                action="{{ route('groups.deletePost', ['groupId' => $group->id, 'postId' => $post->id]) }}"
                                method="POST" style="margin: 10px;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger fa fa-trash-o"style="width: 38px; height:34px; margin-left: 4px;"></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Không có bài viết nào được chấp nhận trong nhóm này.</p>
    @endif
    <h2>Bài viết chưa được chấp nhận:</h2>
    <table>
        <thead>
            <tr>
                <th>Tên Người Dùng</th>
                <th>Nội Dung Bài Đăng</th>
                <th>Thao Tác</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($unapprovedPosts->sortByDesc('created_at') as $post)
                <tr>
                    <td>{{ $post->user->getFullName() }}</td>
                    <td>{{ $post->body }}</td>
                    <td style="display: flex;">
                        <form
                            action="{{ route('groups.approvePost', ['groupId' => $group->id, 'postId' => $post->id]) }}"
                            method="POST" >
                            @csrf
                            <button type="submit" class="btn btn-success" style="width: 38px; height:34px;">
                                <img width="14" height="14" src="https://img.icons8.com/external-flat-icons-inmotus-design/24/FFFFFF/external-Accept-antivirus-flat-icons-inmotus-design.png" alt="external-Accept-antivirus-flat-icons-inmotus-design"/>
                            </button>
                        </form>
                        <form
                            action="{{ route('groups.deletePost', ['groupId' => $group->id, 'postId' => $post->id]) }}"
                            method="POST">
                            @method('DELETE')
                            @csrf
                            <button type="submit" class="btn btn-danger fa fa-trash-o" style="width: 38px; height:34px; margin-left: 4px;"></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>