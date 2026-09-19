<!DOCTYPE html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="author" content="CodePel">
      <title> login Page</title>

      <!-- Style CSS -->
      <link rel="stylesheet" href="./css/style.css">
      <!-- Demo CSS (No need to include it into your project) -->

      <link rel="stylesheet" href="./css/demo.css">
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">

      <style>
   </style>
   </head>
   <body>
      <header class="cd__intro">

      </header>
      <!--$%adsense%$-->
      <main class="cd__main">
         <!-- Start DEMO HTML (Use the following code into your project)-->
         {{-- <script src="https://use.fontawesome.com/f59bcd8580.js"></script> --}}
<div class="container">
<div class="row m-5 no-gutters shadow-lg">
<div class="col-md-6 d-none d-md-block">
<img src="https://images.unsplash.com/photo-1566888596782-c7f41cc184c5?ixlib=rb-1.2.1&auto=format&fit=crop&w=2134&q=80" class="img-fluid" style="min-height:100%;" />
</div>
<div class="col-md-6 bg-white p-2">
<div class="form-style">

    @foreach($Company as $Details)
    <h3 style="font-family: cursive;">
        <center>{{$Details->name}}</center>
    </h3>
    <h6><center>{{$Details->address}}</center></h6>
    @endforeach
  <div class="shadow-lg p-3 mb-5 bg-body-tertiary rounded">
    <form action="{{ route('login') }}" method="post">
      @csrf
      <!-- Email input -->
      <div class="form-outline mb-4">
        <label class="lead" for="form3Example3"><strong>User Name</strong></label>
        <input id="username" type="username" class="form-control @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>

      </div>

      <!-- Password input -->
      <div class="form-outline mb-3">
        <label class="lead" for="form3Example4"><strong>Password</strong></label>
        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

        {{-- <input type="password" id="form3Example4" class="form-control form-control-lg"
          placeholder="Enter password" /> --}}

      </div>

      <div class="text-center text-lg-start mt-4 pt-2">
        <button type="submit" class="btn btn-primary btn-lg"
          style="padding-left: 2.5rem; padding-right: 2.5rem;">Login</button>
      </div>
    </form>

  </div>
</div>

</div>
</div>
</div>


         <!-- END EDMO HTML (Happy Coding!)-->
      </main>
      <footer>
        <p class="text-center">© Smart Omega (PVT) Ltd . All Rights Reserved. Designed by Smart Omega</p>
    </footer>

      <!-- Script JS -->
      <script src="./js/script.js"></script>
      <!--$%analytics%$-->
   </body>
</html>