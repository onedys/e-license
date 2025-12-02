<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SystemController extends Controller
{
    public function clearCache()
    {
        Artisan::call('optimize:clear');
        Cache::flush();
        
        return response()->json(['success' => true]);
    }
    
    public function optimize()
    {
        Artisan::call('optimize');
        
        return response()->json(['success' => true]);
    }
    
    public function enableMaintenance(Request $request)
    {
        $message = $request->message ?: 'Site is under maintenance. Please check back soon.';
        $retry = $request->retry ?: 3600;
        
        Artisan::call('down', [
            '--message' => $message,
            '--retry' => $retry
        ]);
        
        return response()->json(['success' => true]);
    }
    
    public function disableMaintenance()
    {
        Artisan::call('up');
        
        return response()->json(['success' => true]);
    }
}