@extends('layouts.app')

@section('title', 'Konfirmasi Password - e-License')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><i class="fas fa-shield-alt"></i> Konfirmasi Password</h4>
            </div>
            
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle"></i>
                    Untuk melanjutkan, silakan konfirmasi password Anda.
                </div>
                
                <form method="POST" action="{{ route('password.confirm') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock"></i> Password
                        </label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required autofocus>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Konfirmasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
