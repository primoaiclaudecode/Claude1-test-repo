<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DevTool extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:tool';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Developer tool for run scripts';

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
      $files =  DB::table('file_system')->get();
      foreach ($files as $file){
          $dir_path = str_replace('/opt/bitnami/apache2/htdocs/file_share/', '', $file->dir_path);
          $update = DB::table('file_system')->where('id', $file->id)->update(['dir_path'=>$dir_path]);
          dump($update);
      }
    }
}
