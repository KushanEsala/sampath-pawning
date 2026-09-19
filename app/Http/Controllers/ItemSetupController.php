<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\itemSetup;
use App\Models\Category;

class itemSetupController extends Controller
{
    public function index(Request $request)
    {
        $query = itemSetup::query();

        // Server-side search (optional - for pagination support)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('ItemDesc', 'like', "%{$search}%")
                  ->orWhere('Category', 'like', "%{$search}%")
                  ->orWhere('subcategory', 'like', "%{$search}%");
            });
        }

        $items = $query->latest()->paginate(100);
        $itemCategory = Category::all();

        return view('item_setup')
            ->with("item", $items)
            ->with("item_category", $itemCategory);
    }

    // Generate next item code
    public function generateCode()
    {
        $lastItem = itemSetup::orderBy('id', 'desc')->first();

        if (!$lastItem) {
            $nextCode = 'ITM-0001';
        } else {
            $lastCode = $lastItem->code;

            // Handle different code formats
            if (preg_match('/ITM-(\d+)/', $lastCode, $matches)) {
                $lastNumber = (int) $matches[1];
                $nextNumber = $lastNumber + 1;
                $nextCode = 'ITM-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
            } else {
                $nextCode = 'ITM-0001';
            }
        }

        return response()->json([
            'code' => $nextCode
        ]);
    }

    // Create Item using Ajax
    public function create(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:item_setups,code|max:10',
            'description' => 'required|max:80',
            'category' => 'required|max:80',
            'sub_category' => 'nullable|max:30',
        ], [
            'code.required' => 'Item code is required',
            'code.unique' => 'This item code already exists',
            'description.required' => 'Item description is required',
            'category.required' => 'Category is required',
        ]);

        $item = new itemSetup;
        $item->code = $request->code;
        $item->ItemDesc = $request->description;
        $item->Category = $request->category;
        $item->subcategory = $request->sub_category;
        $item->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Item added successfully!'
        ]);
    }

    // Delete item using Ajax
    public function delete(Request $request)
    {
        try {
            $item = itemSetup::findOrFail($request->item_id);
            $item->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Item deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete item'
            ], 500);
        }
    }

    // Update item using Ajax
    public function update(Request $request)
    {
        $request->validate([
            'up_code' => 'required|max:10',
            'up_description' => 'required|max:80',
            'up_category' => 'required|max:80',
            'up_sub_category' => 'nullable|max:30',
        ], [
            'up_code.required' => 'Item code is required',
            'up_description.required' => 'Item description is required',
            'up_category.required' => 'Category is required',
        ]);

        try {
            itemSetup::where('id', $request->up_id)->update([
                'code' => $request->up_code,
                'ItemDesc' => $request->up_description,
                'Category' => $request->up_category,
                'subcategory' => $request->up_sub_category,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Item updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update item'
            ], 500);
        }
    }
}