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
        <div class="card-header">Create User</div>
        <div class="card-body">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="form-group">
                    <label for="name">Nama:</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}">
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" name="password" id="password" class="form-control">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password:</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>
                <div class="form-group">
                    <label for="roles">Roles:</label>
                    <select name="roles[]" id="roles" class="form-control js-example-basic-multiple" multiple="multiple">
                        @foreach ($roles as $key => $value)
                            <option value="{{ $value }}"
                                    @if (in_array($value, old('roles', []))) selected @endif>
                                {{ ucwords(str_replace('_', ' ', $value)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <br>
                <button type="submit" class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.js-example-basic-multiple').select2();
    });
</script>
@endpush
