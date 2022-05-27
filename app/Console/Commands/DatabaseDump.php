<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class DatabaseDump extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:dump';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes database dump and send this dump via email';

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
     * @return mixed
     */
    public function handle()
    {
        // Create new dump
        $filePath = '/home/bitnami/backups/zip/ccsl_backup_' . date('m_d_Y') . '.zip';

        $username = Config::get('database.connections.mysql.username');
        $password = Config::get('database.connections.mysql.password');
        $database = Config::get('database.connections.mysql.database');

        exec("mysqldump -u$username -p$password $database | gzip > $filePath");

        // Send email
        Mail::raw('Database dump ' . date('m/d/Y'), function ($message) use ($filePath)
        {
            $message->to('ataaffe@ccsl.ie');
            $message->cc('chris.primo@primosolutions.ie');
            $message->subject('Database dump');
            $message->attach($filePath);
        });

        // Remove old dump
        $deleteDate = date('m_d_Y', strtotime('-2 month'));

        $oldFilePath = ' /home/bitnami/backups/zip/ccsl_backup_' . $deleteDate . '.zip';

        exec("rm -f $oldFilePath");
    }
}
