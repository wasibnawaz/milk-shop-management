@include('layout.header')

<div class="container col-5 milk" style="margin:5% auto">
    <h1>Edit Milk</h1>

    <form action="{{ route('milk.update', $milk->id) }}" method="post">
        @csrf
        @method('put')

        <label for="name">Name:</label>
        <input type="text" name="name" class="form-control" value="{{ $milk->name }}" required>

        <label for="product_name">Product Name:</label>
        <input type="text" id="myInput" name="product_name" class="form-control" value="{{ $milk->product_name }}" required>
        
        <label for="dealer_name">Cashier Name:</label>
        <input type="text" name="dealer_name" class="form-control" value="{{ $milk->dealer_name }}" required>

        <label for="price">Price:</label>
        <input type="number" name="price" class="form-control" value="{{ $milk->price }}" step="0.01" required>

        <button type="submit" class="mt-3 btn" style="background:#518db1;color:#ffffff">Update Milk</button>
    </form>
</div>

@include('layout.footer')
