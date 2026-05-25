<x-layouts.app pageTitle="Edit Coupon">

    <div class="min-h-screen bg-gradient-to-br from-background via-background to-muted/20">

        @include('coupons.form', [

            'coupon' => $coupon,

            'formAction' => route('coupons.update', $coupon->id),

            'formMethod' => 'PUT',

            'pageTitle' => 'Edit Coupon',

            'pageSubtitle' => 'Adjust this promo code without changing the surrounding marketing flow.',

            'pageIcon' => 'edit-3',

            'submitLabel' => 'Save Changes',

        ])

    </div>

</x-layouts.app>