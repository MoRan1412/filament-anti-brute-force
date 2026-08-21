<?php

it('merges the config under the anti-brute-force key', function () {
    expect(config('anti-brute-force.enabled'))->toBeTrue()
        ->and(config('anti-brute-force.max_attempts'))->toBe(10)
        ->and(config('anti-brute-force.lockout_minutes'))->toBe(15)
        ->and(config('anti-brute-force.ban_hours'))->toBe([24, 72, 168])
        ->and(config('anti-brute-force.attempts_window_minutes'))->toBe(30)
        ->and(config('anti-brute-force.tracking.per_ip'))->toBeTrue()
        ->and(config('anti-brute-force.tracking.per_email'))->toBeTrue()
        ->and(config('anti-brute-force.tracking.guards'))->toBe([])
        ->and(config('anti-brute-force.whitelisted_ips'))->toBe(['127.0.0.1', '::1'])
        ->and(config('anti-brute-force.whitelisted_emails'))->toBe([])
        ->and(config('anti-brute-force.notifications.enabled'))->toBeTrue()
        ->and(config('anti-brute-force.notifications.mail.to'))->toBe([])
        ->and(config('anti-brute-force.notifications.mail.cooldown_minutes'))->toBe(60)
        ->and(config('anti-brute-force.notifications.mail.queue'))->toBeFalse()
        ->and(config('anti-brute-force.admin_page.enabled'))->toBeTrue()
        ->and(config('anti-brute-force.admin_page.slug'))->toBe('anti-brute-force');
});
