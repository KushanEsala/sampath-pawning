@extends('layouts.app')

@section('content')


<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<link href="{{asset('assets/css/login.css')}}" rel="stylesheet">

<div class="container">

    <section class="vh-10">
        <div class="container-fluid h-custom">
          <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-md-9 col-lg-6 col-xl-5">
              <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-login-form/draw2.webp"
                class="img-fluid" alt="Sample image">
            </div>
            <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">

              <form action="{{ route('register') }}" method="post">
                @csrf
                <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start">
                  <p class="lead fw-normal mb-0 me-3">Register </p><br><br>
                </div>



                <!-- User Name input -->
                <div class="form-outline mb-4">
                    <input id="user_name" type="text" class="form-control @error('user_name') is-invalid @enderror" name="user_name" value="{{ old('user_name') }}" required >
                  <label class="form-label" >User Name</label>
                </div>



                <!-- Name input -->
                <div class="form-outline mb-4">
                    <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required >

                  {{-- <input type="email" id="form3Example3" class="form-control form-control-lg"
                    placeholder="Enter a valid email address" /> --}}
                  <label class="form-label" for="form3Example3">Name</label>
                </div>



                <!-- Email input -->
                <div class="form-outline mb-4">
                    <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>

                  {{-- <input type="email" id="form3Example3" class="form-control form-control-lg"
                    placeholder="Enter a valid email address" /> --}}
                  <label class="form-label" for="form3Example3">Email address</label>
                </div>

                <!-- Password input -->
                <div class="form-outline mb-3">
                  <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                  {{-- <input type="password" id="form3Example4" class="form-control form-control-lg"
                    placeholder="Enter password" /> --}}
                  <label class="form-label" for="form3Example4">Password</label>
                </div>

                <!-- Confirm Password input -->
                <div class="form-outline mb-3">
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password_confirmation" required >

                    {{-- <input type="password" id="form3Example4" class="form-control form-control-lg"
                      placeholder="Enter password" /> --}}
                    <label class="form-label" for="form3Example4">Confirm Password</label>
                  </div>



                <div class="text-center text-lg-start mt-4 pt-2">
                  <button type="submit" class="btn btn-primary btn-lg"
                    style="padding-left: 2.5rem; padding-right: 2.5rem;">Register</button>

                </div>

              </form>
            </div>
          </div>
        </div>

      </section>

</div>
@endsection
