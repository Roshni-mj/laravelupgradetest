<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\Controller;
use App\User;
use App\Roles;
use App\Chapter;
use App\Leadertochapter;
use App\Participanttochapter;
use CountryState; // to access countries and states.
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ApiUsersController extends Controller
{
    public $successStatus = 200;


    public function login(){
 	
   		
       if(Auth::attempt(['email' => request('email'), 'password' => request('password')])){
 
           $user = Auth::user();
 
           $success['token'] =  $user->createToken('LiferunnerApp')->accessToken;
 
           return response()->json(['success' => $success], $this->successStatus);
 
       }
 
       else{
 
           return response()->json(['error'=>'Unauthorised'], 401);
 
       }
 
   }


  public function oldInsertParticipant(Request $request) {

       request()->validate([
        //'title'         => 'required',
        'first_name'    => 'required',
        'last_name'     => 'required',
        'role_id'       => 'required',
        'email'         => 'required|email|unique:users',
        // 'password'      => 'required|confirmed',
        // 'password_confirmation' => 'required_with:password|same:password',
        //'live_in_US'    => 'required',
        'chapters'          => 'required',
        //'birth_date'    => 'required',
        //'gender'        => 'required',
        'phone'         => 'nullable|numeric|unique:users',
        //'street_address'=> 'required',
        //'city'          => 'required',
        //'state'         => 'required',
        //'zipcode'       => 'required'
    ]);      

    $participant = User::create([
      'title'         => request('title'),
      'first_name'    => request('first_name'),
      'last_name'     => request('last_name'),
      'role_id'       => request('role_id'),
      'email'         => request('email'),
      'email_optin'   => 1,
      'password'      => bcrypt('liferunner'), 
      'live_in_US'    => request('live_in_US'),
      'birth_date'    => request('birth_date'),
      'gender'        => request('gender'),
      'phone'         => str_replace(array('(', ')', '-'), '', request('phone')),
      'street_address'=> request('street_address'),
      'city'          => request('city'),
      'state'         => request('state'),
      'country'       => request('country'),
      'zipcode'       => request('zipcode')
    ]);
     
       $chapter_name = request('chapters');

       $chapter = DB::table('chapters')->where('chapter_name', $chapter_name)->pluck('id')->toArray();
       
       if(!empty($participant->id) && !empty($chapter)){

            Participanttochapter::create([
                'chapter_id' => $chapter[0],
                'participant_id' => $participant->id
            ]);
        
    }


    /*$chapter_name = DB::table('chapters')->whereIn('id', $chapters)->pluck('chapter_name')->toArray();*/


    // $chapter_name = implode(',', $chapter_name);

    if($participant->live_in_US == 'Yes'){
      $postData = array(
          'FirstName' => $participant->first_name,
          'LastName' => $participant->last_name,
          'phone_number' => $participant->phone,
          'email' => $participant->email,
          'gender' => $participant->gender,
          'chapter' => $chapter_name,
          'live_in_US' => $participant->live_in_US,
          'chapterLeader' => 'No',
          'participantId' => $participant->id,
          'email_optin' => 1
      );

      dd($postData);
      $contactId = initiateTrueDialogContact($postData);

      $participantInfo = User::find($participant->id);
      $participantInfo->contact_id = $contactId;    
      $participantInfo->save();      
    }
  }

  public function insertParticipant(Request $request) {

    request()->validate([
      //'title'         => 'required',
      'first_name'    => 'required',
      'last_name'     => 'required',
      'role_id'       => 'required',
      'email'         => 'required|email',
      // 'password'      => 'required|confirmed',
      // 'password_confirmation' => 'required_with:password|same:password',
      //'live_in_US'    => 'required',
      'chapters'          => 'required',
      //'birth_date'    => 'required',
      //'gender'        => 'required',
      'phone'         => 'nullable|numeric',
      //'street_address'=> 'required',
      //'city'          => 'required',
      //'state'         => 'required',
      //'zipcode'       => 'required'
    ]);  

    $newPhone = request('phone');
    $newEmail = request('email');
    $userData = User::where('email', $newEmail)->orWhere('phone', $newPhone)->first();

    if($userData) {
      $oldPhone = $userData->phone;
      $oldmail = $userData->email;

      $userData->title = request('title');
      $userData->first_name = request('first_name');
      $userData->last_name = request('last_name');
      $userData->role_id = request('role_id');
      $userData->email = request('email');     
      $userData->email_optin = 1;       
      $userData->password = bcrypt('liferunner');
      $userData->live_in_US = request('live_in_US');
      $userData->birth_date = request('birth_date');
      $userData->gender = request('gender');
      $userData->phone = str_replace(array('(', ')', '-'), '', request('phone'));
      $userData->street_address = request('street_address');
      $userData->city = request('city');
      $userData->state = request('state');
      $userData->country = request('country');
      $userData->zipcode = request('zipcode');   

      $chapter_name = request('chapters'); 
      $chapter = DB::table('chapters')->where('chapter_name', $chapter_name)->pluck('id')->toArray();  
      $userData->save();

      if(!empty($userData->id) && !empty($chapter)){
        $participantChapters = DB::table('participanttochapters')->where('participant_id', '=', $userData->id)->pluck('chapter_id')->toArray();
        
        if (!in_array($chapter[0], $participantChapters)) {          
            Participanttochapter::create([
              'chapter_id' => $chapter[0],
              'participant_id' => $userData->id
          ]);
        }         
      }  

      if(request('live_in_US') != 'Yes') {
        if(!empty($userData->contact_id)) {
          $contactId = deleteTrueDialogContact($userData->contact_id);
          $userData->contact_id = NULL;
          $userData->save();
        } else {
          $userData->save();
        } 
      } else {
        if(($newPhone != $oldPhone) && (!empty($newPhone))) {
          $postData = array(
            'FirstName' => $userData->first_name,
            'LastName' => $userData->last_name,
            'phone_number' => $userData->phone,
            'email' => $userData->email,
            'gender' => $userData->gender,
            'chapter' => $chapter_name,
            'live_in_US' => $userData->live_in_US,
            'chapterLeader' => 'No',
            'participantId' => $userData->id,
            'contact_id' => $userData->contact_id,
            'email_optin' => 1
          );
          
          if(!empty($userData->contact_id)) {
            $contactId = initiateTrueDialogContact($postData, 'update');
          } else {
            $contactId = initiateTrueDialogContact($postData);
            $participantInfo = User::find($userData->id);
            $participantInfo->contact_id = $contactId;    
            $participantInfo->save();
          }
        }
      }      
    } else {      
      $participant = User::create([
        'title'         => request('title'),
        'first_name'    => request('first_name'),
        'last_name'     => request('last_name'),
        'role_id'       => request('role_id'),
        'email'         => request('email'),
        'email_optin'   => 1,
        'password'      => bcrypt('liferunner'), 
        'live_in_US'    => request('live_in_US'),
        'birth_date'    => request('birth_date'),
        'gender'        => request('gender'),
        'phone'         => str_replace(array('(', ')', '-'), '', request('phone')),
        'street_address'=> request('street_address'),
        'city'          => request('city'),
        'state'         => request('state'),
        'country'       => request('country'),
        'zipcode'       => request('zipcode')
      ]);

      $chapter_name = request('chapters');
      $chapter = DB::table('chapters')->where('chapter_name', $chapter_name)->pluck('id')->toArray();
       
      if(!empty($participant->id) && !empty($chapter)){

        Participanttochapter::create([
            'chapter_id' => $chapter[0],
            'participant_id' => $participant->id
        ]);
        
      }    

      if($participant->live_in_US == 'Yes'){
        $postData = array(
            'FirstName' => $participant->first_name,
            'LastName' => $participant->last_name,
            'phone_number' => $participant->phone,
            'email' => $participant->email,
            'gender' => $participant->gender,
            'chapter' => $chapter_name,
            'live_in_US' => $participant->live_in_US,
            'chapterLeader' => 'No',
            'participantId' => $participant->id,
            'email_optin' => 1
        );
        
        $contactId = initiateTrueDialogContact($postData);        
        $participantInfo = User::find($participant->id);
        $participantInfo->contact_id = $contactId;    
        $participantInfo->save();      
      }
    }      
  }
}
