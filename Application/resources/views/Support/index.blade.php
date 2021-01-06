@extends('layouts.app')

@section('content')
<div class="main">

<div class="container">
    <div class="row">
    <h3>View Ticket</h3>
    </div>
   </div>
</div>

<div class="container">
    <div class="row">
        <form method="POST" action="{{ url('view') }}">
        {{ csrf_field() }}
 

        <div class="form-group">  <div class="row">
    <label for="ticket" class="col-md-4 control-label">Ticket ID</label> 
    <div class="col-md-8"><input id="ticket" name="ticket" value="" required="required" placeholder="Ticket ID" class="form-control" type="text"></div></div></div>
    

<div class="form-group pull-right">
<input type="submit" class="btn btn-primary" value="Submit">
</div>


    </form>
      


    </div>
</div>
@endsection
