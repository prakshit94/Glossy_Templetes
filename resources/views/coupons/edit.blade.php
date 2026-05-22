<x-layouts.app pageTitle="Edit Coupon">
    @include('coupons.form', [
        'coupon' => $coupon,
        'formAction' => route('coupons.update', $coupon->id),
        'formMethod' => 'PUT',
        'pageTitle' => 'Edit Coupon',
        'pageSubtitle' => 'Adjust this promo code without changing the surrounding marketing flow.',
        'pageIcon' => 'edit-3',
        'submitLabel' => 'Save Changes',
    ])
</x-layouts.app>
