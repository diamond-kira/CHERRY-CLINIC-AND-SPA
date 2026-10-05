@extends('layouts.portal', ['title' => 'Users'])

@section('content')
<div class="cz-page-head"><div><p class="cz-eyebrow">Administration</p><h1 class="cz-page-title">Users</h1><p class="cz-page-intro">Account directory. Patient and staff profiles remain in their own workspaces.</p></div></div>
<div class="cz-table-wrap"><table class="cz-table"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Created</th></tr></thead><tbody>
@forelse ($users as $user)
<tr><td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->role?->name }}</td><td>{{ $user->created_at?->format('M j, Y') }}</td></tr>
@empty
<tr><td class="cz-empty" colspan="4">No users found.</td></tr>
@endforelse
</tbody></table></div>
<div class="mt-5">{{ $users->links() }}</div>
@endsection