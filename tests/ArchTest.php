<?php

it('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

it('it will not use env()')
    ->expect('env')
    ->not->toBeUsed();
