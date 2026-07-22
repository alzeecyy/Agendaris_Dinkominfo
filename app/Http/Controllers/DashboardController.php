<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Agenda;
use App\Models\Presensi;
use App\Models\Bidang;
use App\Models\Notulensi;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the Monthly Overview Dashboard (Role-based).
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Admin dashboard statistics and lists
        if ($user->isAdmin()) {
            $stats = [
                'total_users' => \App\Models\User::where('role', '!=', 'admin')->count(),
                'active_users' => \App\Models\User::where('role', '!=', 'admin')->where('active', true)->count(),
                'inactive_users' => \App\Models\User::where('role', '!=', 'admin')->where('active', false)->count(),
                'total_bidang' => \App\Models\Bidang::count(),
                'total_agenda' => \App\Models\Agenda::count(),
                'total_notulensi' => \App\Models\Notulensi::count(),
                'approved_notulensi' => \App\Models\Notulensi::where('status', 'disetujui')->count(),
                'pending_notulensi' => \App\Models\Notulensi::where('status', 'menunggu_review')->count(),
            ];
            
            $recentUsers = \App\Models\User::where('role', '!=', 'admin')
                ->with('bidang')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();
                
            $recentAgendas = \App\Models\Agenda::with('sekretaris.bidang')
                ->orderBy('tanggal', 'desc')
                ->orderBy('jam_mulai', 'desc')
                ->take(5)
                ->get();

            return view('admin.dashboard', compact('stats', 'recentUsers', 'recentAgendas'));
        }

        // Get the selected month from request (default is current month)
        $monthStr = $request->input('month', Carbon::today()->format('Y-m'));
        $selectedMonth = Carbon::parse($monthStr . '-01');

        // Build monthly calendar grid (Monday to Sunday)
        $startOfGrid = $selectedMonth->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endOfGrid = $selectedMonth->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $gridDates = [];
        $curr = $startOfGrid->copy();
        while ($curr->lte($endOfGrid)) {
            $gridDates[] = $curr->copy();
            $curr->addDay();
        }

        // Fetch all agendas in the grid dates range
        $rawAgendas = Agenda::whereBetween('tanggal', [$startOfGrid->toDateString(), $endOfGrid->toDateString()])
            ->with('sekretaris.bidang')
            ->get();

        // Group agendas by date with access masking
        $agendasByDate = [];
        foreach ($gridDates as $date) {
            $agendasByDate[$date->toDateString()] = [];
        }

        foreach ($rawAgendas as $agenda) {
            $hasAccess = $user->hasAccessToAgenda($agenda);

            // Hide non-accessible agendas completely for staff/pegawai role
            if ($user->role === 'staff' && !$hasAccess) {
                continue;
            }

            $dateStr = $agenda->tanggal->toDateString();

            if (isset($agendasByDate[$dateStr])) {
                $agendasByDate[$dateStr][] = (object) [
                    'id' => $agenda->id,
                    'judul' => $hasAccess ? $agenda->judul : 'Rapat Terbatas',
                    'jam_mulai' => substr($agenda->jam_mulai, 0, 5),
                    'jam_selesai' => substr($agenda->jam_selesai, 0, 5),
                    'bidang_id' => $agenda->sekretaris->bidang_id ?? null,
                    'has_access' => $hasAccess,
                    'kategori' => $agenda->kategori,
                ];
            }
        }

        // Calculate role-based stats KPI cards & highlights
        $kpi = [];
        $highlights = [];
        $links = [];
        $todayStr = Carbon::today()->toDateString();

        if ($user->role === 'staff') {
            // Staff KPI 1: Accessible Agendas this week
            $startOfWeek = Carbon::today()->startOfWeek(Carbon::MONDAY);
            $endOfWeek = Carbon::today()->endOfWeek(Carbon::SUNDAY);
            $weekAgendas = Agenda::whereBetween('tanggal', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->get()
                ->filter(fn($a) => $user->hasAccessToAgenda($a));
            
            $kpi['week_agendas'] = $weekAgendas->count();
            $links['week_agendas'] = route('calendar');

            // Staff KPI 2: Pending/Unfilled presence
            $accessiblePastAgendas = Agenda::where('butuh_presensi', true)
                ->where('tanggal', '<=', $todayStr)
                ->get()
                ->filter(fn($a) => $user->hasAccessToAgenda($a));

            $submittedPresenceIds = Presensi::where('user_id', $user->id)
                ->pluck('agenda_id')
                ->toArray();

            $pendingPresenceAgendas = $accessiblePastAgendas
                ->filter(fn($a) => !in_array($a->id, $submittedPresenceIds));

            $kpi['pending_presence'] = $pendingPresenceAgendas->count();
            $firstPending = $pendingPresenceAgendas->first();
            $links['pending_presence'] = $firstPending ? route('agenda.show', $firstPending->id) : null;

            // Staff Highlight Alerts
            $todayPendingPresences = Agenda::where('tanggal', $todayStr)
                ->where('butuh_presensi', true)
                ->get()
                ->filter(fn($a) => $user->hasAccessToAgenda($a) && !in_array($a->id, $submittedPresenceIds));

            foreach ($todayPendingPresences as $agenda) {
                $highlights[] = [
                    'type' => 'presence',
                    'agenda_id' => $agenda->id,
                    'text' => "Agenda hari ini: '{$agenda->judul}' jam " . substr($agenda->jam_mulai, 0, 5) . " — Anda belum mengisi absen mandiri.",
                    'action_text' => 'Absen Sekarang',
                    'url' => route('agenda.show', $agenda->id),
                ];
            }

        } elseif ($user->role === 'sekretaris_bidang') {
            // Sekre Bidang KPI 1: Bidang agenda count this month
            $kpi['bidang_month_agendas'] = Agenda::whereMonth('tanggal', $selectedMonth->month)
                ->whereYear('tanggal', $selectedMonth->year)
                ->whereHas('sekretaris', function($q) use ($user) {
                    $q->where('bidang_id', $user->bidang_id);
                })
                ->count();
            $links['bidang_month_agendas'] = route('calendar');

            // Sekre Bidang KPI 2: Bidang notulensi waiting review
            $pendingReviews = Notulensi::where('status', 'menunggu_review')
                ->whereHas('agenda', function($q) use ($user) {
                    $q->whereHas('sekretaris', function($sq) use ($user) {
                        $sq->where('bidang_id', $user->bidang_id);
                    });
                })
                ->get();
            $kpi['bidang_pending_reviews'] = $pendingReviews->count();
            $firstPending = $pendingReviews->first();
            $links['bidang_pending_reviews'] = $firstPending ? route('notulensi.review', $firstPending->agenda_id) : null;

            // Sekre Bidang Highlights: Overdue pending reviews (> 3 days)
            $overdueReviews = Notulensi::where('status', 'menunggu_review')
                ->where('created_at', '<=', Carbon::now()->subDays(3))
                ->whereHas('agenda', function($q) use ($user) {
                    $q->whereHas('sekretaris', function($sq) use ($user) {
                        $sq->where('bidang_id', $user->bidang_id);
                    });
                })
                ->count();

            if ($overdueReviews > 0) {
                $highlights[] = [
                    'type' => 'alert',
                    'text' => "Ada {$overdueReviews} notulensi rapat bidang Anda yang menunggu review ketua bidang selama lebih dari 3 hari.",
                ];
            }

            // Sekre Bidang Highlights: Unfilled basic letters for tomorrow's meetings
            $tomorrowStr = Carbon::tomorrow()->toDateString();
            $tomorrowPendingLetters = Agenda::where('tanggal', $tomorrowStr)
                ->whereNull('nomor_surat_dasar')
                ->where('sekretaris_id', $user->id)
                ->get();

            foreach ($tomorrowPendingLetters as $agenda) {
                $highlights[] = [
                    'type' => 'letter',
                    'agenda_id' => $agenda->id,
                    'text' => "Agenda besok '{$agenda->judul}' belum diisi nomor surat dasarnya.",
                    'action_text' => 'Lengkapi Data',
                    'url' => route('agenda.show', $agenda->id),
                ];
            }

        } elseif ($user->role === 'sekretaris_master') {
            // Sekre Master KPI 1: All bidangs agendas this month
            $kpi['master_month_agendas'] = Agenda::whereMonth('tanggal', $selectedMonth->month)
                ->whereYear('tanggal', $selectedMonth->year)
                ->count();
            $links['master_month_agendas'] = route('calendar');

            // Sekre Master KPI 2: All notulensi waiting review > 3 days (Overdue alerts)
            $overdueReviews = Notulensi::where('status', 'menunggu_review')
                ->where('created_at', '<=', Carbon::now()->subDays(3))
                ->get();
            $kpi['master_overdue_reviews'] = $overdueReviews->count();
            $firstOverdue = $overdueReviews->first();
            $links['master_overdue_reviews'] = $firstOverdue ? route('notulensi.review', $firstOverdue->agenda_id) : null;

            if ($kpi['master_overdue_reviews'] > 0) {
                $highlights[] = [
                    'type' => 'alert',
                    'text' => "Peringatan: terdapat {$kpi['master_overdue_reviews']} notulensi dinas yang tertunda dan belum disahkan pimpinan selama lebih dari 3 hari.",
                ];
            }

            // Tomorrow's master meeting warning
            $tomorrowStr = Carbon::tomorrow()->toDateString();
            $tomorrowPendingLetters = Agenda::where('tanggal', $tomorrowStr)
                ->whereNull('nomor_surat_dasar')
                ->where('sekretaris_id', $user->id)
                ->get();

            foreach ($tomorrowPendingLetters as $agenda) {
                $highlights[] = [
                    'type' => 'letter',
                    'agenda_id' => $agenda->id,
                    'text' => "Rapat koordinasi besok '{$agenda->judul}' belum diisi nomor surat dasarnya.",
                    'action_text' => 'Lengkapi Data',
                    'url' => route('agenda.show', $agenda->id),
                ];
            }

        } elseif ($user->role === 'ketua_bidang') {
            // Ketua Bidang KPI: Pending reviews in their bidang
            $pendingReviews = Notulensi::where('status', 'menunggu_review')
                ->whereHas('agenda', function($q) use ($user) {
                    $q->whereHas('sekretaris', function($sq) use ($user) {
                        $sq->where('bidang_id', $user->bidang_id);
                    });
                })
                ->with('agenda')
                ->get();

            $kpi['ketua_pending_reviews'] = $pendingReviews->count();
            $firstPending = $pendingReviews->first();
            $links['ketua_pending_reviews'] = $firstPending ? route('notulensi.review', $firstPending->agenda_id) : null;

            foreach ($pendingReviews as $notulensi) {
                $highlights[] = [
                    'type' => 'review',
                    'agenda_id' => $notulensi->agenda_id,
                    'text' => "Notulensi rapat '{$notulensi->agenda->judul}' menunggu keputusan persetujuan Anda.",
                    'action_text' => 'Tinjau & Sahkan',
                    'url' => route('notulensi.review', $notulensi->agenda_id),
                ];
            }

        } elseif ($user->role === 'ketua_master') {
            // Ketua Master (Kepala Dinas) KPI: Pending reviews of Dinas / Lintas Bidang (sekretaris is null or master)
            $pendingReviews = Notulensi::where('status', 'menunggu_review')
                ->whereHas('agenda', function($q) {
                    $q->whereNull('sekretaris_id')
                      ->orWhereHas('sekretaris', function($sq) {
                          $sq->whereNull('bidang_id');
                      });
                })
                ->with('agenda')
                ->get();

            $kpi['ketua_pending_reviews'] = $pendingReviews->count();
            $firstPending = $pendingReviews->first();
            $links['ketua_pending_reviews'] = $firstPending ? route('notulensi.review', $firstPending->agenda_id) : null;

            foreach ($pendingReviews as $notulensi) {
                $highlights[] = [
                    'type' => 'review',
                    'agenda_id' => $notulensi->agenda_id,
                    'text' => "Notulensi dinas '{$notulensi->agenda->judul}' menunggu pengesahan Anda.",
                    'action_text' => 'Tinjau & Sahkan',
                    'url' => route('notulensi.review', $notulensi->agenda_id),
                ];
            }
        }
        // Recent activity history (max 4 entries)
        $pastAgendas = Agenda::where('tanggal', '<', $todayStr)
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->get()
            ->filter(fn($agenda) => $user->hasAccessToAgenda($agenda));

        $presensis = Presensi::where('user_id', $user->id)->get()->keyBy('agenda_id');

        $riwayatRingkas = $pastAgendas->take(4)->map(function ($agenda) use ($presensis) {
            $status = $presensis->has($agenda->id) ? $presensis[$agenda->id]->status : null;
            if ($agenda->butuh_presensi && !$status && $agenda->isPresensiExpired()) {
                $status = 'alfa';
            }
            return (object) [
                'id' => $agenda->id,
                'judul' => $agenda->judul,
                'tanggal' => $agenda->tanggal,
                'jam_mulai' => $agenda->jam_mulai,
                'jam_selesai' => $agenda->jam_selesai,
                'status_kehadiran' => $status,
                'notulensi_status' => $agenda->notulensi->status ?? null,
            ];
        });

        return view('dashboard', compact('selectedMonth', 'gridDates', 'agendasByDate', 'kpi', 'highlights', 'riwayatRingkas', 'links'));
    }

    /**
     * Display the detailed weekly grid calendar dashboard.
     */
    public function calendar(Request $request)
    {
        $user = Auth::user();

        // Admin does not have access to agenda content, redirect to admin user panel
        if ($user->isAdmin()) {
            return redirect()->route('admin.users.index');
        }

        // Get the selected date from request (default is today)
        $selectedDateStr = $request->input('date', Carbon::today()->toDateString());
        $selectedDate = Carbon::parse($selectedDateStr);

        // Calculate the start date (we display 7 consecutive days starting from Monday of that week)
        $startOfWeek = $selectedDate->copy()->startOfWeek(Carbon::MONDAY);
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = $startOfWeek->copy()->addDays($i);
        }

        $startDateStr = $dates[0]->toDateString();
        $endDateStr = $dates[6]->toDateString();

        // Get all agendas within this range
        // Fetch all agendas for the selected 7-day week range with eager loading
        $rawAgendas = Agenda::whereBetween('tanggal', [$startDateStr, $endDateStr])
            ->with('sekretaris.bidang')
            ->orderBy('jam_mulai')
            ->get();

        // Fetch all agendas for the whole month grid of the mini-calendar to display dots
        $startOfMiniCalendar = $selectedDate->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $endOfMiniCalendar = $selectedDate->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);
        
        $rawMiniCalendarAgendas = Agenda::whereBetween('tanggal', [$startOfMiniCalendar->toDateString(), $endOfMiniCalendar->toDateString()])
            ->get();
            
        $miniCalendarDatesWithEvents = $rawMiniCalendarAgendas
            ->filter(fn($agenda) => $user->hasAccessToAgenda($agenda))
            ->pluck('tanggal')
            ->map(fn($t) => $t->toDateString())
            ->unique()
            ->toArray();

        $allBidangs = Bidang::all()->keyBy('id');

        // Mask/Filter agendas based on user access
        $agendas = $rawAgendas->map(function ($agenda) use ($user, $allBidangs) {
            $hasAccess = $user->hasAccessToAgenda($agenda);
            $hakAkses = $agenda->hak_akses ?? [];

            // Determine badge label based on actual audience scope:
            // - "Semua" if the agenda is open to everyone (semua_orang)
            // - Bidang abbreviation(s) if restricted to specific bidangs
            if (in_array('semua_orang', $hakAkses)) {
                $badgeLabel = 'Semua';
            } else {
                $matchedSingkatans = [];
                foreach ($hakAkses as $id) {
                    if (isset($allBidangs[$id])) {
                        $matchedSingkatans[] = $allBidangs[$id]->singkatan;
                    }
                }
                if (!empty($matchedSingkatans)) {
                    $badgeLabel = implode(', ', $matchedSingkatans);
                } else {
                    $badgeLabel = $agenda->sekretaris->bidang->singkatan ?? 'Dinas';
                }
            }

            return (object) [
                'id' => $agenda->id,
                'judul' => $hasAccess ? $agenda->judul : 'Rapat Terbatas (' . ($agenda->sekretaris->bidang->singkatan ?? 'Dinkominfo') . ')',
                'tanggal' => $agenda->tanggal->toDateString(),
                'jam_mulai' => $agenda->jam_mulai,
                'jam_selesai' => $agenda->jam_selesai,
                'lokasi' => $hasAccess ? $agenda->lokasi : 'Lokasi Terbatas',
                'kategori' => $agenda->kategori,
                'butuh_presensi' => $agenda->butuh_presensi,
                'has_access' => $hasAccess,
                'singkatan_bidang' => $badgeLabel,
                'bidang_id' => $agenda->sekretaris->bidang_id ?? null,
                'hak_akses' => $hakAkses,
            ];
        });

        // Hide non-accessible agendas completely for staff/pegawai role
        if ($user->role === 'staff') {
            $agendas = $agendas->filter(fn($a) => $a->has_access);
        }

        // Group agendas by date for easier rendering in the grid
        $agendasByDate = [];
        foreach ($dates as $date) {
            $dateStr = $date->toDateString();
            $agendasByDate[$dateStr] = [];
            foreach ($agendas as $agenda) {
                if ($agenda->tanggal === $dateStr) {
                    $agendasByDate[$dateStr][] = $agenda;
                }
            }
            
            // Calculate column-splitting offsets for overlapping events on the same day
            $agendasByDate[$dateStr] = $this->calculateOverlaps($agendasByDate[$dateStr]);
        }

        // Get list of all Bidangs to pass to "Tambah Agenda" form
        $bidangs = Bidang::orderBy('nama')->get();

        // Find today's events for the highlighting/side summary panel, filtered by bidang if not master
        $todayStr = Carbon::today()->toDateString();
        $todayAgendas = $agendas->filter(function($a) use ($todayStr, $user) {
            if ($a->tanggal !== $todayStr) {
                return false;
            }
            // Masters (sekretaris_master, ketua_master) can see all agendas regardless of bidang_id
            if ($user->isSekretarisMaster() || $user->isKetuaMaster()) {
                return true;
            }
            // Bidang-level roles: filter by their own bidang or semua_orang
            if ($user->bidang_id) {
                return in_array((string)$user->bidang_id, $a->hak_akses) || in_array('semua_orang', $a->hak_akses);
            }
            return true;
        });

        return view('calendar', compact('dates', 'selectedDate', 'agendasByDate', 'bidangs', 'todayAgendas', 'miniCalendarDatesWithEvents'));
    }

    /**
     * Calculate overlap positions for events to display side-by-side (split-column).
     */
    private function calculateOverlaps($events)
    {
        if (empty($events)) {
            return [];
        }

        // Sort events by start time
        usort($events, function($a, $b) {
            return strcmp($a->jam_mulai, $b->jam_mulai);
        });

        $groups = [];
        
        // Group overlapping events together
        foreach ($events as $event) {
            $placed = false;
            foreach ($groups as &$group) {
                $overlaps = false;
                foreach ($group as $ge) {
                    if ($this->isOverlapping($event->jam_mulai, $event->jam_selesai, $ge->jam_mulai, $ge->jam_selesai)) {
                        $overlaps = true;
                        break;
                    }
                }
                if ($overlaps) {
                    $group[] = $event;
                    $placed = true;
                    break;
                }
            }
            if (!$placed) {
                $groups[] = [$event];
            }
        }
        unset($group); // Unset the reference to avoid polluting subsequent loops

        $processedEvents = [];
        
        // Determine column slots
        foreach ($groups as $group) {
            $columns = [];
            
            foreach ($group as $event) {
                $colIndex = 0;
                $placed = false;
                
                while (!$placed) {
                    if (!isset($columns[$colIndex])) {
                        $columns[$colIndex] = $event->jam_selesai;
                        $event->col_index = $colIndex;
                        $placed = true;
                    } else {
                        if (strcmp($columns[$colIndex], $event->jam_mulai) <= 0) {
                            $columns[$colIndex] = $event->jam_selesai;
                            $event->col_index = $colIndex;
                            $placed = true;
                        } else {
                            $colIndex++;
                        }
                    }
                }
            }

            $totalCols = count($columns);
            foreach ($group as $event) {
                $event->total_cols = $totalCols;
                $processedEvents[] = $event;
            }
        }

        return $processedEvents;
    }

    /**
     * Check if two time ranges overlap.
     */
    private function isOverlapping($startA, $endA, $startB, $endB)
    {
        return max($startA, $startB) < min($endA, $endB);
    }

    /**
     * Show user's activity history.
     */
    public function riwayat()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            return redirect()->route('admin.users.index');
        }

        $agendas = Agenda::where('butuh_presensi', true)
            ->with(['notulensi', 'sekretaris.bidang'])
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai', 'desc')
            ->get()
            ->filter(fn($agenda) => $user->hasAccessToAgenda($agenda));

        $presensis = Presensi::where('user_id', $user->id)
            ->get()
            ->keyBy('agenda_id');

        $riwayatData = $agendas->map(function ($agenda) use ($presensis) {
            $status = $presensis->has($agenda->id) ? $presensis[$agenda->id]->status : null;
            if (!$status && $agenda->isPresensiExpired()) {
                $status = 'alfa';
            }
            
            return (object) [
                'id' => $agenda->id,
                'judul' => $agenda->judul,
                'tanggal' => $agenda->tanggal,
                'jam_mulai' => $agenda->jam_mulai,
                'jam_selesai' => $agenda->jam_selesai,
                'status_kehadiran' => $status,
                'notulensi_status' => $agenda->notulensi->status ?? null,
                'notulensi' => $agenda->notulensi,
                'kategori' => $agenda->kategori,
                'lokasi' => $agenda->lokasi,
            ];
        });

        return view('riwayat.index', compact('riwayatData'));
    }
}
