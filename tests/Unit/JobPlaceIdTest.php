<?php

use App\Models\Post;

// Golden values computed from the original inline implementation. If these change,
// every job already in production gets a new place_id and dedup breaks.
it('keeps the canonical job place_id stable', function (array $args, string $expected) {
    expect(Post::jobPlaceId(...$args))->toBe($expected);
})->with([
    [['PT. Maju Jaya', 'Staff Admin', 'Kota Bandung'], 'job_b83c1508aee85beae14bceb8'],
    [['  CV Sinar Abadi', 'Barista', 'Denpasar'], 'job_e61208a94f0f656503a6830a'],
    [['Yayasan Harapan', 'Guru SD', 'Kab. Bogor'], 'job_f1c141d1e39aa8395a17a5d8'],
]);

it('treats the same job from JobStreet and Glints as one', function () {
    expect(Post::jobPlaceId('PT. Maju Jaya', 'Staff Admin', 'Kota Bandung'))
        ->toBe(Post::jobPlaceId('Maju Jaya', 'staff admin', 'kota bandung'));
});
