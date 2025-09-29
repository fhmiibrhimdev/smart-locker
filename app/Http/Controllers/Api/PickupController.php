<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PickupController extends Controller
{
    /**
     * Verify pickup code dan claim paket
     * Endpoint yang akan dipanggil oleh ESP CAM setelah scan QR Code
     */
    public function verifyAndClaim(Request $request)
    {
        try {
            // Validasi request
            $request->validate([
                'kode_paket' => 'required',
                'kode_pengambilan' => 'required',
                'parcel_id' => 'required',
                'nomor_loker' => 'required'
            ]);

            Log::info('ESP CAM Pickup Request', $request->all());

            // Cari parcel berdasarkan data yang diberikan (tanpa filter status dulu)
            $parcel = DB::table('parcel')
                ->join('loker', 'loker.id', '=', 'parcel.id_loker')
                ->where('parcel.id', $request->parcel_id)
                ->where('parcel.kode_paket', $request->kode_paket)
                ->where('parcel.kode_pengambilan', $request->kode_pengambilan)
                ->where('loker.nomor_loker', $request->nomor_loker)
                ->select(
                    'parcel.*',
                    'loker.nomor_loker',
                    'loker.topic as loker_topic'
                )
                ->first();

            // dd($parcel);

            if (!$parcel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data paket tidak valid atau tidak ditemukan',
                    'error_code' => 'INVALID_DATA'
                ], 404);
            }

            // Check status paket
            if ($parcel->status === 'CLAIMED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Paket sudah pernah diambil sebelumnya',
                    'error_code' => 'ALREADY_CLAIMED',
                    'claimed_at' => $parcel->claimed_at
                ], 409); // 409 Conflict
            }

            if ($parcel->status !== 'STORED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Paket tidak tersimpan di locker atau status tidak valid',
                    'error_code' => 'INVALID_STATUS',
                    'current_status' => $parcel->status
                ], 400);
            }

            // Check apakah sudah expired
            if (Carbon::now() > $parcel->expired_at) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode pengambilan telah expired',
                    'error_code' => 'EXPIRED',
                    'expired_at' => $parcel->expired_at
                ], 400);
            }

            // Update status paket menjadi CLAIMED
            DB::table('parcel')
                ->where('id', $parcel->id)
                ->update([
                    'status' => 'CLAIMED',
                    'claimed_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

            // Update status loker menjadi EMPTY
            DB::table('loker')
                ->where('id', $parcel->id_loker)
                ->update([
                    'status' => 'EMPTY',
                    'updated_at' => Carbon::now()
                ]);

            // Log aktivitas pickup
            DB::table('parcel_logs')->insert([
                'id_parcel' => $parcel->id,
                'action' => 'CLAIMED_VIA_QR',
                'description' => "Paket diambil via ESP CAM QR Scanner - Loker {$parcel->nomor_loker}",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            Log::info('Paket claimed successfully', [
                'parcel_id' => $parcel->id,
                'kode_paket' => $parcel->kode_paket,
                'nomor_loker' => $parcel->nomor_loker
            ]);

            // Return success response untuk ESP CAM
            return response()->json([
                'success' => true,
                'message' => 'Paket berhasil diklaim',
                'data' => [
                    'parcel_id' => $parcel->id,
                    'kode_paket' => $parcel->kode_paket,
                    'nomor_loker' => $parcel->nomor_loker,
                    'loker_topic' => $parcel->loker_topic, // Topic MQTT untuk buka loker
                    'claimed_at' => Carbon::now()->toISOString(),
                    'action_required' => 'OPEN_LOCKER'
                ],
                'mqtt_command' => [
                    'topic' => $parcel->loker_topic,
                    'message' => 'OPEN',
                    'description' => 'Send OPEN command to locker via MQTT'
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('ESP CAM Pickup Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error_code' => 'SYSTEM_ERROR',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify kode pengambilan dari keypad (MQTT)
     * Input: hanya kode_pengambilan dari keypad
     * Format MQTT: smart_locker/pnj/micro/keypad = 78A676
     */
    public function verifyCode(Request $request)
    {
        try {
            $request->validate([
                'kode_pengambilan' => 'required|string|size:6'
            ]);

            $kode_pengambilan = $request->kode_pengambilan;

            Log::info('Keypad verification request', ['kode_pengambilan' => $kode_pengambilan]);

            // Cari parcel dengan kode pengambilan (tanpa filter status dulu)
            $parcel = DB::table('parcel')
                ->join('loker', 'loker.id', '=', 'parcel.id_loker')
                ->where('parcel.kode_pengambilan', $kode_pengambilan)
                ->where('parcel.expired_at', '>', Carbon::now()) // belum expired
                ->select(
                    'parcel.*',
                    'loker.nomor_loker',
                    'loker.topic as loker_topic'
                )
                ->first();

            if (!$parcel) {
                Log::warning('Invalid pickup code or expired', ['kode_pengambilan' => $kode_pengambilan]);
                return response()->json([
                    'success' => false,
                    'message' => 'Kode pengambilan tidak valid atau sudah expired',
                    'action' => 'REJECT'
                ], 404);
            }

            // Check status paket
            if ($parcel->status === 'CLAIMED') {
                Log::info('Paket already claimed', [
                    'kode_pengambilan' => $kode_pengambilan,
                    'claimed_at' => $parcel->claimed_at
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Paket sudah pernah diambil sebelumnya',
                    'action' => 'REJECT',
                    'error_code' => 'ALREADY_CLAIMED',
                    'claimed_at' => $parcel->claimed_at
                ], 409);
            }

            if ($parcel->status !== 'STORED') {
                Log::warning('Invalid parcel status', [
                    'kode_pengambilan' => $kode_pengambilan,
                    'status' => $parcel->status
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Paket tidak tersimpan di locker atau status tidak valid',
                    'action' => 'REJECT',
                    'error_code' => 'INVALID_STATUS',
                    'current_status' => $parcel->status
                ], 400);
            }

            // Update status menjadi CLAIMED
            DB::table('parcel')
                ->where('id', $parcel->id)
                ->update([
                    'status' => 'CLAIMED',
                    'claimed_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);

            // Update status loker menjadi EMPTY
            DB::table('loker')
                ->where('id', $parcel->id_loker)
                ->update([
                    'status' => 'EMPTY',
                    'updated_at' => Carbon::now()
                ]);

            // Log aktivitas pickup
            DB::table('parcel_logs')->insert([
                'id_parcel' => $parcel->id,
                'action' => 'PICKED_UP_KEYPAD',
                'description' => "Paket diambil menggunakan keypad dengan kode: {$kode_pengambilan}",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            Log::info('Paket claimed via keypad successfully', [
                'parcel_id' => $parcel->id,
                'kode_paket' => $parcel->kode_paket,
                'nomor_loker' => $parcel->nomor_loker,
                'kode_pengambilan' => $kode_pengambilan
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Kode pengambilan valid, buka locker',
                'action' => 'OPEN_LOCKER',
                'data' => [
                    'parcel_id' => $parcel->id,
                    'kode_paket' => $parcel->kode_paket,
                    'nomor_loker' => $parcel->nomor_loker,
                    'loker_topic' => $parcel->loker_topic,
                    'kode_pengambilan' => $parcel->kode_pengambilan,
                    'claimed_at' => Carbon::now()->toISOString()
                ],
                'mqtt_command' => [
                    'topic' => $parcel->loker_topic,
                    'message' => 'OPEN',
                    'description' => 'Send OPEN command to locker via MQTT'
                ]
            ], 200);
        } catch (\Exception $e) {
            Log::error('Keypad verification error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem',
                'error' => $e->getMessage(),
                'action' => 'ERROR'
            ], 500);
        }
    }

    /**
     * Get parcel status
     */
    public function getStatus(Request $request)
    {
        try {
            $request->validate([
                'kode_paket' => 'required|string'
            ]);

            $parcel = DB::table('parcel')
                ->join('loker', 'loker.id', '=', 'parcel.id_loker')
                ->join('lokasi', 'lokasi.id', '=', 'loker.id_lokasi')
                ->where('parcel.kode_paket', $request->kode_paket)
                ->select(
                    'parcel.*',
                    'loker.nomor_loker',
                    'loker.status as loker_status',
                    'lokasi.nama_lokasi'
                )
                ->first();

            if (!$parcel) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paket tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'kode_paket' => $parcel->kode_paket,
                    'status' => $parcel->status,
                    'nomor_loker' => $parcel->nomor_loker,
                    'lokasi' => $parcel->nama_lokasi,
                    'expired_at' => $parcel->expired_at,
                    'is_expired' => Carbon::now() > $parcel->expired_at,
                    'created_at' => $parcel->created_at,
                    'claimed_at' => $parcel->claimed_at
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem'
            ], 500);
        }
    }
}
