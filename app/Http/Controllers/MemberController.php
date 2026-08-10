<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function index()
    {
        $members = Member::orderBy('name')->get();

        return view('members.index', compact('members'));
    }

    public function create()
    {
        return view('members.form', ['member' => new Member()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'points' => 'nullable|integer|min:0',
            'level' => 'required|in:silver,gold,platinum',
        ]);

        Member::create($data);

        return redirect()->route('members.index')->with('success', 'Member berhasil disimpan.');
    }

    public function edit(Member $member)
    {
        return view('members.form', compact('member'));
    }

    public function update(Request $request, Member $member)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'points' => 'nullable|integer|min:0',
            'level' => 'required|in:silver,gold,platinum',
        ]);

        $member->update($data);

        return redirect()->route('members.index')->with('success', 'Member berhasil diperbarui.');
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return redirect()->route('members.index')->with('success', 'Member berhasil dihapus.');
    }
}
