@extends('layouts.app')

@section('content')
<div class="main">
<div class="container">
    <div class="row">
    <h3>Open Tickets</h3>
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
      <th>Priority</th>
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
       <td>@if ($ticket->priority == "High") 
                  <a class="label label-danger">  High    </a> 
          @elseif($ticket->priority == "Medium") 
                  <a class="label label-warning">  Medium    </a> 

           @else 
            <a class="label label-default">  Low    </a> 
       
       @endif</td>
       <td>
         <a href="{{ url('admin/ticket/'.$ticket->tid) }}" class="btn btn-primary btn-sm">View</a> 
         @if ($ticket->status == 1)
         <a href="{{ url('admin/ticket/'.$ticket->tid) }}" class="btn btn-primary btn-sm">Open</a> 
        @else 
         <a href="{{ url('ticket/close/'.$ticket->tid) }}" class="btn btn-primary btn-sm" target="_BLANK">Close</a> 

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
