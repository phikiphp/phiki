<?php

it('can highlight a single line', function () {
    $output = markdown(<<<'MD'
    ```php {1}
    class A {}
    ```
    MD);

    expect($output)->toContain('<span class="line highlight">');
});

it('can highlight a range of lines', function () {
    $output = markdown(<<<'MD'
    ```php {1-2}
    class A {}
    function b() {}
    ```
    MD);

    expect(substr_count($output, '<span class="line highlight">'))->toBe(2);
});

it('can highlight multiple lines', function () {
    $output = markdown(<<<'MD'
    ```php {1,3}
    class A {}
    function b() {}
    $a = new A();
    ```
    MD);

    expect(substr_count($output, '<span class="line highlight">'))->toBe(2);
});

it('can highlight multiple ranges of lines', function () {
    $output = markdown(<<<'MD'
    ```php {1-2,4}
    class A {}
    function b() {}
    $a = new A();
    function c() {}
    ```
    MD);

    expect(substr_count($output, '<span class="line highlight">'))->toBe(3);
});

it('can focus a single line', function () {
    $output = markdown(<<<'MD'
    ```php {}{2}
    class A {}
    function b() {}
    ```
    MD);

    expect($output)->toContain('<span class="line focus">');
});

it('can focus a range of lines', function () {
    $output = markdown(<<<'MD'
    ```php {}{1-2}
    class A {}
    function b() {}
    ```
    MD);

    expect(substr_count($output, '<span class="line focus">'))->toBe(2);
});

it('can focus multiple lines', function () {
    $output = markdown(<<<'MD'
    ```php {}{1,3}
    class A {}
    function b() {}
    $a = new A();
    ```
    MD);

    expect(substr_count($output, '<span class="line focus">'))->toBe(2);
});

it('can focus multiple ranges of lines', function () {
    $output = markdown(<<<'MD'
    ```php {}{1-2,4}
    class A {}
    function b() {}
    $a = new A();
    function c() {}
    ```
    MD);

    expect(substr_count($output, '<span class="line focus">'))->toBe(3);
});

it('can highlight and focus lines', function () {
    $output = markdown(<<<'MD'
    ```php {1,3}{2}
    class A {}
    function b() {}
    $a = new A();
    ```
    MD);

    expect(substr_count($output, '<span class="line highlight">'))->toBe(2);
    expect(substr_count($output, '<span class="line focus">'))->toBe(1);
});

it('ignores invalid line numbers', function () {
    $output = markdown(<<<'MD'
    ```php {0,4,5}{0,4,5}
    class A {}
    function b() {}
    ```
    MD);

    expect(substr_count($output, '<span class="line highlight">'))->toBe(0);
    expect(substr_count($output, '<span class="line focus">'))->toBe(0);
});

it('ignores invalid ranges', function () {
    $output = markdown(<<<'MD'
    ```php {2-1,3-2}{2-1,3-2}
    class A {}
    function b() {}
    ```
    MD);

    expect(substr_count($output, '<span class="line highlight">'))->toBe(0);
    expect(substr_count($output, '<span class="line focus">'))->toBe(0);
});

it('can highlight and focus the same line', function () {
    $output = markdown(<<<'MD'
    ```php {2}{2}
    class A {}
    function b() {}
    ```
    MD);

    expect(substr_count($output, '<span class="line highlight focus">'))->toBe(1);
});

it('ignores non-numeric input', function () {
    $output = markdown(<<<'MD'
    ```php {a,b}{c,d}
    class A {}
    function b() {}
    ```
    MD);

    expect(substr_count($output, '<span class="line highlight">'))->toBe(0);
    expect(substr_count($output, '<span class="line focus">'))->toBe(0);
});

it('ignores empty input', function () {
    $output = markdown(<<<'MD'
    ```php {}{}
    class A {}
    function b() {}
    ```
    MD);

    expect(substr_count($output, '<span class="line highlight">'))->toBe(0);
    expect(substr_count($output, '<span class="line focus">'))->toBe(0);
});
