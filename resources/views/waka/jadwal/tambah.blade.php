@extends('layouts.waka.main')
@section('title', 'Susun Jadwal Mengajar')
@section('content')
<div class="container-xl admin-page">
    <div class="card admin-content-card">
        <div class="card-header">
            <div>
                <h3 class="card-title mb-1">Susun Jadwal Mengajar</h3>
                <div class="text-secondary small">Lihat jadwal kelas dan ketersediaan guru sebelum menyimpan pertemuan.</div>
            </div>
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

            @if(!$tahunAktif)
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Belum ada tahun ajaran aktif. Pilih tahun ajaran yang ingin disusun.
                </div>
            @endif

            <form action="{{ route('waka.jadwal.store') }}" method="POST" id="createScheduleForm">
                @csrf
                <div class="row g-3 mb-4">
                    <div class="col-lg-4">
                        <label class="form-label required" for="tahunAjaranSelect">1. Tahun ajaran dan semester</label>
                        <select name="tahun_ajaran_id" id="tahunAjaranSelect" class="form-select" required>
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach($daftarTahunAjaran as $tahun)
                                <option value="{{ $tahun->id }}" {{ old('tahun_ajaran_id', $tahunAktif?->id) == $tahun->id ? 'selected' : '' }}>
                                    {{ $tahun->nama }}, Semester {{ ucfirst($tahun->semester) }}{{ $tahun->status ? ' (Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4">
                        <label class="form-label required" for="kelasSelect">2. Kelas</label>
                        <select name="kelas_id" id="kelasSelect" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($daftarKelas as $kelas)
                                <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}{{ $kelas->tingkat || $kelas->jurusan ? ', '.$kelas->tingkat.' '.$kelas->jurusan : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4 d-flex align-items-end">
                        <div class="small text-secondary"><i class="bi bi-info-circle me-1"></i>Pilih kelas untuk melihat jadwal mingguannya.</div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div>
                                <h4 class="mb-0">Jadwal kelas</h4>
                                <div class="small text-secondary">Jadwal yang sudah terisi tampil dengan mapel dan nama guru. Klik sesi kosong untuk memilihnya.</div>
                            </div>
                            <span class="badge bg-success-lt">Kosong</span>
                        </div>
                        <div class="table-responsive border rounded">
                            <table class="table table-bordered table-sm text-center align-middle mb-0" id="classWeeklyGrid">
                                <tbody><tr><td class="text-secondary py-4">Pilih tahun ajaran dan kelas untuk memuat jadwal.</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div>
                                <h4 class="mb-0">Jadwal guru terpilih</h4>
                                <div class="small text-secondary">Saat guru dipilih, jadwal mengajarnya sepanjang minggu akan terlihat di sini.</div>
                            </div>
                        </div>
                        <div class="table-responsive border rounded">
                            <table class="table table-bordered table-sm text-center align-middle mb-0" id="teacherWeeklyGrid">
                                <tbody><tr><td class="text-secondary py-4">Pilih guru untuk melihat jadwalnya.</td></tr></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label required" for="hariSelect">3. Hari</label>
                        <select name="hari" id="hariSelect" class="form-select" required>
                            <option value="">-- Pilih Hari --</option>
                            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari)
                                <option value="{{ $hari }}" {{ old('hari') == $hari ? 'selected' : '' }}>{{ $hari }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required" for="mapelSelect">4. Mata pelajaran</label>
                        <select name="mata_pelajaran_id" id="mapelSelect" class="form-select" required>
                            <option value="">-- Pilih Mata Pelajaran --</option>
                            @foreach($daftarMapel as $mapel)
                                <option value="{{ $mapel->id }}" {{ old('mata_pelajaran_id') == $mapel->id ? 'selected' : '' }}>
                                    {{ $mapel->nama }}{{ $mapel->kode ? ' ('.$mapel->kode.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label required" for="guruSelect">5. Guru pengampu</label>
                        <select name="guru_id" id="guruSelect" class="form-select" required>
                            <option value="">-- Pilih Guru --</option>
                            @foreach($daftarGuru as $guru)
                                <option value="{{ $guru->id }}" {{ old('guru_id') == $guru->id ? 'selected' : '' }}>
                                    {{ $guru->user->name ?? 'Guru #'.$guru->id }}{{ $guru->nip ? ' (NIP '.$guru->nip.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label required">6. Sesi pertemuan</label>
                        <div class="row g-2 p-3 border rounded" id="sessionChoices">
                            @foreach($daftarJam->where('jenis', 'pelajaran') as $jam)
                                <div class="col-6 col-md-3 col-lg-2">
                                    <label class="form-check border rounded p-2 ps-5 h-100">
                                        <input class="form-check-input" type="checkbox" name="jam_pelajaran_ids[]" value="{{ $jam->id }}" {{ in_array($jam->id, old('jam_pelajaran_ids', [])) ? 'checked' : '' }}>
                                        <span class="form-check-label">
                                            <strong>{{ $jam->nama_sesi }}</strong><br>
                                            <small class="text-secondary">{{ substr($jam->jam_mulai, 0, 5) }}–{{ substr($jam->jam_selesai, 0, 5) }}</small>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <small class="text-secondary">Pilih sesi kosong pada satu hari. Satu pertemuan boleh melewati istirahat.</small>
                    </div>

                    <div class="col-12">
                        <div id="scheduleConflictAlert" class="alert alert-secondary mb-0" role="status" aria-live="polite">Pilih guru, kelas, hari, mapel, dan sesi untuk memeriksa bentrok.</div>
                    </div>

                    <div class="col-12 mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <a href="{{ route('waka.jadwal.data') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary" id="scheduleSaveButton" disabled>
                            <i class="bi bi-save me-1"></i>Simpan Pertemuan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('createScheduleForm');
    const alertBox = document.getElementById('scheduleConflictAlert');
    const saveButton = document.getElementById('scheduleSaveButton');
    const classGrid = document.getElementById('classWeeklyGrid');
    const teacherGrid = document.getElementById('teacherWeeklyGrid');
    const yearSelect = document.getElementById('tahunAjaranSelect');
    const classSelect = document.getElementById('kelasSelect');
    const teacherSelect = document.getElementById('guruSelect');
    const daySelect = document.getElementById('hariSelect');
    let checkTimer;
    let previewController;
    let previewTimer;
    let checkSequence = 0;

    const show = (kind, message) => {
        alertBox.className = `alert alert-${kind} mb-0`;
        alertBox.textContent = message;
    };

    const messageCell = (table, message) => {
        table.innerHTML = '';
        const row = table.insertRow();
        const cell = row.insertCell();
        cell.colSpan = 6;
        cell.className = 'text-secondary py-4';
        cell.textContent = message;
    };

    const renderGrid = (table, sessions, days, assignments, allowPick) => {
        table.innerHTML = '';
        const head = table.createTHead().insertRow();
        ['Sesi / Waktu', ...days].forEach(label => {
            const th = document.createElement('th');
            th.className = 'bg-body-tertiary text-nowrap';
            th.textContent = label;
            head.appendChild(th);
        });
        const body = table.createTBody();
        sessions.forEach(slot => {
            const row = body.insertRow();
            if (slot.jenis === 'istirahat') {
                const breakCell = row.insertCell();
                breakCell.colSpan = days.length + 1;
                breakCell.className = 'bg-warning-lt text-warning-emphasis fw-semibold py-2';
                breakCell.textContent = `${slot.nama}, ${slot.mulai}–${slot.selesai}`;
                return;
            }

            const label = row.insertCell();
            label.className = 'bg-body-tertiary text-nowrap';
            label.innerHTML = `<strong></strong><br><small class="text-secondary"></small>`;
            label.querySelector('strong').textContent = slot.nama;
            label.querySelector('small').textContent = `${slot.mulai}–${slot.selesai}`;
            days.forEach(day => {
                const cell = row.insertCell();
                cell.style.minWidth = '145px';
                const occupied = assignments?.[day]?.[slot.id];
                if (occupied) {
                    cell.className = 'bg-primary-lt';
                    const subject = document.createElement('strong');
                    subject.className = 'd-block small text-primary';
                    subject.textContent = occupied.mapel;
                    const teacher = document.createElement('span');
                    teacher.className = 'd-block small';
                    teacher.textContent = allowPick ? occupied.guru : occupied.kelas;
                    cell.append(subject, teacher);
                } else if (allowPick) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.pickDay = day;
                    button.dataset.pickSlot = slot.id;
                    const input = form.querySelector(`input[name="jam_pelajaran_ids[]"][value="${slot.id}"]`);
                    const selected = daySelect.value === day && input?.checked;
                    button.className = `btn btn-sm ${selected ? 'btn-primary' : 'btn-outline-primary'} py-1`;
                    button.textContent = selected ? 'Terpilih' : 'Pilih sesi';
                    cell.appendChild(button);
                } else {
                    cell.innerHTML = '<span class="text-success small fw-semibold">Kosong</span>';
                }
            });
        });
    };

    const loadPreview = async () => {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(async () => {
            if (!yearSelect.value) {
                messageCell(classGrid, 'Pilih tahun ajaran untuk memuat jadwal.');
                messageCell(teacherGrid, 'Pilih tahun ajaran dan guru untuk melihat jadwal guru.');
                return;
            }
            if (!classSelect.value && !teacherSelect.value) {
                messageCell(classGrid, 'Pilih kelas untuk melihat jadwal mingguannya.');
                messageCell(teacherGrid, 'Pilih guru untuk melihat jadwalnya.');
                return;
            }
            previewController?.abort();
            previewController = new AbortController();
            const params = new URLSearchParams({tahun_ajaran_id: yearSelect.value});
            if (classSelect.value) params.set('kelas_id', classSelect.value);
            if (teacherSelect.value) params.set('guru_id', teacherSelect.value);
            try {
                const response = await fetch(`{{ route('waka.jadwal.preview') }}?${params}`, {headers: {'Accept': 'application/json'}, signal: previewController.signal});
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Jadwal belum dapat dimuat.');
                if (classSelect.value) renderGrid(classGrid, data.sesi, data.hari, data.jadwal_kelas, true);
                else messageCell(classGrid, 'Pilih kelas untuk melihat jadwal mingguannya.');
                if (teacherSelect.value) renderGrid(teacherGrid, data.sesi, data.hari, data.jadwal_guru, false);
                else messageCell(teacherGrid, 'Pilih guru untuk melihat jadwalnya.');
            } catch (error) {
                if (error.name !== 'AbortError') {
                    messageCell(classGrid, error.message);
                    messageCell(teacherGrid, error.message);
                }
            }
        }, 180);
    };

    const checkConflict = async () => {
        clearTimeout(checkTimer);
        const selectedSessions = form.querySelectorAll('input[name="jam_pelajaran_ids[]"]:checked');
        const ready = yearSelect.value && classSelect.value && teacherSelect.value && daySelect.value && form.elements.mata_pelajaran_id.value && selectedSessions.length;
        if (!ready) {
            saveButton.disabled = true;
            show('secondary', 'Pilih tahun ajaran, kelas, guru, hari, mapel, dan minimal satu sesi.');
            return;
        }

        const currentSequence = ++checkSequence;
        saveButton.disabled = true;
        show('info', 'Sedang memeriksa bentrok jadwal…');
        const data = new FormData(form);
        try {
            const response = await fetch('{{ route('waka.jadwal.check') }}', {
                method: 'POST',
                headers: {'Accept': 'application/json', 'X-CSRF-TOKEN': data.get('_token')},
                body: data,
            });
            const result = await response.json();
            if (currentSequence !== checkSequence) return;
            show(response.ok ? 'success' : 'danger', result.message || 'Jadwal bentrok. Periksa kembali pilihan sesi.');
            saveButton.disabled = !response.ok;
        } catch (error) {
            if (currentSequence !== checkSequence) return;
            show('warning', 'Pengecekan awal tidak tersambung. Backend tetap akan memeriksa bentrok saat jadwal disimpan.');
            saveButton.disabled = false;
        }
    };

    const scheduleChange = () => {
        loadPreview();
        clearTimeout(checkTimer);
        checkTimer = setTimeout(checkConflict, 220);
    };

    form.addEventListener('change', scheduleChange);
    form.addEventListener('input', scheduleChange);
    classGrid.addEventListener('click', event => {
        const button = event.target.closest('[data-pick-slot]');
        if (!button) return;
        const day = button.dataset.pickDay;
        const checked = [...form.querySelectorAll('input[name="jam_pelajaran_ids[]"]:checked')];
        if (daySelect.value && daySelect.value !== day && checked.length) {
            if (!confirm('Ganti hari? Sesi yang sudah dipilih akan dikosongkan.')) return;
            checked.forEach(input => { input.checked = false; });
        }
        daySelect.value = day;
        const input = form.querySelector(`input[name="jam_pelajaran_ids[]"][value="${button.dataset.pickSlot}"]`);
        if (input) input.checked = true;
        scheduleChange();
    });

    loadPreview();
    checkConflict();
});
</script>
@endsection
