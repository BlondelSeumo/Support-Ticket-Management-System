<?php
namespace App\Http\Controllers;

use App\Settings;
use App\Ticket;
use App\TicketReply;
use App\User;
use Illuminate\Support\Facades\Mail;
use MercurySeries\Flashy\Flashy;
use Request;

class TicketController extends Controller
{
/*
 * Load basic settings
 */
    public function __construct()
    {
        $sett = Settings::all();
        foreach ($sett as $setts) {
            $this->settings[$setts->name] = $setts->value;
        }
    }
/*
 * Supportdesk index page
 */
    public function index()
    {
        $departments = explode(',', $this->settings['department']);
        return view('welcome', compact('departments'));
    }
/*
 * View Ticket page.
 */
    public function support()
    {
        return view('Support.index');
    }
/*
 * Load ticket from ticket id
 */
    public function loadTicket()
    {
        $request = Request::get('ticket');
        $ticket = Ticket::where('tid', $request)->first();
        if (!$ticket) {
            Flashy::error('Invalid Ticket ID');
            return redirect('view');
        }
        return redirect('ticket/' . $ticket->tid);
    }
/*
 * Slack notification sender.
 */
    public function slack($message, $room = "general", $icon = ":longbox:")
    {
        if ($this->settings['slack'] != "") {
            $room = ($room) ? $room : "general";
            $data = "payload=" . json_encode(array(
                "username" => "dev",
                "channel" => "#{$room}",
                "text" => $message,
                "icon_emoji" => $icon,
            ));
            $ch = curl_init($this->settings['slack']);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        }
    }
/*
 * New ticket store on database
 */
    public function storeTicket()
    {
        $req = Request::all();
// dd($req['email']);
        //Generate Tid
        $tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $segment_chars = 5;
        $num_segments = 4;
        $key_string = '';
        for ($i = 0; $i < $num_segments; $i++) {
            $segment = '';
            for ($j = 0; $j < $segment_chars; $j++) {
                $segment .= $tokens[rand(0, 35)];
            }
            $key_string .= $segment;
            if ($i < ($num_segments - 1)) {
                $key_string .= '-';
            }
        }
        $req['tid'] = $key_string;
        $req['status'] = 0;
        Ticket::create($req);
        Flashy::message('Your ticket opened');
        $data = array(
            'ticket' => $key_string,
            'name' => $req['name'],
        );
        $useremail = $req['email'];
        Mail::send('Emails.ticket', $data, function ($message) use ($useremail) {
            $message->from($this->settings['from'], $_ENV['APP_NAME'] . ' Ticket');
            $message->to($useremail)->subject($_ENV['APP_NAME'] . ' Ticket');
        });
        $staffs = User::where('department', 'LIKE', '%' . $req['department'] . '%')->get();
        foreach ($staffs as $staff) {
            $staffemail = $staff->email;
            Mail::send('Emails.staffEmail', $data, function ($message) use ($staffemail) {
                $message->from($this->settings['from'], $_ENV['APP_NAME'] . ' New Ticket');
                $message->to($staffemail)->subject($_ENV['APP_NAME'] . ' New Ticket');
            });
        }
        $this->slack('New Ticket - ' . $req['message'], 'support', ':robot_face:');
        return redirect('ticket/' . $key_string);
    }
/*
 * View Ticket user page.
 */
    public function viewTicket($tid)
    {
        $ticket = Ticket::where('tid', $tid)->get()->first();
        $email = $ticket->email;
        $default = url('images/user.png');
        $size = 40;
        $userAvatar = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?d=" . urlencode($default) . "&s=" . $size;
        $staffs = TicketReply::where('tid', $tid)->where('staff', '!=', '0')->get();
        $staffAvatar = array();
        foreach ($staffs as $staff) {
            $user = User::where('id', $staff->staff)->select('email')->first();
            $email = $user->email;
            $default = url('images/staff.png');
            $size = 40;
            $staffAvatar[$staff->staff] = "https://www.gravatar.com/avatar/" . md5(strtolower(trim($email))) . "?d=" . urlencode($default) . "&s=" . $size;
        }
        $reply = TicketReply::where('tid', $tid)->paginate(10);
        return view('Support.view', compact('ticket', 'reply', 'userAvatar', 'staffAvatar'));
    }
/*
 * Store ticket reply.
 */
    public function storeReply($tid)
    {
        $ticket = Ticket::where('tid', $tid)->get()->first();
        if ($ticket->status == 1) {
            $ticket->update(['status' => '0']);
        }
        $req = Request::all();
        TicketReply::create($req);
        Flashy::message('Your reply added.');
        $data = array(
            'ticket' => $tid,
            'name' => 'BOT',
        );
        $staffs = User::where('department', 'LIKE', '%' . $ticket->department . '%')->get();
        foreach ($staffs as $staff) {
            $staffemail = $staff->email;
            Mail::send('Emails.staffEmail', $data, function ($message) use ($staffemail) {
                $message->from($this->settings['from'], $_ENV['APP_NAME'] . ' New Ticket Reply');
                $message->to($staffemail)->subject($_ENV['APP_NAME'] . ' New Ticket Reply');
            });
        }
        $this->slack('New Ticket Reply - ' . $req['message'], 'support', ':robot_face:');
        return redirect('ticket/' . $req['tid']);
    }
/*
 * Close Ticket
 */
    public function close($tid)
    {
        $ticket = Ticket::where('tid', $tid)->get()->first();
        $ticket->update(['status' => '1']);
        Flashy::message('Ticket Closed', '');
        return redirect('ticket/' . $tid);
    }
}
