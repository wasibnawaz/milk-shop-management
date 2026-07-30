<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $search = $request->string('search')->toString();

        $users = User::query()
            ->withCount('sales')
            ->when($search, fn ($q, $term) => $q->where(
                fn ($sub) => $sub->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
            ))
            ->orderBy('name')
            ->paginate(config('shop.per_page'))
            ->withQueryString();

        return view('users.index', compact('users', 'search'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', ['roles' => UserRole::options()]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = User::create($request->validated());

        Log::channel('security')->notice('User account created', [
            'created_user_id' => $user->id,
            'role' => $user->role->value,
            'by_user_id' => $request->user()->id,
        ]);

        return redirect()->route('users.index')->with('success', "Account for {$user->name} created.");
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('users.edit', [
            'user' => $user,
            'roles' => UserRole::options(),
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        // Blank password field means "leave it unchanged".
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        Log::channel('security')->notice('User account updated', [
            'updated_user_id' => $user->id,
            'by_user_id' => $request->user()->id,
        ]);

        return redirect()->route('users.index')->with('success', "{$user->name} updated.");
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ($user->is($request->user())) {
            return back()->with('error', 'You cannot delete your own account from here.');
        }

        if ($user->isAdmin() && User::where('role', UserRole::Admin->value)->active()->count() <= 1) {
            return back()->with('error', 'This is the last administrator — promote someone else first.');
        }

        // Soft delete: sales recorded by this user keep their attribution.
        $user->delete();

        Log::channel('security')->notice('User account deleted', [
            'deleted_user_id' => $user->id,
            'by_user_id' => $request->user()->id,
        ]);

        return redirect()->route('users.index')->with('success', "{$user->name}'s account was removed.");
    }
}
