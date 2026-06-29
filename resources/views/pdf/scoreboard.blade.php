<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <title>
        @if (isset($is_bundle) && $is_bundle)
            Klasemen Akhir - {{ $competition->title ?? 'PENA 2026' }} {{ $competition->category ?? 'Kategori Umum' }}
        @else
            Scoreboard - {{ $competition->title ?? 'PENA 2026' }} {{ $competition->category ?? 'Kategori Umum' }}
        @endif
    </title>
    <style>
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 14px;
            color: #000;
        }

        .header-logos img {
            height: 40px;
            margin-right: 10px;
        }

        .title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 0 0 5px 0;
        }

        .title_lomba {
            text-align: center;
            font-size: 16px;
            font-weight: normal;
            margin: 0 0 30px 0;
        }

        .main-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .main-table,
        .main-table th,
        .main-table td {
            border: 1px solid #000;
        }

        .main-table th,
        .main-table td {
            padding: 6px 10px;
            text-align: left;
        }

        .text-center {
            text-align: center !important;
        }

        .font-bold {
            font-weight: bold;
        }

        .bg-blue {
            background-color: #4472c4;
            color: white;
        }

        .score-header {
            background-color: #4472c4;
            color: white;
            text-align: center;
            font-weight: bold;
        }

        .bg-lolos {
            background-color: #00FF00;
        }

        .clear {
            clear: both;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    @foreach ($teams as $team)
        @php
            $ketua = $team->members ? $team->members->where('role', 'Ketua')->first() : null;
            $namaKetua = $ketua ? $ketua->name : 'Ketua Tidak Ditemukan';
        @endphp

        <div class="header-logos">
            @if (file_exists(public_path('assets/img/logo-pena.png')))
                <img src="{{ public_path('assets/img/logo-pena.png') }}" style="height: 65px; width: auto;"
                    alt="Logo PENA 2026">
            @else
                <div
                    style="border: 1px solid #ccc; width: 150px; height: 35px; padding: 2px; text-align: center; font-size: 10px; line-height: 35px; background: #eee;">
                    Logo PENA 2026
                </div>
            @endif
        </div>

        <div style="margin-top: -30px;">
            <h1 class="title">Lembar Penilaian</h1>
            <p class="title_lomba">
                {{ $competition->title ?? 'Karya Tulis Ilmiah' }} - {{ $competition->category ?? 'Kategori Umum' }}
            </p>
        </div>

        <table class="main-table">
            <tr>
                <td style="width: 25%">Nomor</td>
                <td class="font-bold">{{ $loop->iteration }}</td>
            </tr>
            <tr>
                <td>Nama Tim</td>
                <td class="font-bold">{{ $team->name }}</td>
            </tr>
            <tr>
                <td>Nama Ketua Tim</td>
                <td class="font-bold">{{ $namaKetua }} (Ketua)</td>
            </tr>
            <tr>
                <td>Asal Instansi</td>
                <td>{{ $team->institution ?? 'Politeknik Negeri Jakarta' }}</td>
            </tr>
            <tr>
                <td>Judul Karya</td>
                <td>{{ $team->submission->original_filename ?? '-' }}</td>
            </tr>
        </table>

        <p style="margin-bottom: 5px">
            Nilai diberikan dari rentang 0-100 (100 untuk nilai paling tinggi dan 0 nilai paling rendah)
        </p>

        <table class="main-table">
            <tr class="score-header">
                <th>Kriteria Penilaian</th>
                <th style="width: 20%" class="text-center">Bobot (%)</th>
                <th style="width: 15%" class="text-center">Nilai</th>
            </tr>

            @php $calculated_total = 0; @endphp
            @foreach ($team->scores as $score)
                @php
                    $bobot = $score->criteria->weight ?? 0;
                    $nilaiBerbobot = ($score->score * $bobot) / 100;
                    $calculated_total += $nilaiBerbobot;
                @endphp
                <tr>
                    <td>{{ $score->criteria->name ?? 'Kriteria Penilaian' }}</td>
                    <td class="text-center">{{ $bobot }}%</td>
                    <td class="text-center">{{ $score->score }}</td>
                </tr>
            @endforeach

            <tr>
                <td colspan="2" class="text-center bg-blue font-bold">Total Nilai Akhir</td>
                <td class="text-center bg-blue font-bold">{{ number_format($calculated_total, 2) }}</td>
            </tr>

            <tr>
                <td class="text-center font-bold">Notes</td>
                <td colspan="2" style="font-size: 11.5px; line-height: 1.4; padding: 4px 10px; color: #333;">
                    {{ $team->jury_notes ?? '-' }}
                </td>
            </tr>
        </table>

        @php
            $qrUrl = $current_qr_url ?? $team->score_board;
            if ($qrUrl && !\Illuminate\Support\Str::startsWith($qrUrl, 'http')) {
                $baseUrl = rtrim(env('FRONTEND_URL'), '/');
                $qrUrl = $baseUrl . '/storage/' . $qrUrl;
            }
            $juriName = $current_juri_name ?? 'Dewan Juri';
            $signature = $current_juri_signature ?? null;
        @endphp

        <div style="width: 100%; margin-top: 30px; page-break-inside: avoid;">
            <div style="float: right; width: 330px;">
                <p style="text-align: right; margin: 0 0 10px 0; font-size: 14px;">
                    Jakarta, {{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->translatedFormat('d F Y') }}
                </p>

                <div style="width: 100%; height: 90px;">
                    <div style="float: left; width: 100px; text-align: right; height: 90px;">
                        @if ($qrUrl)
                            <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(90)->margin(0)->generate($qrUrl)) }}"
                                alt="QR Code" style="margin-top: 2px;">
                        @endif
                    </div>

                    <div
                        style="float: right; width: 210px; height: 90px; border-top: 1.5px solid #000; border-right: 1.5px solid #000; border-bottom: 1.5px solid #000; position: relative;">
                        <div
                            style="position: absolute; top: -6px; left: 10px; background: white; padding: 0 5px; font-size: 10px; color: #000; line-height: 1;">
                            digitally signed</div>
                        <div
                            style="position: absolute; bottom: -6px; left: 5px; background: white; padding: 0 5px; font-size: 10px; color: #000; line-height: 1;">
                            PENA 2026</div>

                        <div style="text-align: center; width: 100%; height: 100%;">
                            @if ($signature)
                                <img src="{{ $signature }}"
                                    style="
                                        max-height: 95px;
                                        max-width: 240px;
                                        object-fit: contain;
                                        display: block;
                                        margin: 0 auto;
                                    "
                                    alt="TTD">
                            @else
                                <div style="height:90px; line-height:90px; color:#ccc; font-size:10px;">
                                    TTD Kosong
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div style="clear: both;"></div>

                <p style="text-align: right; margin: 15px 0 0 0; font-size: 14px;">
                    {{ $juriName }}
                </p>
            </div>
            <div style="clear: both;"></div>
        </div>

        @if (!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach


    @if (isset($is_bundle) && $is_bundle)
        <div class="page-break"></div>

        <div style="margin-top: 30px;">
            <h1 class="title">KLASEMEN AKHIR</h1>
            <p class="title_lomba">
                {{ $competition->title ?? 'Karya Tulis Ilmiah' }} - {{ $competition->category ?? 'Kategori Umum' }}
            </p>
        </div>

        <table class="main-table">
            <tr>
                <th class="text-center" style="width: 5%">No.</th>
                <th class="text-center" style="width: 35%">Nama Individu/Tim</th>
                <th class="text-center" style="width: 15%">Nilai</th>
                <th class="text-center" style="width: 45%">Asal Instansi</th>
            </tr>
            @foreach ($teams as $index => $team)
                @php
                    $ketuaKlasemen = $team->members ? $team->members->where('role', 'Ketua')->first() : null;
                    $namaKetuaKlasemen = $ketuaKlasemen ? $ketuaKlasemen->name : $team->name;
                @endphp

                <tr class="{{ $index < 5 ? 'bg-lolos' : '' }}">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $namaKetuaKlasemen }} (Ketua Tim)</td>
                    <td class="text-center">{{ $team->total_score }}</td>
                    <td class="text-center">{{ $team->institution ?? 'Politeknik Negeri Jakarta' }}</td>
                </tr>
            @endforeach
        </table>

        <table style="border-collapse: collapse; margin-left: 5%; margin-top: 10px;">
            <tr>
                <td style="padding-right: 10px; font-size: 14px;">Keterangan Lolos Final:</td>
                <td class="bg-lolos" style="width: 100px; height: 20px; border: 1px solid #000;"></td>
            </tr>
        </table>

        @php
            $qrUrlBundle = $bundle_qr_url ?? '';
            if ($qrUrlBundle && !\Illuminate\Support\Str::startsWith($qrUrlBundle, 'http')) {
                $qrUrlBundle = asset('storage/' . $qrUrlBundle);
            }
        @endphp

        <div style="width: 100%; margin-top: 50px; page-break-inside: avoid;">
            <div style="float: right; text-align: right;">
                @if ($qrUrlBundle)
                    <img src="data:image/svg+xml;base64,{{ base64_encode(QrCode::format('svg')->size(100)->margin(0)->generate($qrUrlBundle)) }}"
                        alt="QR Code Validation">
                @endif
            </div>
            <div style="clear: both;"></div>
        </div>
    @endif
</body>

</html>
