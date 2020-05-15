@extends('card.layout')

@section('content')
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left">
                <h2>Add New Card</h2>
            </div>
            <div class="pull-right">
{{--                <a class="btn btn-primary" href="{{ route('card.index') }}"> Back</a>--}}
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form id="pay-form" action="" method="POST">
        <div id="formCardStripe">

            <div class="form-group">
                <label for="formCardStripeElement">
                    Carte de crédit / débit
                </label>
                <div id="formCardStripeElement" class="form-control formStripe" required></div>
                <div id="formCardStripeErrors" class="help-block" role="alert"></div>
            </div>
            <input type="hidden" id="msg" name="msg" value=""/>
            <input type="hidden" id="status" name="status" value=""/>
            <input type="hidden" id="amountType" name="amoutType" value=""/>
            <input type="hidden" id="descriptionPayement" name="descriptionPayement" value=""/>
            <input type="hidden" name="tokenStripe" id="formCardStripeToken" value=""/>
            <input type="hidden" name="StripeAPIkey" id="StripeAPIkey" value="pk_test_oeS9emnzVKWaIR9jckSkGRj0">
        </div>


        <div class="row justify-content-between">



            <a href="#"  >
                <div class="form-control actionButton btn-primary">
                    Valider</div>
            </a>
        </div>
    </form>


{{--    <form action="{{ route('card.store') }}" method="POST">--}}
{{--        @csrf--}}

{{--        <div class="row">--}}
{{--            <div class="col-xs-12 col-sm-12 col-md-12">--}}
{{--                <div class="form-group">--}}
{{--                    <strong>Name:</strong>--}}
{{--                    <input type="text" name="name" class="form-control" placeholder="Name">--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-xs-12 col-sm-12 col-md-12">--}}
{{--                <div class="form-group">--}}
{{--                    <strong>Detail:</strong>--}}
{{--                    <textarea class="form-control" style="height:150px" name="detail" placeholder="Detail"></textarea>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-xs-12 col-sm-12 col-md-12 text-center">--}}
{{--                <button type="submit" class="btn btn-primary">Submit</button>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--    </form>--}}

    <script src="https://js.stripe.com/v3/"></script>
    <script>

        window.formCardStripe = document.getElementById('formCardStripe');
        window.formCardStripeErrors = document.getElementById('formCardStripeErrors');
        window.StripeAPI = Stripe(document.getElementById('StripeAPIkey').value);
        window.StripeElements = StripeAPI.elements();
        window.StripeCard = StripeElements.create('card');
        StripeCard.mount('#formCardStripeElement');
        verifStripe = false;
        StripeCard.addEventListener('change', function (event) {
            if (event.error) {
                formCardStripeErrors.textContent = event.error.message;
                formCardStripe.classList.add('has-error');
                verifStripe = false;
            } else {
                formCardStripeErrors.textContent = '';
                formCardStripe.classList.remove('has-error');
                verifStripe = true;
            }
        });
@endsection
