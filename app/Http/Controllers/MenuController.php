<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('parent')
            ->withCount('children')
            ->orderByRaw('COALESCE(weight, 9999)')
            ->orderBy('name')
            ->get();

        $menuTree = $this->buildMenuTree($menus);

        return view('menus.index', [
            'menus' => $menus,
            'menuTree' => $menuTree,
        ]);
    }

    public function create()
    {
        $menu = new Menu([
            'inner_link' => true,
        ]);

        return view('menus.create', [
            'menu' => $menu,
            'parentOptions' => $this->parentOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        Menu::create($data);

        return redirect()->route('menus.index')->with('status', 'Menú creado correctamente.');
    }

    public function edit(Menu $menu)
    {
        return view('menus.edit', [
            'menu' => $menu,
            'parentOptions' => $this->parentOptions($menu),
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $data = $this->validateData($request, $menu);
        $menu->update($data);

        return redirect()->route('menus.index')->with('status', 'Menú actualizado correctamente.');
    }

    public function destroy(Menu $menu)
    {
        if ($menu->children()->exists()) {
            return redirect()
                ->route('menus.index')
                ->with('error', 'Elimina o reasigna los submenús antes de borrar este elemento.');
        }

        $menu->delete();

        return redirect()->route('menus.index')->with('status', 'Menú eliminado correctamente.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', 'exists:menus,id'],
            'items.*.weight' => ['required', 'integer'],
            'items.*.parent_id' => ['nullable', 'integer', 'exists:menus,id'],
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $item) {
                Menu::where('id', $item['id'])->update([
                    'parent_id' => $item['parent_id'] ?? null,
                    'weight' => $item['weight'],
                ]);
            }
        });

        return response()->json([
            'status' => 'ok',
        ]);
    }

    private function parentOptions(?Menu $except = null)
    {
        return Menu::when(
                $except,
                fn ($query) => $query->where('id', '!=', $except->id)
            )
            ->orderBy('name')
            ->get();
    }

    private function buildMenuTree($menus)
    {
        $grouped = $menus->groupBy('parent_id');

        $build = function ($parentId) use (&$build, $grouped) {
            $items = $grouped->get($parentId, collect())
                ->sortBy(fn (Menu $menu) => $menu->weight ?? 9999)
                ->values();

            return $items->map(function (Menu $menu) use (&$build) {
                return [
                    'id' => $menu->id,
                    'name' => $menu->name,
                    'url' => $menu->url,
                    'children' => $build($menu->id),
                ];
            })->all();
        };

        return $build(null);
    }

    private function validateData(Request $request, ?Menu $menu = null): array
    {
        $parentRule = ['nullable', 'integer', 'exists:menus,id'];
        if ($menu) {
            $parentRule[] = Rule::notIn([$menu->id]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:250'],
            'url' => ['nullable', 'string', 'max:250'],
            'parent_id' => $parentRule,
            'weight' => ['nullable', 'integer'],
            'inner_link' => ['required', 'boolean'],
        ]);

        $validated['inner_link'] = $request->boolean('inner_link');
        $validated['parent_id'] = $validated['parent_id'] ?? null;
        $validated['weight'] = $validated['weight'] ?? null;

        return $validated;
    }
}
