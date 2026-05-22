<x-layouts.app pageTitle="Create Coupon">
    @include('coupons.form', [
        'coupon' => null,
        'formAction' => route('coupons.store'),
        'formMethod' => 'POST',
        'pageTitle' => 'Create Coupon',
        'pageSubtitle' => 'Add a new promo code with clear limits, expiry windows, and discount rules.',
        'pageIcon' => 'gift',
        'submitLabel' => 'Create Coupon',
    ])
</x-layouts.app>
