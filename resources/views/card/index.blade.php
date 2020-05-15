@extends('card.layout')

@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Stripe</h2>
            </div>
            <div class="pull-right">
                <a class="btn btn-success" href="{{ route('cards.create') }}"> Create New Product</a>
            </div>
        </div>
    </div>

    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif

    <table class="table table-bordered">
        <tr>
            <th>No</th>
            <th>Name</th>
            <th>Details</th>
            <th width="280px">Action</th>
        </tr>
        @foreach ($cards as $card)
            <tr>
                <td>{{ ++$i }}</td>
                <td>{{ $card->card_last_four }}</td>
                <td>{{ $card->card_brand }}</td>
{{--                <td>--}}
{{--                    <form action="{{ route('products.destroy',$product->id) }}" method="POST">--}}

{{--                        <a class="btn btn-info" href="{{ route('products.show',$product->id) }}">Show</a>--}}

{{--                        <a class="btn btn-primary" href="{{ route('products.edit',$product->id) }}">Edit</a>--}}

{{--                        @csrf--}}
{{--                        @method('DELETE')--}}

{{--                        <button type="submit" class="btn btn-danger">Delete</button>--}}
{{--                    </form>--}}
{{--                </td>--}}
            </tr>
        @endforeach
    </table>
@endsection
