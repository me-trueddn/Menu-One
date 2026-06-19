{{ __('menu.mail_test_body', [
    'app' => config('app.name'),
    'user' => $sender->name,
    'time' => now()->format('d.m.Y H:i:s'),
]) }}
