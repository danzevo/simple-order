<?php

namespace App\Traits;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Throwable;

trait BugsnagTrait
{
    public function report(Throwable $e)
    {
        if (env('APP_ENV') === 'production') {
            Bugsnag::notifyException($e, function ($report) {
                $report->setName(env('APP_NAME'));
                $report->setSeverity('error');
            });
        }
    }

    public static function staticReport(Throwable $e)
    {
        if (env('APP_ENV') === 'production') {
            Bugsnag::notifyException($e, function ($report) {
                $report->setName(env('APP_NAME'));
                $report->setSeverity('error');
            });
        }
    }

    public function sendLogInfo(string $type, string $msg)
    {
        Bugsnag::notifyError($type, $msg, function ($report) {
            $report->setSeverity('info');
        });
    }
}
