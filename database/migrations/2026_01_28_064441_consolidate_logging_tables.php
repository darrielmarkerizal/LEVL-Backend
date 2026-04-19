<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    
    public function up(): void
    {
        
        
        
        DB::table('user_activities')->orderBy('id')->chunk(100, function ($rows) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'log_name' => 'user_activity',
                    'description' => $row->activity_type,
                    'event' => $row->activity_type,
                    'subject_type' => $row->related_type,
                    'subject_id' => $row->related_id,
                    'causer_type' => 'Modules\Auth\Models\User',
                    'causer_id' => $row->user_id,
                    'properties' => $row->activity_data, 
                    
                    
                    
                    'created_at' => $row->created_at,
                    'updated_at' => $row->created_at,
                ];
            }
            if (! empty($data)) {
                DB::table('activity_log')->insert($data);
            }
        });

        
        
        DB::table('audit_logs')->orderBy('id')->chunk(100, function ($rows) {
            $data = [];
            foreach ($rows as $row) {
                $data[] = [
                    'log_name' => 'audit_log',
                    'description' => $row->action,
                    'event' => $row->action,
                    'subject_type' => $row->subject_type,
                    'subject_id' => $row->subject_id,
                    'causer_type' => $row->actor_type,
                    'causer_id' => $row->actor_id,
                    'properties' => $row->context,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->created_at,
                ];
            }
            if (! empty($data)) {
                DB::table('activity_log')->insert($data);
            }
        });

        
        
        DB::table('profile_audit_logs')->orderBy('id')->chunk(100, function ($rows) {
            $data = [];
            foreach ($rows as $row) {
                $props = [];
                if (! empty($row->changes)) {
                    
                    $decoded = is_string($row->changes) ? json_decode($row->changes, true) : $row->changes;
                    $props = ['attributes' => $decoded];
                }

                $data[] = [
                    'log_name' => 'profile_audit',
                    'description' => $row->action,
                    'event' => $row->action,
                    'subject_type' => 'Modules\Auth\Models\User',
                    'subject_id' => $row->user_id,
                    'causer_type' => 'Modules\Auth\Models\User',
                    'causer_id' => $row->admin_id,
                    'properties' => json_encode($props),
                    'created_at' => $row->created_at,
                    'updated_at' => $row->created_at,
                ];
            }
            if (! empty($data)) {
                DB::table('activity_log')->insert($data);
            }
        });
    }

    
    public function down(): void
    {
        
        DB::table('activity_log')->whereIn('log_name', ['user_activity', 'audit_log', 'profile_audit'])->delete();
    }
};
