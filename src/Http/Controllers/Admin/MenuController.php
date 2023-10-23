<?php

namespace jCube\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use jCube\Models\Menu;
use Illuminate\Support\Facades\Lang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MenuController extends Controller
{
    public function lang()
    {
        return session('edit.lang') ?: Lang::getLocale();
    }

    public function index()
    {
        $pageTitle = "All Menu items";
        $empty_message = "No menu items yet";
        return view('admin::menu.index', compact('pageTitle', 'empty_message'));
    }

    public function list()
    {
        Menu::$lang = $this->lang();
        $menus = Menu::with(['allSubItems'])
            ->where('parent_id', null)
            ->orderBy('position')
            ->get();

        $items = collect($menus)->map(function ($item) {
            return $this->mapList($item);
        });

        return response($items, 200);
    }

    public function store(Request $request, $id)
    {
        $validation_rule = [
            'parent_id' => 'nullable|integer|gt:0',
            'name' => 'required|string|max:50',
        ];
        Menu::$lang = $this->lang();
        $menu = Menu::where('id', $id)->firstOrCreate();

        $validator = Validator::make($request->all(), $validation_rule, [
            'meta_keywords.array.*' => 'All keywords',
        ]);

        if ($validator->fails()) return back()->withErrors($validator->errors());

        $menu->parent_id = $request->parent_id;
        $menu->icon = $request->icon ?: 'la la-circle-thin';
        $menu->object_type = $request->object_type ?: '';
        $menu->object_id = $request->object_id ?: '';

        $menu->save();
        $menu->setAttribute('name', $request->name);


        if ($id === 0) {
            $message = 'Menu Item Added Successfully';
        } else {
            $message = 'Menu Item Updated Successfully';
        }
        return back()->with('notify', [['success', $message]]);
    }

    public function move(Request $request): \Illuminate\Http\JsonResponse
    {
        $menu = Menu::where('id', $request->id)->firstOrFail();
        $menu->parent_id = $request->parent > 0 ? $request->parent : null;
        $menu->save();

        $this->changePosition($menu->id, $request->position);

        return response()->json([$menu]);
    }

    public function delete($id)
    {
        $menu = Menu::where('id', $id)->withTrashed()->first();
        $menu->delete();
        $notify[] = ['success', 'Menu Item Deleted Successfully'];
        return redirect()->back()->withNotify($notify);
    }

    private function mapList($item)
    {
        $array = [
            'id' => $item->id,
            'text' => $item->name,
            'icon' => $item->icon ?: 'la la-folder',
            'state' => [
                'opened' => true,
            ],
            'data' => [
                'id' => $item->id,
                'name' => $item->name,
                'icon' => $item->icon,
                'object_type' => $item->object_type,
                'object_id' => $item->object_id,
                'parent_id' => $item->parent_id,
            ]
        ];
        if ($item->subItems->count()) {
            $array['children'] = collect($item->subItems)->map(function ($sub_item) {
                return $this->mapList($sub_item);
            });
        }

        return $array;
    }

    public function changePosition($itemId, $newPosition)
    {
        $item = Menu::find($itemId);
        $oldPosition = $item->position;

        if ($newPosition < $oldPosition) {
            // Если элемент перемещается вверх по списку
            Menu::where('position', '>=', $newPosition)
                ->where('position', '<', $oldPosition)
                ->increment('position');
        } else {
            // Если элемент перемещается вниз по списку
            Menu::where('position', '<=', $newPosition)
                ->where('position', '>', $oldPosition)
                ->decrement('position');
        }

        // Обновите позицию выбранного элемента
        $item->position = $newPosition;
        $item->save();
    }
}
