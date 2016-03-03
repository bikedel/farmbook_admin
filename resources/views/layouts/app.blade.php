<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>ProteaDB</title>

    <!-- Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700" rel='stylesheet' type='text/css'>



    <!-- Styles -->
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/1.1.2/css/select.bootstrap.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    {{-- <link href="{{ elixir('css/app.css') }}" rel="stylesheet"> --}}

    <style>
    body {
        font-family: 'Lato';
        background-color: rgba(46, 81, 86, .4);

    }
    .tlabel{

        color:#000000;
        font-weight: 900;
    }

    .navbar-default {
        border-color: Transparent; 

        margin-bottom: 2cm;

    }
    .panel-body{
       background-color: rgba(232, 232, 232, 1);
   }

   .navbar .navbar-brand{
      font-weight: 900;
      color:#2e78ba;
  }

  .settings{
    margin-right: 6px;
    color:red;
}

.users{
    margin-right: 6px;
    color:teal;
}

.logs{
    margin-right: 6px;
    color:orange;
}


.admin{
    margin-right: 6px;
    color:red;
}

.panel-primary .panel-heading{

}



.farmbooks{
    margin-right: 6px;
    color:green;
}

.id {
    border-color: Transparent; 
    border:none;
}
.fa-btn {
    margin-right: 6px;
}
</style>
</head>
<body id="app-layout">
    <nav class="navbar navbar-default">
        <div class="container">
            <div class="navbar-header">

                <!-- Collapsed Hamburger -->
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <!-- Branding Image -->
                <a class="navbar-brand" href="{{ url('/') }}"> <span class="glyphicon glyphicon-grain"></span>
                    ProteaDB
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                 @if (!Auth::guest())
                 <li><a href="{{ url('/userfarmbooks') }}">{{ Auth::user()->getDatabaseName() }} </a></li>



                 <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                     Canvass <span class="caret"></span>
                 </a>

                 <ul class="dropdown-menu" role="menu">
                    <li><a href="{{ url('/home') }}"><i class="farmbooks glyphicon glyphicon-road"></i>by Street</a></li> 
                    <li><a href="{{ url('/home') }}"> <i class=""> <span class=" users     glyphicon glyphicon-th"> </span></i>by Complex</a></li>
                    <li><a href="{{ url('/home') }}"> <i class=""> <span class=" admin     glyphicon glyphicon-home"> </span></i>by Erf</a></li>
                    <li><a href="{{ url('/home') }}"> <i class=""> <span class=" users     glyphicon glyphicon-user"> </span></i>by Owner</a></li>
                </ul>
            </li>


            <li><a href="{{ url('/datatables') }}">Owners</a></li>
            @endif
        </ul>


        <!-- Right Side Of Navbar -->
        <ul class="nav navbar-nav navbar-right">
            <!-- Authentication Links -->
            @if (Auth::guest())
            <li><a href="{{ url('/login') }}">Login</a></li>

            @else
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                    {{ Auth::user()->name }} <span class="caret"></span>
                </a>

                <ul class="dropdown-menu" role="menu">
                    <li><a href="{{ url('/logout') }}"><i class="fa fa-btn fa-sign-out"></i>Logout</a></li> 
                    @if (Auth::user()->isAdmin())
                    <li><a href="{{ url('/users') }}"> <i class=""> <span class=" users    glyphicon glyphicon-user"> </span></i>Users</a></li>
                    <li><a href="{{ url('/farmbooks') }}"> <i class=""> <span class=" farmbooks    glyphicon glyphicon-grain"> </span></i>Farmbooks</a></li>
                    <li><a href="{{ url('/logs') }}"> <i class=""> <span class=" logs glyphicon glyphicon-file"> </span></i>Logs</a></li>
                    @endif
                </ul>
            </li>
            @endif
        </ul>
    </div>
</div>
</nav>

@yield('content')
<!-- jQuery -->
<script src="//code.jquery.com/jquery.js"></script>



<!-- DataTables -->



<script src="//cdn.datatables.net/1.10.11/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/select/1.1.2/js/dataTables.select.min.js"></script>
<script src="//cdn.datatables.net/1.10.11/js/dataTables.bootstrap.min.js"></script>
<!-- Bootstrap JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>






<!-- App scripts -->
@stack('scripts')
<!-- JavaScripts -->


{{-- <script src="{{ elixir('js/app.js') }}"></script> --}}
</body>
</html>
