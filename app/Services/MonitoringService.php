<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AuditRepository;
use App\Repositories\DashboardRepository;
use App\Repositories\PeriodeRepository;
use App\Repositories\SampelRutaRepository;
use InvalidArgumentException;
use PDO;
use RuntimeException;

/**
 * Logika bisnis Dashboard Monitoring.
 *
 * Tanggung jawab:
 *  - normalisasi & validasi seluruh parameter filter (whitelist ketat),
 *  - penyusunan Tier 1 (KPI), Tier 2 (chart), Tier 3 (grid),
 *  - laporan konsistensi antar-widget (Zero Data Discrepancy),
 *  - aksi cepat (quick verify) yang SELALU terekam audit trail.
 */
final class MonitoringService
{
    public const TONE_OK = 'ok';
    public const TONE_WARN = 'warn';
    public const TONE_DANGER = 'danger';
    public const TONE_NEUTRAL = 'neutral';

    /** Ambang ketercapaian target (persen). */
    private const THRESHOLD_OK = 90.0;
    private const THRESHOLD_WARN = 70.0;

    public const RANGE_PRESETS = ['today', '7d', '30d', 'periode', 'custom'];
    public const TRANSFER_STAGES = ['K', 'KP', 'SERUTI', 'BELUM_K'];
    private const DOKUMEN_STATUS = ['BELUM', 'ADA'];
    private const SELESAI_STATUS = ['BELUM', 'SUDAH'];
    private const PER_PAGE_ALLOWED = [15, 25, 50, 100];

    /** Kolom yang boleh diubah lewat aksi cepat dashboard. */
    private const QUICK_FIELDS = ['status_dokumen', 'status_transfer_k', 'status_transfer_kp', 'status_transfer_seruti'];

    /** Peran yang boleh melakukan aksi cepat transfer/status dokumen. */
    public const PENGOLAH_ROLES = ['PENGOLAH', 'OPERATOR', 'PENGAWAS_OLAH', 'SM_PLS', 'ADMIN'];

    public function __construct(
        private PDO $pdo,
        private DashboardRepository $repo,
        private PeriodeRepository $periodeRepo,
        private SampelRutaRepository $rutaRepo,
        private AuditRepository $auditRepo,
        private PengolahanService $pengolahan,
    ) {
    }

    // ------------------------------------------------------------- filtering

    /**
     * Normalisasi + validasi seluruh input filter (whitelist, bukan blacklist).
     *
     * @param array<string,mixed> $in
     * @return array<string,mixed>
     */
    public function normalizeFilters(array $in): array
    {
        $str = static fn (mixed $v): string => is_scalar($v) ? trim((string) $v) : '';
        $int = static fn (mixed $v): int => (int) $v;

        $range = $str($in['range'] ?? 'periode');
        $stage = $str($in['transfer_stage'] ?? '');
        $sort = $str($in['sort'] ?? 'nks');
        $perPage = $int($in['per_page'] ?? 25);

        $f = [
            'periode_id' => max(0, $int($in['periode_id'] ?? 0)),
            'range' => in_array($range, self::RANGE_PRESETS, true) ? $range : 'periode',
            'date_from' => $this->validDate($str($in['date_from'] ?? '')),
            'date_to' => $this->validDate($str($in['date_to'] ?? '')),

            'kec' => preg_match('/^[0-9]{3}$/', $str($in['kec'] ?? '')) === 1 ? $str($in['kec']) : '',
            'desa_id' => max(0, $int($in['desa_id'] ?? 0)),
            'pengolah_id' => max(0, $int($in['pengolah_id'] ?? 0)),
            'pcl_id' => max(0, $int($in['pcl_id'] ?? 0)),
            'pml_id' => max(0, $int($in['pml_id'] ?? 0)),

            'status_dokumen' => in_array($str($in['status_dokumen'] ?? ''), self::DOKUMEN_STATUS, true) ? $str($in['status_dokumen']) : '',
            'status_selesai' => in_array($str($in['status_selesai'] ?? ''), self::SELESAI_STATUS, true) ? $str($in['status_selesai']) : '',
            'has_error' => in_array($str($in['has_error'] ?? ''), ['0', '1'], true) ? $str($in['has_error']) : '',
            'transfer_stage' => in_array($stage, self::TRANSFER_STAGES, true) ? $stage : '',

            'q' => mb_substr($str($in['q'] ?? ''), 0, 60),
            'sort' => array_key_exists($sort, DashboardRepository::SORTABLE) ? $sort : 'nks',
            'dir' => strtoupper($str($in['dir'] ?? 'ASC')) === 'DESC' ? 'DESC' : 'ASC',
            'page' => max(1, $int($in['page'] ?? 1)),
            'per_page' => in_array($perPage, self::PER_PAGE_ALLOWED, true) ? $perPage : 25,
        ];

        return $this->applyRange($f);
    }

    /** Terjemahkan preset rentang waktu menjadi date_from/date_to. */
    private function applyRange(array $f): array
    {
        $today = date('Y-m-d');
        switch ($f['range']) {
            case 'today':
                $f['date_from'] = $today;
                $f['date_to'] = $today;
                break;
            case '7d':
                $f['date_from'] = date('Y-m-d', strtotime('-6 days'));
                $f['date_to'] = $today;
                break;
            case '30d':
                $f['date_from'] = date('Y-m-d', strtotime('-29 days'));
                $f['date_to'] = $today;
                break;
            case 'periode':
                $f['date_from'] = '';
                $f['date_to'] = '';
                break;
            case 'custom':
            default:
                if ($f['date_from'] !== '' && $f['date_to'] !== '' && $f['date_from'] > $f['date_to']) {
                    [$f['date_from'], $f['date_to']] = [$f['date_to'], $f['date_from']];
                }
                break;
        }

        return $f;
    }

