@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">Roles and Permissions</div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Permissions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roless as $role)
                            <tr>
                                <td>{{ ucwords(str_replace('_', ' ', $role->name)) }}</td>
                                <td>
                                    @foreach ($role->permissions as $permission)
                                        {{ ucwords(str_replace('_', ' ', $permission->name)) }}
                                        @if (!$loop->last)
                                            ,
                                        @endif
                                    @endforeach
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <br>

        <div class="card">
            <div class="card-header">Manage Users</div>
            <div class="card-body">
                <div class="d-flex justify-content-end">
                    <a type="button" class="btn btn-primary mb-3" href="{{ route('users.create') }}">
                        Add User
                    </a>
                </div>

                <table id="example" class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $index => $user)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        {{ ucwords(str_replace('_', ' ', $role->name)) }}
                                        @if (!$loop->last)
                                            ,
                                        @endif
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-primary">Edit</a>
                                     <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this user?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const table = new DataTable('#example');

    table.on('mouseenter', 'td', function () {
        let colIdx = table.cell(this).index().column;

        table
            .cells()
            .nodes()
            .each((el) => el.classList.remove('highlight'));

        table
            .column(colIdx)
            .nodes()
            .each((el) => el.classList.add('highlight'));
    });
</script>
@endpush
