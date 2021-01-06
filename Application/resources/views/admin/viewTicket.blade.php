@extends('layouts.app')
@section('title') 
Support 
@endsection
@section('content') 
 <div class="container margin-20t">
<div class="row">
  <div class="col-md-8">

<div class="message-box">
<div class="message-title">
  {{ $ticket->subject }} 
  <div class="pull-right">
                         by {{ $ticket->name }}
                </div>
</div>
<div class="message-content">  
   <div class="message-padding">   <div class="message clearfix">
           <img src="{{ $userAvatar }}" alt="" width="40" height="40">
                                                           

          <div class="message-recontent clearfix">
            
            <span class="time">{{  date('F d, Y h:m A', strtotime($ticket->created_at)) }}</span>

 
            <p>  {{ nl2br($ticket->message) }} </p>

          </div> <!-- end message-content -->

        </div>
        </div>

 <hr>

 @foreach($reply as $post)
                  @if ($post->staff != 0)
    <div class="message-padding oddone">   
@else 
    <div class="message-padding">   
@endif

    <div class="message clearfix">
              @if ($post->name == $ticket->name)
          <img src="{{ $userAvatar }}" alt="" width="40" height="40">
                                                          
                                                          @else


                                                           @foreach($staffAvatar as $sa => $k)
                                                           @if ($sa == $post->staff)
                                                           
                                                              <img src=" {{ $k }}" alt="" width="40" height="40">

                                                           @endif
                                                            @endforeach
                                                     
 
                                                @endif


          <div class="message-recontent clearfix">
            
            <span class="time">{{  date('F d, Y h:m A', strtotime($post->created_at)) }}</span>

            <h5>{{ $post->name }}</h5>

            <p>  {{ $post->message }}</p>

          </div> <!-- end message-content -->

        </div>
        </div>

 <hr>
  @endforeach
      <div class="message-padding"> 
  {{ $reply->render() }}
 

   <form class="ui reply form" method="POST" action="{{ url('admin/ticket/'.$ticket->tid) }}">
   {{ csrf_field() }}
   <input type="hidden" name="name" value="{{ $ticket->name }}">
   <input type="hidden" name="tid" value="{{ $ticket->tid }}">
    <div>
      <textarea id="message" style="width:100%;" name="message" class="form-control" placeholder="Message"></textarea>
    </div>
    <br>
    <div class="">
    <button type="submit" class="btn btn-primary">
     Add Reply
    </button>

   
    </div>
  </form>
  </div>
</div> 


</div>
</div>
  <div class="col-md-4">
    <div class="thumbnail">
      <p class="ticket-info">Ticket ID: <b class="pull-right">{{ $ticket->tid }}</b></p>
      <p class="ticket-info"> Created on: <b class="pull-right">{{  date('F d, Y h:m A', strtotime($ticket->created_at)) }}</b></p>
      <p class="ticket-info"> Priority: <b class="pull-right">{{ $ticket->priority }}</b></p>
      <p class="ticket-info"> Department: <b class="pull-right">{{ $ticket->department }}</b></p>
      <p class="ticket-info"> Status: <b class="pull-right">@if ($ticket->status == 0)  <a class="color--olive ">   Open    
          </a>
      @else 
                <a class="color--gray">  Closed      </a>
      @endif</b></p>
 
      

       
      

<div class="row">
  <div class="col-md-4"><a href="#message" class="btn  btn-block btn-primary">    <i class="fa fa-comment"></i>   Reply </a></div>
  <div class="col-md-4"><a href="{{ url('ticket/close/'.$ticket->tid) }}" target="_blank" class="btn btn-block  btn-primary">    <i class="fa fa-close"></i>   Close </a>
</div>
<div class="col-md-4"><a href="{{ url('manage/ticket/delete/'.$ticket->tid) }}" class="btn btn-block  btn-danger">    <i class="fa fa-trash"></i>   Delete </a>
</div>
</div>

    </div>
  </div>
</div>
 </div>
         
@endsection
