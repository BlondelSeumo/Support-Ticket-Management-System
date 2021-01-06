<?php
namespace App\Http\Controllers;

use App\Settings;
use App\Ticket;
use App\TicketReply;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use MercurySeries\Flashy\Flashy;
use Request;

class HomeController extends Controller
{
/**
 * Load basic settings
 */
    public function __construct()
    {
        $this->middleware('auth');
        $sett = Settings::all();
        foreach ($sett as $setts) {
            $this->settings[$setts->name] = $setts->value;
        }
    }
/**
 * Verify admin is logged in
 */
    public function validateAdmin()
    {
        if (Auth::user()->is_admin != 1) {
            die('You have no permission');
        }

        $sett = Settings::all();
        foreach ($sett as $setts) {
            $this->settings[$setts->name] = $setts->value;
        }
    }
/**
 * Show the application dashboard.
 */
    public function index()
    {
        $replies = array();
        if (Auth::user()->is_admin == 1) {
            $tickets = Ticket::orderBy('status', 'DESC')->orderBy('priority', 'DESC')->where('status', '0')->paginate(10);
        } else {
            $departments = explode(',', Auth::user()->department);
            $tickets = Ticket::whereIn('department', $departments)->orderBy('status', 'DESC')->orderBy('priority', 'DESC')->where('status', '0')->paginate(10);
        }
        foreach ($tickets as $ticket) {
            $replies[] = TicketReply::where('tid', $ticket->tid)->orderBy('id', 'DESC')->get()->take(1);
        }
        return view('home', compact('tickets', 'replies'));
    }
/**
 * Search
 */
    public function search()
    {
        $request = Request::get('search');
        $ticket = Ticket::where('tid', $request)->get()->first();
        if (!$ticket) {
            Flashy::error("Ticket id not found");
            return redirect('home');
        }
        return redirect('admin/ticket/' . $request);

    }
/**
 * View Ticket from admin panel
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
        return view('admin.viewTicket', compact('ticket', 'reply', 'ticket', 'reply', 'userAvatar', 'staffAvatar'));
    }
/**
 * Store reply by staff
 */
    public function storeReply($tid)
    {
        $ticket = Ticket::where('tid', $tid)->get()->first();
        if ($ticket->status == 1) {
            $ticket->update(['status' => '0']);
        }
        $req = Request::all();
        $req['staff'] = Auth::user()->id;
        $req['name'] = Auth::user()->name;

        TicketReply::create($req);
        Flashy::message('Your reply added.');
// Mail user this info
        $data = array(
            'ticket' => $ticket->tid,
            'name' => $ticket->name,
        );
        $useremail = $ticket->email;
        Mail::send('Emails.ticketReply', $data, function ($message) use ($useremail) {
            $message->from($this->settings['from'], $_ENV['APP_NAME'] . ' Ticket');
            $message->to($useremail)->subject($_ENV['APP_NAME'] . ' Ticket');
        });
        return redirect('admin/ticket/' . $req['tid']);
    }
/**
 * View Closed tickets
 */
    public function closedTickets()
    {
        $replies = [];
        if (Auth::user()->is_admin == 1) {
            $tickets = Ticket::orderBy('status', 'DESC')->orderBy('priority', 'DESC')->paginate(10);
        } else {
            $departments = explode(',', Auth::user()->department);
            $tickets = Ticket::whereIn('department', $departments)->orderBy('status', 'DESC')->orderBy('priority', 'DESC')->paginate(10);
        }
        foreach ($tickets as $ticket) {
            $replies[] = TicketReply::where('tid', $ticket->tid)->orderBy('id', 'DESC')->get()->take(1);
        }
        return view('admin.manageTickets', compact('tickets', 'replies'));
    }
/**
 *Manage staffs
 */
    public function users()
    {
        $this->validateAdmin();
        $users = User::paginate('10');
        return view('admin.manageUser', compact('users'));
    }
/**
 * Settings page.
 */
    public function settings()
    {
        $this->validateAdmin();
        $settings = Settings::all();
        foreach ($settings as $sett) {
            $data[$sett->name] = $sett->value;
        }
        return view('admin.settings', compact('data'));
    }
/**
 * Delete support ticket
 */
    public function deleteTicket($tid)
    {
        Ticket::where('tid', $tid)->delete();
        TicketReply::where('tid', $tid)->delete();
        return redirect('manage/tickets');
    }
/**
 * User Profile page
 */
    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }
/**
 * User profile data update
 */
    public function updateProfile()
    {
        $input = Request::all();
        $user = Auth::user();
        $user->update($input);
        Flashy::message('Your profile updated successfully');
        return redirect('home');
    }
/**
 * User password update.
 */
    public function updateProfilePassword()
    {
        $input = Request::all();
        $user = Auth::user();
        $account['password'] = bcrypt($input['password']);
        $user->update($account);
        Flashy::message('Your password updated successfully');
        return redirect('home');
    }
/**
 * Add new staff
 */
    public function userAdd()
    {$this->validateAdmin();
        $departments = explode(',', $this->settings['department']);
        return view('admin.newUser', compact('departments'));
    }
/**
 * Add user to database
 */
    public function storeUser()
    {$this->validateAdmin();
        $request = Request::all();

        $department = implode(',', $request['department']);

        $data['name'] = $request['name'];
        $data['email'] = $request['email'];
        $data['department'] = $department;
        $data['password'] = bcrypt($request['password']);
        User::create($data);
        Flashy::message('Staff added successfully.');
        return redirect('manage/user');
    }
/**
 * Edit staff
 */
    public function editUser($id)
    {$this->validateAdmin();
        $user = User::findOrFail($id);
        $departments = explode(',', $this->settings['department']);
        $selected = explode(',', $user->department);
        return view('admin.editUser', compact('user', 'departments', 'selected'));
    }
/**
 * Update staff details
 */
    public function updateUser($id)
    {$this->validateAdmin();
        $user = User::findOrFail($id);
        $request = Request::all();
        $department = implode(',', $request['department']);

        $data['name'] = $request['name'];
        $data['email'] = $request['email'];
        $data['department'] = $department;
        $user->update($data);
        Flashy::message('Staff updated successfully.');
        return redirect('manage/user');
    }
/**
 * Update settings
 */
    public function updateSettings()
    {$this->validateAdmin();
        $requests = Request::all();
        foreach ($requests as $req => $k) {
            if ($req != "_token") {
                if ($k) {
                    $settings = Settings::where('name', $req)->first();
                    $settings->update(['value' => $k]);
                }
            }
        }
        Flashy::message('Settings updated');
        return redirect('manage/settings');
    }
}
