@extends('layouts.waka.main')
@section('title', 'Edit Jadwal Mengajar')
@section('content')
<div class="container-xl admin-page">
    <div class="card admin-content-card">
        <div class="card-header">
            <h3 class="card-title">Edit Jadwal Mengajar</h3>
        </div>

        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('waka.jadwal.update', $jadwal) }}" method="POST">
                @csrf
                @method('PUT')

                <input type="hidden" name="tahun_ajaran_id" value="{{ $jadwal->tahun_ajaran_id }}">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label required">1. Guru Pengampu</label>
                        <select name="guru_id" class="form-select" required>
                            @foreach($daftarGuru as $g)
                                <option value="{{ $g->id }}" {{ old('guru_id', $jadwal->guru_id) == $g->id ? 'selected' : '' }}>
                                    {{ $g->user->name ?? 'Guru #'.$g->id }} (NIP: {{ $g->nip ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">2. Kelas yang Diajar</label>
                        <select name="kelas_id" class="form-select" required>
                            @foreach($daftarKelas as $k)
                                <option value="{{ $k->id }}" {{ old('kelas_id', $jadwal->kelas_id) == $k->id ? 'selected' : '' }}>
                                    {{ $k->nama_kelas }} ({{ $k->tingkat ?? '' }} {{ $k->jurusan ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">3. Mata Pelajaran</label>
                        <select name="mata_pelajaran_id" class="form-select" required>
                            @foreach($daftarMapel as $m)
                                <option value="{{ $m->id }}" {{ old('mata_pelajaran_id', $jadwal->mata_pelajaran_id) == $m->id ? 'selected' : '' }}>
                                    {{ $m->nama }} {{ $m->kode ? '('.$m->kode.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label required">4. Hari Mengajar</label>
                        <select name="hari" class="form-select" required>
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $h)
                                <option value="{{ $h }}" {{ old('hari', $jadwal->hari) == $h ? 'selected' : '' }}>{{ $h }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label required">5. Sesi Jam Pelajaran</label>
                        <div class="row g-2 p-3 border rounded">
                            @php $sesiForm = old('jam_pelajaran_ids', $jamTerpilih); @endphp
                            @foreach($daftarJam->where('jenis', 'pelajaran') as $jam)
                                <div class="col-6 col-md-3 col-lg-2">
                                    <label class="form-check border rounded p-2 ps-5 h-100">
                                        <input class="form-check-input" type="checkbox" name="jam_pelajaran_ids[]" value="{{ $jam->id }}" {{ in_array($jam->id, $sesiForm) ? 'checked' : '' }}>
                                        <span class="form-check-label">
                                            <strong>{{ $jam->nama_sesi }}</strong><br>
                                            <small class="text-secondary">{{ substr($jam->jam_mulai, 0, 5) }}–{{ substr($jam->jam_selesai, 0, 5) }}</small>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-secondary">Pilih satu atau beberapa sesi yang berurutan. Rentang sesi boleh melewati waktu istirahat.</small>
                    </div>

                    <div class="col-12">
                        <div id="scheduleConflictAlert" class="alert alert-secondary mb-0" role="status" aria-live="polite">Memeriksa bentrok jadwal…</div>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('waka.jadwal.data') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" id="scheduleSaveButton" disabled>
                            <i class="bi bi-save me-1"></i>Perbarui Jadwal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form[action="{{ route('waka.jadwal.update', $jadwal) }}"]');
    const alertBox = document.getElementById('scheduleConflictAlert');
    const saveButton = document.getElementById('scheduleSaveButton');
    if (!form || !alertBox || !saveButton) return;

    let timer;
    const show = (kind, message) => {
        alertBox.className = `alert alert-${kind} mb-0`;
        alertBox.textContent = message;
    };
    const check = async () => {
        clearTimeout(timer);
        const fields = ['tahun_ajaran_id', 'guru_id', 'kelas_id', 'mata_pelajaran_id', 'hari'];
        const complete = fields.every(name => form.elements[name]?.value) && form.querySelectorAll('input[name="jam_pelajaran_ids[]"]:checked').length > 0;
        if (!complete) {
            saveButton.disabled = true;
            show('secondary', 'Pilih guru, kelas, hari, mapel, dan minimal satu sesi untuk memeriksa bentrok.');
            return;
        }

        saveButton.disabled = true;
        show('info', 'Sedang memeriksa bentrok jadwal…');
        const data = new FormData(form);
        data.delete('_method');
        data.append('jadwal_id', '{{ $jadwal->id }}');
        try {
            const response = await fetch('{{ route('waka.jadwal.check') }}', {
                method: 'POST',
                headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': data.get('_token')},
                body: data,
            });
            const result = await response.json();
            show(response.ok ? 'success' : 'danger', result.message || 'Jadwal bentrok. Periksa kembali pilihan sesi.');
            saveButton.disabled = !response.ok;
        } catch (error) {
            show('warning', 'Pengecekan awal tidak tersambung. Saat disimpan, backend tetap akan memeriksa bentrok.');
            saveButton.disabled = false;
        }
    };

    form.addEventListener('change', () => { timer = setTimeout(check, 250); });
    form.addEventListener('input', () => { timer = setTimeout(check, 250); });
    check();
});
</script>
@endsection
