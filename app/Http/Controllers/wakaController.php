<?php

namespace App\Http\Controllers;

use App\Models\tahunAjaran;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class wakaController extends Controller
{
    public function dataTahunAjaran(Request $request): View
    {
        $tahunAjaran = tahunAjaran::query()
            ->when($request->string('search')->trim()->toString(), function ($query, $search) {
                $query->where('nama', 'like', "%{$search}%")
                    ->orWhere('semester', 'like', "%{$search}%");
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('waka.tahunAjaran.data', [
            'tahunAjaran' => $tahunAjaran,
            'search' => $request->input('search'),
        ]);
    }

    public function createTahunAjaran(): View
    {
        return view('waka.tahunAjaran.tambah');
    }

    public function storeTahunAjaran(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:9', Rule::unique('tahun_ajaran')->where(fn ($query) => $query->where('semester', $request->semester))],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated) {
            if ($validated['status'] ?? false) {
                tahunAjaran::where('status', true)->update(['status' => false]);
            }

            tahunAjaran::create([
                ...$validated,
                'status' => $validated['status'] ?? false,
            ]);
        });

        return redirect()->route('waka.tahun-ajaran.data')
            ->with('success', 'Data tahun ajaran berhasil ditambahkan.');
    }

    public function editTahunAjaran(tahunAjaran $tahunAjaran): View
    {
        return view('waka.tahunAjaran.edit', compact('tahunAjaran'));
    }

    public function updateTahunAjaran(Request $request, tahunAjaran $tahunAjaran): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => [
                'required',
                'string',
                'max:9',
                Rule::unique('tahun_ajaran')->where(fn ($query) => $query->where('semester', $request->semester))->ignore($tahunAjaran->id),
            ],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'status' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $tahunAjaran) {
            if ($validated['status'] ?? false) {
                tahunAjaran::where('id', '!=', $tahunAjaran->id)->where('status', true)->update(['status' => false]);
            }

            $tahunAjaran->update([
                ...$validated,
                'status' => $validated['status'] ?? false,
            ]);
        });

        return redirect()->route('waka.tahun-ajaran.data')
            ->with('success', 'Data tahun ajaran berhasil diperbarui.');
    }

    public function destroyTahunAjaran(tahunAjaran $tahunAjaran): RedirectResponse
    {
        $tahunAjaran->delete();

        return redirect()->route('waka.tahun-ajaran.data')
            ->with('success', 'Data tahun ajaran berhasil dihapus.');
    }
}