    private function validDate(string $s): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $s) === 1 ? $s : '';
    }

    /**
     * Periode terpilih; jika kosong pakai periode AKTIF, lalu periode terbaru.
     *
     * @param array<int,array<string,mixed>> $periodes
     */
    public function resolvePeriodeId(int $requested, array $periodes): int
    {
        foreach ($periodes as $p) {
            if ((int) $p['id'] === $requested) {
                return $requested;
            }
        }

        $aktif = array_filter($periodes, static fn (array $p): bool => (string) ($p['status'] ?? '') === 'AKTIF');
        if ($aktif !== []) {
            // Jika terdapat lebih dari 1 periode aktif, prioritaskan yang memiliki dokumen masuk terbanyak
            if (count($aktif) > 1) {
                $ids = array_map(static fn (array $p): int => (int) $p['id'], $aktif);
                $in = implode(',', $ids);
                $stmt = $this->pdo->query(
                    "SELECT sp.periode_id, COUNT(*) AS jml_dok
                     FROM sampel_ruta sr
                     JOIN sampel sp ON sp.id = sr.sampel_id
                     WHERE sp.periode_id IN ({$in}) AND sr.status_dokumen = 'ADA'
                     GROUP BY sp.periode_id
                     ORDER BY jml_dok DESC
                     LIMIT 1"
                );
                $best = $stmt->fetchColumn();
                if ($best !== false && (int) $best > 0) {
                    return (int) $best;
                }
            }

            return (int) array_values($aktif)[0]['id'];
        }

        return $periodes === [] ? 0 : (int) $periodes[0]['id'];
    }

    // ------------------------------------------------------------- formatter

    public function num(int|float|null $v, int $dec = 0): string
    {
        return number_format((float) ($v ?? 0), $dec, ',', '.');
    }

    public function pct(?float $v, int $dec = 1): string
    {
        return $v === null ? '—' : number_format($v, $dec, ',', '.') . '%';
    }

    /** Rasio aman (0 bila penyebut 0). */
    private function rate(int|float $part, int|float $total): float
    {
        return $total > 0 ? round(($part / $total) * 100, 2) : 0.0;
    }

    private function tone(float $pct): string
    {
        if ($pct >= self::THRESHOLD_OK) {
            return self::TONE_OK;
        }
        if ($pct >= self::THRESHOLD_WARN) {
            return self::TONE_WARN;
        }

        return self::TONE_DANGER;
    }

    private function toneLabel(string $tone): string
    {
        return match ($tone) {
            self::TONE_OK => 'Target tercapai',
            self::TONE_WARN => 'Perlu perhatian',
            self::TONE_DANGER => 'Deviasi / kritis',
            default => 'Tanpa target',
        };
    }

    /** Ikon semantik pendamping warna (WCAG 2.1 AA: jangan andalkan warna saja). */
    private function toneIcon(string $tone): string
    {
        return match ($tone) {
            self::TONE_OK => 'fa-circle-check',
            self::TONE_WARN => 'fa-triangle-exclamation',
            self::TONE_DANGER => 'fa-circle-exclamation',
            default => 'fa-circle-info',
        };
    }

    /**
     * Laju target: persentase target yang "seharusnya" sudah dicapai
     * berdasarkan proporsi waktu periode berjalan.
     *
     * @param array<string,mixed>|null $periode
     */
    public function targetPace(?array $periode): ?float
    {
        if ($periode === null) {
            return null;
        }
        $mulai = (string) ($periode['tgl_mulai'] ?? '');
        $selesai = (string) ($periode['tgl_selesai'] ?? '');
        if ($mulai === '' || $selesai === '') {
            return null;
        }
        $start = strtotime($mulai);
        $end = strtotime($selesai);
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }
        $now = strtotime(date('Y-m-d'));
        if ($now === false) {
            return null;
        }
        if ($now <= $start) {
            return 0.0;
        }
        if ($now >= $end) {
            return 100.0;
        }

        return round((($now - $start) / ($end - $start)) * 100, 2);
    }

    public function periodeStatus(int $periodeId): string
    {
        $stmt = $this->pdo->prepare('SELECT status FROM periode WHERE id = :p LIMIT 1');
        $stmt->execute([':p' => $periodeId]);
        $s = $stmt->fetchColumn();

        return $s === false ? '' : (string) $s;
    }

    // -------------------------------------------------------- tier 1 & 2

    /** Rasio dengan interpretasi terbalik (semakin kecil semakin baik). */
    private function toneInverted(float $pctBad): string
    {
        if ($pctBad <= 2.0) {
            return self::TONE_OK;
        }
        if ($pctBad <= 10.0) {
            return self::TONE_WARN;
        }

        return self::TONE_DANGER;
    }

    /**
     * Payload lengkap dashboard: Tier 1 (KPI), Tier 2 (chart), dan meta.
     *
     * @param array<string,mixed> $f
     * @param array<string,mixed>|null $periode
     * @param array<string,mixed> $user
     * @return array<string,mixed>
     */
    public function buildPayload(array $f, ?array $periode, array $user): array
    {
        $summary = $this->repo->summary($f);
        $target = $this->repo->targetOfFiltered($f);
        $kendalaRaw = $this->repo->kendala($f);
        $bebanRaw = $this->repo->bebanByPengolah($f);
        $bebanTanpaPengolah = $this->repo->rowsWithoutPengolah($f);
        $komposisiRaw = $this->repo->composition($f);
        $trendRaw = $this->repo->trendDaily($f);
        $stamp = $this->repo->activityStamp((int) $f['periode_id']);
        $gridCount = $this->repo->gridCount($f);

        $total = (int) ($summary['total_ruta'] ?? 0);
        $pace = $this->targetPace($periode);

        $m = [
            'target' => $target,
            'total' => $total,
            'sls' => (int) ($summary['total_sls'] ?? 0),
            'dok_ada' => (int) ($summary['dok_ada'] ?? 0),
            'transfer_k' => (int) ($summary['transfer_k'] ?? 0),
            'transfer_kp' => (int) ($summary['transfer_kp'] ?? 0),
            'transfer_seruti' => (int) ($summary['transfer_seruti'] ?? 0),
            'selesai' => (int) ($summary['selesai'] ?? 0),
            'anomali' => (int) ($summary['anomali'] ?? 0),
            'uji_petik' => (int) ($summary['uji_petik'] ?? 0),
            'pending_entri' => (int) ($summary['pending_entri'] ?? 0),
            'pending_validasi' => (int) ($summary['pending_validasi'] ?? 0),
            'dok_ada_k' => (int) ($summary['dok_ada_k'] ?? 0),
            'k_kp' => (int) ($summary['k_kp'] ?? 0),
            'kp_tanpa_k' => (int) ($summary['kp_tanpa_k'] ?? 0),
            'tanpa_tanggal' => (int) ($summary['tanpa_tanggal'] ?? 0),
        ];

        $trend = $this->buildTrend($trendRaw, $m);

        return [
            'kpi' => $this->kpiCards($m, $trend, $pace),
            'stacked' => $this->stackedProgress($m),
            'trend' => $trend,
            'komposisi' => $this->komposisiSegments($komposisiRaw, $total),
            'kendala' => $this->kendalaItems($kendalaRaw, $total),
            'beban' => $this->bebanRanking($bebanRaw, $m, $bebanTanpaPengolah),
            'meta' => [
                'periode_id' => (int) $f['periode_id'],
                'periode_label' => (string) ($periode['label'] ?? '—'),
                'periode_status' => (string) ($periode['status'] ?? '—'),
                'periode_mulai' => (string) ($periode['tgl_mulai'] ?? ''),
                'periode_selesai' => (string) ($periode['tgl_selesai'] ?? ''),
                'target_pace' => $pace,
                'target_pace_display' => $this->pct($pace),
                'terakhir_aktivitas' => (string) ($stamp['ruta_terakhir'] ?? ''),
                'audit_terakhir' => (string) ($stamp['audit_terakhir'] ?? ''),
                'audit_24jam' => (int) ($stamp['audit_24jam'] ?? 0),
                'server_time' => date('Y-m-d H:i:s'),
                'total_filtered' => $total,
            ],
            'consistency' => $this->consistencyReport($m, $gridCount, $komposisiRaw, $bebanRaw, $bebanTanpaPengolah),
        ];
    }

    /**
     * Seri kumulatif dari data harian + garis target linear.
     *
     * @param array<int,array<string,mixed>> $rows
     * @param array<string,mixed> $m
     * @return array<string,mixed>
     */
    private function buildTrend(array $rows, array $m): array
    {
        $keys = ['total', 'dok_ada', 'transfer_k', 'transfer_kp', 'selesai', 'anomali', 'pending'];
        $labels = [];
        $daily = [];
        $acc = array_fill_keys($keys, 0);
        $cum = array_fill_keys($keys, []);

        foreach ($rows as $r) {
            $labels[] = (string) $r['tanggal'];
            $pendingHari = (int) ($r['pending_entri'] ?? 0) + (int) ($r['pending_validasi'] ?? 0);
            $daily[] = [
                'tanggal' => (string) $r['tanggal'],
                'total' => (int) ($r['total'] ?? 0),
                'dok_ada' => (int) ($r['dok_ada'] ?? 0),
                'transfer_k' => (int) ($r['transfer_k'] ?? 0),
                'transfer_kp' => (int) ($r['transfer_kp'] ?? 0),
                'anomali' => (int) ($r['anomali'] ?? 0),
                'pending' => $pendingHari,
            ];
            foreach ($keys as $k) {
                $val = $k === 'pending' ? $pendingHari : (int) ($r[$k] ?? 0);
                $acc[$k] += $val;
                $cum[$k][] = $acc[$k];
            }
        }

        $n = count($labels);
        $targetLine = [];
        if ($n > 0 && $m['target'] > 0) {
            for ($i = 1; $i <= $n; $i++) {
                $targetLine[] = (int) round($m['target'] * ($i / $n));
            }
        }

        return [
            'labels' => $labels,
            'daily' => $daily,
            'series' => [
                ['key' => 'dok_ada', 'label' => 'Dokumen Diterima', 'color' => '#2563eb', 'values' => $cum['dok_ada']],
                ['key' => 'transfer_k', 'label' => 'Entri Data (Transfer K)', 'color' => '#d97706', 'values' => $cum['transfer_k']],
                ['key' => 'transfer_kp', 'label' => 'Valid (Transfer KP)', 'color' => '#15803d', 'values' => $cum['transfer_kp']],
            ],
            'target' => [
                'label' => 'Target linear',
                'color' => '#94a3b8',
                'values' => $targetLine,
            ],
            'meta' => [
                'basis' => 'Tanggal aktivitas terakhir baris ruta (updated_at)',
                'n_hari' => $n,
                'tanpa_tanggal' => (int) $m['tanpa_tanggal'],
                'akhir_total' => (int) ($acc['total'] ?? 0),
                'akhir_dok_ada' => (int) ($acc['dok_ada'] ?? 0),
                'akhir_transfer_k' => (int) ($acc['transfer_k'] ?? 0),
                'akhir_transfer_kp' => (int) ($acc['transfer_kp'] ?? 0),
            ],
        'cumulative' => $cum,
        ];
    }

    /**
     * Tier 1 — kartu KPI eksekutif (nilai, persentase, delta, sparkline, tone).
     *
     * @param array<string,mixed> $m
     * @param array<string,mixed> $trend
     * @return array<int,array<string,mixed>>
     */
    private function kpiCards(array $m, array $trend, ?float $pace): array
    {
        $cum = $trend['cumulative'];
        $target = max(0, (int) $m['target']);
        $total = (int) $m['total'];
        $pending = (int) $m['pending_entri'] + (int) $m['pending_validasi'];

        $deltaPct = static function (?float $pct) use ($pace): ?float {
            return ($pct === null || $pace === null) ? null : round($pct - $pace, 2);
        };
        $deltaText = static function (?float $d): string {
            if ($d === null) {
                return '—';
            }

            return ($d > 0 ? '+' : '') . number_format($d, 1, ',', '.') . ' pp';
        };

        $cards = [];

        // 1. Total Target
        $pctTarget = $target > 0 ? $this->rate($total, $target) : 0.0;
        $cards[] = $this->card(
            'target',
            'Total Target',
            'fa-bullseye',
            $target,
            'ruta',
            $pctTarget,
            $deltaText($deltaPct($pctTarget)),
            $this->tone($pctTarget),
            $cum['total'] ?? [],
            sprintf('%d ruta terdaftar dari %d SLS', $total, (int) $m['sls']),
            [
                ['label' => 'Target ruta', 'value' => $this->num($target)],
                ['label' => 'Ruta terdaftar', 'value' => $this->num($total)],
                ['label' => 'Jumlah SLS', 'value' => $this->num((int) $m['sls'])],
                ['label' => 'Laju target periode', 'value' => $this->pct($pace)],
            ]
        );

        // 2. Progres Selesai
        $pctSelesai = $target > 0 ? $this->rate((int) $m['selesai'], $target) : 0.0;
        $cards[] = $this->card(
            'selesai',
            'Progres Selesai',
            'fa-flag-checkered',
            (int) $m['selesai'],
            'ruta',
            $pctSelesai,
            $deltaText($deltaPct($pctSelesai)),
            $this->tone($pctSelesai),
            $cum['selesai'] ?? [],
            'Kuesioner berstatus selesai',
            [
                ['label' => 'Selesai', 'value' => $this->num((int) $m['selesai'])],
                ['label' => 'Belum selesai', 'value' => $this->num(max(0, $target - (int) $m['selesai']))],
                ['label' => 'Capaian', 'value' => $this->pct($pctSelesai)],
                ['label' => 'Delta vs laju target', 'value' => $deltaText($deltaPct($pctSelesai))],
            ]
        );

        // 3. Dokumen Diterima
        $pctDok = $target > 0 ? $this->rate((int) $m['dok_ada'], $target) : 0.0;
        $cards[] = $this->card(
            'dok_ada',
            'Dokumen Diterima',
            'fa-inbox',
            (int) $m['dok_ada'],
            'ruta',
            $pctDok,
            $deltaText($deltaPct($pctDok)),
            $this->tone($pctDok),
            $cum['dok_ada'] ?? [],
            'Berkas fisik sudah ada di kantor',
            [
                ['label' => 'Diterima', 'value' => $this->num((int) $m['dok_ada'])],
                ['label' => 'Belum diterima', 'value' => $this->num(max(0, $total - (int) $m['dok_ada']))],
                ['label' => 'Capaian', 'value' => $this->pct($pctDok)],
                ['label' => 'Delta vs laju target', 'value' => $deltaText($deltaPct($pctDok))],
            ]
        );

        // 4. Tervalidasi (Transfer KP)
        $pctValid = $target > 0 ? $this->rate((int) $m['transfer_kp'], $target) : 0.0;
        $cards[] = $this->card(
            'transfer_kp',
            'Tervalidasi / Transfer KP',
            'fa-file-circle-check',
            (int) $m['transfer_kp'],
            'ruta',
            $pctValid,
            $deltaText($deltaPct($pctValid)),
            $this->tone($pctValid),
            $cum['transfer_kp'] ?? [],
            'Transfer KP selesai; Transfer Seruti: ' . $this->num((int) $m['transfer_seruti']),
            [
                ['label' => 'Transfer KP', 'value' => $this->num((int) $m['transfer_kp'])],
                ['label' => 'Transfer K (entri)', 'value' => $this->num((int) $m['transfer_k'])],
                ['label' => 'Transfer Seruti', 'value' => $this->num((int) $m['transfer_seruti'])],
                ['label' => 'Capaian', 'value' => $this->pct($pctValid)],
            ]
        );

        // 5. Anomali / Error Kritis (semakin kecil semakin baik)
        $pctAnomali = $total > 0 ? $this->rate((int) $m['anomali'], $total) : 0.0;
        $cards[] = $this->card(
            'anomali',
            'Anomali / Error Kritis',
            'fa-triangle-exclamation',
            (int) $m['anomali'],
            'ruta',
            $pctAnomali,
            '—',
            $this->toneInverted($pctAnomali),
            $cum['anomali'] ?? [],
            'Catatan error KP/Modul + temuan uji petik',
            [
                ['label' => 'Baris berkendala', 'value' => $this->num((int) $m['anomali'])],
                ['label' => 'Temuan uji petik', 'value' => $this->num((int) $m['uji_petik'])],
                ['label' => 'KP tanpa Transfer K', 'value' => $this->num((int) $m['kp_tanpa_k'])],
                ['label' => 'Rasio dari beban', 'value' => $this->pct($pctAnomali)],
            ]
        );

        // 6. Pending Action
        $pctPending = $total > 0 ? $this->rate($pending, $total) : 0.0;
        $cards[] = $this->card(
            'pending',
            'Pending Action',
            'fa-hourglass-half',
            $pending,
            'ruta',
            $pctPending,
            '—',
            $this->toneInverted($pctPending),
            $cum['pending'] ?? [],
            'Menunggu tindak lanjut pengolahan',
            [
                ['label' => 'Menunggu entri (K)', 'value' => $this->num((int) $m['pending_entri'])],
                ['label' => 'Menunggu validasi (KP)', 'value' => $this->num((int) $m['pending_validasi'])],
                ['label' => 'Total pending', 'value' => $this->num($pending)],
                ['label' => 'Rasio dari beban', 'value' => $this->pct($pctPending)],
            ]
        );

        return $cards;
    }

    /**
     * Susun satu kartu KPI lengkap dengan atribut aksesibilitas.
     *
     * @param array<int,int|float> $series
     * @param array<int,array{label:string,value:string}> $detail
     * @return array<string,mixed>
     */
    private function card(
        string $key,
        string $label,
        string $icon,
        int|float $value,
        string $unit,
        float $pct,
        string $deltaText,
        string $tone,
        array $series,
        string $hint,
        array $detail
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'icon' => $icon,
            'value' => $value,
            'value_display' => $this->num((int) round((float) $value)),
            'unit' => $unit,
            'pct' => $pct,
            'pct_display' => $this->pct($pct),
            'delta_display' => $deltaText,
            'tone' => $tone,
            'tone_label' => $this->toneLabel($tone),
            'tone_icon' => $this->toneIcon($tone),
            'series' => array_values(array_map('intval', $series)),
            'hint' => $hint,
            'detail' => $detail,
            'aria' => sprintf(
                '%s: %s %s, capaian %s. %s. %s',
                $label,
                $this->num((int) round((float) $value)),
                $unit,
                $this->pct($pct, 0),
                $this->toneLabel($tone),
                $hint
            ),
        ];
    }

    // ------------------------------------------------------------- tier 2

    /**
     * Stacked progress bar berlapis (kumulatif) terhadap target:
     * Biru = Dokumen Diterima, Kuning = Entri Data, Hijau = Valid/Transfer.
     *
     * @param array<string,mixed> $m
     * @return array<string,mixed>
     */
    private function stackedProgress(array $m): array
    {
        $target = max(1, (int) $m['target']);
        $def = [
            ['key' => 'dok_ada', 'label' => 'Dokumen Diterima', 'color' => '#2563eb', 'bg' => 'dokumen', 'icon' => 'fa-inbox', 'filter' => ['status_dokumen' => 'ADA']],
            ['key' => 'transfer_k', 'label' => 'Entri Data (Transfer K)', 'color' => '#d97706', 'bg' => 'entri', 'icon' => 'fa-keyboard', 'filter' => ['transfer_stage' => 'K']],
            ['key' => 'transfer_kp', 'label' => 'Valid / Transfer KP', 'color' => '#15803d', 'bg' => 'valid', 'icon' => 'fa-circle-check', 'filter' => ['transfer_stage' => 'KP']],
        ];

        $items = [];
        foreach ($def as $d) {
            $n = (int) $m[$d['key']];
            $p = $this->rate($n, $target);
            $items[] = [
                'key' => $d['key'],
                'label' => $d['label'],
                'color' => $d['color'],
                'bg' => $d['bg'],
                'icon' => $d['icon'],
                'n' => $n,
                'n_display' => $this->num($n),
                'pct' => $p,
                'pct_display' => $this->pct($p),
                'filter' => $d['filter'],
                'aria' => sprintf('%s: %s ruta dari target %s (%s)', $d['label'], $this->num($n), $this->num($target), $this->pct($p)),
            ];
        }

        return [
            'basis' => 'Berlapis (kumulatif) — setiap lapisan adalah himpunan bagian lapisan sebelumnya',
            'target' => (int) $m['target'],
            'total' => (int) $m['total'],
            'items' => $items,
        ];
    }

    /**
     * Donut komposisi status: partisi eksklusif & menyeluruh (jumlah = total).
     *
     * @param array<int,array<string,mixed>> $raw
     * @return array<string,mixed>
     */
    private function komposisiSegments(array $raw, int $total): array
    {
        $def = [
            'TERVALIDASI' => [
                'label' => 'Valid / Transfer KP', 'color' => '#15803d', 'icon' => 'fa-circle-check',
                'tone' => self::TONE_OK, 'filter' => ['transfer_stage' => 'KP'],
            ],
            'ENTRI' => [
                'label' => 'Entri Data (Transfer K)', 'color' => '#d97706', 'icon' => 'fa-keyboard',
                'tone' => self::TONE_WARN, 'filter' => ['status_dokumen' => 'ADA', 'transfer_stage' => 'K'],
            ],
            'DOKUMEN' => [
                'label' => 'Dokumen Diterima, Belum Entri', 'color' => '#2563eb', 'icon' => 'fa-inbox',
                'tone' => self::TONE_NEUTRAL, 'filter' => ['status_dokumen' => 'ADA', 'transfer_stage' => 'BELUM_K'],
            ],
            'BELUM' => [
                'label' => 'Dokumen Belum Diterima', 'color' => '#94a3b8', 'icon' => 'fa-hourglass-half',
                'tone' => self::TONE_DANGER, 'filter' => ['status_dokumen' => 'BELUM'],
            ],
        ];

        $byKey = [];
        foreach ($raw as $r) {
            $byKey[(string) $r['bucket']] = $r;
        }

        $segments = [];
        $sum = 0;
        foreach ($def as $key => $d) {
            $n = (int) ($byKey[$key]['n'] ?? 0);
            $anomali = (int) ($byKey[$key]['anomali'] ?? 0);
            $sum += $n;
            $p = $this->rate($n, $total);
            $segments[] = [
                'key' => $key,
                'label' => $d['label'],
                'color' => $d['color'],
                'icon' => $d['icon'],
                'tone' => $d['tone'],
                'tone_label' => $this->toneLabel($d['tone']),
                'n' => $n,
                'n_display' => $this->num($n),
                'pct' => $p,
                'pct_display' => $this->pct($p),
                'anomali' => $anomali,
                'filter' => $d['filter'],
                'aria' => sprintf('%s: %s ruta (%s) dari %s', $d['label'], $this->num($n), $this->pct($p), $this->num($total)),
            ];
        }

        return [
            'basis' => 'Partisi eksklusif — setiap baris masuk tepat satu segmen',
            'total' => $total,
            'sum' => $sum,
            'segments' => $segments,
        ];
    }

    /**
     * Rincian kendala per sumber catatan (bisa bertumpang pada satu baris).
     *
     * @param array<string,mixed> $raw
     * @return array<string,mixed>
     */
    private function kendalaItems(array $raw, int $total): array
    {
        $def = [
            ['key' => 'kp_pengolah', 'label' => 'Error KP — catatan Pengolah', 'color' => '#b91c1c', 'icon' => 'fa-clipboard-check', 'tone' => self::TONE_DANGER],
            ['key' => 'kp_lapangan', 'label' => 'Error KP — konfirmasi Lapangan', 'color' => '#d97706', 'icon' => 'fa-people-arrows', 'tone' => self::TONE_WARN],
            ['key' => 'kp_sosial', 'label' => 'Error KP — tindak lanjut Tim Sosial', 'color' => '#7c3aed', 'icon' => 'fa-user-tie', 'tone' => self::TONE_WARN],
            ['key' => 'm_pengolah', 'label' => 'Error Modul — catatan Pengolah', 'color' => '#b91c1c', 'icon' => 'fa-clipboard-check', 'tone' => self::TONE_DANGER],
            ['key' => 'm_lapangan', 'label' => 'Error Modul — konfirmasi Lapangan', 'color' => '#d97706', 'icon' => 'fa-people-arrows', 'tone' => self::TONE_WARN],
            ['key' => 'm_sosial', 'label' => 'Error Modul — tindak lanjut Tim Sosial', 'color' => '#7c3aed', 'icon' => 'fa-user-tie', 'tone' => self::TONE_WARN],
            ['key' => 'uji_petik', 'label' => 'Temuan Uji Petik Pengawas', 'color' => '#0f766e', 'icon' => 'fa-magnifying-glass-chart', 'tone' => self::TONE_NEUTRAL],
        ];

        $items = [];
        $max = 0;
        foreach ($def as $d) {
            $n = (int) ($raw[$d['key']] ?? 0);
            $max = max($max, $n);
            $items[] = [
                'key' => $d['key'],
                'label' => $d['label'],
                'color' => $d['color'],
                'icon' => $d['icon'],
                'tone' => $d['tone'],
                'tone_label' => $this->toneLabel($d['tone']),
                'n' => $n,
                'n_display' => $this->num($n),
                'pct' => $this->rate($n, $total),
                'pct_display' => $this->pct($this->rate($n, $total)),
                'rel' => $max > 0 ? $this->rate($n, $max) : 0.0,
                'filter' => ['has_error' => '1'],
                'aria' => sprintf('%s: %s baris', $d['label'], $this->num($n)),
            ];
        }

        return [
            'note' => 'Satu baris dapat memuat lebih dari satu kendala sehingga nilai tidak dijumlahkan sebagai persentase.',
            'total' => $total,
            'items' => $items,
        ];
    }

    /**
     * Ranking beban & kinerja per pengolah (sumber Horizontal Bar Chart).
     *
     * @param array<int,array<string,mixed>> $rows
     * @param array<string,mixed> $m
     * @return array<string,mixed>
     */
    private function bebanRanking(array $rows, array $m, int $tanpaPengolah): array
    {
        $total = (int) $m['total'];
        $items = [];
        $sum = 0;

        foreach (array_values($rows) as $i => $r) {
            $n = (int) $r['total_ruta'];
            $sum += $n;
            $capaian = $this->rate((int) $r['transfer_kp'], max(1, $n));
            $tone = $this->tone($capaian);
            $share = $this->rate($n, $total);

            $items[] = [
                'peringkat' => $i + 1,
                'orang_id' => (int) $r['orang_id'],
                'nama' => (string) $r['nama'],
                'total_sls' => (int) $r['total_sls'],
                'total_ruta' => $n,
                'total_ruta_display' => $this->num($n),
                'share_pct' => $share,
                'share_display' => $this->pct($share),
                'dok_ada' => (int) $r['dok_ada'],
                'transfer_k' => (int) $r['transfer_k'],
                'transfer_kp' => (int) $r['transfer_kp'],
                'anomali' => (int) $r['anomali'],
                'capaian_pct' => $capaian,
                'capaian_display' => $this->pct($capaian),
                'tone' => $tone,
                'tone_label' => $this->toneLabel($tone),
                'tone_icon' => $this->toneIcon($tone),
                'filter' => ['pengolah_id' => (int) $r['orang_id']],
                'aria' => sprintf(
                    'Peringkat %d, %s: %s ruta dari %d SLS, capaian transfer KP %s, %s',
                    $i + 1,
                    (string) $r['nama'],
                    $this->num($n),
                    (int) $r['total_sls'],
                    $this->pct($capaian),
                    $this->toneLabel($tone)
                ),
            ];
        }

        $jumlah = count($items);
        $rata = $jumlah > 0 ? round($sum / $jumlah, 2) : 0.0;

        return [
            'total' => $total,
            'sum' => $sum,
            'tanpa_pengolah' => $tanpaPengolah,
            'rata_rata' => $rata,
            'rata_rata_display' => $this->num($rata, 1),
            'jumlah_pengolah' => $jumlah,
            'items' => $items,
        ];
    }

    /**
     * Laporan konsistensi antar-widget (Acceptance Criteria: Zero Data Discrepancy).
     * Setiap butir adalah identitas matematis yang HARUS bernilai benar.
     *
     * @param array<string,mixed> $m
     * @param array<int,array<string,mixed>> $komposisiRaw
     * @param array<int,array<string,mixed>> $bebanRaw
     * @return array<string,mixed>
     */
    public function consistencyReport(
        array $m,
        int $gridCount,
        array $komposisiRaw,
        array $bebanRaw,
        int $tanpaPengolah
    ): array {
        $komposisiSum = 0;
        foreach ($komposisiRaw as $r) {
            $komposisiSum += (int) $r['n'];
        }
        $bebanSum = 0;
        foreach ($bebanRaw as $r) {
            $bebanSum += (int) $r['total_ruta'];
        }

        $checks = [
            ['label' => 'Total KPI = jumlah baris grid', 'a' => (int) $m['total'], 'b' => $gridCount],
            ['label' => 'Jumlah segmen komposisi = total KPI', 'a' => $komposisiSum, 'b' => (int) $m['total']],
            [
                'label' => 'Beban petugas + tanpa pengolah = total KPI',
                'a' => $bebanSum + $tanpaPengolah,
                'b' => (int) $m['total'],
            ],
            [
                'label' => 'Pending entri = dokumen diterima - sudah entri',
                'a' => (int) $m['pending_entri'],
                'b' => (int) $m['dok_ada'] - (int) $m['dok_ada_k'],
            ],
            [
                'label' => 'Pending validasi = entri - (entri dan valid)',
                'a' => (int) $m['pending_validasi'],
                'b' => (int) $m['transfer_k'] - (int) $m['k_kp'],
            ],
        ];

        $result = [];
        $allOk = true;
        foreach ($checks as $c) {
            $ok = $c['a'] === $c['b'];
            $allOk = $allOk && $ok;
            $result[] = [
                'label' => $c['label'],
                'expected' => $c['b'],
                'actual' => $c['a'],
                'ok' => $ok,
                'tone' => $ok ? self::TONE_OK : self::TONE_DANGER,
                'tone_icon' => $ok ? 'fa-circle-check' : 'fa-circle-exclamation',
            ];
        }

        // Pemeriksaan alur: KP seharusnya tidak ada tanpa K.
        $flowOk = (int) $m['kp_tanpa_k'] === 0;
        $result[] = [
            'label' => 'Alur berurutan: tidak ada Valid (KP) tanpa Entri (K)',
            'expected' => 0,
            'actual' => (int) $m['kp_tanpa_k'],
            'ok' => $flowOk,
            'tone' => $flowOk ? self::TONE_OK : self::TONE_WARN,
            'tone_icon' => $flowOk ? 'fa-circle-check' : 'fa-triangle-exclamation',
        ];

        return [
            'ok' => $allOk,
            'checks' => $result,
            'jumlah_ok' => count(array_filter($result, static fn (array $r): bool => $r['ok'])),
            'jumlah_periksa' => count($result),
            'anomali_alur' => $flowOk ? [] : ['Valid (KP) tanpa Entri (K): ' . (int) $m['kp_tanpa_k'] . ' baris'],
            'checked_at' => date('Y-m-d H:i:s'),
        ];
    }

    // -------------------------------------------------------------- tier 3

    /**
     * Data grid granular (server-side pagination + sorting).
     *
     * @param array<string,mixed> $f
     * @return array<string,mixed>
     */
    public function grid(array $f): array
    {
        $perPage = max(1, (int) $f['per_page']);
        $total = $this->repo->gridCount($f);
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $f['page']), $pages);

        $rows = $this->repo->gridRows($f, (string) $f['sort'], (string) $f['dir'], $page, $perPage);

        $data = [];
        foreach ($rows as $r) {
            $dokumen = (string) $r['status_dokumen'] === 'ADA';
            $k = (int) $r['status_transfer_k'] === 1;
            $kp = (int) $r['status_transfer_kp'] === 1;
            $anomali = $r['ket_kp_pengolah'] !== null || $r['ket_m_pengolah'] !== null || $r['uji_petik_pengawas'] !== null;

            if ($kp) {
                $stage = ['key' => 'TERVALIDASI', 'label' => 'Valid / Transfer KP', 'tone' => self::TONE_OK, 'icon' => 'fa-circle-check'];
            } elseif ($k) {
                $stage = ['key' => 'ENTRI', 'label' => 'Entri Data (K)', 'tone' => self::TONE_WARN, 'icon' => 'fa-keyboard'];
            } elseif ($dokumen) {
                $stage = ['key' => 'DOKUMEN', 'label' => 'Dokumen Diterima', 'tone' => self::TONE_NEUTRAL, 'icon' => 'fa-inbox'];
            } else {
                $stage = ['key' => 'BELUM', 'label' => 'Belum Diterima', 'tone' => self::TONE_DANGER, 'icon' => 'fa-hourglass-half'];
            }

            $data[] = [
                'id' => (int) $r['id'],
                'sampel_id' => (int) $r['sampel_id'],
                'nks' => (string) $r['nks'],
                'no_urut_ruta' => (int) $r['no_urut_ruta'],
                'ruta_label' => 'Ruta ' . (int) $r['no_urut_ruta'],
                'kode_full' => (string) ($r['kode_full'] ?? ''),
                'nama_sls' => (string) ($r['nama_sls'] ?? ''),
                'kecamatan' => (string) ($r['nama_kecamatan'] ?? ''),
                'desa' => (string) ($r['nama_desa'] ?? ''),
                'wilayah' => trim((string) ($r['nama_desa'] ?? '') . ', ' . (string) ($r['nama_kecamatan'] ?? ''), ', '),
                'rw_rt' => 'RW ' . (string) ($r['rw'] ?? '-') . '/RT ' . (string) ($r['rt'] ?? '-'),
                'dusun' => (string) ($r['dusun'] ?? ''),
                'pcl' => (string) ($r['nama_pcl'] ?? '—'),
                'pml' => (string) ($r['nama_pml'] ?? '—'),
                'pengolah' => (string) ($r['nama_pengolah'] ?? '—'),
                'pengolah_id' => (int) ($r['pengolah_id'] ?? 0),
                'status_dokumen' => (string) $r['status_dokumen'],
                'status_dokumen_label' => $dokumen ? 'Ada' : 'Belum',
                'status_selesai_label' => (string) $r['status_selesai'] === 'SUDAH' ? 'Selesai' : 'Belum selesai',
                'transfer_k' => $k,
                'transfer_kp' => $kp,
                'transfer_seruti' => (int) $r['status_transfer_seruti'] === 1,
                'stage' => $stage,
                'anomali' => $anomali,
                'anomali_label' => $anomali ? 'Ada catatan' : 'Bersih',
                'tgl_pengiriman' => (string) ($r['tgl_pengiriman'] ?? ''),
                'updated_at' => (string) ($r['updated_at'] ?? ''),
                'detail_url' => '/pengolahan?periode_id=' . (int) $r['periode_id'] . '&q=' . rawurlencode((string) $r['nks']),
            ];
        }

        return [
            'rows' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'pages' => $pages,
            'sort' => (string) $f['sort'],
            'dir' => (string) $f['dir'],
            'dari' => $total === 0 ? 0 : (($page - 1) * $perPage) + 1,
            'sampai' => min($total, $page * $perPage),
        ];
    }

    // ---------------------------------------------------------------- detail

    /**
     * Opsi filter (cascading) untuk periode terpilih.
     *
     * @return array<string,mixed>
     */
    public function options(int $periodeId): array
    {
        return [
            'kecamatan' => $this->repo->optionsKecamatan($periodeId),
            'desa' => $this->repo->optionsDesa($periodeId),
            'petugas' => $this->repo->optionsPetugas($periodeId),
        ];
    }

    /** Apakah pengguna boleh melakukan aksi cepat pengolahan? */
    public function canQuickVerify(array $user): bool
    {
        return in_array((string) ($user['role_code'] ?? 'VIEWER'), self::PENGOLAH_ROLES, true);
    }

    /** Ringkas perbedaan before/after JSON untuk timeline audit. */
    private function diffRingkas(?string $beforeJson, ?string $afterJson): array
    {
        $b = $beforeJson === null ? [] : (json_decode($beforeJson, true) ?: []);
        $a = $afterJson === null ? [] : (json_decode($afterJson, true) ?: []);
        if (!is_array($b) || !is_array($a)) {
            return [];
        }
        $out = [];
        foreach ($a as $k => $v) {
            if (!array_key_exists($k, $b) || (string) $b[$k] === (string) $v) {
                continue;
            }
            $out[] = ['field' => (string) $k, 'before' => (string) ($b[$k] ?? ''), 'after' => (string) ($v ?? '')];
        }

        return array_slice($out, 0, 6);
    }

    /**
     * Detail baris ruta + jejak audit untuk drawer Quick View.
     *
     * @return array<string,mixed>
     */
    public function detail(int $rutaId, array $user): array
    {
        $row = $this->repo->rutaDetail($rutaId);
        if ($row === null) {
            throw new InvalidArgumentException('Data ruta tidak ditemukan.');
        }

        $periodeId = (int) $row['periode_id'];
        $status = $this->periodeStatus($periodeId);
        $canQuick = $this->canQuickVerify($user) && $status === 'AKTIF';
        $alasan = [];
        if (!$this->canQuickVerify($user)) {
            $alasan[] = 'Peran Anda tidak berwenang mengubah status pengolahan.';
        }
        if ($status !== 'AKTIF') {
            $alasan[] = 'Periode berstatus ' . ($status === '' ? 'tidak diketahui' : $status) . ' (bukan AKTIF).';
        }

        $trail = [];
        foreach ($this->repo->rutaAuditTrail($rutaId, 10) as $t) {
            $trail[] = [
                'id' => (int) $t['id'],
                'aksi' => (string) $t['aksi'],
                'user' => (string) ($t['nama_user'] ?? 'Sistem'),
                'waktu' => (string) $t['created_at'],
                'ip' => (string) ($t['ip_address'] ?? ''),
                'perubahan' => $this->diffRingkas(
                    is_string($t['before_json'] ?? null) ? $t['before_json'] : null,
                    is_string($t['after_json'] ?? null) ? $t['after_json'] : null
                ),
            ];
        }

        $klasifikasi = (int) ($row['klasifikasi'] ?? 0);

        return [
            'row' => [
                'id' => $rutaId,
                'nks' => (string) $row['nks'],
                'ruta_label' => 'Ruta ' . (int) $row['no_urut_ruta'],
                'kode_full' => (string) ($row['kode_full'] ?? ''),
                'kecamatan' => (string) ($row['nama_kecamatan'] ?? ''),
                'desa' => (string) ($row['nama_desa'] ?? ''),
                'nama_sls' => (string) ($row['nama_sls'] ?? ''),
                'dusun' => (string) ($row['dusun'] ?? ''),
                'rw' => (string) ($row['rw'] ?? ''),
                'rt' => (string) ($row['rt'] ?? ''),
                'jml_kk' => (int) ($row['jml_kk'] ?? 0),
                'klasifikasi' => $klasifikasi === 1 ? 'Perkotaan' : ($klasifikasi === 2 ? 'Perdesaan' : '—'),
                'pcl' => (string) ($row['nama_pcl'] ?? '—'),
                'pml' => (string) ($row['nama_pml'] ?? '—'),
                'pengolah' => (string) ($row['nama_pengolah'] ?? '—'),
                'hp_pml' => (string) ($row['hp_pml'] ?? ''),
                'status_dokumen' => (string) $row['status_dokumen'],
                'status_selesai' => (string) $row['status_selesai'],
                'transfer_k' => (int) $row['status_transfer_k'] === 1,
                'transfer_kp' => (int) $row['status_transfer_kp'] === 1,
                'transfer_seruti' => (int) $row['status_transfer_seruti'] === 1,
                'tgl_pengiriman' => (string) ($row['tgl_pengiriman'] ?? ''),
                'ttd_sos' => (string) ($row['ttd_sos'] ?? ''),
                'ttd_ipds' => (string) ($row['ttd_ipds'] ?? ''),
                'ket_kp_pengolah' => (string) ($row['ket_kp_pengolah'] ?? ''),
                'ket_kp_lapangan' => (string) ($row['ket_kp_lapangan'] ?? ''),
                'ket_kp_sosial' => (string) ($row['ket_kp_sosial'] ?? ''),
                'ket_m_pengolah' => (string) ($row['ket_m_pengolah'] ?? ''),
                'uji_petik_pengawas' => (string) ($row['uji_petik_pengawas'] ?? ''),
                'updated_at' => (string) ($row['updated_at'] ?? ''),
                'periode_id' => $periodeId,
            ],
            'riwayat' => $trail,
            'aksi' => [
                'can_quick_verify' => $canQuick,
                'alasan' => $alasan,
                'fields' => self::QUICK_FIELDS,
                'detail_url' => '/pengolahan?periode_id=' . $periodeId . '&q=' . rawurlencode((string) $row['nks']),
            ],
        ];
    }

    /**
     * Aksi cepat ubah status pengolahan. Wajib periode AKTIF, peran berwenang,
     * dan SELALU tercatat ke audit_logs (before/after) via PengolahanService.
     *
     * @param array<string,mixed> $payload
     * @return array<string,mixed>
     */
    public function quickUpdate(int $rutaId, array $payload, array $user, string $ip, string $userAgent): array
    {
        $detail = $this->repo->rutaDetail($rutaId);
        if ($detail === null) {
            throw new InvalidArgumentException('Data ruta tidak ditemukan.');
        }

        $periodeId = (int) $detail['periode_id'];
        $status = $this->periodeStatus($periodeId);
        if ($status !== 'AKTIF') {
            throw new RuntimeException('Aksi cepat hanya tersedia pada periode AKTIF (status saat ini: ' . ($status === '' ? 'tidak diketahui' : $status) . ').');
        }
        if (!$this->canQuickVerify($user)) {
            throw new RuntimeException('Aksi cepat pengolahan hanya dapat dilakukan oleh petugas pengolahan.');
        }

        $data = [];
        foreach (self::QUICK_FIELDS as $field) {
            if (!array_key_exists($field, $payload)) {
                continue;
            }
            if ($field === 'status_dokumen') {
                $v = strtoupper(trim((string) $payload[$field]));
                if (in_array($v, ['BELUM', 'ADA'], true)) {
                    $data[$field] = $v;
                }
                continue;
            }
            $data[$field] = !empty($payload[$field]) ? 1 : 0;
        }

        if ($data === []) {
            throw new InvalidArgumentException('Tidak ada perubahan status yang dikirim.');
        }

        // Penegakan aturan (role, binaan) + audit trail dilakukan PengolahanService.
        $actor = $user;
        $actor['ip'] = $ip;
        $actor['user_agent'] = $userAgent;
        $after = $this->pengolahan->updateRuta($rutaId, $data, $actor);

        return [
            'ruta_id' => $rutaId,
            'changed' => array_keys($data),
            'status_dokumen' => (string) ($after['status_dokumen'] ?? ''),
            'transfer_k' => (int) ($after['status_transfer_k'] ?? 0) === 1,
            'transfer_kp' => (int) ($after['status_transfer_kp'] ?? 0) === 1,
            'transfer_seruti' => (int) ($after['status_transfer_seruti'] ?? 0) === 1,
        ];
    }

    /**
     * Aksi massal transfer (baris terpilih di grid).
     * Menegakkan periode AKTIF + peran, lalu mendelegasikan ke
     * PengolahanService::batchTransfer (whitelist field + audit trail).
     *
     * @param array<int,int|string> $rutaIds
     * @return array<string,mixed>
     */
    public function bulkTransfer(array $rutaIds, string $field, bool $value, int $periodeId, array $user, string $ip, string $userAgent): array
    {
        $allowed = ['status_transfer_k', 'status_transfer_kp', 'status_transfer_seruti'];
        if (!in_array($field, $allowed, true)) {
            throw new InvalidArgumentException('Bidang transfer tidak valid. Pilihan: ' . implode(', ', $allowed) . '.');
        }

        $ids = [];
        foreach ($rutaIds as $id) {
            $n = (int) $id;
            if ($n > 0) {
                $ids[$n] = $n;
            }
        }
        $ids = array_values($ids);
        if ($ids === []) {
            throw new InvalidArgumentException('Tidak ada baris yang dipilih.');
        }
        if (count($ids) > 500) {
            throw new InvalidArgumentException('Maksimal 500 baris per aksi massal.');
        }
        if ($periodeId <= 0) {
            throw new InvalidArgumentException('Periode tidak valid.');
        }

        $status = $this->periodeStatus($periodeId);
        if ($status !== 'AKTIF') {
            throw new RuntimeException('Aksi massal hanya tersedia pada periode AKTIF (status saat ini: ' . ($status === '' ? 'tidak diketahui' : $status) . ').');
        }
        if (!$this->canQuickVerify($user)) {
            throw new RuntimeException('Aksi massal pengolahan hanya dapat dilakukan oleh petugas pengolahan.');
        }

        $actor = $user;
        $actor['ip'] = $ip;
        $actor['user_agent'] = $userAgent;

        $count = $this->pengolahan->batchTransfer([
            'periode_id' => $periodeId,
            'ruta_ids' => $ids,
            'field' => $field,
            'value' => $value ? 1 : 0,
        ], $actor);

        return ['field' => $field, 'value' => $value, 'diminta' => count($ids), 'terupdate' => $count];
    }

    /**
     * Baris lengkap untuk ekspor (tanpa pagination), dibatasi agar aman.
     *
     * @param array<string,mixed> $f
     * @return array{0:array<int,string>,1:array<int,array<int,string|int>>}
     */
    public function exportRows(array $f, int $limit = 20000): array
    {
        $headers = [
            'NKS', 'Ruta', 'Kode SLS', 'Kecamatan', 'Desa', 'SLS', 'RW/RT',
            'PCL', 'PML', 'Pengolah', 'Dokumen', 'Selesai',
            'Transfer K', 'Transfer KP', 'Transfer Seruti', 'Kendala',
            'Tgl Kirim', 'Aktivitas Terakhir',
        ];

        $rows = [];
        $count = 0;
        $maxPage = (int) ceil(min($limit, 20000) / 100);
        for ($p = 1; $p <= $maxPage; $p++) {
            $f['page'] = $p;
            $f['per_page'] = 100;
            $grid = $this->grid($f);
            foreach ($grid['rows'] as $r) {
                if ($count >= $limit) {
                    break 2;
                }
                $rows[] = [
                    $r['nks'], $r['no_urut_ruta'], $r['kode_full'], $r['kecamatan'], $r['desa'], $r['nama_sls'], $r['rw_rt'],
                    $r['pcl'], $r['pml'], $r['pengolah'], $r['status_dokumen_label'], $r['status_selesai_label'],
                    $r['transfer_k'] ? 'Ya' : 'Tidak', $r['transfer_kp'] ? 'Ya' : 'Tidak', $r['transfer_seruti'] ? 'Ya' : 'Tidak',
                    $r['anomali_label'], $r['tgl_pengiriman'], $r['updated_at'],
                ];
                $count++;
            }
            if ($grid['pages'] <= $p) {
                break;
            }
        }

        return [$headers, $rows];
    }
}