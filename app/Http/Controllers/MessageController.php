<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $userRole = Auth::user()->role;
        
        $query = Message::query();

        // 1. Filter Status (Masuk/Terkirim)
        if ($request->status === 'terkirim') {
            $query->where('sender_id', $userId);
        } elseif ($request->status === 'masuk') {
            $query->where(function($q) use ($userId, $userRole) {
                $q->where('receiver_id', $userId)
                ->orWhere('target_role', $userRole)
                ->orWhere('target_role', 'all');
            })->where('sender_id', '!=', $userId);
        } else {
            $query->where(function($q) use ($userId, $userRole) {
                $q->where('receiver_id', $userId)
                ->orWhere('target_role', $userRole)
                ->orWhere('target_role', 'all')
                ->orWhere('sender_id', $userId);
            });
        }

        // 2. Filter Bulan (Baru)
        if ($request->filled('month')) {
            $query->whereMonth('created_at', $request->month);
        }

        // 3. Filter Tahun (Baru)
        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        $messages = $query->with('sender', 'receiver')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('messages.index', compact('messages'));
    }

    public function create(Request $request)
    {
        $users = User::where('id', '!=', Auth::id())->get();
        
        // Logika tambahan untuk fitur balas
        $replyTo = null;
        if ($request->has('reply_to')) {
            $replyTo = Message::with('sender')->find($request->reply_to);
        }
        
        return view('messages.create', compact('users', 'replyTo'));
    }

    public function store(Request $request)
    {
        // 1. Validasi disesuaikan dengan field yang ada di View
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'receiver_id' => 'required', // Menangkap input dari select name="receiver_id"
        ]);

        $messageData = [
            'sender_id' => Auth::id(),
            'subject' => $request->subject,
            'body' => $request->body,
        ];

        // 2. Logika Penentuan Penerima berdasarkan nilai dropdown
        if ($request->receiver_id === 'all') {
            // Jika pilih "Semua", kita set target_role ke null atau kades/super_role
            // Tergantung bagaimana Anda ingin mendefinisikan "semua" di database
            $messageData['target_role'] = 'all'; 
            $messageData['receiver_id'] = null;
        } else {
            // Jika pilih individu, masukkan ID user
            $messageData['receiver_id'] = $request->receiver_id;
            $messageData['target_role'] = null;
        }

        Message::create($messageData);

        return redirect()->route('messages.index')->with('success', 'Pesan berhasil dikirim.');
    }

    public function show(Message $message)
    {
        // Cek apakah user berhak melihat pesan
        if ($message->receiver_id == Auth::id() || 
            $message->target_role == Auth::user()->role || 
            $message->target_role == 'all') {
            
            // Catat sebagai 'sudah dibaca' jika belum ada di tabel message_reads
            if (!$message->isReadBy(Auth::id())) {
                $message->readers()->attach(Auth::id());
            }
        }

        return view('messages.show', compact('message'));
    }
}