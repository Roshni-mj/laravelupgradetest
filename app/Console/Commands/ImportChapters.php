<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use App\Chapter;

class ImportChapters extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:chapters';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Chapter to Database and Create Contact List on TrueDialog';

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
        // Uncomment to use
        $file_full_path = base_path('resources/import/chapters/chapter-list-test.csv');   

        //read the data into an array
        $data = array_map('str_getcsv', file($file_full_path));

        //loop over the data
        foreach($data as $key => $chapter_name) {
            if($key != 0) {
                // Insert to Chapters table
                $chapter = Chapter::create([
                    'chapter_name' => $chapter_name[0]
                ]);

                /*
                if(strlen($chapter_name[0]) <=40) {
                    // Prepare the post data for Truedialog
                    $postData = array(
                        'ChapterName' => $chapter_name[0]
                    );    
                    $leaderContactListId = createTrueDialogContactList($postData,'chapter_leader');
                    $participantContactListId = createTrueDialogContactList($postData,'participant');

                    // Update details from Truedialog to Chapters table
                    $chapter_info = Chapter::find($chapter->id);
                    $chapter_info->truedialog_leaderchapterlist_id = $leaderContactListId;
                    $chapter_info->truedialog_participantchapterlist_id = $participantContactListId;
                    $chapter_info->save();                      
                }    
                */                   
            }
        }       

        $this->info('Completed importing chapters. '.$file_full_path);
    }
}
