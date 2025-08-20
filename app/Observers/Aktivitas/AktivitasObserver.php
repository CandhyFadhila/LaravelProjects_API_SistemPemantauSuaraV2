<?php

namespace App\Observers\Aktivitas;

use App\Models\AktivitasPelaksana;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AktivitasObserver
{
    public function created(AktivitasPelaksana $aktivitas)
    {
        $loggedInUser = Auth::user();
        $keyTags = $loggedInUser ? $loggedInUser->id : 'guest';

        Cache::forget('public_get_all_aktivitas_' . $keyTags);
        Cache::forget('aktivitas_role_1_' . $keyTags);
        Cache::forget('aktivitas_role_2_' . $keyTags);
        Cache::forget('aktivitas_role_3_' . $keyTags);
        Cache::forget('public_get_all_status_aktivitas_rws_kelurahan_' . $keyTags);
        Cache::forget('public_get_all_data_upcoming_tps_' . $keyTags);
    }

    public function updated(AktivitasPelaksana $aktivitas)
    {
        $loggedInUser = Auth::user();
        $keyTags = $loggedInUser ? $loggedInUser->id : 'guest';

        Cache::forget('public_get_all_aktivitas_' . $keyTags);
        Cache::forget('aktivitas_role_1_' . $keyTags);
        Cache::forget('aktivitas_role_2_' . $keyTags);
        Cache::forget('aktivitas_role_3_' . $keyTags);
        Cache::forget('public_get_all_status_aktivitas_rws_kelurahan_' . $keyTags);
        Cache::forget('public_get_all_data_upcoming_tps_' . $keyTags);
    }

    public function deleted(AktivitasPelaksana $aktivitas)
    {
        $loggedInUser = Auth::user();
        $keyTags = $loggedInUser ? $loggedInUser->id : 'guest';

        Cache::forget('public_get_all_aktivitas_' . $keyTags);
        Cache::forget('aktivitas_role_1_' . $keyTags);
        Cache::forget('aktivitas_role_2_' . $keyTags);
        Cache::forget('aktivitas_role_3_' . $keyTags);
        Cache::forget('public_get_all_status_aktivitas_rws_kelurahan_' . $keyTags);
        Cache::forget('public_get_all_data_upcoming_tps_' . $keyTags);
    }
}
