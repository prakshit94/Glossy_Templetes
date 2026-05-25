<x-layouts.app pageTitle="Create Coupon">

    <div class="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">

        @include('coupons.form', [

            'coupon' => null,

            'formAction' => route('coupons.store'),

            'formMethod' => 'POST',

            'pageTitle' => 'Create Coupon',

            'pageSubtitle' => 'Add a new promo code with clear limits, expiry windows, and discount rules.',

            'pageIcon' => 'gift',

            'submitLabel' => 'Create Coupon',

        ])

    </div>

</x-layouts.app>