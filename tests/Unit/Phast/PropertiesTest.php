<?php

use Phiki\Phast\Properties;

it('can create a property list with properties', function () {
    $properties = new Properties([
        'key1' => 'value1',
    ]);

    expect($properties->get('key1'))->toBe('value1');
});

it('can set a property', function () {
    $properties = new Properties();
    $properties->set('key1', 'value1');

    expect($properties->get('key1'))->toBe('value1');
});

it('can get a property', function () {
    $properties = new Properties(['key1' => 'value1']);

    expect($properties->get('key1'))->toBe('value1');
});

it('returns null for a non-existing property', function () {
    $properties = new Properties();

    expect($properties->get('non_existing_key'))->toBeNull();
});

it('can check if a property exists', function () {
    $properties = new Properties(['key1' => 'value1']);

    expect($properties->has('key1'))->toBeTrue();
    expect($properties->has('non_existing_key'))->toBeFalse();
});

it('can remove a property', function () {
    $properties = new Properties(['key1' => 'value1']);
    $properties->remove('key1');

    expect($properties->has('key1'))->toBeFalse();
});

it('can convert properties to a string', function () {
    $properties = new Properties([
        'key1' => 'value1',
        'key2' => 'value2',
    ]);

    expect((string) $properties)->toBe('key1="value1" key2="value2"');
});
