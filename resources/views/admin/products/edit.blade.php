@extends('layouts.admin')

@section('title', 'Edit Product - e-License')
@section('page-title', 'Edit Product')

@section('breadcrumbs')
<li class="breadcrumb-item">
    <a href="{{ route('admin.products.index') }}">Products</a>
</li>
<li class="breadcrumb-item active">Edit</li>
@endsection

@section('page-actions')
<a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
    <i class="fas fa-arrow-left"></i> Back
</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Product: {{ $product->name }}</h5>
            </div>
            
            <div class="card-body">
                <form method="POST" action="{{ route('admin.products.update', $product) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-box"></i> Product Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left"></i> Description
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="price" class="form-label">
                                <i class="fas fa-tag"></i> Price (Rp) <span class="text-danger">*</span>
                            </label>
                            <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                   id="price" name="price" value="{{ old('price', $product->price) }}" min="0" required>
                            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="category" class="form-label">
                                <i class="fas fa-folder"></i> Category <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('category') is-invalid @enderror" 
                                    id="category" name="category" required>
                                <option value="">-- Select Category --</option>
                                <option value="windows" {{ old('category', $product->category) == 'windows' ? 'selected' : '' }}>Windows</option>
                                <option value="office" {{ old('category', $product->category) == 'office' ? 'selected' : '' }}>Microsoft Office</option>
                                <option value="server" {{ old('category', $product->category) == 'server' ? 'selected' : '' }}>Server</option>
                                <option value="antivirus" {{ old('category', $product->category) == 'antivirus' ? 'selected' : '' }}>Antivirus</option>
                                <option value="other" {{ old('category', $product->category) == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('category')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="stock_type" class="form-label">
                                <i class="fas fa-database"></i> Stock Type <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('stock_type') is-invalid @enderror" 
                                    id="stock_type" name="stock_type" required onchange="toggleStockField()">
                                <option value="">-- Select Type --</option>
                                <option value="1" {{ old('stock_type', $product->stock_type) == '1' ? 'selected' : '' }}>Unlimited</option>
                                <option value="2" {{ old('stock_type', $product->stock_type) == '2' ? 'selected' : '' }}>Limited Quantity</option>
                            </select>
                            @error('stock_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6">
                            <label for="available_stock" class="form-label">
                                <i class="fas fa-boxes"></i> Available Stock
                            </label>
                            <input type="number" class="form-control @error('available_stock') is-invalid @enderror" 
                                   id="available_stock" name="available_stock" 
                                   value="{{ old('available_stock', $product->available_stock) }}" 
                                   min="0" {{ $product->stock_type == 2 ? '' : 'disabled' }}>
                            @error('available_stock')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="features" class="form-label">
                            <i class="fas fa-star"></i> Features (comma separated)
                        </label>
                        <input type="text" class="form-control @error('features') is-invalid @enderror" 
                               id="features" name="features" 
                               value="{{ old('features', $product->features ? implode(', ', $product->features) : '') }}"
                               placeholder="Retail, Permanent, Digital Delivery, etc.">
                        @error('features')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                               value="1" {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Product is active (visible in store)
                        </label>
                    </div>
                    
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle"></i>
                        <strong>Product Statistics:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Total Orders: {{ $product->orders_count ?? 0 }}</li>
                            <li>License Pool: {{ $product->license_pools_count ?? 0 }} keys</li>
                            <li>Created: {{ $product->created_at->format('d M Y') }}</li>
                            <li>Last Updated: {{ $product->updated_at->format('d M Y H:i') }}</li>
                        </ul>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save"></i> Update Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleStockField() {
    const stockType = document.getElementById('stock_type').value;
    const stockField = document.getElementById('available_stock');
    
    if (stockType === '2') {
        stockField.disabled = false;
        stockField.required = true;
    } else {
        stockField.disabled = true;
        stockField.required = false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleStockField();
});
</script>
@endsection
