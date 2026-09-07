<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingController extends Controller
{
    /**
     * Display a listing of all settings or filter by group with smart defaults.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query();

        if ($group = $request->input('group')) {
            $query->where('group', $group);
        }

        $settings = $query->get();

        // Standard defaults
        $defaults = [
            'company' => [
                'company_name' => 'Enterprise CRM Global',
                'company_email' => 'contact@crm.local',
                'company_phone' => '+1 (555) 019-2834',
                'company_address' => '100 Silicon Valley Blvd, Suite 400',
                'currency_symbol' => '$',
                'currency_code' => 'USD',
                'timezone' => 'UTC',
                'date_format' => 'Y-m-d',
            ],
            'email' => [
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.mailtrap.io',
                'mail_port' => '2525',
                'mail_username' => 'crm_system_mailer',
                'mail_password' => '••••••••••••',
                'mail_encryption' => 'tls',
                'mail_from_address' => 'noreply@crm.local',
                'mail_from_name' => 'CRM Cloud Notifications',
            ],
            'integrations' => [
                'webhook_url' => 'https://api.crm.local/webhooks/incoming',
                'zapier_webhook' => '',
                'slack_webhook' => '',
                'api_rate_limit' => '120',
                'enable_api_access' => 'true',
            ],
            'system' => [
                'app_name' => 'Smart CRM',
                'allow_registration' => 'true',
                'session_lifetime' => '120',
                'maintenance_mode' => 'false',
                'audit_logging' => 'true',
                'max_file_upload_mb' => '25',
            ],
        ];

        $grouped = $settings->groupBy('group')->map(function ($groupSettings) {
            return $groupSettings->pluck('value', 'key');
        })->toArray();

        // Merge defaults
        foreach ($defaults as $grp => $grpDefaults) {
            if (!isset($grouped[$grp])) {
                $grouped[$grp] = [];
            }
            foreach ($grpDefaults as $k => $v) {
                if (!isset($grouped[$grp][$k])) {
                    $grouped[$grp][$k] = $v;
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $grouped,
            'settings' => SettingResource::collection($settings),
            'grouped' => $grouped,
        ]);
    }

    /**
     * Store or update settings in bulk.
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        if ($user && !$user->hasPermissionTo('manage-settings') && !$user->hasRole('Admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $updated = [];

        if ($request->has('settings') && is_array($request->input('settings'))) {
            foreach ($request->input('settings') as $item) {
                $setting = Setting::set(
                    $item['key'],
                    $item['value'] ?? '',
                    $item['group'] ?? 'general'
                );
                $updated[] = new SettingResource($setting);
            }
        } else {
            foreach ($request->all() as $group => $items) {
                if (is_array($items)) {
                    foreach ($items as $k => $v) {
                        $setting = Setting::set($k, $v ?? '', $group);
                        $updated[] = new SettingResource($setting);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings saved successfully',
            'data' => $updated,
        ]);
    }
}
