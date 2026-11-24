<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $role = $request->query('role');

        $users = User::query()
            ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_STAFF])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($userQuery) use ($search) {
                    $userQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(in_array($role, [User::ROLE_ADMIN, User::ROLE_STAFF], true), fn ($query) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request, AuditLogger $auditLogger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_STAFF])],
            'is_active' => ['required', 'boolean'],
        ]);

        $user = User::create($data);

        $auditLogger->record(
            $request->user(),
            'user.created',
            $user,
            'Created '.$user->role.' account '.$user->email.'.',
            $request,
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Account created successfully.');
    }

    public function edit(User $user): View
    {
        abort_unless(in_array($user->role, [User::ROLE_ADMIN, User::ROLE_STAFF], true), 404);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless(in_array($user->role, [User::ROLE_ADMIN, User::ROLE_STAFF], true), 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'password' => ['nullable', 'string', 'min:10', 'confirmed'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_STAFF])],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->id === $request->user()->id && (
            $data['role'] !== $user->role || ! (bool) $data['is_active']
        )) {
            return back()
                ->withErrors(['role' => 'You cannot change your own role or deactivate your own account.'])
                ->withInput();
        }

        $wouldRemoveLastAdmin = $user->role === User::ROLE_ADMIN
            && $user->is_active
            && ($data['role'] !== User::ROLE_ADMIN || ! (bool) $data['is_active'])
            && User::where('role', User::ROLE_ADMIN)->where('is_active', true)->count() <= 1;

        if ($wouldRemoveLastAdmin) {
            return back()
                ->withErrors(['role' => 'The last active administrator cannot be demoted or deactivated.'])
                ->withInput();
        }

        $userData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'is_active' => $data['is_active'],
        ];

        if (! empty($data['password'])) {
            $userData['password'] = $data['password'];
        }

        $user->update($userData);

        $auditLogger->record(
            $request->user(),
            'user.updated',
            $user,
            'Updated account '.$user->email.'.',
            $request,
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Account updated successfully.');
    }

    public function destroy(Request $request, User $user, AuditLogger $auditLogger): RedirectResponse
    {
        abort_unless(in_array($user->role, [User::ROLE_ADMIN, User::ROLE_STAFF], true), 404);

        if ($user->id === $request->user()->id) {
            return back()->withErrors(['account' => 'You cannot deactivate your own account.']);
        }

        if (
            $user->role === User::ROLE_ADMIN
            && $user->is_active
            && User::where('role', User::ROLE_ADMIN)->where('is_active', true)->count() <= 1
        ) {
            return back()->withErrors(['account' => 'The last active administrator cannot be deactivated.']);
        }

        $user->update(['is_active' => false]);

        $auditLogger->record(
            $request->user(),
            'user.deactivated',
            $user,
            'Deactivated account '.$user->email.'.',
            $request,
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Account deactivated. Existing audit and academic records were preserved.');
    }
}
