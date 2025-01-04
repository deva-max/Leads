@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="p-4">Create Lead</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('leads.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input 
                type="text" 
                class="form-control @error('name') is-invalid @enderror" 
                id="name" 
                name="name" 
                value="" 
                required
            >
            @error('name')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input 
                type="email" 
                class="form-control @error('email') is-invalid @enderror" 
                id="email" 
                name="email" 
                value="" 
                required
            >
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input 
                type="text" 
                class="form-control @error('phone') is-invalid @enderror" 
                id="phone" 
                name="phone" 
                value="" 
                required
            >
            @error('phone')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select 
                class="form-control @error('status') is-invalid @enderror" 
                id="status" 
                name="status" 
                required
            >
            <option value="">Select Status</option>
            <option value="New" >New</option>
            <option value="In Progress" >In Progress</option>
            <option value="Closed" >Closed</option>
            </select>
            @error('status')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Create Lead</button>
        <a href="{{ route('leads.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
