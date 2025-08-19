<?php

use Phiki\Phast\ClassList;
use Phiki\Phast\Element;
use Phiki\Phast\Text;

it('can create an element with a tag name', function () {
    $el = new Element('p');

    expect($el->tagName)->toBe('p');
    expect($el->__toString())->toBe('<p></p>');
});

it('can create an element with properties', function () {
    $el = new Element('p');
    $el->properties->set('class', new ClassList(['foo', 'bar']));

    expect($el->properties->get('class')->contains('foo'))->toBeTrue();
    expect($el->properties->get('class')->contains('baz'))->toBeFalse();
    expect($el->__toString())->toBe('<p class="foo bar"></p>');
});

it('can create an element with children', function () {
    $el = new Element('div');
    $el->children[] = new Element('p', children: [new Text('Hello, world!')]);

    expect($el->__toString())->toBe('<div><p>Hello, world!</p></div>');
});
