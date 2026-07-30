@include('layout.header')

<div class="container col-9" style="margin:2% auto; margin-left:18%">

    <div class="d-flex justify-content-between" style="margin-bottom: 10%">
        <h2>Milk Shop Earnings</h2>
        <h2>Total Earnings: {{ number_format($totalEarnings) }} PKR</h2>
    </div>

    <h3>Milk Entries:</h3>

    @if (session('success'))
        <div>{{ session('success') }}</div>
    @endif
    <table class="table table-bordered table-striped" id="myTable">
        <thead>
            <tr>
                <td>Id</td>
                <td>Name</td>
                <td>Cashier</td>
                <td>Product</td>
                <td>Price</td>
                <td>Action</td>
            </tr>
        </thead>
        <tbody>
            @foreach ($milks as $milk)
                <tr>
                    <td>{{ $milk->id }}</td>
                    <td>{{ $milk->name }}</td>
                    <td>{{ $milk->dealer_name }}</td>
                    <td>{{ $milk->product_name }}</td>
                    <td>{{ number_format($milk->price) }} PKR</td>
                    <td width="14%">
                        <a href="{{ route('milk.edit', $milk->id) }}" class="btn"
                            style="background:#be7777;color:#fff"><i class="fa-solid fa-pen"></i></a>
                        <form method="post" action="{{ route('milk.delete', $milk->id) }}" style="display: inline;">
                            @csrf
                            @method('delete')
                            <button type="submit" class="btn" style="background:#49456e;color:#fff"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pagination" id="pagination"></div>
</div>

@include('layout.footer')
