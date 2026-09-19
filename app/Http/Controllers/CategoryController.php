<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $itemCategory = Category::orderBy('created_at', 'desc')->get();
        return view('Category')->with("itemCategoryData", $itemCategory);
    }

    // Create Category using Ajax
    public function create(Request $request)
    {
        $request->validate([
            'categoryName' => 'required|max:80|unique:categories,category',
        ], [
            'categoryName.required' => 'Category name is required',
            'categoryName.unique' => 'This category already exists',
            'categoryName.max' => 'Category name cannot exceed 80 characters',
        ]);

        try {
            $category = new Category;
            $category->category = $request->categoryName;
            $category->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Category added successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to add category'
            ], 500);
        }
    }

    // Delete category using Ajax
    public function delete(Request $request)
    {
        try {
            $category = Category::findOrFail($request->id);
            $category->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Category deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete category'
            ], 500);
        }
    }

    // Update category using Ajax
    public function update(Request $request)
    {
        $request->validate([
            'up_categoryName' => 'required|max:80',
        ], [
            'up_categoryName.required' => 'Category name is required',
            'up_categoryName.max' => 'Category name cannot exceed 80 characters',
        ]);

        try {
            Category::where('id', $request->up_id)->update([
                'category' => $request->up_categoryName,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Category updated successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update category'
            ], 500);
        }
    }

    // Legacy methods (can be removed if not used elsewhere)
    public function addCategory(Request $request)
    {
        $CategoryId = $request->id;
        $Category = Category::updateOrCreate(
            ['id' => $CategoryId],
            ['category' => $request->category]
        );
        return Response()->json($Category);
    }

    public function editCategory(Request $request)
    {
        $where = array('id' => $request->id);
        $Category = Category::where($where)->first();
        return Response()->json($Category);
    }

    public function deleteCategory(Request $request)
    {
        $Category = Category::where('id', $request->id)->delete();
        return Response()->json($Category);
    }

    public function show($id)
    {
        // Implementation if needed
    }

    public function edit($id)
    {
        // Implementation if needed
    }

    public function destroy($id)
    {
        // Implementation if needed
    }
}