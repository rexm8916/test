<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branches = Branch::all();
        return view('branches.index', compact('branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('branches.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
        ]);

        Branch::create($request->all());

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Branch $branch)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
            'address' => 'required|string',
            'phone' => 'nullable|string|max:20',
        ]);

        $branch->update($request->all());

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Branch $branch)
    {
        // Hindari menghapus cabang jika masih ada user yang terhubung (opsional tergantung business logic)
        if ($branch->users()->count() > 0) {
            return redirect()->route('branches.index')->with('error', 'Cabang tidak dapat dihapus karena masih ada pengguna yang terhubung.');
        }

        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil dihapus');
    }
}
