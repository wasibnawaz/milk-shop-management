@include('layout.header')

<div class="container col-5 milk" style="margin:5% auto">
    <h1>Add Milk</h1>

    <form action="" method="post">
        @csrf
        <label for="name">Name:</label>
        <input type="text" name="name" class="form-control auto-next" required>

        <label for="product_name">Product Name:</label>
        <input type="text" id="myInput" class="form-control auto-next" name="product_name" required>

        <label for="dealer_name">Cashier Name:</label>
        <input type="text" class="form-control auto-next" name="dealer_name" required>

        <label for="price">Price:</label>
        <input type="number" name="price" class="form-control auto-next" step="0.01" required>

        <button type="submit" class="mt-3 btn" style="background:#518db1;color:#ffffff">Add Milk</button>
    </form>
</div>


@include('layout.footer')
