<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::query()
            ->visibleForUser($request->user())
            ->orderBy('name')
            ->get();
        $customCategories = Category::query()
            ->where('user_id', $request->user()->id)
            ->orderBy('name')
            ->get();

        return view('categories.index', [
            'categories' => $categories,
            'customCategories' => $customCategories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', new Enum(TransactionType::class)],
            'icon' => ['nullable', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        $request->user()->categories()->create($validated);

        return to_route('categories.index');
    }
}
