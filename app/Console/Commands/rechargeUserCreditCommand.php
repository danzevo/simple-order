<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User\UserCredit;
use DB;

class rechargeUserCreditCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'recharge:usercredit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command for recharge user credit';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $userCredit = UserCredit::where('credit_deduction', '>', 0)->cursor();

            foreach($userCredit as $row) {
                $row->credit -= $row->credit_deduction;
                $row->credit_deduction = 0;
                $row->save();
            }

            DB::commit();
        } catch (Throwable $e) {
            \Log::error($e);
            DB::rollback();
        }
    }
}
