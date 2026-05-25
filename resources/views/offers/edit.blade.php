<x-layouts.app pageTitle="Edit Offer">

    <div class="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">

        @include('offers.form', [

            'offer' => $offer

        ])

    </div>

</x-layouts.app>