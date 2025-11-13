<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    //
    public function index(Request $request)
    {
        $categories = Category::all();

        $products = Product::all();

        return view('admin.product', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'photo' => 'nullable|mimes:png,jpeg,jpg|max:2048',
            'product_name' => 'required|string|unique:products,product_name',
            'description' => 'required|string',
            'brand' => 'required|string',
            'cpu' => 'required|string',
            'gpu' => 'required|string',
            'memory' => 'required|string',
            'storage' => 'required|string',
            'stock' => 'required|integer',
            'price' => 'required|numeric',
            'category_id' => 'required',
        ], [
            'product_name.unique' => 'Product name has been used',
        ]);

        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('photos', 'public');
            $validated['photo'] = $photoPath;
        }

        Product::create($validated);

        return redirect()->back()->with('success', 'Product added successfully');
    }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'photo' => 'nullable|mimes:png,jpeg,jpg|max:2048',
    //         'product_name' => 'required|string|unique:products,product_name',
    //         'description' => 'required|string',
    //         'brand' => 'required|string',
    //         'cpu' => 'required|string',
    //         'gpu' => 'required|string',
    //         'memory' => 'required|string',
    //         'storage' => 'required|string',
    //         'stock' => 'required|integer',
    //         'price' => 'required|numeric',
    //         'category_id' => 'required',
    //     ]);

    //     if ($request->hasFile('photo')) {
    //         // simpan langsung ke S3
    //         $path = $request->file('photo')->store('photos', 's3');
    //         $validated['photo'] = $path;
    //     }

    //     Product::create($validated);

    //     return redirect()->back()->with('success', 'Product added successfully');
    // }


    // public function update(Request $request, Product $product)
    // {
    //     $validated = $request->validate([
    //         'photo' => 'nullable|mimes:png,jpeg,jpg|max:2048',
    //         'product_name' => 'required|string',
    //         'description' => 'required|string',
    //         'brand' => 'required|string',
    //         'cpu' => 'required|string',
    //         'gpu' => 'required|string',
    //         'memory' => 'required|string',
    //         'storage' => 'required|string',
    //         'stock' => 'required|integer',
    //         'price' => 'required|numeric',
    //         'category_id' => 'required',
    //     ]);

    //     if ($request->hasFile('photo')) {
    //         if ($product->photo && Storage::disk('public')->exists($product->photo)) {
    //             Storage::disk('public')->delete($product->photo);
    //         }

    //         $photoPath = $request->file('photo')->store('photos', 'public');
    //         $validated['photo'] = $photoPath;
    //     }

    //     $product->update($validated);
    //     return redirect()->back()->with('success', 'Product updated successfully');
    // }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'photo' => 'nullable|mimes:png,jpeg,jpg|max:2048',
            'product_name' => 'required|string',
            'description' => 'required|string',
            'brand' => 'required|string',
            'cpu' => 'required|string',
            'gpu' => 'required|string',
            'memory' => 'required|string',
            'storage' => 'required|string',
            'stock' => 'required|integer',
            'price' => 'required|numeric',
            'category_id' => 'required',
        ]);

        if ($request->hasFile('photo')) {
            // hapus foto lama di S3 (jika ada)
            if ($product->photo && Storage::disk('s3')->exists($product->photo)) {
                Storage::disk('s3')->delete($product->photo);
            }

            // upload baru ke S3
            $path = $request->file('photo')->store('photos', 's3');
            $validated['photo'] = $path;
        }

        $product->update($validated);

        return redirect()->back()->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product)
    {
        if ($product->photo && Storage::disk('public')->exists($product->photo)) {
            Storage::disk('public')->delete($product->photo);
        }

        $product->delete();
        return redirect()->back()->with('success', 'Product deleted successfully');
    }

    public function checkProductInCart($id)
    {
        $inCart = DB::table('carts')->where('product_id', $id)->exists();

        return response()->json(['inCart' => $inCart]);
    }
}
