<?php

namespace VientoDigital\LaravelExpoPushNotifications\Channels;

use Illuminate\Notifications\Notification;
use GuzzleHttp\Client as HttpClient;
use VientoDigital\LaravelExpoPushNotifications\ExpoPushNotifiable;
use Illuminate\Support\Facades\Log;

class ExpoPushNotificationChannel
{
    protected $http;
    protected $url;

    public function __construct(HttpClient $http)
    {
        $this->http = $http;
        $this->url = 'https://exp.host/--/api/v2/push/send';
    }

    public function send($notifiable, Notification $notification)
    {
        if (!method_exists($notification, 'toExpo')) {
            throw new RuntimeException('Notification is missing toExpo method.');
        }
        $data = $notification->toExpo($notifiable);
        $this->sendTokens($notifiable, $notification, $data);
    }

    private function sendTokens(ExpoPushNotifiable $notifiable, Notification $notification, $data)
    {
        $tokens = collect($notifiable->tokensForExpo());
        $tokens->each(function ($to, $key) use ($notifiable, $notification,$data) {
            $data = $this->buildMessage($notification->toExpo($notifiable));
            $data['to'] = $to;
            Log::info(['token' => $data]);
            $this->http->request('POST', $this->url, ['json' => $data]);
        });
    }

    protected function buildMessage($data):array
    {
        $result = [
            'title' => $data->title,
            'body' => $data->body,
            'subtitle' => $data->subtitle,
            'priority' => $data->priority,
            'sound' => $data->sound,
            'badge' => $data->badgeCount,
            'data' => $data->data,
        ];
        return $result;
    }
}
