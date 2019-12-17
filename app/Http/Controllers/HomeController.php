<?php

namespace App\Http\Controllers;

use App\Message;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Pusher\Pusher;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {




        //    $users=DB::table('users')
        //     ->select('users.name')
        //     ->leftJoin('messages','users_id','=','messages.from')
        //     ->where("messages.is_read","=",0)
        //     ->where("messages.to","=",Auth::user()->_id)
        //     ->where('users._id','!=',Auth::user()->_id)
        //     ->get();
        // dd($users);
        // select all users except logged in user
        $users=User::select('name','email')
        ->leftJoin('messages','_id','=','messages.from')
        ->where('_id','!=',Auth::user()->_id)
        ->get();

        //count how many message are unread from the selected user
        // $users = DB::select("select users._id, users.name, users.email, count(is_read) as unread
        // from users LEFT  JOIN  messages ON users._id = messages.from and is_read = 0 and messages.to = " . Auth::user()->_id . "
        // where users._id != " . Auth::user()->_id . "
        // group by users._id, users.name, users.email");

        return view('home', ['users' => $users]);
    }

    public function getMessage($user_id)
    {

        $my_id = Auth::user()->_id;

        // Make read all unread message
       Message::where(['from' => $user_id, 'to' => $my_id])->update(['is_read' => 1]);

        // Get all message from selected user
        $messages = Message::where(function ($query) use ($user_id, $my_id) {
            $query->where('from', $user_id)->where('to', $my_id);
        })->oRwhere(function ($query) use ($user_id, $my_id) {
            $query->where('from', $my_id)->where('to', $user_id);
        })->get();

        return view('messages.index')->with('messages',$messages);
    }

    public function sendMessage(Request $request)
    {

        $from = Auth::user()->_id;
        $to = $request->receiver_id;
        $message = $request->message;

        $data = new Message();
        $data->from = $from;
        $data->to = $to;
        $data->message = $message;
        $data->is_read = 0; // message will be unread when sending message
        $data->save();

        // pusher
        $options = array(
            'cluster' => 'ap2',
            'useTLS' => true
        );

        $pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            $options
        );

        $data = ['from' => $from, 'to' => $to,"Hii I am Rahul"]; // sending from and to user id when pressed enter
        $pusher->trigger('my-channel', 'my-event', $data);
    }
}
