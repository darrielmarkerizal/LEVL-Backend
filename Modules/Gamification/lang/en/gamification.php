<?php

return [
    // Badge messages
    'badge_earned' => 'Badge Earned!',
    'badge_earned_description' => 'You earned the :name badge',
    'badge_created' => 'Badge created successfully',
    'badge_updated' => 'Badge updated successfully',
    'badge_deleted' => 'Badge deleted successfully',
    'badge_not_found' => 'Badge not found',
    
    // Event counter messages
    'counter_incremented' => 'Counter incremented successfully',
    'counter_reset' => 'Counter reset successfully',
    'counters_cleaned' => ':count expired counters cleaned up',
    
    // Event log messages
    'event_logged' => 'Event logged successfully',
    'logs_cleaned' => ':count old event logs cleaned up',
    
    // Badge version messages
    'version_created' => 'Badge version created successfully',
    'initial_versions_created' => ':count initial badge versions created',
    
    // Cache messages
    'cache_warming' => 'Warming badge rules cache...',
    'cached_event' => 'Cached rules for event: :event',
    'cache_warmed' => 'Successfully cached :count event types',
    'cache_cleared' => 'Cache cleared successfully',
    
    // Cleanup messages
    'cleaning_logs' => 'Cleaning up event logs older than :days days...',
    'cleaning_counters' => 'Cleaning up expired event counters...',
    'creating_versions' => 'Creating initial badge versions...',
    
    // Validation messages
    'invalid_window' => 'Invalid window type. Must be: daily, weekly, monthly, or lifetime',
    'invalid_event_type' => 'Invalid event type',
    'threshold_required' => 'Threshold is required for this badge',
    
    // Success messages
    'operation_successful' => 'Operation completed successfully',
    'data_saved' => 'Data saved successfully',
    
    // Error messages
    'operation_failed' => 'Operation failed',
    'data_not_saved' => 'Failed to save data',
    'insufficient_progress' => 'Insufficient progress to earn this badge',
    
    // Metrics messages
    'metrics_collected' => 'Metrics collected successfully',
    'badge_evaluations_total' => 'Total Badge Evaluations',
    'badge_awarded_total' => 'Total Badges Awarded',
    'badge_awarded_last_hour' => 'Badges Awarded (Last Hour)',
    'counter_increment_total' => 'Total Counter Increments',
    'active_counters' => 'Active Counters',
    'event_logs_total' => 'Total Event Logs',
    'event_logs_last_hour' => 'Event Logs (Last Hour)',
    'rule_eval_duration_ms' => 'Rule Evaluation Duration (ms)',
    'cache_hit_rate' => 'Cache Hit Rate',
    'cooldowns_active' => 'Active Cooldowns',
    'badge_versions_active' => 'Active Badge Versions',
    
    // Badge control messages
    'badge_repeatable' => 'This badge can be earned multiple times',
    'badge_non_repeatable' => 'This badge can only be earned once',
    'max_awards_reached' => 'Maximum awards limit reached for this badge',
    'rule_disabled' => 'This rule is currently disabled',
    'rule_enabled' => 'This rule is currently enabled',
    
    // Forum activity messages
    'thread_created_xp' => 'Created a new discussion thread',
    'reply_created_xp' => 'Replied to a discussion',
    'reaction_received_xp' => 'Received a reaction on your post',
];
