<div class="profile-header">
    <div class="profile-icon">
        <i class="fas fa-user"></i>
    </div>
    <div>
        <h3 class="mb-1">{{ $editUser->name }}</h3>
        <p class="text-muted mb-0">{{ $editUser->role }} - {{ $editUser->Branch }}</p>
    </div>
</div>

<form action="{{ route('profile.update') }}" method="POST">
    @csrf

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label">Username</label>
                <input type="text" class="form-control readonly-field" value="{{ $editUser->username }}" readonly>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $editUser->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $editUser->email) }}" >
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label">Role</label>
                <input type="text" class="form-control readonly-field" value="{{ $editUser->role }}" readonly>
                <small class="text-muted">Only admins can change roles</small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label">Branch</label>
                <input type="text" class="form-control readonly-field" value="{{ $editUser->Branch }}" readonly>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label">BC</label>
                <input type="text" class="form-control readonly-field" value="{{ $editUser->BC }}" readonly>
            </div>
        </div>
    </div>

    <hr class="my-4">
    <h5 class="mb-3">Change Password (Optional)</h5>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label">New Password</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                <small class="text-muted">Minimum 6 characters</small>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                <label class="form-label">Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
        </div>
    </div>

    <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary-custom">
            <i class="fas fa-save me-2"></i>Update Profile
        </button>
    </div>
</form>