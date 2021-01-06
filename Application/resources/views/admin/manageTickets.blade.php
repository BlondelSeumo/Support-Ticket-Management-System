@extends('layouts.app')

@section('content')
<div class="main">
<div class="container">
    <div class="row">
    <h3>Manage Tickets</h3>
    </div>
   </div>
</div>

<div class="container">
    <div class="row">
     


                 <table class="ui table">
  <thead>
    <tr>
      <th>Subject</th>
      <th>Date</th>
      <th>Last Reply</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>


    
     @foreach($tickets as $ticket)
       <tr>
       <td>{{ $ticket->subject }}</td>
       <td>{{  date('F d, Y h:m A', strtotime($ticket->created_at)) }}</td>
       <td>
           @foreach($replies as $reply => $v)
               @foreach($v as $data)
                 @if ($ticket->tid == $data->tid)
                    {{ $data->name }}
                  @endif
                @endforeach
              @endforeach
             </td>
       <td>@if ($ticket->status == 0) 
       <a class="color--olive ">
            <i class="ticket icon"></i>  Open    
          </a>
           @else 
            <a class="color--gray">
            <i class="ticket icon"></i>  Closed    
          </a> 
       
       @endif</td>
       <td>
         <a href="{{ url('admin/ticket/'.$ticket->tid) }}" class="btn btn-primary btn-sm">View</a> 
         @if ($ticket->status == 1)
         <a href="{{ url('admin/ticket/'.$ticket->tid) }}" target="_blank" class="btn btn-primary btn-sm">Open</a> 
        @else 
         <a href="{{ url('ticket/close/'.$ticket->tid) }}" target="_blank" class="btn btn-primary btn-sm">Close</a> 

         @endif
         <a href="{{ url('manage/ticket/delete/'.$ticket->tid) }}" class="btn btn-danger btn-sm">Delete</a> 
          

       </td>
       </tr>
     @endforeach

     @if (count($tickets) ==0)
     <tr> <td colspan="5"> There is no open tickets.</td> </tr>
     @endif
  

  </tbody>
</table>



{{ $tickets->render() }}

 

    </div>
</div>
@endsection
